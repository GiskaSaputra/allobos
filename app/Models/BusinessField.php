<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessField extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'slug', 'kind', 'keywords', 'sort'];

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }

    public function podcasts(): HasMany
    {
        return $this->hasMany(Podcast::class);
    }

    public function bossProfiles(): HasMany
    {
        return $this->hasMany(BossProfile::class);
    }

    public function scopeUsaha(Builder $query): Builder
    {
        return $query->where('kind', 'usaha')->orderBy('sort');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByRaw("case when kind = 'usaha' then 0 else 1 end")->orderBy('sort');
    }
}
