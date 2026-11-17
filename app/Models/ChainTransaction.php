<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChainTransaction extends Model
{
    protected $fillable = ['kind', 'program_id', 'chain_name', 'tx_hash', 'block_number', 'status', 'attempts', 'error'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
