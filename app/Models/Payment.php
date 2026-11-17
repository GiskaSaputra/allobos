<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = ['user_id', 'enrollment_id', 'order_id', 'provider', 'provider_payment_id', 'amount', 'fee', 'status', 'payment_url', 'expires_at', 'paid_at', 'last_event'];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'last_event' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /** Masih bisa dibayar: menunggu dan belum lewat batas waktu. */
    public function isOpen(): bool
    {
        return $this->status === PaymentStatus::Menunggu && $this->payment_url && (! $this->expires_at || $this->expires_at->isFuture());
    }
}
