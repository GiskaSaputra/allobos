<?php

namespace App\Policies;

use App\Models\PlanStep;
use App\Models\User;

class PlanStepPolicy
{
    public function update(User $user, PlanStep $step): bool
    {
        return $step->user_id === $user->id;
    }

    public function delete(User $user, PlanStep $step): bool
    {
        return $step->user_id === $user->id;
    }
}
