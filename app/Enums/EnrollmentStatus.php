<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case MenungguBayar = 'menunggu_bayar';
    case Terdaftar = 'terdaftar';
    case LulusHadir = 'lulus_hadir';
    case Batal = 'batal';

    public function label(): string
    {
        return match ($this) {
            self::MenungguBayar => 'Menunggu Pembayaran',
            self::LulusHadir => 'Lulus Hadir',
            default => ucfirst($this->value),
        };
    }

    /** Peserta sah kelas: sudah terdaftar (lunas bila berbayar) atau sudah lulus. Pendaftar yang belum membayar hanya menahan kursi. */
    public static function participating(): array
    {
        return [self::Terdaftar->value, self::LulusHadir->value];
    }

    public function tone(): string
    {
        return match ($this) {
            self::MenungguBayar => 'warning',
            self::Terdaftar => 'info',
            self::LulusHadir => 'success',
            self::Batal => 'neutral',
        };
    }
}
