<?php

namespace App\Models;

use App\Enums\GameStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopGame extends Model
{
    protected $fillable = ['user_id', 'kind', 'seed', 'status', 'day', 'phase', 'state', 'score', 'ending', 'debrief', 'saved_at'];

    protected function casts(): array
    {
        return ['status' => GameStatus::class, 'state' => 'array', 'debrief' => 'array', 'saved_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(ShopDay::class)->orderBy('day');
    }
}
