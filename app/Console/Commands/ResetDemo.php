<?php

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Console\Command;

class ResetDemo extends Command
{
    protected $signature = 'allobos:demo {--force : Jalankan tanpa konfirmasi} {--jika-kosong : Hanya mengisi data demo bila belum ada pengguna, tanpa mengosongkan basis data}';

    protected $description = 'Mengosongkan basis data lalu mengisi ulang data acuan dan data demo';

    public function handle(): int
    {
        if ($this->option('jika-kosong')) {
            if (User::query()->exists()) {
                $this->info('Basis data sudah berisi pengguna, data demo tidak diisi ulang.');

                return self::SUCCESS;
            }
            $this->call('db:seed', ['--class' => DemoSeeder::class, '--force' => true]);

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Semua data akan dihapus dan diganti data demo. Lanjutkan?')) {
            return self::FAILURE;
        }

        $this->call('migrate:fresh', ['--force' => true]);
        $this->call('db:seed', ['--class' => DemoSeeder::class, '--force' => true]);

        return self::SUCCESS;
    }
}
