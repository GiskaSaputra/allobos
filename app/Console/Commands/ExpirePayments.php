<?php

namespace App\Console\Commands;

use App\Services\Payments\PaymentService;
use Illuminate\Console\Command;

class ExpirePayments extends Command
{
    protected $signature = 'allobos:pembayaran-kedaluwarsa';

    protected $description = 'Menandai pembayaran kelas yang lewat batas waktu dan melepas kursinya';

    public function handle(PaymentService $payments): int
    {
        $this->info($payments->expireStale().' pembayaran kedaluwarsa.');

        return self::SUCCESS;
    }
}
