<?php

namespace App\Models;

use App\Enums\MentoringStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentoringSession extends Model
{
    protected $fillable = ['boss_slot_id', 'boss_profile_id', 'user_id', 'topic', 'consult_note_id', 'status', 'meeting_link', 'address', 'reason', 'boss_notes', 'boss_steps', 'rating', 'rating_comment', 'reminded_day_at', 'reminded_hour_at'];

    protected function casts(): array
    {
        return [
            'status' => MentoringStatus::class,
            'boss_steps' => 'array',
            'reminded_day_at' => 'datetime',
            'reminded_hour_at' => 'datetime',
        ];
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(BossSlot::class, 'boss_slot_id');
    }

    public function boss(): BelongsTo
    {
        return $this->belongsTo(BossProfile::class, 'boss_profile_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(ConsultNote::class, 'consult_note_id');
    }
}
