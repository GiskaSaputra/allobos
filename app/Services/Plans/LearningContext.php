<?php

namespace App\Services\Plans;

use App\Enums\GameStatus;
use App\Models\Attendance;
use App\Models\ConsultNote;
use App\Models\PodcastListen;
use App\Models\ShopGame;
use App\Models\User;

/** Ringkasan riwayat warga untuk masukan AI, tanpa data pribadi. */
class LearningContext
{
    public function for(User $user): string
    {
        $notes = ConsultNote::query()->where('user_id', $user->id)->latest()->limit(3)->pluck('problem');
        $programs = Attendance::query()->where('attendances.user_id', $user->id)
            ->join('program_sessions', 'program_sessions.id', '=', 'attendances.program_session_id')
            ->join('programs', 'programs.id', '=', 'program_sessions.program_id')
            ->distinct()->limit(5)->pluck('programs.title');
        $podcasts = PodcastListen::query()->where('podcast_listens.user_id', $user->id)->whereNotNull('listened_at')
            ->join('podcasts', 'podcasts.id', '=', 'podcast_listens.podcast_id')->limit(5)->pluck('podcasts.title');
        $game = ShopGame::query()->where('user_id', $user->id)->where('status', GameStatus::Selesai->value)->orderByDesc('score')->first();

        return implode("\n", [
            'Profil:',
            $user->profile?->forAi() ?? '-',
            'Catatan konsultasi: '.($notes->implode('; ') ?: 'belum ada'),
            'Kelas yang dihadiri: '.($programs->implode(', ') ?: 'belum ada'),
            'Siniar yang didengar: '.($podcasts->implode(', ') ?: 'belum ada'),
            'Simulasi warung: '.($game ? "nilai {$game->score}, laba total ".number_format($game->state['total_profit'] ?? 0, 0, ',', '.').' rupiah' : 'belum pernah'),
        ]);
    }
}
