<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiRequest extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'feature', 'status', 'duration_ms', 'tokens', 'error'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }
}
