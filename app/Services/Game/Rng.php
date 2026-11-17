<?php

namespace App\Services\Game;

/** Pembangkit acak deterministik (mulberry32) supaya benih yang sama selalu memberi hasil yang sama. */
class Rng
{
    private int $state;

    public function __construct(int|string $seed)
    {
        $this->state = crc32((string) $seed) & 0xFFFFFFFF;
    }

    public function float(): float
    {
        $this->state = ($this->state + 0x6D2B79F5) & 0xFFFFFFFF;
        $t = $this->state;
        $t = $this->mul($t ^ ($t >> 15), $t | 1);
        $t ^= ($t + $this->mul($t ^ ($t >> 7), $t | 61)) & 0xFFFFFFFF;

        return (($t ^ ($t >> 14)) & 0xFFFFFFFF) / 4294967296;
    }

    public function between(float $min, float $max): float
    {
        return $min + ($max - $min) * $this->float();
    }

    private function mul(int $a, int $b): int
    {
        return (int) ((($a & 0xFFFF) * $b + (((($a >> 16) * $b) & 0xFFFF) << 16)) & 0xFFFFFFFF);
    }
}
