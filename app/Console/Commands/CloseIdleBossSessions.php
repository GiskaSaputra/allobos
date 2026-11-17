<?php

namespace App\Console\Commands;

use App\Enums\SessionStatus;
use App\Jobs\SummarizeBossSession;
use App\Models\BossSession;
use App\Models\BossTurn;
use Illuminate\Console\Command;

class CloseIdleBossSessions extends Command
{
    protected $signature = 'allobos:ruang-bos-rapikan';

    protected $description = 'Menutup sesi Ruang Bos yang diam lebih dari 30 menit dan menghapus transkrip lebih dari 180 hari';

    public function handle(): int
    {
        $idle = BossSession::query()
            ->where('status', SessionStatus::Berjalan->value)
            ->where('last_activity_at', '<', now()->subMinutes(config('features.idle_session_minutes')))
            ->get();

        foreach ($idle as $session) {
            if ($session->turns()->where('role', 'warga')->doesntExist()) {
                $session->delete();

                continue;
            }
            $session->update(['status' => SessionStatus::Dirangkum, 'ended_at' => now()]);
            SummarizeBossSession::dispatch($session);
        }

        // AB-16: transkrip dihapus setelah masa simpan, Catatan Konsultasi tetap.
        $pruned = BossTurn::query()->where('created_at', '<', now()->subDays(config('features.transcript_days')))->delete();

        $this->info("Ditutup {$idle->count()} sesi, dihapus {$pruned} giliran lama.");

        return self::SUCCESS;
    }
}
