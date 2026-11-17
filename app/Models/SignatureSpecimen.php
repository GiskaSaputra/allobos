<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignatureSpecimen extends Model
{
    protected $fillable = ['user_id', 'path'];
}
