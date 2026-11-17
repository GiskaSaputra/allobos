<?php

namespace App\Enums;

enum ContentStatus: string
{
    case Draf = 'draf';
    case Terbit = 'terbit';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function tone(): string
    {
        return $this === self::Terbit ? 'success' : 'neutral';
    }
}
