<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = ['shop_id', 'name', 'photo_path', 'price', 'unit', 'description', 'in_stock', 'hidden', 'sort', 'product_check_id'];

    protected function casts(): array
    {
        return ['in_stock' => 'boolean', 'hidden' => 'boolean'];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }
}
