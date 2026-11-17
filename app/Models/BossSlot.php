<?php

namespace App\Models;

use App\Enums\MentoringStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BossSlot extends Model
{
    public const MODES = ['daring' => 'Daring', 'tatap_muka' => 'Tatap muka'];

    protected $fillable = ['boss_profile_id', 'starts_at', 'duration_minutes', 'mode'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime'];
    }

    public function boss(): BelongsTo
    {
        return $this->belongsTo(BossProfile::class, 'boss_profile_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(MentoringSession::class);
    }

    /** Slot yang belum ditahan sesi aktif. */
    public function scopeFree(Builder $query): Builder
    {
        $holding = array_map(fn ($s) => $s->value, array_filter(MentoringStatus::cases(), fn ($s) => $s->holdsSlot()));

        return $query->whereDoesntHave('sessions', fn ($q) => $q->whereIn('status', $holding));
    }

    /** AB-03: hanya dapat dipesan minimal 12 jam sebelumnya. */
    public function scopeBookable(Builder $query): Builder
    {
        return $query->free()->where('starts_at', '>=', now()->addHours(12));
    }

    public function isBookable(): bool
    {
        return static::query()->bookable()->whereKey($this->id)->exists();
    }
}
