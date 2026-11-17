<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReadinessSnapshot extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'score', 'breakdown', 'reason'];

    protected function casts(): array
    {
        return ['breakdown' => 'array', 'created_at' => 'datetime'];
    }
}
