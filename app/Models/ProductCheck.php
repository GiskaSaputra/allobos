<?php

namespace App\Models;

use App\Enums\CheckStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductCheck extends Model
{
    protected $fillable = ['user_id', 'photo_path', 'status', 'name', 'result', 'error'];

    protected function casts(): array
    {
        return ['status' => CheckStatus::class, 'result' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photoUrl(): string
    {
        return Storage::disk('public')->url($this->photo_path);
    }

    public function suggestedPrice(): int
    {
        $min = (int) data_get($this->result, 'price_min', 0);
        $max = (int) data_get($this->result, 'price_max', 0);

        return (int) (round(($min + $max) / 2 / 500) * 500);
    }
}
