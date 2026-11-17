<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopDay extends Model
{
    protected $fillable = ['shop_game_id', 'day', 'decisions', 'event', 'result'];

    protected function casts(): array
    {
        return ['decisions' => 'array', 'event' => 'array', 'result' => 'array'];
    }
}
