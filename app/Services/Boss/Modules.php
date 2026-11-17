<?php

namespace App\Services\Boss;

use Illuminate\Support\Facades\Route;

/** Modul alloBos yang boleh disarankan AI, beserta label dan rutenya. */
class Modules
{
    public const ALL = [
        'kelas' => ['Kelas Bos', 'warga.programs.index'],
        'siniar' => ['Siniar Bos', 'podcasts.index'],
        'warung' => ['Warung Bos 3D', 'game.index'],
        'peluang' => ['Peluang Usaha', 'warga.opportunities.index'],
        'rencana' => ['Rencana Usaha', 'warga.plan.show'],
        'cek_produk' => ['Cek Produk', 'warga.checks.index'],
        'lapak' => ['Lapak', 'warga.shop.edit'],
        'temu_bos' => ['Temu Bos', 'warga.mentoring.index'],
    ];

    /** @return list<array{key:string,label:string,url:?string}> */
    public static function links(array $keys): array
    {
        return collect($keys)->filter(fn ($k) => isset(self::ALL[$k]))->unique()->values()
            ->map(fn ($k) => ['key' => $k, 'label' => self::ALL[$k][0], 'url' => Route::has(self::ALL[$k][1]) ? route(self::ALL[$k][1]) : null])
            ->all();
    }
}
