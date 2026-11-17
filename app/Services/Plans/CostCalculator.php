<?php

namespace App\Services\Plans;

/** Hitungan HPP dan titik impas. Rumus berjalan tanpa AI (PU-05). */
class CostCalculator
{
    /**
     * @param  list<array{kind:string,amount:int|float}>  $items  bahan = per sekali produksi, tetap = per bulan
     * @return array{material:int,fixed:int,variable_unit:float,hpp_unit:float,margin:float,margin_pct:float,break_even_units:?int,break_even_batches:?int,profit_month:float,valid:bool}
     */
    public function calculate(array $items, int $unitsPerBatch, int $batchesPerMonth, int $price): array
    {
        $material = (int) collect($items)->where('kind', 'bahan')->sum('amount');
        $fixed = (int) collect($items)->where('kind', 'tetap')->sum('amount');
        $valid = $unitsPerBatch > 0 && $batchesPerMonth > 0;

        $variableUnit = $valid ? $material / $unitsPerBatch : 0.0;
        $hppUnit = $valid ? $variableUnit + $fixed / ($unitsPerBatch * $batchesPerMonth) : 0.0;
        $contribution = $price - $variableUnit;
        $breakEven = $valid && $contribution > 0 ? (int) ceil($fixed / $contribution) : null;

        return [
            'material' => $material,
            'fixed' => $fixed,
            'variable_unit' => round($variableUnit, 2),
            'hpp_unit' => round($hppUnit, 2),
            'margin' => round($price - $hppUnit, 2),
            'margin_pct' => $price > 0 ? round(($price - $hppUnit) / $price * 100, 1) : 0.0,
            'break_even_units' => $breakEven,
            'break_even_batches' => $breakEven !== null ? (int) ceil($breakEven / max(1, $unitsPerBatch)) : null,
            'profit_month' => $valid ? round(($price - $hppUnit) * $unitsPerBatch * $batchesPerMonth, 2) : 0.0,
            'valid' => $valid,
        ];
    }
}
