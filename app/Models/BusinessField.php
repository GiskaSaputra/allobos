<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BusinessField extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'slug', 'kind', 'keywords', 'sort'];

    public function scopeUsaha(Builder $query): Builder
    {
        return $query->where('kind', 'usaha')->orderBy('sort');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByRaw("case when kind = 'usaha' then 0 else 1 end")->orderBy('sort');
    }
}
