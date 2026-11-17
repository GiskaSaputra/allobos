<?php

namespace App\Models;

use App\Enums\SessionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BossSession extends Model
{
    public const TOPICS = [
        'Mulai usaha dari nol',
        'Menentukan harga jual',
        'Menjual lebih jauh dari kampung',
        'Mengatur uang usaha',
        'Kemasan dan foto produk',
        'Menghadapi pesaing',
    ];

    protected $fillable = ['user_id', 'topic', 'mode', 'status', 'turns_count', 'started_at', 'last_activity_at', 'ended_at'];

    protected function casts(): array
    {
        return [
            'status' => SessionStatus::class,
            'started_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function turns(): HasMany
    {
        return $this->hasMany(BossTurn::class)->orderBy('id');
    }

    public function note(): HasOne
    {
        return $this->hasOne(ConsultNote::class);
    }
}
