<?php

namespace App\Services\Game;

/**
 * Perhitungan permainan Warung Bos. Fungsi murni: keadaan masuk, keadaan dan hasil keluar.
 * Server adalah sumber kebenaran, peramban hanya menampilkan.
 */
class GameEngine
{
    public function newState(string $kind): array
    {
        $rules = GameRules::KINDS[$kind] ?? throw new GameException('Jenis warung tidak dikenal.');

        return [
            'kind' => $kind,
            'cash' => $rules['cash'],
            'start_cash' => $rules['cash'],
            'satisfaction' => 60,
            'cost_multiplier' => 1.0,
            'stock' => array_map(fn () => ['qty' => 0, 'cost' => 0.0], $rules['items']),
            'display' => array_map(fn () => 0, $rules['items']),
            'day_plan' => null,
            'prices' => array_map(fn ($item) => $item['fair'], $rules['items']),
            'pending' => [],
            'total_profit' => 0,
            'total_revenue' => 0,
            'waste_value' => 0,
            'lost_total' => 0,
            'demand_total' => 0,
            'negative_days' => 0,
            'event' => null,
            'last_result' => null,
        ];
    }

    /** Harga beli per unit hari ini, mengikuti kenaikan bahan sejak babak 5. */
    public function unitCost(array $state, string $item, ?array $event = null): int
    {
        $base = GameRules::KINDS[$state['kind']]['items'][$item]['cost'];
        $multiplier = max($state['cost_multiplier'], (float) ($event['effect']['cost'] ?? 1.0));

        return (int) (round($base * $multiplier / 100) * 100);
    }

    /** @param array<string,int> $quantities */
    public function buy(array $state, array $quantities): array
    {
        $items = GameRules::KINDS[$state['kind']]['items'];
        $total = 0;

        foreach ($quantities as $key => $qty) {
            if (! isset($items[$key])) {
                throw new GameException('Barang tidak dikenal.');
            }
            if (! is_int($qty) || $qty < 0 || $qty > 500) {
                throw new GameException('Jumlah belanja harus antara 0 dan 500.');
            }
            $total += $qty * $this->unitCost($state, $key, $state['event']);
        }

        if ($total > $state['cash']) {
            throw new GameException('Kas tidak cukup. Modal tidak boleh minus, kurangi jumlah belanja.');
        }

        foreach ($quantities as $key => $qty) {
            if ($qty === 0) {
                continue;
            }
            $old = $state['stock'][$key];
            $unit = $this->unitCost($state, $key, $state['event']);
            $newQty = $old['qty'] + $qty;
            $state['stock'][$key] = ['qty' => $newQty, 'cost' => round(($old['qty'] * $old['cost'] + $qty * $unit) / $newQty, 2)];
        }
        $state['cash'] -= $total;

        return $state;
    }

    /** @param array<string,int> $prices */
    public function setPrices(array $state, array $prices): array
    {
        foreach (GameRules::KINDS[$state['kind']]['items'] as $key => $item) {
            $price = $prices[$key] ?? $state['prices'][$key];
            if (! is_int($price) || $price < (int) ($item['cost'] * 0.5) || $price > $item['fair'] * 3) {
                throw new GameException('Harga '.$item['name'].' harus antara Rp'.number_format($item['cost'] * 0.5, 0, ',', '.').' dan Rp'.number_format($item['fair'] * 3, 0, ',', '.').'.');
            }
            $state['prices'][$key] = $price;
        }

        return $state;
    }

    /**
     * Menjalankan satu hari sekaligus (Mode Ringan): semua permintaan dilayani sebanyak stok.
     * Benih yang sama memberi hasil yang sama.
     *
     * @return array{0:array,1:array} keadaan baru dan hasil hari itu
     */
    public function run(array $state, int $day, string $promoKey, string $specialKey, int|string $seed): array
    {
        [$state, $plan] = $this->plan($state, $day, $promoKey, $specialKey, $seed);
        $sold = [];
        foreach ($plan['items'] as $key => $line) {
            $sold[$key] = min($line['demand'], $state['stock'][$key]['qty']);
        }

        return $this->settle($state, $plan, $sold);
    }

