<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopEvent extends Model
{
    protected $table = 'shop_event_bank';

    protected $fillable = ['day', 'kind', 'title', 'story', 'demand', 'cost', 'cash'];

    protected function casts(): array
    {
        return ['demand' => 'float', 'cost' => 'float', 'cash' => 'integer'];
    }
}
