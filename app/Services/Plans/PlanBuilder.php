<?php

namespace App\Services\Plans;

use App\Enums\StepOrigin;
use App\Models\BusinessPlan;
use App\Models\Opportunity;
use App\Models\PlanStep;
use App\Models\User;
use App\Services\Ai\AiException;
use App\Services\Ai\FeatureSchemas;
use App\Services\Ai\GeminiClient;
use App\Services\Readiness\ReadinessCalculator;

class PlanBuilder
{
    private const CAPITAL_BY_RANGE = [
        'lt1' => [['Bahan baku awal', 350000], ['Peralatan sederhana', 250000], ['Kemasan dan label', 150000]],
        '1-5' => [['Bahan baku awal', 1000000], ['Peralatan', 1500000], ['Kemasan dan label', 400000]],
        '5-15' => [['Bahan baku awal', 2500000], ['Peralatan', 5000000], ['Kemasan, label, promosi', 1500000]],
        'gt15' => [['Bahan baku awal', 5000000], ['Peralatan', 10000000], ['Tempat dan promosi', 4000000]],
    ];

    public function __construct(
        private readonly GeminiClient $ai,
        private readonly LearningContext $context,
        private readonly ReadinessCalculator $readiness,
    ) {}

    public function build(User $user, Opportunity $opportunity): BusinessPlan
    {
        try {
            $data = $this->ai->generateJson('business_plan', $this->context->for($user)."\n\nUsaha tujuan: {$opportunity->title}\nAlasan: {$opportunity->why}\nPerkiraan modal: {$opportunity->capital_range}", FeatureSchemas::for('business_plan'), $user);
            $data['is_ai'] = true;
        } catch (AiException $e) {
            if ($e->isQuota()) {
                throw $e;
            }
            $data = $this->fallback($user, $opportunity);
        }

        $plan = BusinessPlan::query()->updateOrCreate(['user_id' => $user->id], [
            'opportunity_id' => $opportunity->id,
            'title' => $opportunity->title,
            'product' => $data['product'],
            'customers' => $data['customers'],
            'channels' => $data['channels'],
            'rivals' => $data['rivals'],
            'capital' => collect($data['capital'])->map(fn ($c) => ['name' => $c['name'], 'amount' => max(0, (int) $c['amount'])])->values()->all(),
            'risks' => array_values($data['risks']),
            'is_ai' => $data['is_ai'],
            'built_at' => now(),
        ]);

        // Langkah lama dari rencana yang belum dikerjakan diganti, langkah lain tetap.
        PlanStep::query()->where('user_id', $user->id)->where('origin', StepOrigin::Rencana->value)->whereNull('done_at')->delete();
        $sort = (int) PlanStep::query()->where('user_id', $user->id)->max('sort');
        foreach ($data['steps'] as $i => $step) {
            PlanStep::query()->create([
                'user_id' => $user->id,
                'week' => min(4, max(1, (int) $step['week'])),
                'title' => mb_substr($step['title'], 0, 250),
                'origin' => StepOrigin::Rencana,
                'sort' => $sort + $i + 1,
            ]);
        }

        $this->readiness->record($user, 'rencana', 'Menyusun Rencana Usaha: '.$plan->title, route('warga.plan.show'));

        return $plan;
    }

    private function fallback(User $user, Opportunity $opportunity): array
    {
        $district = $user->profile?->district?->name ?? 'Gunungkidul';

        return [
            'product' => "{$opportunity->title}. Mulai dari satu produk utama yang paling kamu kuasai, lalu tambah varian setelah ada pembeli tetap.",
            'customers' => "Tetangga dan warga sekitar {$district}, warung yang mau dititipi, dan wisatawan bila lokasimu dekat jalur wisata.",
            'channels' => 'Titip warung terdekat, jual langsung di pasar, dan terima pesanan lewat WhatsApp. Setelah ada foto produk yang bagus, buka Lapak alloBos.',
            'rivals' => 'Usaha serupa di pasar terdekat. Bedakan lewat rasa atau mutu, kemasan yang rapi, dan pelayanan yang ramah.',
            'capital' => collect(self::CAPITAL_BY_RANGE[$user->profile?->capital_range] ?? self::CAPITAL_BY_RANGE['lt1'])->map(fn ($c) => ['name' => $c[0], 'amount' => $c[1]])->all(),
            'risks' => [$opportunity->risk, 'Pembeli awal masih sedikit sehingga uang kembali lebih lama'],
            'steps' => [
                ['week' => 1, 'title' => 'Tanya harga bahan di tiga penjual berbeda'],
                ['week' => 1, 'title' => 'Hitung HPP di Kalkulator HPP'],
                ['week' => 2, 'title' => 'Buat contoh produk dan minta pendapat lima orang'],
                ['week' => 2, 'title' => 'Siapkan kemasan dan label sederhana'],
                ['week' => 3, 'title' => 'Titipkan produk ke dua warung terdekat'],
                ['week' => 3, 'title' => 'Foto produk dan periksa di Cek Produk'],
                ['week' => 4, 'title' => 'Buka Lapak alloBos dan bagikan tautannya'],
                ['week' => 4, 'title' => 'Catat penjualan dan untung bulan pertama'],
            ],
            'is_ai' => false,
        ];
    }
}
