<?php

namespace App\Enums;

enum MentoringStatus: string
{
    case Menunggu = 'menunggu';
    case Disetujui = 'disetujui';
    case Ditolak = 'ditolak';
    case Kedaluwarsa = 'kedaluwarsa';
    case Dibatalkan = 'dibatalkan';
    case Selesai = 'selesai';
    case TidakHadir = 'tidak_hadir';

    public function label(): string
    {
        return $this === self::TidakHadir ? 'Tidak Hadir' : ucfirst($this->value);
    }

    public function tone(): string
    {
        return match ($this) {
            self::Menunggu => 'warning',
            self::Disetujui => 'info',
            self::Selesai => 'success',
            self::Ditolak, self::TidakHadir => 'danger',
            self::Kedaluwarsa, self::Dibatalkan => 'neutral',
        };
    }

    /** Sesi yang masih menahan slot. */
    public function holdsSlot(): bool
    {
        return in_array($this, [self::Menunggu, self::Disetujui, self::Selesai, self::TidakHadir], true);
    }
}
