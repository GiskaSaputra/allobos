<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    public const MODULES = [
        'profil' => 'Profil',
        'ruang_bos' => 'Ruang Bos',
        'temu_bos' => 'Temu Bos',
        'kelas' => 'Kelas',
        'sertifikat' => 'Sertifikat',
        'siniar' => 'Siniar',
        'warung' => 'Warung 3D',
        'rencana' => 'Rencana',
        'cek_produk' => 'Cek Produk',
        'lapak' => 'Lapak',
    ];

    protected $fillable = ['user_id', 'module', 'description', 'url'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }
}