    /**
     * Bagian pertama hari: menerapkan efek tertunda dan menghitung permintaan tiap barang.
     *
     * @return array{0:array,1:array} keadaan baru dan rencana hari itu
     */
    public function plan(array $state, int $day, string $promoKey, string $specialKey, int|string $seed): array
    {
        $rules = GameRules::KINDS[$state['kind']];
        GameRules::THEMES[$day] ?? throw new GameException('Hari permainan tidak valid.');
        $promo = GameRules::PROMOS[$promoKey] ?? throw new GameException('Pilihan promosi tidak dikenal.');
        $special = GameRules::SPECIALS[$day]['options'][$specialKey] ?? throw new GameException('Pilih salah satu keputusan babak ini.');
        $event = $state['event'] ?? ['effect' => ['demand' => 1.0, 'cost' => 1.0, 'cash' => 0]];
        $rng = new Rng($seed.'-'.$day);

        $satBefore = $state['satisfaction'];
        $pendingDemand = 1.0;
        foreach ($state['pending'] as $i => $p) {
            if ($p['day'] === $day) {
                $state['satisfaction'] += $p['sat'];
                $pendingDemand *= $p['demand'];
                unset($state['pending'][$i]);
            }
        }
        $state['pending'] = array_values($state['pending']);
        $state['satisfaction'] = $this->clamp($state['satisfaction'], 0, 100);

        $sat = $state['satisfaction'];
        $satFactor = $day === 6 ? 0.6 + $sat * 0.008 : 0.85 + $sat * 0.003;
        $dayFactor = (float) $event['effect']['demand'] * $promo['demand'] * $special['demand'] * $pendingDemand * $satFactor;
        $priceMultiplier = $promo['price'] * ($special['price'] ?? 1.0);

        $items = [];
        $priceRatio = 0;
        foreach ($rules['items'] as $key => $item) {
            $price = (int) round($state['prices'][$key] * $priceMultiplier / 100) * 100;
            $priceFactor = $price <= $item['fair']
                ? min(1.4, 1 + ($item['fair'] - $price) / $item['fair'] * 0.8)
                : max(0.05, 1 - ($price - $item['fair']) / $item['fair'] * 1.6);
            $noise = $rng->between(0.9, 1.1);
            $items[$key] = ['price' => $price, 'demand' => (int) floor($item['demand'] * $dayFactor * $priceFactor * $noise)];
            $priceRatio += $state['prices'][$key] * $priceMultiplier / $item['fair'];
        }

        return [$state, [
            'day' => $day,
            'promo' => $promoKey,
            'special' => $specialKey,
            'sat_before' => $satBefore,
            'avg_price' => $priceRatio / count($rules['items']),
            'items' => $items,
        ]];
    }

    /**
     * Bagian kedua hari: menutup buku dari jumlah yang benar-benar terjual.
     *
     * @param  array<string,int>  $sold
     * @return array{0:array,1:array} keadaan baru dan hasil hari itu
     */
    public function settle(array $state, array $plan, array $sold): array
    {
        $rules = GameRules::KINDS[$state['kind']];
        $day = $plan['day'];
        $theme = GameRules::THEMES[$day];
        $promo = GameRules::PROMOS[$plan['promo']];
        $special = GameRules::SPECIALS[$day]['options'][$plan['special']];
        $event = $state['event'] ?? ['effect' => ['demand' => 1.0, 'cost' => 1.0, 'cash' => 0]];
        $rain = $theme['rain'] ?? false;

        $lines = [];
        $revenue = 0;
        $cogs = 0;
        $wasteValue = 0;
        $lostTotal = 0;
        $demandTotal = 0;
        $rescued = 0;

        foreach ($rules['items'] as $key => $item) {
            ['price' => $price, 'demand' => $demand] = $plan['items'][$key];
            $stock = $state['stock'][$key];
            $sold[$key] = max(0, min((int) ($sold[$key] ?? 0), $demand, $stock['qty']));
            $left = $stock['qty'] - $sold[$key];
            $unitCost = $stock['cost'] * ($special['cogs'] ?? 1.0);

            $spoilRate = max($item['spoil'], $rain ? $item['wet'] * ($special['wet'] ?? 1.0) : 0);
            $wasted = (int) round($left * $spoilRate);
            $rescue = isset($special['rescue']) ? (int) floor(($left - $wasted) * $special['rescue']) : 0;

            $revenue += $sold[$key] * $price + $rescue * (int) $stock['cost'];
            $cogs += $sold[$key] * $unitCost + $rescue * $stock['cost'];
            $wasteValue += $wasted * $stock['cost'];
            $rescued += $rescue;
            $lostTotal += $demand - $sold[$key];
            $demandTotal += $demand;

            $state['stock'][$key]['qty'] = $left - $wasted - $rescue;
            if ($state['stock'][$key]['qty'] === 0) {
                $state['stock'][$key]['cost'] = 0.0;
            }
            if (isset($state['display'][$key])) {
                $state['display'][$key] = min($state['display'][$key], $state['stock'][$key]['qty']);
            }

            $lines[] = ['key' => $key, 'name' => $item['name'], 'unit' => $item['unit'], 'price' => $price, 'demand' => $demand, 'sold' => $sold[$key], 'lost' => $demand - $sold[$key], 'left' => $state['stock'][$key]['qty'], 'wasted' => $wasted, 'revenue' => $sold[$key] * $price];
        }

        $fixed = (int) round($rules['fixed'] * ($special['fixed'] ?? 1.0));
        $eventCash = (int) $event['effect']['cash'];
        $profit = (int) round($revenue - $cogs - $promo['cost'] - $special['cost'] - $fixed + $eventCash);
        $state['cash'] += (int) round($revenue - $promo['cost'] - $special['cost'] - $fixed + $eventCash);

        $lostRatio = $demandTotal ? $lostTotal / $demandTotal : 0;
        $avgPrice = $plan['avg_price'];
        $satDelta = $promo['sat'] + $special['sat']
            + ($lostRatio > 0.3 ? -8 : ($lostRatio > 0.1 ? -3 : 1))
            + ($avgPrice > 1.15 ? -6 : ($avgPrice < 0.95 ? 3 : 0));
        $state['satisfaction'] = $this->clamp($state['satisfaction'] + $satDelta, 0, 100);

        foreach ($special['later'] ?? [] as $laterDay => $laterSat) {
            $state['pending'][] = ['day' => $laterDay, 'sat' => $laterSat, 'demand' => 1.0 + $laterSat / 100];
        }
        if ($day === 5) {
            $state['cost_multiplier'] = max($state['cost_multiplier'], (float) $event['effect']['cost']);
        }

        $state['total_profit'] += $profit;
        $state['total_revenue'] += (int) $revenue;
        $state['waste_value'] += (int) round($wasteValue);
        $state['lost_total'] += $lostTotal;
        $state['demand_total'] += $demandTotal;
        if ($state['cash'] < 0) {
            $state['negative_days']++;
        }

        $result = [
            'day' => $day,
            'items' => $lines,
            'revenue' => (int) $revenue,
            'cogs' => (int) round($cogs),
            'promo_cost' => $promo['cost'],
            'special_cost' => $special['cost'],
            'fixed_cost' => $fixed,
            'event_cash' => $eventCash,
            'profit' => $profit,
            'cash' => $state['cash'],
            'waste_value' => (int) round($wasteValue),
            'lost_total' => $lostTotal,
            'demand_total' => $demandTotal,
            'sold_total' => array_sum(array_column($lines, 'sold')),
            'rescued' => $rescued,
            'satisfaction_before' => $plan['sat_before'],
            'satisfaction_after' => $state['satisfaction'],
            'hit_target' => $profit >= $this->dailyTarget($state),
            'promo' => $plan['promo'],
            'special' => $plan['special'],
        ];
        $state['last_result'] = $result;

        return [$state, $result];
    }

