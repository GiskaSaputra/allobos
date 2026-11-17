<?php

namespace App\Services\Ai;

use Carbon\CarbonInterface;
use RuntimeException;

class AiException extends RuntimeException
{
    public function __construct(public readonly string $reason, string $message)
    {
        parent::__construct($message);
    }

    public static function disabled(string $feature): self
    {
        return new self('disabled', "Fitur AI {$feature} sedang dimatikan.");
    }

    public static function noKey(): self
    {
        return new self('no_key', 'Layanan AI belum diatur di server.');
    }

    public static function quota(CarbonInterface $resetsAt): self
    {
        return new self('quota', 'Jatah AI hari ini sudah habis. Bisa dipakai lagi '.$resetsAt->translatedFormat('l, H:i').' WIB.');
    }

    public static function http(string $detail): self
    {
        return new self('http', 'Layanan AI tidak menjawab: '.$detail);
    }

    public static function invalid(string $detail): self
    {
        return new self('invalid', 'Jawaban AI tidak sesuai bentuk yang diminta: '.$detail);
    }

    public function isQuota(): bool
    {
        return $this->reason === 'quota';
    }
}
