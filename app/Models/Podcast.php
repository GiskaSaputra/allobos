<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Podcast extends Model
{
    protected $fillable = ['title', 'slug', 'youtube_id', 'thumbnail_url', 'business_field_id', 'duration_seconds', 'source_notes', 'summary', 'lessons', 'summary_is_ai', 'summary_state', 'status', 'published_at', 'is_demo'];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'lessons' => 'array',
            'summary_is_ai' => 'boolean',
            'published_at' => 'datetime',
            'is_demo' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(BusinessField::class, 'business_field_id');
    }

    public function listens(): HasMany
    {
        return $this->hasMany(PodcastListen::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Terbit->value);
    }

    public function thumbnail(): string
    {
        return $this->thumbnail_url ?: "https://i.ytimg.com/vi/{$this->youtube_id}/hqdefault.jpg";
    }

    public function durationLabel(): ?string
    {
        return $this->duration_seconds ? (int) ceil($this->duration_seconds / 60).' menit' : null;
    }
}
