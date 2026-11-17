<?php

namespace App\Enums;

enum SessionStatus: string
{
    case Disiapkan = 'disiapkan';
    case Berjalan = 'berjalan';
    case Dirangkum = 'dirangkum';
    case Selesai = 'selesai';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function tone(): string
    {
        return match ($this) {
            self::Disiapkan => 'neutral',
            self::Berjalan => 'info',
            self::Dirangkum => 'warning',
            self::Selesai => 'success',
        };
    }
}
