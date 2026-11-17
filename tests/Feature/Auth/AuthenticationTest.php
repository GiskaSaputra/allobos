<?php

namespace Tests\Feature\Auth;

use App\Enums\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_masuk_tampil(): void
    {
        $this->get('/masuk')->assertOk()->assertSee('Masuk');
    }

    public function test_pengguna_masuk_dan_diarahkan_ke_dashboard_peran(): void
    {
        $user = $this->userWithRole(RoleName::Pengelola);

        $this->post('/masuk', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->get(route('dashboard'))->assertRedirect(route('pengelola.dashboard'));
    }

    public function test_sandi_salah_ditolak(): void
    {
        $user = $this->userWithRole();

        $this->post('/masuk', ['email' => $user->email, 'password' => 'salah'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_pengguna_dapat_keluar(): void
    {
        $this->actingAs($this->userWithRole())->post('/keluar')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_akun_nonaktif_dikeluarkan(): void
    {
        $user = $this->userWithRole(RoleName::Bos, ['is_active' => false]);

        $this->actingAs($user)->get(route('bos.dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
