<?php

namespace App\Enums;

enum GameStatus: string
{
    case Berjalan = 'berjalan';
    case Selesai = 'selesai';
    case Ditinggalkan = 'ditinggalkan';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function tone(): string
    {
        return match ($this) {
            self::Berjalan => 'info',
            self::Selesai => 'success',
            self::Ditinggalkan => 'neutral',
        };
    }
}
