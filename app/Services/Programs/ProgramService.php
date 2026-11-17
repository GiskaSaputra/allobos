<?php

namespace App\Services\Programs;

use App\Enums\CertificateStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\ProgramStatus;
use App\Enums\RoleName;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\ProgramSession;
use App\Models\User;
use App\Notifications\Pemberitahuan;
use App\Services\Readiness\ReadinessCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ProgramService
{
    public const PASS_RATIO = 0.75;

    public function __construct(private readonly ReadinessCalculator $readiness) {}

    /** Status Dibuka, Penuh, dan Berjalan mengikuti kuota dan jadwal (Alur Bisnis 4.2). */
    public function syncStatus(Program $program): Program
    {
        if (in_array($program->status, [ProgramStatus::Draf, ProgramStatus::Selesai], true)) {
            return $program;
        }

        $program->loadMissing('sessions');
        $next = match (true) {
            $program->hasStarted() => ProgramStatus::Berjalan,
            $program->seatsLeft() === 0 => ProgramStatus::Penuh,
            default => ProgramStatus::Dibuka,
        };

        if ($next !== $program->status) {
            $program->update(['status' => $next]);
        }

        return $program;
    }

    public function publish(Program $program): void
    {
        if ($program->status !== ProgramStatus::Draf) {
            throw new RuntimeException('Kelas ini sudah terbit.');
        }
        if ($program->sessions()->doesntExist()) {
            throw new RuntimeException('Tambahkan minimal satu jadwal sesi sebelum menerbitkan kelas.');
        }

        $program->update(['status' => ProgramStatus::Dibuka]);
        $this->syncStatus($program->fresh());
        AuditLog::record('kelas.terbitkan', 'program:'.$program->id);
    }

    public function enroll(User $user, Program $program): Enrollment
    {
        $enrollment = DB::transaction(function () use ($user, $program) {
            $program = Program::query()->lockForUpdate()->findOrFail($program->id);
            $this->syncStatus($program);

            $existing = Enrollment::query()->where('program_id', $program->id)->where('user_id', $user->id)->first();
            // Pendaftar kelas berbayar yang belum membayar diarahkan ke pembayaran yang sama, bukan mendaftar ulang.
            if ($existing?->status === EnrollmentStatus::MenungguBayar) {
                return $existing;
            }
            if ($existing && $existing->status !== EnrollmentStatus::Batal) {
                throw new RuntimeException('Kamu sudah terdaftar di kelas ini.');
            }
            if ($program->status === ProgramStatus::Penuh || $program->seatsLeft() === 0) {
                throw new RuntimeException('Kuota kelas sudah penuh. Pilih kelas lain atau tunggu angkatan berikutnya.');
            }
            if ($program->status !== ProgramStatus::Dibuka) {
                throw new RuntimeException('Pendaftaran kelas ini sudah ditutup.');
            }

            $enrollment = $existing ?? new Enrollment(['program_id' => $program->id, 'user_id' => $user->id]);
            // Kelas berbayar menahan kursi dulu. Status menjadi Terdaftar setelah pembayaran lunas.
            $enrollment->fill(['status' => $program->isPaid() ? EnrollmentStatus::MenungguBayar : EnrollmentStatus::Terdaftar])->save();
            $this->syncStatus($program);

            return $enrollment;
        });

        if ($enrollment->status === EnrollmentStatus::Terdaftar) {
            $this->confirmed($enrollment);
        }

        return $enrollment;
    }

    /** Pemberitahuan dan Jejak Usaha saat pendaftaran sah (gratis langsung, berbayar setelah lunas). */
    public function confirmed(Enrollment $enrollment): void
    {
        $program = $enrollment->program;
        $enrollment->user->notify(new Pemberitahuan('Pendaftaran kelas berhasil', 'Kamu terdaftar di '.$program->title.'. Pengingat dikirim sehari sebelum sesi.', route('warga.programs.index')));
        $this->readiness->record($enrollment->user, 'kelas', 'Mendaftar kelas: '.$program->title, route('programs.show', $program));
    }

    /** Melepas kursi pendaftar yang pembayarannya gagal atau kedaluwarsa. */
    public function release(Enrollment $enrollment): void
    {
        if ($enrollment->status !== EnrollmentStatus::MenungguBayar) {
            return;
        }
        $enrollment->update(['status' => EnrollmentStatus::Batal]);
        $this->syncStatus($enrollment->program->fresh());
    }

    public function cancel(User $user, Program $program): void
    {
        $enrollment = Enrollment::query()->where('program_id', $program->id)->where('user_id', $user->id)
            ->whereIn('status', [EnrollmentStatus::Terdaftar->value, EnrollmentStatus::MenungguBayar->value])->first()
            ?? throw new RuntimeException('Kamu tidak terdaftar di kelas ini.');

        if ($program->load('sessions')->hasStarted()) {
            throw new RuntimeException('Kelas sudah dimulai, pendaftaran tidak bisa dibatalkan.');
        }

        $enrollment->update(['status' => EnrollmentStatus::Batal]);
        $this->syncStatus($program->fresh());
    }

    public function openCode(ProgramSession $session): string
    {
        $code = Str::upper(Str::random(6));
        $code = strtr($code, ['0' => '7', 'O' => 'K', '1' => '4', 'I' => 'M', 'L' => 'X']);
        $session->update(['attendance_code' => $code]);

        return $code;
    }

    public function attend(User $user, ProgramSession $session, string $code): void
    {
        $enrolled = Enrollment::query()->where('program_id', $session->program_id)->where('user_id', $user->id)
            ->whereIn('status', EnrollmentStatus::participating())->exists();

        if (! $enrolled) {
            throw new RuntimeException('Kamu belum terdaftar di kelas ini. Daftar kelas dulu sebelum mencatat hadir.');
        }
        if (! $session->isRunning()) {
            throw new RuntimeException('Kode kehadiran hanya berlaku saat sesi berlangsung, '.$session->starts_at->translatedFormat('d F H:i').' sampai '.$session->ends_at->format('H:i').' WIB.');
        }
        if (! $session->attendance_code) {
            throw new RuntimeException('Kode kehadiran belum dibuka pengelola. Tunggu sampai kode ditampilkan di ruang kelas.');
        }
        if (! hash_equals($session->attendance_code, Str::upper(trim($code)))) {
            throw new RuntimeException('Kode kehadiran salah. Periksa lagi kode yang ditampilkan di ruang kelas.');
        }

        $attendance = Attendance::query()->firstOrCreate(['program_session_id' => $session->id, 'user_id' => $user->id]);
        if ($attendance->wasRecentlyCreated) {
            $this->readiness->record($user, 'kelas', 'Hadir di '.$session->program->title.': '.$session->title, route('warga.programs.index'));
        }
    }

    public function openMaterial(User $user, Program $program, int $index): void
    {
        $enrollment = Enrollment::query()->where('program_id', $program->id)->where('user_id', $user->id)->first();
        if ($enrollment) {
            $opened = collect($enrollment->materials_opened ?? [])->push($index)->unique()->values()->all();
            $enrollment->update(['materials_opened' => $opened]);
        }
    }

    /** @return array{attended:int,total:int,materials:int,materials_total:int,percent:int,passed:bool} */
    public function progress(User $user, Program $program): array
    {
        $total = $program->sessions->count();
        $attended = Attendance::query()->where('user_id', $user->id)->whereIn('program_session_id', $program->sessions->pluck('id'))->count();
        $materialsTotal = count($program->materials ?? []);
        $materials = count(Enrollment::query()->where('program_id', $program->id)->where('user_id', $user->id)->first()?->materials_opened ?? []);
        $units = $total + $materialsTotal;

        return [
            'attended' => $attended,
            'total' => $total,
            'materials' => min($materials, $materialsTotal),
            'materials_total' => $materialsTotal,
            'percent' => $units ? (int) round(($attended + min($materials, $materialsTotal)) / $units * 100) : 0,
            'passed' => $total > 0 && $attended / $total >= self::PASS_RATIO,
        ];
    }

    /** @param list<array{user_id:int,title:string}> $signers */
    public function setSigners(Program $program, array $signers): void
    {
        if ($program->signers()->whereNotNull('signed_at')->exists()) {
            throw new RuntimeException('Penanda tangan tidak bisa diganti karena sertifikat sudah mulai ditandatangani.');
        }

        foreach ($signers as $signer) {
            $user = User::query()->findOrFail($signer['user_id']);
            $speakerBoss = $user->hasRole(RoleName::Bos->value) && $program->speakers()->where('user_id', $user->id)->exists();
            $leader = $user->hasAnyRole([RoleName::Pengelola->value, RoleName::Admin->value]);
            if (! $speakerBoss && ! $leader) {
                throw new RuntimeException($user->name.' bukan pembicara kelas ini atau pimpinan IPI, jadi tidak bisa menjadi penanda tangan.');
            }
        }

        DB::transaction(function () use ($program, $signers) {
            $program->signers()->delete();
            foreach (array_values($signers) as $i => $signer) {
                $program->signers()->create(['user_id' => $signer['user_id'], 'title' => $signer['title'], 'sort' => $i + 1]);
            }
        });
        AuditLog::record('kelas.penanda_tangan', 'program:'.$program->id, ['penanda_tangan' => array_column($signers, 'user_id')]);
    }

    /** Menutup kelas lalu membuat draf sertifikat untuk peserta yang hadir minimal 75 persen (KB-09, AB-07, AB-17). */
    public function close(Program $program): int
    {
        $program->load(['sessions', 'signers.user']);

        if ($program->status === ProgramStatus::Selesai) {
            throw new RuntimeException('Kelas ini sudah ditutup.');
        }
        if ($program->signers->isEmpty()) {
            throw new RuntimeException('Tetapkan minimal satu penanda tangan sertifikat sebelum menutup kelas.');
        }
        if (! $program->endsAt()?->isPast()) {
            throw new RuntimeException('Kelas baru bisa ditutup setelah sesi terakhir selesai.');
        }

        $count = DB::transaction(function () use ($program) {
            $program->update(['status' => ProgramStatus::Selesai, 'closed_at' => now()]);
            $count = 0;

            foreach ($program->enrollments()->with('user')->whereIn('status', EnrollmentStatus::participating())->get() as $enrollment) {
                if (! $this->progress($enrollment->user, $program)['passed']) {
                    continue;
                }
                $enrollment->update(['status' => EnrollmentStatus::LulusHadir]);
                Certificate::query()->firstOrCreate(
                    ['program_id' => $program->id, 'user_id' => $enrollment->user_id],
                    ['code' => Certificate::generateCode(), 'status' => CertificateStatus::MenungguTandaTangan, 'recipient_name' => $enrollment->user->name, 'is_demo' => $program->is_demo],
                );
                $count++;
            }

            return $count;
        });

        AuditLog::record('kelas.tutup', 'program:'.$program->id, ['sertifikat' => $count]);

        if ($count > 0) {
            foreach ($program->signers as $signer) {
                $signer->user->notify(new Pemberitahuan(
                    'Sertifikat menunggu tanda tangan',
                    "{$count} sertifikat kelas {$program->title} menunggu tanda tanganmu.",
                    route('signing.show', $program),
                    mail: true,
                ));
            }
        }

        return $count;
    }
}
