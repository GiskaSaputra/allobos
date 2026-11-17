<?php

namespace App\Policies;

use App\Models\ProductCheck;
use App\Models\User;

class ProductCheckPolicy
{
    public function view(User $user, ProductCheck $check): bool
    {
        return $check->user_id === $user->id;
    }

    public function update(User $user, ProductCheck $check): bool
    {
        return $check->user_id === $user->id;
    }
}
