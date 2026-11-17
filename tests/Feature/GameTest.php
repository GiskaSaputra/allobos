<?php

namespace Tests\Feature;

use App\Enums\GameStatus;
use App\Models\ReadinessSnapshot;
use App\Models\ShopGame;
use App\Services\Game\GameRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameTest extends TestCase
{
    use RefreshDatabase;

    public function test_tujuh_hari_sampai_rapor_dan_pelajaran_tersimpan(): void
    {
        config(['ai.keys' => []]);
        $user = $this->onboardedWarga();
        $this->actingAs($user)->get(route('game.index'))->assertOk()->assertSee('Buka Warung');
        $this->post(route('game.store'))->assertRedirect();
        $game = ShopGame::firstOrFail();
        $this->assertSame('singkong', $game->kind);
        $this->assertSame('bank', $game->state['event']['source']);
        $this->get(route('game.play', $game))->assertOk()->assertSee('data-warung', false);

        $this->postJson(route('game.price', $game), ['prices' => ['keripik' => 7000]])->assertStatus(422);

        for ($day = 1; $day <= 7; $day++) {
            $this->postJson(route('game.buy', $game), ['qty' => ['keripik' => 15, 'tiwul' => 5, 'gethuk' => 5, 'es_teh' => 15]])->assertOk()->assertJsonPath('view.phase', 'harga');
            $this->postJson(route('game.price', $game), ['prices' => ['keripik' => 7000, 'tiwul' => 6000, 'gethuk' => 5000, 'es_teh' => 3000]])->assertOk();
            $special = array_key_first(GameRules::SPECIALS[$day]['options']);
            $this->postJson(route('game.run', $game), ['promo' => 'tanpa', 'special' => $special])->assertOk()->assertJsonPath('view.phase', 'laporan')->assertJsonStructure(['result' => ['profit', 'items', 'bos_comment']]);
            $response = $this->postJson(route('game.next', $game))->assertOk();
            if ($day < 7) {
                $response->assertJsonPath('view.day', $day + 1);
            }
        }

        $game->refresh();
        $this->assertSame(GameStatus::Selesai, $game->status);
        $this->assertSame(7, $game->days()->count());
        $this->assertCount(3, $game->debrief['lessons']);
        $this->assertFalse($game->debrief['is_ai']);

        $this->get(route('game.report', $game))->assertOk()->assertSee('Simpan Pelajaran ke Jejak Usaha');
        $this->post(route('game.save', $game))->assertSessionHas('status');
        $this->assertNotNull($game->fresh()->saved_at);
        $this->assertGreaterThan(10, ReadinessSnapshot::where('user_id', $user->id)->latest('id')->value('score') ?? 0);
    }

    public function test_tahap_tidak_bisa_dilompati_dan_milik_sendiri(): void
    {
        config(['ai.keys' => []]);
        $user = $this->onboardedWarga();
        $this->actingAs($user)->post(route('game.store'));
        $game = ShopGame::firstOrFail();

        $this->postJson(route('game.run', $game), ['promo' => 'tanpa', 'special' => 'rapi'])->assertStatus(422);
        $this->postJson(route('game.buy', $game), ['qty' => ['keripik' => 100]])->assertStatus(422)->assertJsonPath('message', 'Kas tidak cukup. Modal tidak boleh minus, kurangi jumlah belanja.');

        $this->actingAs($this->onboardedWarga())->postJson(route('game.buy', $game), ['qty' => ['keripik' => 1]])->assertForbidden();
    }

    public function test_main_lagi_meninggalkan_permainan_lama(): void
    {
        config(['ai.keys' => []]);
        $this->actingAs($this->onboardedWarga())->post(route('game.store'));
        $this->post(route('game.store'));

        $this->assertSame(1, ShopGame::where('status', GameStatus::Ditinggalkan->value)->count());
        $this->assertSame(1, ShopGame::where('status', GameStatus::Berjalan->value)->count());
    }
}
