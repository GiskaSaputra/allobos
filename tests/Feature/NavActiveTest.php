<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Services\Chain\ChainClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakeChainClient;
use Tests\TestCase;

class NavActiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_tiap_halaman_admin_hanya_menyalakan_satu_menu(): void
    {
        // Halaman Blockchain menanyakan status jaringan; tes tidak boleh bergantung pada node sungguhan.
        $this->app->instance(ChainClient::class, new FakeChainClient);
        $this->actingAs($this->userWithRole(RoleName::Admin));

        foreach (['admin.dashboard' => 'Ringkasan Admin', 'admin.pages.index' => 'Halaman', 'admin.chain' => 'Blockchain', 'admin.audit' => 'Audit', 'admin.users.index' => 'Pengguna', 'pengelola.insights' => 'Wawasan', 'pengelola.programs.index' => 'Kelas', 'pengelola.dashboard' => 'Ringkasan'] as $route => $label) {
            $html = $this->get(route($route))->assertOk()->getContent();
            preg_match_all('/<a [^>]*aria-current="page"[^>]*>.*?<\/a>/s', $html, $matches);
            $this->assertCount(1, $matches[0], "Menu aktif di {$route}");
            $this->assertStringContainsString($label, strip_tags($matches[0][0]), "Menu aktif di {$route}");
        }
    }

    public function test_warga_tetap_di_tampilan_dashboard_saat_membuka_halaman_umum(): void
    {
        foreach (['podcasts.index', 'shops.index', 'programs.index', 'bosses.index'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('aria-label="Navigasi utama"', false)->assertDontSee('aria-label="Modul warga"', false);
        }

        $this->actingAs($this->onboardedWarga());
        foreach (['podcasts.index', 'shops.index', 'programs.index', 'bosses.index'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('aria-label="Modul warga"', false)->assertDontSee('aria-label="Navigasi utama"', false);
        }
        $this->get(route('home'))->assertOk()->assertSee('aria-label="Navigasi utama"', false);
    }

    public function test_halaman_turunan_menyalakan_menu_induknya(): void
    {
        $this->actingAs($this->userWithRole(RoleName::Admin));

        $html = $this->get(route('pengelola.programs.create'))->assertOk()->getContent();
        preg_match_all('/<a [^>]*aria-current="page"[^>]*>.*?<\/a>/s', $html, $matches);
        $this->assertCount(1, $matches[0]);
        $this->assertStringContainsString('Kelas', strip_tags($matches[0][0]));
    }
}
