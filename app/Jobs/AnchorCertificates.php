<?php

namespace App\Jobs;

use App\Models\Program;
use App\Services\Certificates\AnchorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class AnchorCertificates implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 240;

    public function __construct(public Program $program) {}

    /** @return list<int> */
    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(AnchorService $anchors): void
    {
        $anchors->anchor($this->program);
    }

    public function failed(?Throwable $e): void
    {
        app(AnchorService::class)->anchorFailed($this->program, $e?->getMessage() ?? 'Tidak diketahui');
    }
}
