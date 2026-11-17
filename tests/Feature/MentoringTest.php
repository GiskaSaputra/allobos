<?php

namespace Tests\Feature;

use App\Enums\BossStatus;
use App\Enums\MentoringStatus;
use App\Enums\RoleName;
use App\Enums\StepOrigin;
use App\Models\BossProfile;
use App\Models\BossSlot;
use App\Models\MentoringSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MentoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_warga_memesan_bos_menyetujui_menyelesaikan_dan_warga_menilai(): void
    {
        [$bos, $profile] = $this->activeBoss();
        $warga = $this->onboardedWarga();

        $this->actingAs($bos)->post(route('bos.slots.store'), ['date' => now()->addDays(2)->format('Y-m-d'), 'time' => '10:00', 'duration_minutes' => 60, 'mode' => 'daring'])->assertSessionHas('status');
        $slot = BossSlot::firstOrFail();

        $this->actingAs($warga)->get(route('warga.mentoring.index'))->assertOk()->assertSee($bos->name);
        $this->get(route('warga.mentoring.boss', $profile))->assertOk()->assertSee('Ajukan Sesi');
        $this->post(route('warga.mentoring.book', $slot), ['topic' => 'Cara titip keripik ke toko oleh-oleh'])->assertRedirect();
        $session = MentoringSession::firstOrFail();
        $this->assertSame(MentoringStatus::Menunggu, $session->status);
        $this->assertCount(1, $bos->fresh()->notifications);
        $this->assertFalse($slot->isBookable());

        $this->actingAs($this->onboardedWarga())->post(route('warga.mentoring.book', $slot), ['topic' => 'Topik lain yang panjang'])->assertSessionHas('error');
        $this->actingAs($warga)->get(route('warga.mentoring.show', $session))->assertDontSee('meet.google.com');

        $this->actingAs($bos)->post(route('bos.sessions.approve', $session), [])->assertSessionHas('error');
        $this->post(route('bos.sessions.approve', $session), ['meeting_link' => 'https://meet.google.com/abc-defg-hij'])->assertSessionHas('status');
        $this->actingAs($warga)->get(route('warga.mentoring.show', $session))->assertSee('Buka Tautan Rapat');

        $this->actingAs($bos)->post(route('bos.sessions.complete', $session), ['notes' => 'Mulai dari dua toko di Baron.', 'steps' => "Siapkan contoh produk\nBuat daftar harga"])->assertSessionHas('status');
        $this->assertSame(MentoringStatus::Selesai, $session->fresh()->status);

        $this->actingAs($warga)->post(route('warga.mentoring.rate', $session), ['rating' => 5, 'comment' => 'Sangat jelas'])->assertSessionHas('status');
        $this->post(route('warga.mentoring.rate', $session), ['rating' => 1])->assertSessionHas('error');
        $this->assertSame(5.0, $profile->fresh()->rating_avg);

        $this->post(route('warga.mentoring.steps', $session))->assertSessionHas('status');
        $this->assertSame(2, $warga->steps()->where('origin', StepOrigin::TemuBos->value)->count());
    }

    public function test_aturan_pemesanan_dan_pembatalan(): void
    {
        [, $profile] = $this->activeBoss();
        $warga = $this->onboardedWarga();
        $soon = $profile->slots()->create(['starts_at' => now()->addHours(5), 'duration_minutes' => 60, 'mode' => 'daring']);
        $later = $profile->slots()->create(['starts_at' => now()->addDays(3), 'duration_minutes' => 60, 'mode' => 'tatap_muka']);
        $other = $profile->slots()->create(['starts_at' => now()->addDays(4), 'duration_minutes' => 60, 'mode' => 'daring']);

        $this->actingAs($warga)->post(route('warga.mentoring.book', $soon), ['topic' => 'Topik yang cukup panjang'])->assertSessionHas('error');
        $this->post(route('warga.mentoring.book', $later), ['topic' => 'Topik yang cukup panjang']);
        $this->post(route('warga.mentoring.book', $other), ['topic' => 'Topik kedua yang panjang'])->assertSessionHas('error');

        $session = MentoringSession::firstOrFail();
        $this->post(route('warga.mentoring.cancel', $session))->assertSessionHas('status');
        $this->assertSame(MentoringStatus::Dibatalkan, $session->fresh()->status);
        $this->assertTrue($later->fresh()->isBookable());
    }

    public function test_permintaan_tanpa_jawaban_48_jam_kedaluwarsa(): void
    {
        [, $profile] = $this->activeBoss();
        $slot = $profile->slots()->create(['starts_at' => now()->addDays(5), 'duration_minutes' => 60, 'mode' => 'daring']);
        $session = MentoringSession::create(['boss_slot_id' => $slot->id, 'boss_profile_id' => $profile->id, 'user_id' => $this->onboardedWarga()->id, 'topic' => 'Topik', 'status' => MentoringStatus::Menunggu]);
        MentoringSession::whereKey($session->id)->update(['created_at' => now()->subHours(49)]);

        $this->artisan('allobos:pengingat')->assertSuccessful();
        $this->assertSame(MentoringStatus::Kedaluwarsa, $session->fresh()->status);
    }

    public function test_bos_belum_aktif_tidak_bisa_membuka_slot(): void
    {
        $bos = $this->userWithRole(RoleName::Bos);
        BossProfile::create(['user_id' => $bos->id, 'business_name' => 'Usaha', 'business_desc' => 'Deskripsi', 'status' => BossStatus::Diajukan]);

        $this->actingAs($bos)->post(route('bos.slots.store'), ['date' => now()->addDays(2)->format('Y-m-d'), 'time' => '10:00', 'duration_minutes' => 60, 'mode' => 'daring'])
            ->assertRedirect(route('bos.dashboard'));
        $this->assertSame(0, BossSlot::count());
    }

    /** @return array{User, BossProfile} */
    private function activeBoss(): array
    {
        $bos = $this->userWithRole(RoleName::Bos);
        $profile = BossProfile::create(['user_id' => $bos->id, 'business_name' => 'Homestay Pantai', 'business_desc' => 'Penginapan di Tepus', 'business_field_id' => 6, 'status' => BossStatus::Aktif]);

        return [$bos, $profile];
    }
}
