<?php

namespace App\Policies;

use App\Models\BossSession;
use App\Models\User;

class BossSessionPolicy
{
    public function view(User $user, BossSession $session): bool
    {
        return $session->user_id === $user->id;
    }
}
