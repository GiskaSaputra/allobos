<?php

namespace Tests\Feature;

use App\Models\BusinessField;
use App\Models\District;
use App\Models\ReadinessSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_warga_menyelesaikan_lima_langkah_dan_skor_awal_dihitung(): void
    {
        $user = $this->userWithRole();
        $this->actingAs($user);
        $fields = BusinessField::query()->usaha()->take(2)->pluck('id')->all();

        $this->get(route('warga.onboarding.show', 1))->assertOk()->assertSee('Ponjong');
        $this->post(route('warga.onboarding.store', 1), ['district_id' => District::where('slug', 'ponjong')->value('id')])->assertRedirect(route('warga.onboarding.show', 2));
        $this->post(route('warga.onboarding.store', 2), ['business_status' => 'sudah', 'existing_business' => 'Keripik singkong'])->assertRedirect(route('warga.onboarding.show', 3));
        $this->post(route('warga.onboarding.store', 3), ['interests' => $fields])->assertRedirect(route('warga.onboarding.show', 4));
        $this->post(route('warga.onboarding.store', 4), ['capital_range' => 'lt1', 'free_time' => '2-4'])->assertRedirect(route('warga.onboarding.show', 5));
        $this->post(route('warga.onboarding.store', 5), ['skills' => 'Memasak dan menggoreng keripik'])->assertRedirect(route('warga.dashboard'));

        $profile = $user->fresh()->profile;
        $this->assertNotNull($profile->onboarded_at);
        $this->assertSame($fields, $profile->interests);
        $this->assertSame(10, ReadinessSnapshot::where('user_id', $user->id)->latest('id')->value('score'));

        $this->actingAs($user->fresh());
        $this->get(route('warga.dashboard'))->assertOk()->assertSee('Skor Siap Usaha')->assertSee('Ikut kelas dari pengusaha asli');
        $this->get(route('warga.journey'))->assertOk()->assertSee('Melengkapi Profil Usaha');
    }

    public function test_tidak_bisa_melompati_langkah(): void
    {
        $this->actingAs($this->userWithRole())
            ->get(route('warga.onboarding.show', 4))
            ->assertRedirect(route('warga.onboarding.show', 1));
    }

    public function test_bidang_minat_paling_banyak_tiga(): void
    {
        $user = $this->userWithRole();
        $user->profile()->create(['step' => 3, 'district_id' => 1, 'business_status' => 'belum']);

        $this->actingAs($user)->post(route('warga.onboarding.store', 3), ['interests' => BusinessField::query()->usaha()->take(4)->pluck('id')->all()])
            ->assertSessionHasErrors('interests');
    }
}
