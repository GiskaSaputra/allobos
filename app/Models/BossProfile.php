<?php

namespace App\Models;

use App\Enums\BossStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BossProfile extends Model
{
    public const STANDARDS = [
        'aktif' => 'Bisnis aktif saat ini',
        'rekam_jejak' => 'Rekam jejak jelas',
        'pengalaman' => 'Pengalaman riil',
        'telusur' => 'Dapat ditelusuri',
    ];

    protected $fillable = ['user_id', 'business_name', 'business_desc', 'business_field_id', 'district_id', 'years_running', 'track_record', 'trace_url', 'photo_path', 'status', 'checklist', 'reject_reason', 'rating_avg', 'rating_count', 'is_demo'];

    protected function casts(): array
    {
        return ['status' => BossStatus::class, 'checklist' => 'array', 'rating_avg' => 'float', 'is_demo' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(BusinessField::class, 'business_field_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(BossSlot::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(MentoringSession::class);
    }
}
