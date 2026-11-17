<?php

namespace App\Enums;

enum CertificateStatus: string
{
    case MenungguTandaTangan = 'menunggu_tanda_tangan';
    case Ditandatangani = 'ditandatangani';
    case MenungguPencatatan = 'menunggu_pencatatan';
    case Tercatat = 'tercatat';
    case Dicabut = 'dicabut';
    // Alur bisnis 4.3: nama dikeluarkan penanda tangan sebelum ditandatangani.
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::MenungguTandaTangan => 'Menunggu Tanda Tangan',
            self::MenungguPencatatan => 'Menunggu Pencatatan',
            default => ucfirst($this->value),
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::MenungguTandaTangan, self::MenungguPencatatan => 'warning',
            self::Ditandatangani => 'info',
            self::Tercatat => 'success',
            self::Dicabut => 'danger',
            self::Dibatalkan => 'neutral',
        };
    }

    public function downloadable(): bool
    {
        return in_array($this, [self::MenungguPencatatan, self::Tercatat], true);
    }
}
