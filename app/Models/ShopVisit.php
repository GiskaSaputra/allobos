<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopVisit extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['shop_id', 'product_id', 'kind'];
}
