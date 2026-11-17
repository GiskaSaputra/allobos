<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    public const SLUGS = ['tentang' => 'Tentang', 'bantuan' => 'Bantuan', 'privasi' => 'Privasi', 'syarat' => 'Syarat', 'kontak' => 'Kontak'];

    protected $fillable = ['slug', 'title', 'body', 'updated_by'];
}
