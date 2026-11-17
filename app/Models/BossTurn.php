<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BossTurn extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['boss_session_id', 'role', 'content', 'meta'];

    protected function casts(): array
    {
        return ['meta' => 'array', 'created_at' => 'datetime'];
    }
}
