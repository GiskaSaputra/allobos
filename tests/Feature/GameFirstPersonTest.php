<?php

namespace Tests\Feature;

use App\Models\ShopGame;
use App\Services\Game\GameEngine;
use App\Services\Game\GameRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameFirstPersonTest extends TestCase
{
    use RefreshDatabase;

    public function test_satu_hari_3d_rak_buka_layani_tutup_dan_idempoten(): void
    {
        config(['ai.keys' => []]);
        $this->actingAs($this->onboardedWarga())->post(route('game.store'));
        $game = ShopGame::firstOrFail();

        $this->postJson(route('game.stock', $game), ['item' => 'keripik', 'qty' => 10, 'rid' => 'r0'])->assertStatus(422)->assertJsonPath('message', 'Stok di gudang habis. Belanja dulu di meja pemasok.');
        $this->postJson(route('game.buy', $game), ['qty' => ['keripik' => 30, 'es_teh' => 20]])->assertOk();
        $this->postJson(route('game.buy', $game), ['qty' => ['tiwul' => 5]])->assertOk()->assertJsonPath('view.phase', 'harga');

        $this->postJson(route('game.stock', $game), ['item' => 'keripik', 'qty' => 10, 'rid' => 'r1'])->assertOk()->assertJsonPath('result.moved', 10);
        $this->postJson(route('game.stock', $game), ['item' => 'keripik', 'qty' => 10, 'rid' => 'r1'])->assertOk()->assertJsonPath('view.items.0.display', 10);
        $this->postJson(route('game.stock', $game), ['item' => 'es_teh', 'qty' => 10, 'rid' => 'r2'])->assertOk();

        $this->postJson(route('game.price', $game), ['prices' => ['keripik' => 7000]])->assertOk()->assertJsonPath('view.phase', 'buka');
        $this->postJson(route('game.open', $game), ['promo' => 'tanpa', 'special' => 'rapi', 'rid' => 'o1'])->assertOk()->assertJsonPath('view.phase', 'melayani');
        $this->postJson(route('game.open', $game), ['promo' => 'tanpa', 'special' => 'rapi', 'rid' => 'o1'])->assertOk();

        $customers = $game->fresh()->state['day_plan']['customers'];
        $this->assertNotEmpty($customers);
        $this->assertLessThanOrEqual(8, count($customers));

        $first = $this->postJson(route('game.serve', $game), ['customer' => 0, 'rid' => 's0'])->assertOk()->json('result');
        $again = $this->postJson(route('game.serve', $game), ['customer' => 0, 'rid' => 's0b'])->assertOk()->json('result');
        $this->assertSame($first, $again);
        if (count($customers) > 1) {
            $this->postJson(route('game.miss', $game), ['customer' => 1, 'rid' => 'm1'])->assertOk();
        }

        $display = $game->fresh()->state['display'];
        $this->assertSame(20 - array_sum($first['sold']), $display['keripik'] + $display['es_teh']);

        $result = $this->postJson(route('game.close', $game), ['rid' => 'c1'])->assertOk()->assertJsonPath('view.phase', 'laporan')->json('result');
        $this->assertSame(array_sum($first['sold']), $result['sold_total']);
        $this->assertSame(1, $game->days()->count());
        $this->postJson(route('game.close', $game), ['rid' => 'c1'])->assertOk()->assertJsonPath('result.sold_total', $result['sold_total']);

        $this->postJson(route('game.next', $game))->assertOk()->assertJsonPath('view.day', 2);
    }

    public function test_pembeli_dibagi_paling_banyak_delapan_dan_jumlahnya_sama_dengan_permintaan(): void
    {
        $engine = new GameEngine;
        $state = $engine->buy($engine->newState('singkong'), ['keripik' => 50]);
        [, $plan] = $engine->plan($state, 4, 'spanduk', 'normal', 99);
        $customers = $engine->customers($plan, 99);

        $this->assertLessThanOrEqual(8, count($customers));
        $this->assertSame(array_sum(array_column($plan['items'], 'demand')), array_sum(array_map(fn ($c) => array_sum($c['items']), $customers)));
        $this->assertSame($customers, $engine->customers($plan, 99));
    }

    public function test_rak_tidak_melebihi_kapasitas(): void
    {
        config(['ai.keys' => []]);
        $this->actingAs($this->onboardedWarga())->post(route('game.store'));
        $game = ShopGame::firstOrFail();
        $this->postJson(route('game.buy', $game), ['qty' => ['es_teh' => 60]]);

        for ($i = 0; $i < GameRules::SHELF_CAPACITY / GameRules::CRATE_SIZE; $i++) {
            $this->postJson(route('game.stock', $game), ['item' => 'es_teh', 'qty' => 10, 'rid' => "r$i"])->assertOk();
        }
        $this->postJson(route('game.stock', $game), ['item' => 'es_teh', 'qty' => 10, 'rid' => 'lebih'])->assertStatus(422)->assertJsonPath('message', 'Rak sudah penuh.');
    }
}
