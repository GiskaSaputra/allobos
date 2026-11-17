<?php

namespace App\Models;

use App\Enums\CertificateStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    protected $fillable = ['code', 'program_id', 'user_id', 'status', 'recipient_name', 'issued_on', 'hash', 'pdf_path', 'chain_transaction_id', 'contract_address', 'show_full_name', 'show_on_shop', 'reason', 'revoked_at', 'revoke_transaction_id', 'is_demo'];

    protected function casts(): array
    {
        return [
            'status' => CertificateStatus::class,
            'issued_on' => 'date',
            'show_full_name' => 'boolean',
            'show_on_shop' => 'boolean',
            'revoked_at' => 'datetime',
            'is_demo' => 'boolean',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(ChainTransaction::class, 'chain_transaction_id');
    }

    public function revokeTransaction(): BelongsTo
    {
        return $this->belongsTo(ChainTransaction::class, 'revoke_transaction_id');
    }

    /** Format ALB-TAHUN-6 karakter tanpa huruf atau angka yang mudah tertukar. */
    public static function generateCode(): string
    {
        do {
            $code = 'ALB-'.now()->year.'-'.collect(range(1, 6))->map(fn () => 'ACDEFHJKMNPQRTUVWXY34679'[random_int(0, 23)])->implode('');
        } while (static::query()->where('code', $code)->exists());

        return $code;
    }

    public function displayName(): string
    {
        if ($this->show_full_name) {
            return $this->recipient_name;
        }

        return collect(preg_split('/\s+/', trim($this->recipient_name)))->filter()
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)).'.')->implode(' ');
    }

    public function verifyUrl(): string
    {
        return route('certificates.verify', $this->code);
    }
}
