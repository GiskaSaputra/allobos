<?php

namespace App\Models;

use App\Enums\StepOrigin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanStep extends Model
{
    protected $fillable = ['user_id', 'week', 'title', 'origin', 'done_at', 'sort'];

    protected function casts(): array
    {
        return ['origin' => StepOrigin::class, 'done_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
