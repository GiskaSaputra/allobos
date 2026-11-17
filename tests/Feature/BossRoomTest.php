<?php

namespace Tests\Feature;

use App\Enums\SessionStatus;
use App\Enums\StepOrigin;
use App\Models\AiRequest;
use App\Models\BossSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BossRoomTest extends TestCase
{
    use RefreshDatabase;

    public function test_sesi_berjalan_dari_sapaan_sampai_catatan_dan_langkah(): void
    {
        $user = $this->onboardedWarga();
        $this->fakeGemini(
            ['reply' => 'Harga jual sebaiknya dihitung dari biaya bahan dulu.', 'follow_up' => 'Berapa biaya bahan untuk satu kali goreng?', 'suggested_modules' => ['rencana']],
            ['problem' => 'Belum tahu harga jual keripik.', 'advice' => ['Hitung HPP'], 'next_steps' => ['Catat biaya bahan', 'Hitung HPP di kalkulator'], 'modules' => ['rencana']],
        );

        $this->actingAs($user)->get(route('boss-room.setup'))->assertOk()->assertSee('Mulai Bicara dengan Bos Allo');
        $this->post(route('boss-room.store'), ['topic' => 'Menentukan harga jual', 'mode' => 'teks'])->assertRedirect();
        $session = BossSession::firstOrFail();
        $this->assertSame(SessionStatus::Disiapkan, $session->status);

        $this->get(route('boss-room.show', $session))->assertOk()->assertSee('data-boss-room', false);
        $this->postJson(route('boss-room.start', $session))->assertOk()->assertJsonPath('status', 'berjalan')
            ->assertJson(fn ($json) => $json->where('greeting', fn ($g) => str_contains($g, 'Bos Allo'))->etc());

        $this->postJson(route('boss-room.send', $session), ['message' => 'Keripik saya dijual 5000, pas tidak? Nomor saya 081234567890'])
            ->assertOk()->assertJsonPath('follow_up', 'Berapa biaya bahan untuk satu kali goreng?')->assertJsonPath('turns_left', 29);

        Http::assertSent(fn ($request) => str_contains(json_encode($request->data()), '[nomor]') && ! str_contains(json_encode($request->data()), '081234567890'));

        $this->post(route('boss-room.finish', $session))->assertRedirect(route('boss-room.note', $session));
        $this->assertSame(SessionStatus::Selesai, $session->fresh()->status);
        $this->get(route('boss-room.note', $session))->assertOk()->assertSee('Belum tahu harga jual keripik.');

        $this->post(route('boss-room.steps', $session))->assertSessionHas('status');
        $this->assertSame(2, $user->steps()->where('origin', StepOrigin::RuangBos->value)->count());
        $this->get(route('boss-room.note.pdf', $session))->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_ai_gagal_tidak_menghitung_giliran_dan_catatan_tetap_dibuat(): void
    {
        $user = $this->onboardedWarga();
        config(['ai.keys' => ['kunci-uji']]);
        Http::fake(['*' => Http::response(['error' => 'down'], 500)]);
        $session = BossSession::create(['user_id' => $user->id, 'topic' => 'Mulai usaha', 'mode' => 'teks', 'status' => SessionStatus::Berjalan, 'started_at' => now()]);

        $this->actingAs($user)->postJson(route('boss-room.send', $session), ['message' => 'Saya mau mulai jualan'])
            ->assertStatus(503)->assertJsonPath('quota', false);
        $this->assertSame(0, $session->turns()->count());

        $session->turns()->create(['role' => 'warga', 'content' => 'Saya mau mulai jualan']);
        $this->post(route('boss-room.finish', $session));
        $note = $session->fresh()->note;
        $this->assertFalse($note->is_ai);
        $this->assertNotEmpty($note->next_steps);
    }

    public function test_topik_pinjaman_menyarankan_temu_bos(): void
    {
        $user = $this->onboardedWarga();
        $this->fakeGemini(['reply' => 'Soal pinjaman sebaiknya ditanyakan ke lembaga resmi.', 'follow_up' => 'Untuk apa modalnya?', 'suggested_modules' => []]);
        $session = BossSession::create(['user_id' => $user->id, 'topic' => 'Modal', 'mode' => 'teks', 'status' => SessionStatus::Berjalan, 'started_at' => now()]);

        $this->actingAs($user)->postJson(route('boss-room.send', $session), ['message' => 'Apakah saya perlu pinjaman KUR?'])
            ->assertOk()->assertJsonPath('modules.0.key', 'temu_bos');
    }

    public function test_kuota_giliran_habis_ditolak_dengan_waktu_kembali(): void
    {
        $user = $this->onboardedWarga();
        config(['ai.keys' => ['kunci-uji']]);
        for ($i = 0; $i < 30; $i++) {
            AiRequest::create(['user_id' => $user->id, 'feature' => 'boss_chat', 'status' => 'ok']);
        }
        $session = BossSession::create(['user_id' => $user->id, 'topic' => 'Modal', 'mode' => 'teks', 'status' => SessionStatus::Berjalan, 'started_at' => now()]);

        $this->actingAs($user)->postJson(route('boss-room.send', $session), ['message' => 'Halo'])
            ->assertStatus(429)->assertJsonPath('quota', true);
    }

    public function test_warga_lain_tidak_bisa_membuka_sesi(): void
    {
        $session = BossSession::create(['user_id' => $this->onboardedWarga()->id, 'topic' => 'Modal', 'mode' => 'teks', 'status' => SessionStatus::Berjalan]);

        $this->actingAs($this->onboardedWarga())->get(route('boss-room.show', $session))->assertForbidden();
    }

    public function test_sesi_diam_ditutup_otomatis(): void
    {
        $user = $this->onboardedWarga();
        config(['ai.keys' => []]);
        $session = BossSession::create(['user_id' => $user->id, 'topic' => 'Modal', 'mode' => 'teks', 'status' => SessionStatus::Berjalan, 'started_at' => now()->subHour(), 'last_activity_at' => now()->subMinutes(45)]);
        $session->turns()->create(['role' => 'warga', 'content' => 'Halo']);

        $this->artisan('allobos:ruang-bos-rapikan')->assertSuccessful();
        $this->assertSame(SessionStatus::Selesai, $session->fresh()->status);
    }
}
