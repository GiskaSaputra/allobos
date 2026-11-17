<?php

namespace App\Jobs;

use App\Models\Certificate;
use App\Services\Certificates\AnchorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RevokeCertificate implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @return list<int> */
    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function __construct(public Certificate $certificate) {}

    public function handle(AnchorService $anchors): void
    {
        $anchors->revoke($this->certificate);
    }
}
