<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    public const AUDIENCES = ['semua' => 'Semua pengguna', 'warga' => 'Warga', 'bos' => 'Bos'];

    protected $fillable = ['title', 'body', 'audience', 'published_at', 'created_by'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function scopeVisibleTo(Builder $query, string $role): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now())
            ->whereIn('audience', ['semua', $role])->latest('published_at');
    }
}
