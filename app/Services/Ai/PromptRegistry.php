<?php

namespace App\Services\Ai;

use App\Models\AiPrompt;

class PromptRegistry
{
    public function active(string $feature): string
    {
        return AiPrompt::query()->where('feature', $feature)->where('is_active', true)->value('system_prompt')
            ?? config("ai.features.{$feature}.prompt");
    }

    public function activeVersion(string $feature): int
    {
        return (int) AiPrompt::query()->where('feature', $feature)->where('is_active', true)->value('version');
    }

    public function publish(string $feature, string $prompt, ?int $userId): AiPrompt
    {
        AiPrompt::query()->where('feature', $feature)->update(['is_active' => false]);

        return AiPrompt::query()->create([
            'feature' => $feature,
            'version' => (int) AiPrompt::query()->where('feature', $feature)->max('version') + 1,
            'system_prompt' => $prompt,
            'is_active' => true,
            'created_by' => $userId,
        ]);
    }
}
