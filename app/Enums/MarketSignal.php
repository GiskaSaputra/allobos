<?php

namespace App\Enums;

enum MarketSignal: string
{
    case Naik = 'naik';
    case Stabil = 'stabil';
    case Turun = 'turun';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function tone(): string
    {
        return match ($this) {
            self::Naik => 'success',
            self::Stabil => 'info',
            self::Turun => 'warning',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Naik => 'phosphor-trend-up',
            self::Stabil => 'phosphor-arrows-horizontal',
            self::Turun => 'phosphor-trend-down',
        };
    }
}
