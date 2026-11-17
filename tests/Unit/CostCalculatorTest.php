<?php

namespace Tests\Unit;

use App\Services\Plans\CostCalculator;
use PHPUnit\Framework\TestCase;

class CostCalculatorTest extends TestCase
{
    public function test_hpp_margin_dan_titik_impas(): void
    {
        $r = (new CostCalculator)->calculate([
            ['kind' => 'bahan', 'amount' => 100000],
            ['kind' => 'tetap', 'amount' => 80000],
        ], 50, 8, 5000);

        $this->assertSame(2000.0, $r['variable_unit']);
        $this->assertSame(2200.0, $r['hpp_unit']);
        $this->assertSame(2800.0, $r['margin']);
        $this->assertSame(27, $r['break_even_units']);
        $this->assertSame(1, $r['break_even_batches']);
        $this->assertSame(1120000.0, $r['profit_month']);
    }

    public function test_harga_di_bawah_biaya_bahan_tidak_punya_titik_impas(): void
    {
        $r = (new CostCalculator)->calculate([['kind' => 'bahan', 'amount' => 100000]], 50, 8, 1500);

        $this->assertNull($r['break_even_units']);
        $this->assertLessThan(0, $r['margin']);
    }

    public function test_produksi_nol_tidak_valid(): void
    {
        $this->assertFalse((new CostCalculator)->calculate([], 0, 8, 5000)['valid']);
    }
}
