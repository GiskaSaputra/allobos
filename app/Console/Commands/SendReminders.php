<?php

namespace App\Console\Commands;

use App\Enums\EnrollmentStatus;
use App\Models\ProgramSession;
use App\Notifications\Pemberitahuan;
use App\Services\Mentoring\MentoringService;
use App\Services\Programs\ProgramService;
use Illuminate\Console\Command;

class SendReminders extends Command
{
    protected $signature = 'allobos:pengingat';

    protected $description = 'Mengirim pengingat sesi dan kelas, menutup permintaan Temu Bos kedaluwarsa, dan memperbarui status kelas';

    public function handle(MentoringService $mentoring, ProgramService $programs): int
    {
        $expired = $mentoring->expire();
        $reminded = $mentoring->remind();

        $classes = ProgramSession::query()->with('program')->whereNull('reminded_at')
            ->whereBetween('starts_at', [now(), now()->addDay()])->get();
        foreach ($classes as $session) {
            $enrollments = $session->program->enrollments()->with('user')->where('status', '!=', EnrollmentStatus::Batal->value)->get();
            foreach ($enrollments as $enrollment) {
                $enrollment->user->notify(new Pemberitahuan('Pengingat kelas', $session->program->title.', '.$session->title.', '.$session->starts_at->translatedFormat('l, d F H:i').' WIB di '.$session->program->location.'.', route('warga.programs.index'), mail: true));
            }
            $session->update(['reminded_at' => now()]);
        }

        $session = ProgramSession::query()->with('program')->where('starts_at', '<=', now())->where('ends_at', '>=', now()->subDay())->get();
        $session->pluck('program')->unique('id')->each(fn ($program) => $programs->syncStatus($program));

        $this->info("Kedaluwarsa {$expired}, pengingat Temu Bos {$reminded}, pengingat kelas {$classes->count()}.");

        return self::SUCCESS;
    }
}
