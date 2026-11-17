<?php

namespace App\Enums;

enum CheckStatus: string
{
    case Diperiksa = 'diperiksa';
    case Selesai = 'selesai';
    case Gagal = 'gagal';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function tone(): string
    {
        return match ($this) {
            self::Diperiksa => 'warning',
            self::Selesai => 'success',
            self::Gagal => 'danger',
        };
    }
}
