<?php

namespace App\Support;

use Illuminate\Support\Str;

/** Penanda menu aktif di sidebar dan navigasi warga. */
class Nav
{
    /** Akhiran rute halaman utama sebuah bagian. Hanya rute seperti ini yang ikut menyala di halaman turunannya. */
    private const SECTION_ENDINGS = ['index', 'show', 'edit', 'setup'];

    /**
     * `admin.pages.index` menyala juga di `admin.pages.edit`, sedangkan `admin.audit` hanya menyala di halaman Audit.
     * Memotong `admin.audit` menjadi `admin.*` akan menyalakan semua menu admin sekaligus.
     */
    public static function active(string $route): bool
    {
        if (request()->routeIs($route, $route.'.*')) {
            return true;
        }

        return in_array(Str::afterLast($route, '.'), self::SECTION_ENDINGS, true)
            && request()->routeIs(Str::beforeLast($route, '.').'.*');
    }
}
