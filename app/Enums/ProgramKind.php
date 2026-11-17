<?php

namespace App\Enums;

enum ProgramKind: string
{
    case Seminar = 'seminar';
    case Workshop = 'workshop';
    case Bootcamp = 'bootcamp';
    case Mentoring = 'mentoring';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
