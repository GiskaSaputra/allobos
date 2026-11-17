<?php

namespace App\Jobs;

use App\Models\Program;
use App\Services\Certificates\AnchorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateCertificates implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public function __construct(public Program $program) {}

    public function handle(AnchorService $anchors): void
    {
        $anchors->prepare($this->program);
        AnchorCertificates::dispatch($this->program);
    }
}
