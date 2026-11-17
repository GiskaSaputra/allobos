<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Opportunity extends Model
{
    protected $fillable = ['user_id', 'batch', 'title', 'why', 'capital_range', 'risk', 'ipi_field', 'is_ai', 'is_custom', 'chosen_at'];

    protected function casts(): array
    {
        return ['is_ai' => 'boolean', 'is_custom' => 'boolean', 'chosen_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(BusinessField::class, 'ipi_field', 'slug');
    }
}
