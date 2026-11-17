<?php

namespace App\Enums;

enum ProgramStatus: string
{
    case Draf = 'draf';
    case Dibuka = 'dibuka';
    case Penuh = 'penuh';
    case Berjalan = 'berjalan';
    case Selesai = 'selesai';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function tone(): string
    {
        return match ($this) {
            self::Draf, self::Selesai => 'neutral',
            self::Dibuka => 'success',
            self::Penuh => 'warning',
            self::Berjalan => 'info',
        };
    }
}
