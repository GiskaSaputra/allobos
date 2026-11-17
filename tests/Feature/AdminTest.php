<?php

namespace Tests\Feature;

use App\Enums\BossStatus;
use App\Enums\RoleName;
use App\Models\BossProfile;
use App\Models\Invitation;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use App\Services\Ai\AiSettings;
use App\Services\Ai\PromptRegistry;
use App\Services\Chain\ChainClient;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\Fakes\FakeChainClient;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_pengelola_dan_admin_terbuka(): void
    {
        $this->app->instance(ChainClient::class, new FakeChainClient);
        $admin = $this->userWithRole(RoleName::Admin);
        $this->seed(PageSeeder::class);

        $this->actingAs($admin);
        foreach (['pengelola.dashboard', 'pengelola.bosses.index', 'pengelola.announcements.index', 'pengelola.insights', 'pengelola.programs.index', 'pengelola.speakers.index', 'pengelola.podcasts.index', 'pengelola.certificates.index', 'pengelola.shops.index',
            'admin.dashboard', 'admin.users.index', 'admin.ai.index', 'admin.pages.index', 'admin.events.index', 'admin.settings.edit', 'admin.chain', 'admin.audit'] as $route) {
            $this->get(route($route))->assertOk();
        }
        $this->get(route('pengelola.insights', ['unduh' => 'xlsx']))->assertOk();
    }

    public function test_kurasi_bos_butuh_empat_standar(): void
    {
        $pengelola = $this->userWithRole(RoleName::Pengelola);
        $bos = $this->userWithRole(RoleName::Bos);
        $profile = BossProfile::create(['user_id' => $bos->id, 'business_name' => 'Usaha', 'business_desc' => 'Deskripsi usaha', 'status' => BossStatus::Diajukan]);

        $this->actingAs($pengelola)->post(route('pengelola.bosses.activate', $profile), ['checklist' => ['aktif' => 1, 'rekam_jejak' => 1]])->assertSessionHas('error');
        $this->post(route('pengelola.bosses.activate', $profile), ['checklist' => ['aktif' => 1, 'rekam_jejak' => 1, 'pengalaman' => 1, 'telusur' => 1]])->assertSessionHas('status');
        $this->assertSame(BossStatus::Aktif, $profile->fresh()->status);
    }

    public function test_undangan_bertanda_tangan_membuat_akun_bos(): void
    {
        Notification::fake();
        $this->actingAs($this->userWithRole(RoleName::Pengelola))->post(route('pengelola.bosses.invite'), ['email' => 'bos.baru@contoh.id'])->assertSessionHas('status');
        $invitation = Invitation::firstOrFail();
        auth()->logout();

        $this->get(route('invitations.accept', $invitation))->assertForbidden();
        $url = URL::temporarySignedRoute('invitations.accept', now()->addDay(), ['invitation' => $invitation->id]);
        $this->get($url)->assertOk()->assertSee('Terima Undangan');
        $this->post($url, ['name' => 'Bos Baru', 'whatsapp' => '081234567890', 'password' => 'sandi-panjang-1', 'password_confirmation' => 'sandi-panjang-1'])->assertRedirect(route('bos.profile.edit'));

        $user = User::where('email', 'bos.baru@contoh.id')->firstOrFail();
        $this->assertTrue($user->hasRole('bos'));
        $this->post($url, [])->assertRedirect(route('home'));
    }

    public function test_jadi_bos_dari_tamu(): void
    {
        Notification::fake();
        $this->post(route('become-boss.store'), [
            'name' => 'Pak Arif', 'email' => 'arif@contoh.id', 'whatsapp' => '081234567890', 'password' => 'sandi-panjang-1', 'password_confirmation' => 'sandi-panjang-1', 'consent' => 1,
            'business_name' => 'Homestay Arif', 'business_desc' => 'Penginapan keluarga di Tepus sejak 2015.', 'business_field_id' => 6, 'years_running' => 9,
        ])->assertRedirect(route('bos.dashboard'));

        $this->assertSame(BossStatus::Diajukan, User::where('email', 'arif@contoh.id')->first()->bossProfile->status);
    }

    public function test_tautan_bos_hanya_http_atau_https(): void
    {
        $this->post(route('become-boss.store'), [
            'name' => 'Usil', 'email' => 'usil@contoh.id', 'whatsapp' => '081234567890', 'password' => 'sandi-panjang-1', 'password_confirmation' => 'sandi-panjang-1', 'consent' => 1,
            'business_name' => 'Usaha', 'business_desc' => 'Deskripsi usaha yang cukup panjang.', 'business_field_id' => 6, 'years_running' => 1,
            'trace_url' => 'javascript:alert(document.cookie)',
        ])->assertSessionHasErrors('trace_url');
    }

    public function test_bobot_skor_harus_100_dan_prompt_berversi(): void
    {
        $admin = $this->userWithRole(RoleName::Admin);
        $this->actingAs($admin);

        $this->patch(route('admin.settings.update'), ['weights' => ['profil' => 10, 'konsultasi' => 15, 'belajar' => 25, 'simulasi' => 15, 'rencana' => 25, 'lapak' => 20], 'sessions' => 5])->assertSessionHas('error');
        $this->patch(route('admin.settings.update'), ['weights' => ['profil' => 10, 'konsultasi' => 15, 'belajar' => 25, 'simulasi' => 15, 'rencana' => 20, 'lapak' => 15], 'sessions' => 3])->assertSessionHas('status');
        $this->assertSame(15, Setting::read('readiness.weights')['lapak']);

        $this->post(route('admin.ai.prompts.store', 'boss_chat'), ['prompt' => 'Kamu Bos Allo versi baru yang ramah dan lugas.'])->assertSessionHas('status');
        $this->assertSame('Kamu Bos Allo versi baru yang ramah dan lugas.', app(PromptRegistry::class)->active('boss_chat'));

        $this->fakeGemini(['reply' => 'Halo', 'follow_up' => 'Apa kabar?', 'suggested_modules' => []]);
        $this->post(route('admin.ai.prompts.test', 'boss_chat'), ['prompt' => 'Kamu Bos Allo untuk uji coba prompt.', 'input' => 'Halo'])->assertSessionHas('test', fn ($t) => $t['ok'] === true);

        $this->patch(route('admin.ai.update', 'boss_chat'), ['enabled' => 0, 'temperature' => 0.5, 'quota' => 10])->assertSessionHas('status');
        $this->assertFalse(app(AiSettings::class)->for('boss_chat')['enabled']);
    }

    public function test_halaman_publik_cms_dirender_aman(): void
    {
        Page::where('slug', 'tentang')->update(['body' => "## Judul\n\n<script>alert(1)</script>"]);

        $this->get('/tentang')->assertOk()->assertSee('<h2>Judul</h2>', false)->assertDontSee('<script>alert(1)</script>', false);
    }
}
