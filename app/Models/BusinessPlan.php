<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessPlan extends Model
{
    public const SECTIONS = [
        'product' => 'Produk',
        'customers' => 'Pembeli',
        'channels' => 'Cara jual',
        'rivals' => 'Pesaing',
    ];

    protected $fillable = ['user_id', 'opportunity_id', 'title', 'product', 'customers', 'channels', 'rivals', 'capital', 'risks', 'batch_units', 'selling_price', 'is_ai', 'built_at'];

    protected function casts(): array
    {
        return ['capital' => 'array', 'risks' => 'array', 'is_ai' => 'boolean', 'built_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function costItems(): HasMany
    {
        return $this->hasMany(CostItem::class);
    }

    public function capitalTotal(): int
    {
        return (int) collect($this->capital ?? [])->sum('amount');
    }
}
