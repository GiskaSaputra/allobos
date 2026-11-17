<?php

namespace App\Jobs;

use App\Models\Podcast;
use App\Models\User;
use App\Services\Podcasts\PodcastService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SummarizePodcast implements ShouldQueue
{
    use Queueable;

    public int $timeout = 180;

    public function __construct(public Podcast $podcast, public ?User $user = null) {}

    public function handle(PodcastService $podcasts): void
    {
        $podcasts->summarize($this->podcast, $this->user);
    }
}
