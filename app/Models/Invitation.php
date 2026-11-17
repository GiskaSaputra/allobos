<?php

namespace App\Models;

use App\Enums\RoleName;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $fillable = ['email', 'role', 'invited_by', 'expires_at', 'accepted_at'];

    protected function casts(): array
    {
        return ['role' => RoleName::class, 'expires_at' => 'datetime', 'accepted_at' => 'datetime'];
    }

    public function usable(): bool
    {
        return $this->accepted_at === null && $this->expires_at->isFuture();
    }
}
