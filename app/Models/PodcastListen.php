<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PodcastListen extends Model
{
    protected $fillable = ['podcast_id', 'user_id', 'listened_at', 'note'];

    protected function casts(): array
    {
        return ['listened_at' => 'datetime'];
    }

    public function podcast(): BelongsTo
    {
        return $this->belongsTo(Podcast::class);
    }
}
