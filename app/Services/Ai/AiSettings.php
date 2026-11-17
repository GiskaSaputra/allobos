<?php

namespace App\Services\Ai;

use App\Models\Setting;

class AiSettings
{
    /** @return array{label:string,enabled:bool,model:string,temperature:float,quota:?int,search:bool} */
    public function for(string $feature): array
    {
        $base = config("ai.features.{$feature}") ?? throw new \InvalidArgumentException("Fitur AI tidak dikenal: {$feature}");
        $saved = Setting::read("ai.{$feature}", []);

        return [
            'label' => $base['label'],
            'enabled' => (bool) ($saved['enabled'] ?? true),
            'model' => ($saved['model'] ?? null) ?: config('ai.model'),
            'temperature' => (float) ($saved['temperature'] ?? $base['temperature']),
            'quota' => array_key_exists('quota', $saved) ? $saved['quota'] : $base['quota'],
            'search' => (bool) ($saved['search'] ?? false),
        ];
    }

    public function save(string $feature, array $values): void
    {
        Setting::write("ai.{$feature}", $values);
    }

    /** @return list<string> */
    public function features(): array
    {
        return array_keys(config('ai.features'));
    }
}
