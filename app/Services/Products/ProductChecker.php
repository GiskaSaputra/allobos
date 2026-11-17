<?php

namespace App\Services\Products;

use App\Enums\CheckStatus;
use App\Enums\MarketSignal;
use App\Models\ProductCheck;
use App\Notifications\Pemberitahuan;
use App\Services\Ai\AiException;
use App\Services\Ai\GeminiClient;
use App\Services\Ai\Schema;
use App\Services\Plans\CostCalculator;
use App\Services\Readiness\ReadinessCalculator;
use Illuminate\Support\Facades\Storage;

class ProductChecker
{
    public function __construct(
        private readonly GeminiClient $ai,
        private readonly CostCalculator $calculator,
        private readonly ReadinessCalculator $readiness,
    ) {}

    public function check(ProductCheck $check): void
    {
        $user = $check->user;

        try {
            $data = $this->ai->generateJson('product_check', $this->context($check), Schema::obj([
                'name' => Schema::str(),
                'category' => Schema::str(),
                'photo_score' => Schema::int(),
                'pack_score' => Schema::int(),
                'good' => Schema::arr(Schema::str(), 1, 3),
                'fixes' => Schema::arr(Schema::str(), 1, 3),
                'price_min' => Schema::int(),
                'price_max' => Schema::int(),
                'price_reason' => Schema::str(),
                'signal' => Schema::str(array_column(MarketSignal::cases(), 'value')),
                'reasons' => Schema::arr(Schema::str(), 1, 3),
                'description' => Schema::str(),
            ]), $user, [
                'image' => ['mime' => 'image/webp', 'data' => base64_encode(Storage::disk('public')->get($check->photo_path))],
            ]);
        } catch (AiException $e) {
            $check->update([
                'status' => CheckStatus::Gagal,
                'error' => $e->isQuota() ? $e->getMessage() : 'Layanan AI sedang tidak tersedia, jadi foto belum bisa diperiksa. Foto tetap tersimpan. Coba unggah lagi beberapa saat lagi.',
            ]);

            return;
        }

        $data['photo_score'] = min(4, max(1, (int) $data['photo_score']));
        $data['pack_score'] = min(4, max(1, (int) $data['pack_score']));
        [$data['price_min'], $data['price_max']] = [min($data['price_min'], $data['price_max']), max($data['price_min'], $data['price_max'])];
        $data['sources'] = $data['_sources'] ?? [];
        unset($data['_sources']);

        $check->update(['status' => CheckStatus::Selesai, 'name' => $data['name'], 'result' => $data, 'error' => null]);

        $url = route('warga.checks.show', $check);
        $user->notify(new Pemberitahuan('Hasil Cek Produk siap', 'Foto '.$data['name'].' sudah diperiksa. Lihat nilai, saran harga, dan deskripsinya.', $url));
        $this->readiness->record($user, 'cek_produk', 'Memeriksa produk: '.$data['name'], $url);
    }

    private function context(ProductCheck $check): string
    {
        $user = $check->user;
        $plan = $user->plan()->with('costItems')->first();
        $lines = ['Warga berjualan di Kapanewon '.($user->profile?->district?->name ?? 'Gunungkidul').'.'];

        if ($plan && $plan->selling_price > 0) {
            $cost = $this->calculator->calculate($plan->costItems->toArray(), $plan->batch_units, $plan->batches_per_month, $plan->selling_price);
            $lines[] = 'Rencana usaha: '.$plan->title.'. HPP per unit Rp'.(int) $cost['hpp_unit'].', harga jual rencana Rp'.$plan->selling_price.'. Pakai sebagai pembanding, jangan sarankan harga di bawah HPP.';
        }

        return implode("\n", $lines)."\nPeriksa foto produk terlampir.";
    }

    /** @return array{hpp:float,price:int}|null */
    public function planReference(ProductCheck $check): ?array
    {
        $plan = $check->user->plan()->with('costItems')->first();
        if (! $plan || $plan->selling_price <= 0) {
            return null;
        }
        $cost = $this->calculator->calculate($plan->costItems->toArray(), $plan->batch_units, $plan->batches_per_month, $plan->selling_price);

        return $cost['valid'] ? ['hpp' => $cost['hpp_unit'], 'price' => $plan->selling_price] : null;
    }
}
