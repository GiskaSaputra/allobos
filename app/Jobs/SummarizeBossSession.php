<?php

namespace App\Jobs;

use App\Models\BossSession;
use App\Services\Boss\NoteBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SummarizeBossSession implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function __construct(public BossSession $session) {}

    public function handle(NoteBuilder $builder): void
    {
        $builder->build($this->session);
    }
}
