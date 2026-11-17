<?php

namespace Tests\Feature;

use App\Enums\SessionStatus;
use App\Models\BossSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BossVoiceTest extends TestCase
{
    use RefreshDatabase;

    private function bossSession(): BossSession
    {
        $session = BossSession::create(['user_id' => $this->onboardedWarga()->id, 'topic' => 'Modal', 'mode' => 'suara', 'status' => SessionStatus::Berjalan, 'started_at' => now()]);
        $session->turns()->create(['role' => 'bos', 'content' => 'Halo, saya Bos Allo.', 'meta' => ['follow_up' => 'Usahamu apa?']]);

        return $session;
    }

    public function test_tanpa_kunci_peramban_membacakan_sendiri(): void
    {
        config(['services.speechify.key' => null]);
        $session = $this->bossSession();

        $this->actingAs($session->user)->post(route('boss-room.voice', $session))->assertNotFound();
    }

    public function test_suara_jawaban_terakhir_dari_speechify_dan_disimpan(): void
    {
        Storage::fake('local');
        config(['services.speechify.key' => 'kunci-uji']);
        Http::fake(['api.speechify.ai/*' => Http::response(['audio_data' => base64_encode('MP3PALSU'), 'audio_format' => 'mp3'])]);
        $session = $this->bossSession();

        $this->actingAs($session->user)->post(route('boss-room.voice', $session))
            ->assertOk()->assertHeader('content-type', 'audio/mpeg');
        $this->post(route('boss-room.voice', $session))->assertOk();

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request['voice_id'] === 'bima' && $request['input'] === "Halo, saya Bos Allo.\n\nUsahamu apa?" && $request->hasHeader('Authorization', 'Bearer kunci-uji'));
    }

    public function test_suara_memakai_jawaban_terbaru_atau_jawaban_yang_diminta(): void
    {
        Storage::fake('local');
        config(['services.speechify.key' => 'kunci-uji']);
        Http::fake(['api.speechify.ai/*' => Http::response(['audio_data' => base64_encode('MP3PALSU')])]);
        $session = $this->bossSession();
        $greeting = $session->turns()->first();
        $session->turns()->create(['role' => 'warga', 'content' => 'Saya mau jualan keripik.']);
        $session->turns()->create(['role' => 'bos', 'content' => 'Jawaban kedua.']);

        $this->actingAs($session->user)->postJson(route('boss-room.voice', $session))->assertOk();
        Http::assertSent(fn ($request) => $request['input'] === 'Jawaban kedua.');

        $this->postJson(route('boss-room.voice', $session), ['turn' => $greeting->id])->assertOk();
        Http::assertSent(fn ($request) => str_starts_with($request['input'], 'Halo, saya Bos Allo.'));
    }

    public function test_warga_lain_tidak_bisa_mengambil_suara(): void
    {
        $session = $this->bossSession();

        $this->actingAs($this->onboardedWarga())->post(route('boss-room.voice', $session))->assertForbidden();
    }
}
