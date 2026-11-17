<?php

namespace Tests\Unit;

use App\Services\Game\GameEngine;
use App\Services\Game\GameException;
use App\Services\Game\GameRules;
use PHPUnit\Framework\TestCase;

class GameEngineTest extends TestCase
{
    private GameEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new GameEngine;
    }

    public function test_modal_tidak_bisa_negatif_saat_belanja(): void
    {
        $state = $this->engine->newState('singkong');

        $this->expectException(GameException::class);
        $this->engine->buy($state, ['keripik' => 76]);
    }

    public function test_belanja_mengurangi_kas_dan_menambah_stok(): void
    {
        $state = $this->engine->buy($this->engine->newState('singkong'), ['keripik' => 20, 'tiwul' => 10]);

        $this->assertSame(300000 - 20 * 4000 - 10 * 3000, $state['cash']);
        $this->assertSame(20, $state['stock']['keripik']['qty']);
    }

    public function test_hasil_sama_untuk_masukan_dan_benih_yang_sama(): void
    {
        $state = $this->engine->buy($this->engine->newState('kelontong'), ['air' => 30, 'mi' => 15, 'roti' => 10, 'telur' => 20]);
        $state['event'] = ['effect' => ['demand' => 0.9, 'cost' => 1.0, 'cash' => 0]];

        [$a, $resultA] = $this->engine->run($state, 1, 'spanduk', 'rapi', 12345);
        [$b, $resultB] = $this->engine->run($state, 1, 'spanduk', 'rapi', 12345);
        [, $resultC] = $this->engine->run($state, 1, 'spanduk', 'rapi', 999);

        $this->assertSame($resultA, $resultB);
        $this->assertSame($a, $b);
        $this->assertNotSame($resultA['items'], $resultC['items']);
    }

    public function test_harga_jauh_di_atas_wajar_menurunkan_permintaan(): void
    {
        $base = $this->engine->buy($this->engine->newState('singkong'), ['keripik' => 60]);
        $base['event'] = ['effect' => ['demand' => 1.0, 'cost' => 1.0, 'cash' => 0]];
        $mahal = $this->engine->setPrices($base, ['keripik' => 14000]);

        [, $normal] = $this->engine->run($base, 1, 'tanpa', 'rapi', 7);
        [, $tinggi] = $this->engine->run($mahal, 1, 'tanpa', 'rapi', 7);

        $this->assertGreaterThan($tinggi['items'][0]['demand'], $normal['items'][0]['demand']);
    }

    public function test_menaikkan_harga_saat_rombongan_menurunkan_kepuasan_di_babak_ulasan(): void
    {
        $run = function (string $choice) {
            $state = $this->engine->buy($this->engine->newState('singkong'), ['keripik' => 40, 'es_teh' => 40]);
            $state['event'] = ['effect' => ['demand' => 1.7, 'cost' => 1.0, 'cash' => 0]];
            [$state] = $this->engine->run($state, 4, 'tanpa', $choice, 1);

            return $state;
        };

        $naik = $run('naik');
        $normal = $run('normal');
        $this->assertSame(6, $naik['pending'][0]['day']);
        $this->assertSame(-12, $naik['pending'][0]['sat']);

        $naik['event'] = $normal['event'] = ['effect' => ['demand' => 1.0, 'cost' => 1.0, 'cash' => 0]];
        [$afterNaik] = $this->engine->run($naik, 6, 'tanpa', 'abaikan', 2);
        [$afterNormal] = $this->engine->run($normal, 6, 'tanpa', 'abaikan', 2);
        $this->assertLessThan($afterNormal['satisfaction'], $afterNaik['satisfaction']);
    }

    public function test_hujan_membuat_stok_basah_terbuang(): void
    {
        $state = $this->engine->buy($this->engine->newState('singkong'), ['keripik' => 40]);
        $state['event'] = ['effect' => ['demand' => 0.5, 'cost' => 1.0, 'cash' => 0]];

        [, $result] = $this->engine->run($state, 2, 'tanpa', 'tutup', 3);

        $this->assertGreaterThan(0, $result['items'][0]['wasted']);
        $this->assertGreaterThan(0, $result['waste_value']);
    }

    public function test_nilai_akhir_selalu_dalam_rentang_0_sampai_100(): void
    {
        foreach (array_keys(GameRules::KINDS) as $kind) {
            foreach ([[0, 0, 0], [999999999, 0, 0], [-500000, 999999, 3], [100000, 50000, 10]] as [$profit, $waste, $negative]) {
                $state = $this->engine->newState($kind);
                $state['total_profit'] = $profit;
                $state['waste_value'] = $waste;
                $state['negative_days'] = $negative;
                $state['lost_total'] = 50;
                $state['demand_total'] = 60;
                $score = $this->engine->finalScore($state)['score'];
                $this->assertGreaterThanOrEqual(0, $score);
                $this->assertLessThanOrEqual(100, $score);
            }
        }
    }

    public function test_efek_kejadian_dijepit_ke_tema_babak(): void
    {
        $effect = $this->engine->clampEffect(2, ['demand' => 1.8, 'cost' => 3, 'cash' => 99999]);

        $this->assertSame(['demand' => 0.65, 'cost' => 1.05, 'cash' => 0], $effect);
    }

    public function test_jenis_warung_mengikuti_bidang_minat(): void
    {
        $this->assertSame('kelontong', GameRules::kindFor(['perdagangan-dan-kelontong']));
        $this->assertSame('oleh_oleh', GameRules::kindFor(['oleh-oleh-dan-suvenir']));
        $this->assertSame('singkong', GameRules::kindFor(['kuliner-dan-makanan-olahan']));
        $this->assertSame('singkong', GameRules::kindFor([]));
    }
}
