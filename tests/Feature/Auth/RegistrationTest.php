<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_daftar_tampil(): void
    {
        $this->get('/daftar')->assertOk()->assertSee('Buat Akun');
    }

    public function test_warga_baru_mendaftar_dan_diarahkan_ke_onboarding(): void
    {
        $this->post('/daftar', [
            'name' => 'Sri Lestari',
            'whatsapp' => '081234567890',
            'email' => 'sri@contoh.id',
            'password' => 'rahasia-panjang',
            'password_confirmation' => 'rahasia-panjang',
            'consent' => '1',
        ])->assertRedirect(route('warga.onboarding.show', ['step' => 1]));

        $user = User::where('email', 'sri@contoh.id')->firstOrFail();
        $this->assertTrue($user->hasRole('warga'));
        $this->assertSame('6281234567890', $user->whatsapp);
        $this->assertAuthenticatedAs($user);
    }

    public function test_tujuan_ke_situs_lain_diabaikan(): void
    {
        $this->get('/daftar?tujuan=//situs-lain.example')->assertOk()->assertSessionMissing('url.intended');
        $this->get('/daftar?tujuan=/ruang-bos')->assertSessionHas('url.intended', url('/ruang-bos'));
    }

    public function test_tanpa_persetujuan_ditolak(): void
    {
        $this->post('/daftar', [
            'name' => 'Dimas', 'whatsapp' => '081234567890', 'email' => 'dimas@contoh.id',
            'password' => 'rahasia-panjang', 'password_confirmation' => 'rahasia-panjang',
        ])->assertSessionHasErrors('consent');

        $this->assertGuest();
    }
}
