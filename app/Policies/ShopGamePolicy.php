<?php

namespace App\Policies;

use App\Models\ShopGame;
use App\Models\User;

class ShopGamePolicy
{
    public function play(User $user, ShopGame $game): bool
    {
        return $game->user_id === $user->id;
    }
}
