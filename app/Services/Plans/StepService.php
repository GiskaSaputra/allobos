<?php

namespace App\Services\Plans;

use App\Enums\StepOrigin;
use App\Models\PlanStep;
use App\Models\User;
use App\Services\Readiness\ReadinessCalculator;
use Illuminate\Support\Str;

class StepService
{
    public function __construct(private readonly ReadinessCalculator $readiness) {}

    /** Menambah langkah ke minggu pertama yang belum penuh. */
    public function addMany(User $user, array $titles, StepOrigin $origin, int $week = 1): void
    {
        $sort = (int) PlanStep::query()->where('user_id', $user->id)->max('sort');

        foreach (array_values($titles) as $i => $title) {
            PlanStep::query()->create([
                'user_id' => $user->id,
                'week' => $week,
                'title' => Str::limit(trim($title), 250, ''),
                'origin' => $origin,
                'sort' => $sort + $i + 1,
            ]);
        }

        $this->readiness->refresh($user, 'Langkah baru ditambahkan');
    }
}
