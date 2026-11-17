<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_tiap_peran_membuka_dashboardnya(): void
    {
        $this->actingAs($this->userWithRole(RoleName::Bos))->get(route('bos.dashboard'))->assertOk();
        $this->actingAs($this->userWithRole(RoleName::Pengelola))->get(route('pengelola.dashboard'))->assertOk();
        $this->actingAs($this->userWithRole(RoleName::Admin))->get(route('admin.dashboard'))->assertOk();
    }

    public function test_warga_tanpa_profil_diarahkan_ke_onboarding(): void
    {
        $this->actingAs($this->userWithRole())->get(route('warga.dashboard'))
            ->assertRedirect(route('warga.onboarding.show', ['step' => 1]));
    }

    public function test_peran_lain_ditolak(): void
    {
        $this->actingAs($this->userWithRole(RoleName::Bos))->get(route('pengelola.dashboard'))->assertForbidden();
        $this->actingAs($this->userWithRole(RoleName::Pengelola))->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_boleh_membuka_area_pengelola(): void
    {
        $this->actingAs($this->userWithRole(RoleName::Admin))->get(route('pengelola.dashboard'))->assertOk();
    }

    public function test_akun_multi_peran_berganti_peran_aktif(): void
    {
        $user = $this->userWithRole(RoleName::Bos);
        $user->assignRole('pengelola');

        $this->actingAs($user)->post(route('active-role.update'), ['role' => 'pengelola'])
            ->assertRedirect(route('pengelola.dashboard'));
        $this->assertSame(RoleName::Pengelola, $user->fresh()->active_role);

        $this->actingAs($user)->post(route('active-role.update'), ['role' => 'admin'])->assertForbidden();
    }

    public function test_beranda_publik_tampil(): void
    {
        $this->get('/')->assertOk()->assertSee('Tanya Bos Allo');
    }
}
