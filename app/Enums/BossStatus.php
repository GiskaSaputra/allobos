<?php

namespace App\Enums;

enum BossStatus: string
{
    case Diajukan = 'diajukan';
    case Aktif = 'aktif';
    case Ditolak = 'ditolak';
    case Nonaktif = 'nonaktif';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function tone(): string
    {
        return match ($this) {
            self::Diajukan => 'warning',
            self::Aktif => 'success',
            self::Ditolak => 'danger',
            self::Nonaktif => 'neutral',
        };
    }
}
