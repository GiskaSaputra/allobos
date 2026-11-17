<?php

namespace App\Services\Certificates;

use App\Enums\CertificateStatus;
use App\Jobs\GenerateCertificates;
use App\Models\AuditLog;
use App\Models\Certificate;
use App\Models\Program;
use App\Models\ProgramSigner;
use App\Models\SignatureSpecimen;
use App\Models\User;
use App\Notifications\Pemberitahuan;
use App\Services\Chain\ChainClient;
use App\Services\Chain\ChainException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SigningService
{
    public function __construct(private readonly CertificateHasher $hasher) {}

    public function signerFor(Program $program, User $user): ?ProgramSigner
    {
        return $program->signers()->where('user_id', $user->id)->first();
    }

    /** Menandatangani seluruh sertifikat kelas. Tidak dapat diwakilkan (AB-18). */
    public function sign(Program $program, User $user, string $ip): void
    {
        $signer = $this->signerFor($program, $user) ?? throw new RuntimeException('Kamu bukan penanda tangan kelas ini.');

        if ($signer->signed_at) {
            throw new RuntimeException('Kamu sudah menandatangani sertifikat kelas ini.');
        }
        if (SignatureSpecimen::query()->where('user_id', $user->id)->doesntExist()) {
            throw new RuntimeException('Simpan spesimen tanda tanganmu dulu.');
        }
        $pending = $program->certificates()->where('status', CertificateStatus::MenungguTandaTangan->value)->count();
        if ($pending === 0) {
            throw new RuntimeException('Tidak ada sertifikat yang menunggu tanda tangan di kelas ini.');
        }

        $complete = DB::transaction(function () use ($signer, $program, $ip) {
            $signer->update(['signed_at' => now(), 'signed_ip' => $ip]);

            if ($program->signers()->whereNull('signed_at')->exists()) {
                return false;
            }
            $program->certificates()->where('status', CertificateStatus::MenungguTandaTangan->value)
                ->update(['status' => CertificateStatus::Ditandatangani->value, 'issued_on' => now()->toDateString()]);

            return true;
        });

        AuditLog::record('sertifikat.tanda_tangan', 'program:'.$program->id, ['jumlah' => $pending, 'lengkap' => $complete]);

        if ($complete) {
            GenerateCertificates::dispatch($program);
        }
    }

    public function exclude(Program $program, Certificate $certificate, User $user, string $reason): void
    {
        if (! $this->signerFor($program, $user) || $certificate->program_id !== $program->id) {
            throw new RuntimeException('Kamu tidak berhak mengubah daftar penerima kelas ini.');
        }
        if ($certificate->status !== CertificateStatus::MenungguTandaTangan) {
            throw new RuntimeException('Nama hanya bisa dikeluarkan sebelum sertifikat ditandatangani.');
        }

        $certificate->update(['status' => CertificateStatus::Dibatalkan, 'reason' => $reason]);
        AuditLog::record('sertifikat.keluarkan', 'certificate:'.$certificate->id, ['alasan' => $reason]);
        $certificate->user->notify(new Pemberitahuan('Sertifikat dibatalkan', 'Sertifikat kelas '.$program->title.' tidak diterbitkan untukmu. Alasan: '.$reason, route('warga.certificates.index')));
    }

    /** Tanda tangan dompet opsional atas id kelas dan sidik gabungan (SR-15, EIP-191). */
    public function walletMessage(Program $program): string
    {
        $hashes = $program->certificates()->whereIn('status', [CertificateStatus::MenungguTandaTangan->value, CertificateStatus::Ditandatangani->value])
            ->orderBy('id')->pluck('code')->implode(',');

        return "alloBos: saya menandatangani sertifikat kelas {$program->id}\nKelas: {$this->hasher->classId($program->id)}\nSidik gabungan: 0x".hash('sha256', $hashes);
    }

    public function attachWallet(Program $program, User $user, string $address, string $signature, ChainClient $chain): void
    {
        $signer = $this->signerFor($program, $user) ?? throw new RuntimeException('Kamu bukan penanda tangan kelas ini.');

        try {
            $recovered = $chain->recoverSigner($this->walletMessage($program), $signature);
        } catch (ChainException) {
            throw new RuntimeException('Tanda tangan dompet tidak bisa diperiksa sekarang. Alur tanda tangan biasa tetap berjalan.');
        }
        if (strcasecmp($recovered, $address) !== 0) {
            throw new RuntimeException('Tanda tangan dompet tidak cocok dengan alamat dompetmu.');
        }

        $signer->update(['wallet_address' => $recovered, 'wallet_signature' => $signature]);
        AuditLog::record('sertifikat.dompet', 'program:'.$program->id, ['alamat' => $recovered]);
    }
}
