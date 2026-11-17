<?php

namespace Tests\Feature;

use App\Enums\CertificateStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\ProgramKind;
use App\Enums\ProgramStatus;
use App\Enums\RoleName;
use App\Models\Program;
use App\Models\ProgramSession;
use App\Models\Speaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengelola_membuat_dan_menerbitkan_kelas_lalu_warga_mendaftar(): void
    {
        $pengelola = $this->userWithRole(RoleName::Pengelola);
        $speaker = Speaker::create(['name' => 'Pembicara Uji', 'business' => 'Usaha Uji']);

        $this->actingAs($pengelola)->post(route('pengelola.programs.store'), [
            'title' => 'Workshop Kemasan', 'kind' => 'workshop', 'description' => 'Belajar kemasan.', 'location' => 'Wonosari',
            'quota' => 1, 'is_official' => 1, 'speakers' => [$speaker->id],
            'sessions' => [['title' => 'Sesi 1', 'date' => now()->addDays(3)->format('Y-m-d'), 'start' => '09:00', 'end' => '11:00']],
        ])->assertRedirect();
        $program = Program::firstOrFail();
        $this->assertSame(ProgramStatus::Draf, $program->status);
        $this->get(route('programs.show', $program))->assertNotFound();

        $this->post(route('pengelola.programs.publish', $program))->assertSessionHas('status');
        $this->get(route('programs.show', $program))->assertOk()->assertSee('Daftar Kelas');

        $warga = $this->onboardedWarga();
        $this->actingAs($warga)->post(route('warga.programs.enroll', $program))->assertRedirect(route('warga.programs.index'));
        $this->assertSame(ProgramStatus::Penuh, $program->fresh()->status);
        $this->post(route('warga.programs.enroll', $program))->assertSessionHas('error');

        $this->actingAs($this->onboardedWarga())->post(route('warga.programs.enroll', $program))->assertSessionHas('error', 'Kuota kelas sudah penuh. Pilih kelas lain atau tunggu angkatan berikutnya.');

        $this->actingAs($warga)->post(route('warga.programs.cancel', $program))->assertSessionHas('status');
        $this->assertSame(ProgramStatus::Dibuka, $program->fresh()->status);
    }

    public function test_kode_kehadiran_hanya_berlaku_saat_sesi_dan_kelas_ditutup_membuat_draf_sertifikat(): void
    {
        $pengelola = $this->userWithRole(RoleName::Pengelola);
        [$program, $sessions] = $this->runningProgram();
        $hadir = $this->onboardedWarga();
        $jarang = $this->onboardedWarga();
        foreach ([$hadir, $jarang] as $w) {
            $program->enrollments()->create(['user_id' => $w->id, 'status' => EnrollmentStatus::Terdaftar]);
        }

        $this->actingAs($pengelola)->post(route('pengelola.programs.code', $sessions[3]))->assertOk();
        $code = $sessions[3]->fresh()->attendance_code;
        $this->actingAs($hadir)->post(route('warga.programs.attend', $sessions[3]), ['code' => 'SALAH1'])->assertSessionHasErrors('code_'.$sessions[3]->id);
        $this->post(route('warga.programs.attend', $sessions[3]), ['code' => strtolower($code)])->assertSessionHas('status');
        $this->post(route('warga.programs.attend', $sessions[0]), ['code' => 'APAPUN'])->assertSessionHasErrors('code_'.$sessions[0]->id);

        foreach (array_slice($sessions, 0, 3) as $s) {
            $s->attendances()->create(['user_id' => $hadir->id]);
        }
        $sessions[0]->attendances()->create(['user_id' => $jarang->id]);
        $sessions[3]->update(['ends_at' => now()->subMinute()]);

        $this->actingAs($pengelola)->post(route('pengelola.programs.close', $program))->assertSessionHas('error');
        $this->post(route('pengelola.programs.signers', $program), ['signers' => [['user_id' => $pengelola->id, 'title' => 'Pimpinan IPI']]])->assertSessionHas('status');
        $this->post(route('pengelola.programs.close', $program))->assertSessionHas('status');

        $this->assertSame(ProgramStatus::Selesai, $program->fresh()->status);
        $this->assertSame(1, $program->certificates()->count());
        $certificate = $program->certificates()->first();
        $this->assertSame($hadir->id, $certificate->user_id);
        $this->assertSame(CertificateStatus::MenungguTandaTangan, $certificate->status);
        $this->assertMatchesRegularExpression('/^ALB-\d{4}-[ACDEFHJKMNPQRTUVWXY34679]{6}$/', $certificate->code);
        $this->assertCount(1, $pengelola->fresh()->notifications);

        $this->get(route('pengelola.programs.export', $program))->assertOk()->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_penanda_tangan_harus_pembicara_atau_pimpinan(): void
    {
        [$program] = $this->runningProgram();
        $bosLain = $this->userWithRole(RoleName::Bos);

        $this->actingAs($this->userWithRole(RoleName::Pengelola))
            ->post(route('pengelola.programs.signers', $program), ['signers' => [['user_id' => $bosLain->id, 'title' => 'Bos']]])
            ->assertSessionHas('error');
    }

    /** @return array{Program, list<ProgramSession>} */
    private function runningProgram(): array
    {
        $program = Program::create(['title' => 'Bootcamp Keripik', 'slug' => 'bootcamp-keripik', 'kind' => ProgramKind::Bootcamp, 'description' => 'Uji', 'location' => 'Ponjong', 'quota' => 10, 'status' => ProgramStatus::Berjalan]);
        $sessions = [];
        foreach ([-4, -3, -2] as $day) {
            $sessions[] = $program->sessions()->create(['title' => 'Hari '.(5 + $day), 'starts_at' => now()->addDays($day), 'ends_at' => now()->addDays($day)->addHours(2)]);
        }
        $sessions[] = $program->sessions()->create(['title' => 'Hari 4', 'starts_at' => now()->subHour(), 'ends_at' => now()->addHour()]);

        return [$program, $sessions];
    }
}
