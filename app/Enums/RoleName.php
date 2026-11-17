<?php

namespace App\Enums;

enum RoleName: string
{
    case Warga = 'warga';
    case Bos = 'bos';
    case Pengelola = 'pengelola';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Warga => 'Warga',
            self::Bos => 'Bos',
            self::Pengelola => 'Pengelola IPI',
            self::Admin => 'Admin',
        };
    }

    public function homeRoute(): string
    {
        return match ($this) {
            self::Warga => 'warga.dashboard',
            self::Bos => 'bos.dashboard',
            self::Pengelola => 'pengelola.dashboard',
            self::Admin => 'admin.dashboard',
        };
    }
}
