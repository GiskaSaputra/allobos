<?php

namespace App\Enums;

enum StepOrigin: string
{
    case Rencana = 'rencana';
    case RuangBos = 'ruang_bos';
    case TemuBos = 'temu_bos';
    case Sendiri = 'sendiri';

    public function label(): string
    {
        return match ($this) {
            self::Rencana => 'Dari Rencana',
            self::RuangBos => 'Dari Ruang Bos',
            self::TemuBos => 'Dari Temu Bos',
            self::Sendiri => 'Buatan sendiri',
        };
    }
}
