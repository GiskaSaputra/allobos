<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case Terdaftar = 'terdaftar';
    case LulusHadir = 'lulus_hadir';
    case Batal = 'batal';

    public function label(): string
    {
        return $this === self::LulusHadir ? 'Lulus Hadir' : ucfirst($this->value);
    }

    public function tone(): string
    {
        return match ($this) {
            self::Terdaftar => 'info',
            self::LulusHadir => 'success',
            self::Batal => 'neutral',
        };
    }
}
