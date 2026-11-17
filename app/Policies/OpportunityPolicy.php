<?php

namespace App\Policies;

use App\Models\Opportunity;
use App\Models\User;

class OpportunityPolicy
{
    public function update(User $user, Opportunity $opportunity): bool
    {
        return $opportunity->user_id === $user->id;
    }
}
