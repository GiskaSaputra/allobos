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

    public function toggle(PlanStep $step, bool $done): void
    {
        $step->update(['done_at' => $done ? ($step->done_at ?? now()) : null]);

        if ($done) {
            $this->readiness->record($step->user, 'rencana', 'Menyelesaikan langkah: '.$step->title, route('warga.steps.index'));
        } else {
            $this->readiness->refresh($step->user, 'Langkah dibuka lagi');
        }
    }

    public function remove(PlanStep $step): void
    {
        $step->delete();
        $this->readiness->refresh($step->user, 'Langkah dihapus');
    }

    /** @return array{total:int,done:int,percent:int} */
    public function progress(User $user): array
    {
        $total = PlanStep::query()->where('user_id', $user->id)->count();
        $done = PlanStep::query()->where('user_id', $user->id)->whereNotNull('done_at')->count();

        return ['total' => $total, 'done' => $done, 'percent' => $total ? (int) round($done / $total * 100) : 0];
    }
}
