<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramSigner extends Model
{
    protected $fillable = ['program_id', 'user_id', 'title', 'sort', 'signed_at', 'signed_ip', 'wallet_address', 'wallet_signature'];

    protected function casts(): array
    {
        return ['signed_at' => 'datetime'];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
