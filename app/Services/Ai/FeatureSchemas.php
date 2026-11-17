<?php

namespace App\Services\Ai;

use App\Enums\MarketSignal;
use App\Services\Boss\Modules;

/** Skema JSON tiap fitur AI (PRD bagian 5). Dipakai service dan halaman Uji Prompt. */
class FeatureSchemas
{
    public static function for(string $feature, array $context = []): array
    {
        $modules = array_keys(Modules::ALL);

        return match ($feature) {
            'boss_chat' => Schema::obj([
                'reply' => Schema::str(),
                'follow_up' => Schema::str(),
                'suggested_modules' => Schema::arr(Schema::str($modules), 0, 2),
            ]),
            'boss_summary' => Schema::obj([
                'problem' => Schema::str(),
                'advice' => Schema::arr(Schema::str(), 1, 4),
                'next_steps' => Schema::arr(Schema::str(), 1, 5),
                'modules' => Schema::arr(Schema::str($modules), 0, 3),
            ]),
            'opportunity' => Schema::obj([
                'items' => Schema::arr(Schema::obj([
                    'title' => Schema::str(),
                    'why' => Schema::str(),
                    'capital_range' => Schema::str(),
                    'risk' => Schema::str(),
                    'ipi_field' => Schema::str($context['fields'] ?? null),
                ]), 3, 3),
            ]),
            'business_plan' => Schema::obj([
                'product' => Schema::str(),
                'customers' => Schema::str(),
                'channels' => Schema::str(),
                'rivals' => Schema::str(),
                'capital' => Schema::arr(Schema::obj(['name' => Schema::str(), 'amount' => Schema::int()]), 1, 8),
                'risks' => Schema::arr(Schema::str(), 1, 4),
                'steps' => Schema::arr(Schema::obj(['week' => Schema::int(), 'title' => Schema::str()]), 4, 12),
            ]),
            'product_check' => Schema::obj([
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
            ]),
            'podcast_summary' => Schema::obj(['summary' => Schema::str(), 'lessons' => Schema::arr(Schema::str(), 3, 3)]),
            'shop_event' => Schema::obj([
                'title' => Schema::str(),
                'story' => Schema::str(),
                'effect' => Schema::obj(['demand' => Schema::num(), 'cost' => Schema::num(), 'cash' => Schema::num()]),
            ]),
            'shop_debrief' => Schema::obj(['best' => Schema::str(), 'worst' => Schema::str(), 'lessons' => Schema::arr(Schema::str(), 3, 3)]),
            default => throw new \InvalidArgumentException("Fitur AI tidak dikenal: {$feature}"),
        };
    }
}
