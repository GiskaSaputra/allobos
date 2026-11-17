<?php

namespace App\Jobs;

use App\Models\ProductCheck;
use App\Services\Products\ProductChecker;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckProduct implements ShouldQueue
{
    use Queueable;

    public int $timeout = 90;

    public function __construct(public ProductCheck $check) {}

    public function handle(ProductChecker $checker): void
    {
        $checker->check($this->check);
    }
}
