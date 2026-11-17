<?php

namespace Tests\Feature;

use App\Enums\CertificateStatus;
use App\Enums\ProgramKind;
use App\Enums\ProgramStatus;
use App\Enums\RoleName;
use App\Models\Certificate;
use App\Models\Program;
use App\Models\SignatureSpecimen;
use App\Models\User;
use App\Services\Chain\ChainClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Fakes\FakeChainClient;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use RefreshDatabase;

    private FakeChainClient $chain;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->chain = new FakeChainClient;
        $this->app->instance(ChainClient::class, $this->chain);
        config(['chain.salt' => 'garam-uji', 'chain.name' => 'Hardhat Lokal']);
    }

    public function test_dua_penanda_tangan_lalu_pdf_tercatat_dan_terverifikasi(): void
    {
        [$program, $certificate, $warga] = $this->closedProgram();
        $pengelola = $program->signers[0]->user;
        $bos = $program->signers[1]->user;

        $this->actingAs($pengelola)->get(route('signing.show', $program))->assertRedirect(route('signing.specimen.edit'));
        $this->post(route('signing.specimen.store'), ['file' => UploadedFile::fake()->image('ttd.png', 300, 100)])->assertRedirect(route('signing.show', $program));
        $this->get(route('signing.show', $program))->assertOk()->assertSee('Tanda Tangani 1 Sertifikat');
        $this->post(route('signing.sign', $program), ['password' => 'salah'])->assertSessionHasErrors('password');
        $this->post(route('signing.sign', $program), ['password' => 'password'])->assertSessionHas('status');
        $this->assertSame(CertificateStatus::MenungguTandaTangan, $certificate->fresh()->status);

        $this->actingAs($warga)->get(route('warga.certificates.show', $certificate))->assertOk()->assertSee('menunggu tanda tangan');
        $this->get(route('warga.certificates.pdf', $certificate))->assertSessionHas('error');

        $png = imagecreatetruecolor(300, 100);
        ob_start();
        imagepng($png);
        $this->actingAs($bos)->post(route('signing.specimen.store'), ['drawing' => 'data:image/png;base64,'.base64_encode(ob_get_clean())]);
        $this->post(route('signing.sign', $program), ['password' => 'password'])->assertSessionHas('status');

        $certificate->refresh();
        $this->assertSame(CertificateStatus::Tercatat, $certificate->status);
        $this->assertNotNull($certificate->hash);
        Storage::disk('local')->assertExists($certificate->pdf_path);
        $this->assertArrayHasKey($certificate->hash, $this->chain->records);
        $this->assertSame(1, $certificate->transaction()->count());

        $this->actingAs($warga)->get(route('warga.certificates.pdf', $certificate))->assertOk()->assertDownload('sertifikat-'.$certificate->code.'.pdf');
        $this->actingAs($this->onboardedWarga())->get(route('warga.certificates.pdf', $certificate))->assertForbidden();

        auth()->logout();
        $this->get(route('certificates.verify', $certificate->code))->assertOk()->assertSee('Asli')->assertSee('Jaringan uji')->assertSee($warga->name);
        $this->post(route('certificates.verify.lookup'), ['code' => strtolower($certificate->code)])->assertRedirect(route('certificates.verify', $certificate->code));
        $this->get(route('certificates.verify', 'ALB-2026-TIDAKA'))->assertSee('Tidak Ditemukan');

        $certificate->update(['show_full_name' => false]);
        $this->get(route('certificates.verify', $certificate->code))->assertDontSee($warga->name)->assertSee($certificate->displayName());
    }

    public function test_data_diubah_menjadi_tidak_cocok_dan_pencabutan_tercatat(): void
    {
        [$program, $certificate] = $this->closedProgram(signed: true);
        $certificate->refresh();
        $this->assertSame(CertificateStatus::Tercatat, $certificate->status);

        Certificate::whereKey($certificate->id)->update(['recipient_name' => 'Nama Palsu']);
        $this->get(route('certificates.verify', $certificate->code))->assertSee('Tidak Cocok');
        Certificate::whereKey($certificate->id)->update(['recipient_name' => $certificate->recipient_name]);

        $this->actingAs($this->userWithRole(RoleName::Pengelola))
            ->post(route('pengelola.certificates.revoke', $certificate), ['reason' => 'Peserta keliru'])->assertSessionHas('status');
        $this->assertSame(CertificateStatus::Dicabut, $certificate->fresh()->status);
        $this->assertTrue($this->chain->records[$certificate->hash]['revoked']);
        auth()->logout();
        $this->get(route('certificates.verify', $certificate->code))->assertSee('Dicabut')->assertSee('Peserta keliru');
    }

    public function test_jaringan_gagal_sertifikat_tetap_bisa_diunduh_dan_catat_ulang(): void
    {
        $this->chain->down = true;
        [$program, $certificate, $warga] = $this->closedProgram(signed: true);

        $certificate->refresh();
        $this->assertSame(CertificateStatus::MenungguPencatatan, $certificate->status);
        $this->actingAs($warga)->get(route('warga.certificates.pdf', $certificate))->assertOk();
        $this->get(route('certificates.verify', $certificate->code))->assertSee('Belum Tercatat');

        $this->chain->down = false;
        $this->actingAs($this->userWithRole(RoleName::Pengelola))->post(route('pengelola.certificates.retry', $program))->assertSessionHas('status');
        $certificate->refresh();
        $this->assertSame(CertificateStatus::Tercatat, $certificate->status);
    }

    public function test_penanda_tangan_mengeluarkan_nama_dan_akun_lain_ditolak(): void
    {
        [$program, $certificate] = $this->closedProgram();

        $this->actingAs($this->userWithRole(RoleName::Bos))->get(route('signing.show', $program))->assertForbidden();
        $this->actingAs($program->signers[0]->user)->post(route('signing.exclude', [$program, $certificate]), ['reason' => 'Tidak ikut praktik'])->assertSessionHas('status');
        $this->assertSame(CertificateStatus::Dibatalkan, $certificate->fresh()->status);
        $this->get(route('certificates.verify', $certificate->code))->assertSee('Tidak Ditemukan');
    }

    public function test_lencana_lapak_hanya_untuk_sertifikat_tercatat(): void
    {
        [, $certificate, $warga] = $this->closedProgram();

        $this->actingAs($warga)->patch(route('warga.certificates.update', $certificate), ['show_on_shop' => 1])->assertSessionHas('error');
        $this->patch(route('warga.certificates.update', $certificate), ['show_full_name' => 0])->assertSessionHas('status');
        $this->assertFalse($certificate->fresh()->show_full_name);
    }

    /** @return array{Program, Certificate, User} */
    private function closedProgram(bool $signed = false): array
    {
        $pengelola = $this->userWithRole(RoleName::Pengelola);
        $bos = $this->userWithRole(RoleName::Bos);
        $warga = $this->onboardedWarga();
        $program = Program::create(['title' => 'Kelas Uji', 'slug' => 'kelas-uji', 'kind' => ProgramKind::Workshop, 'description' => 'Uji', 'location' => 'Wonosari', 'quota' => 5, 'status' => ProgramStatus::Selesai, 'closed_at' => now()]);
        $program->sessions()->create(['title' => 'Sesi 1', 'starts_at' => now()->subDays(2), 'ends_at' => now()->subDays(2)->addHours(2)]);
        $program->signers()->create(['user_id' => $pengelola->id, 'title' => 'Pimpinan IPI', 'sort' => 1]);
        $program->signers()->create(['user_id' => $bos->id, 'title' => 'Pembicara', 'sort' => 2]);
        $certificate = Certificate::create(['code' => Certificate::generateCode(), 'program_id' => $program->id, 'user_id' => $warga->id, 'status' => CertificateStatus::MenungguTandaTangan, 'recipient_name' => $warga->name]);

        if ($signed) {
            foreach ([$pengelola, $bos] as $signer) {
                SignatureSpecimen::create(['user_id' => $signer->id, 'path' => 'spesimen/'.$signer->id.'.png']);
                $img = imagecreatetruecolor(200, 80);
                ob_start();
                imagepng($img);
                Storage::disk('local')->put('spesimen/'.$signer->id.'.png', ob_get_clean());
                $this->actingAs($signer)->post(route('signing.sign', $program), ['password' => 'password']);
            }
        }

        return [$program->load('signers.user'), $certificate, $warga];
    }
}
