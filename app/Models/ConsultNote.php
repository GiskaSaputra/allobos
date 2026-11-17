<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultNote extends Model
{
    protected $fillable = ['boss_session_id', 'user_id', 'problem', 'advice', 'next_steps', 'modules', 'is_ai', 'steps_added_at'];

    protected function casts(): array
    {
        return ['advice' => 'array', 'next_steps' => 'array', 'modules' => 'array', 'is_ai' => 'boolean', 'steps_added_at' => 'datetime'];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(BossSession::class, 'boss_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
