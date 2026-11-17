<?php

namespace App\Models;

use App\Enums\ShopStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Shop extends Model
{
    protected $fillable = ['user_id', 'name', 'slug', 'description', 'district_id', 'business_field_id', 'whatsapp', 'cover_path', 'status', 'reason', 'published_at', 'is_demo'];

    protected function casts(): array
    {
        return ['status' => ShopStatus::class, 'published_at' => 'datetime', 'is_demo' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(BusinessField::class, 'business_field_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->orderBy('sort')->orderBy('id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(ShopVisit::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ShopStatus::Terbit->value);
    }

    public function coverUrl(): ?string
    {
        return $this->cover_path ? Storage::disk('public')->url($this->cover_path) : null;
    }

    public function hasPhotoProduct(): bool
    {
        return $this->products()->whereNotNull('photo_path')->exists();
    }
}
