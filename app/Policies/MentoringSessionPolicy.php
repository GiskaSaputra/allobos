<?php

namespace App\Policies;

use App\Models\MentoringSession;
use App\Models\User;

class MentoringSessionPolicy
{
    public function viewAsWarga(User $user, MentoringSession $session): bool
    {
        return $session->user_id === $user->id;
    }

    public function viewAsBoss(User $user, MentoringSession $session): bool
    {
        return $session->boss->user_id === $user->id;
    }
}
