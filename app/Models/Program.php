<?php

namespace App\Models;

use App\Enums\ProgramKind;
use App\Enums\ProgramStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    protected $fillable = ['title', 'slug', 'kind', 'business_field_id', 'description', 'location', 'quota', 'price', 'status', 'materials', 'is_official', 'is_demo', 'closed_at', 'created_by'];

    protected function casts(): array
    {
        return [
            'kind' => ProgramKind::class,
            'status' => ProgramStatus::class,
            'materials' => 'array',
            'is_official' => 'boolean',
            'is_demo' => 'boolean',
            'closed_at' => 'datetime',
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

    public function sessions(): HasMany
    {
        return $this->hasMany(ProgramSession::class)->orderBy('starts_at');
    }

    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(Speaker::class);
    }

    public function signers(): HasMany
    {
        return $this->hasMany(ProgramSigner::class)->orderBy('sort');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('status', '!=', ProgramStatus::Draf->value);
    }

    public function activeEnrollmentCount(): int
    {
        return $this->enrollments()->where('status', '!=', 'batal')->count();
    }

    public function isPaid(): bool
    {
        return $this->price > 0;
    }

    public function priceLabel(): string
    {
        return $this->isPaid() ? 'Rp'.number_format($this->price, 0, ',', '.') : 'Gratis';
    }

    public function seatsLeft(): int
    {
        return max(0, $this->quota - $this->activeEnrollmentCount());
    }

    public function startsAt(): ?CarbonInterface
    {
        return $this->sessions->first()?->starts_at;
    }

    public function endsAt(): ?CarbonInterface
    {
        return $this->sessions->last()?->ends_at;
    }

    public function hasStarted(): bool
    {
        return (bool) $this->startsAt()?->isPast();
    }

    public function isArchived(): bool
    {
        return $this->status === ProgramStatus::Selesai || (bool) $this->endsAt()?->isPast();
    }
}
