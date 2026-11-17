<?php

namespace App\Enums;

enum ShopStatus: string
{
    case Draf = 'draf';
    case Menunggu = 'menunggu';
    case Terbit = 'terbit';
    case Tutup = 'tutup';
    case Diturunkan = 'diturunkan';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function tone(): string
    {
        return match ($this) {
            self::Draf, self::Tutup => 'neutral',
            self::Menunggu => 'warning',
            self::Terbit => 'success',
            self::Diturunkan => 'danger',
        };
    }
}