    /**
     * Membagi permintaan hari itu menjadi paling banyak delapan pembeli 3D.
     * Satu pembeli bisa mewakili beberapa orang supaya satu hari tetap singkat dimainkan.
     *
     * @return list<array{id:int,items:array<string,int>,status:string}>
     */
    public function customers(array $plan, int|string $seed): array
    {
        $units = [];
        foreach ($plan['items'] as $key => $line) {
            array_push($units, ...array_fill(0, $line['demand'], $key));
        }
        if ($units === []) {
            return [];
        }

        $rng = new Rng($seed.'-pembeli-'.$plan['day']);
        for ($i = count($units) - 1; $i > 0; $i--) {
            $j = (int) floor($rng->float() * ($i + 1));
            [$units[$i], $units[$j]] = [$units[$j], $units[$i]];
        }

        $count = (int) min(8, max(1, ceil(count($units) / 4)));
        $customers = [];
        for ($i = 0; $i < $count; $i++) {
            $customers[$i] = ['id' => $i, 'items' => [], 'status' => 'menunggu'];
        }
        foreach ($units as $n => $key) {
            $items = &$customers[$n % $count]['items'];
            $items[$key] = ($items[$key] ?? 0) + 1;
            unset($items);
        }

        return $customers;
    }

    public function dailyTarget(array $state): int
    {
        return (int) round($state['start_cash'] * 0.12 / 1000) * 1000;
    }

    /** @return array{score:int,ending:string,parts:array} */
    public function finalScore(array $state): array
    {
        $start = $state['start_cash'];
        $parts = [
            'laba' => round($this->clamp($state['total_profit'] / ($start * 0.8), 0, 1) * 50, 1),
            'kepuasan' => round($state['satisfaction'] / 100 * 25, 1),
            'stok_terbuang' => round(15 * (1 - min(1, $state['waste_value'] / ($start * 0.3))), 1),
            'pembeli_terlayani' => round(10 * (1 - min(1, ($state['demand_total'] ? $state['lost_total'] / $state['demand_total'] : 0) * 1.5)), 1),
            'kas_minus' => -5 * min(3, $state['negative_days']),
        ];
        $score = (int) $this->clamp(round(array_sum($parts)), 0, 100);

        return ['score' => $score, 'ending' => $score >= 75 ? 'ramai' : ($score >= 45 ? 'bertahan' : 'belum'), 'parts' => $parts];
    }

    /** Menjepit efek kejadian ke rentang tema babak. */
    public function clampEffect(int $day, array $effect): array
    {
        $theme = GameRules::THEMES[$day];

        return [
            'demand' => round($this->clamp((float) ($effect['demand'] ?? 1), ...$theme['demand']), 2),
            'cost' => round($this->clamp((float) ($effect['cost'] ?? 1), ...$theme['cost']), 2),
            'cash' => (int) $this->clamp((int) ($effect['cash'] ?? 0), ...$theme['cash']),
        ];
    }

    private function clamp(float|int $value, float|int $min, float|int $max): float|int
    {
        return max($min, min($max, $value));
    }
}
