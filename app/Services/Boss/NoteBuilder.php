<?php

namespace App\Services\Boss;

use App\Enums\SessionStatus;
use App\Models\BossSession;
use App\Models\ConsultNote;
use App\Services\Ai\AiException;
use App\Services\Ai\FeatureSchemas;
use App\Services\Ai\GeminiClient;
use App\Services\Readiness\ReadinessCalculator;
use Illuminate\Support\Str;

class NoteBuilder
{
    public function __construct(
        private readonly GeminiClient $ai,
        private readonly ReadinessCalculator $readiness,
    ) {}

    public function build(BossSession $session): ConsultNote
    {
        if ($session->note) {
            return $session->note;
        }

        $turns = $session->turns()->get();
        $transcript = $turns->map(fn ($t) => ($t->role === 'bos' ? 'Bos Allo: ' : 'Warga: ').$t->content)->implode("\n");

        try {
            $data = $this->ai->generateJson('boss_summary', "Topik: {$session->topic}\n\nPercakapan:\n{$transcript}", FeatureSchemas::for('boss_summary'), $session->user);
            $data['is_ai'] = true;
        } catch (AiException) {
            $data = $this->fallback($session);
        }

        $note = ConsultNote::query()->create([
            'boss_session_id' => $session->id,
            'user_id' => $session->user_id,
            'problem' => $data['problem'],
            'advice' => array_values($data['advice']),
            'next_steps' => array_values($data['next_steps']),
            'modules' => array_values($data['modules']),
            'is_ai' => $data['is_ai'],
        ]);

        $session->update(['status' => SessionStatus::Selesai, 'ended_at' => $session->ended_at ?? now()]);
        $this->readiness->record($session->user, 'ruang_bos', 'Konsultasi dengan Bos Allo: '.$session->topic, route('boss-room.note', $session));

        return $note;
    }

    /** Catatan tanpa AI: diringkas dari giliran yang tersimpan. */
    private function fallback(BossSession $session): array
    {
        $turns = $session->turns()->get();
        $firstWarga = $turns->firstWhere('role', 'warga')?->content;
        $advice = $turns->where('role', 'bos')->reject(fn ($t) => $t->meta['greeting'] ?? false)
            ->map(fn ($t) => Str::of($t->content)->before('. ')->finish('.')->limit(160)->toString())
            ->take(3)->values()->all();
        $modules = $turns->flatMap(fn ($t) => $t->meta['modules'] ?? [])->unique()->take(3)->values()->all();

        return [
            'problem' => $firstWarga ? Str::limit('Topik '.$session->topic.'. Cerita warga: '.$firstWarga, 300) : 'Topik '.$session->topic.'.',
            'advice' => $advice ?: ['Lanjutkan percakapan dengan Bos Allo atau ajukan sesi Temu Bos untuk saran lebih rinci.'],
            'next_steps' => [
                'Tulis tiga hal yang paling mengganjal dari usahamu minggu ini',
                'Hitung biaya membuat satu produk di Kalkulator HPP',
                'Pilih satu kelas atau siniar yang sesuai topik ini',
            ],
            'modules' => $modules ?: ['kelas', 'rencana'],
            'is_ai' => false,
        ];
    }
}
