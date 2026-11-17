<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Menunggu = 'menunggu';
    case Lunas = 'lunas';
    case Gagal = 'gagal';
    case Kedaluwarsa = 'kedaluwarsa';

    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu Pembayaran',
            self::Lunas => 'Lunas',
            self::Gagal => 'Gagal',
            self::Kedaluwarsa => 'Kedaluwarsa',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Menunggu => 'warning',
            self::Lunas => 'success',
            self::Gagal => 'danger',
            self::Kedaluwarsa => 'neutral',
        };
    }
}
