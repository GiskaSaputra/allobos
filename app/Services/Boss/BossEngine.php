<?php

namespace App\Services\Boss;

use App\Enums\SessionStatus;
use App\Models\BossSession;
use App\Models\User;
use App\Services\Ai\AiException;
use App\Services\Ai\FeatureSchemas;
use App\Services\Ai\GeminiClient;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use RuntimeException;

class BossEngine
{
    // Topik yang tidak boleh dijawab dengan kepastian (RB-09).
    private const SENSITIVE = '/\b(pajak|hukum|gugat|pidana|pinjam|pinjaman|kredit|kur|utang|hutang|bunga bank|riba|izin usaha|nib|sertifikat halal|legal)\b/iu';

    public function __construct(private readonly GeminiClient $ai) {}

    public function sessionsPerDay(): int
    {
        return (int) config('features.boss_sessions_per_day', 5);
    }

    public function sessionsLeft(User $user): int
    {
        $used = BossSession::query()->where('user_id', $user->id)->whereNotNull('started_at')
            ->where('started_at', '>=', CarbonImmutable::now()->startOfDay())->count();

        return max(0, $this->sessionsPerDay() - $used);
    }

    public function turnsLeft(User $user): ?int
    {
        return $this->ai->quotaLeft('boss_chat', $user);
    }

    public function resetsAt(): CarbonImmutable
    {
        return $this->ai->quotaResetsAt();
    }

    public function create(User $user, string $topic, string $mode): BossSession
    {
        return BossSession::query()->create([
            'user_id' => $user->id,
            'topic' => Str::limit(trim($topic), 155, ''),
            'mode' => $mode,
            'status' => SessionStatus::Disiapkan,
        ]);
    }

    /** Memulai sesi dan menyapa. Sapaan disusun tanpa AI supaya tidak memakan jatah. */
    public function start(BossSession $session): string
    {
        if ($session->status === SessionStatus::Disiapkan) {
            if ($this->sessionsLeft($session->user) === 0) {
                throw new RuntimeException('Jatah sesi Ruang Bos hari ini sudah habis. Bisa dipakai lagi '.$this->resetsAt()->translatedFormat('l').' pukul 00.00 WIB.');
            }

            $session->update(['status' => SessionStatus::Berjalan, 'started_at' => now(), 'last_activity_at' => now()]);
        }

        $first = $session->turns()->where('role', 'bos')->first();
        if ($first) {
            return $first->content;
        }

        $profile = $session->user->profile;
        $greeting = 'Halo '.$session->user->firstName().', saya Bos Allo. '
            .($profile?->district ? 'Senang ngobrol dengan warga '.$profile->district->name.'. ' : '')
            .'Hari ini kita bahas "'.$session->topic.'". Coba ceritakan dulu kondisi usahamu sekarang, atau apa yang paling bikin kamu bingung?';

        $session->turns()->create(['role' => 'bos', 'content' => $greeting, 'meta' => ['greeting' => true]]);

        return $greeting;
    }

    /**
     * @return array{reply:string,follow_up:string,modules:list<array>}
     *
     * @throws AiException
     */
    public function reply(BossSession $session, string $message): array
    {
        $history = $session->turns()->latest('id')->limit(10)->get()->reverse()
            ->map(fn ($turn) => ($turn->role === 'bos' ? 'Bos Allo: ' : 'Warga: ').$turn->content)->implode("\n");

        $content = "Profil warga:\n".($session->user->profile?->forAi() ?? '-')
            ."\n\nTopik sesi: {$session->topic}\n\nPercakapan sebelumnya:\n{$history}\n\nPesan baru warga:\n{$message}";

        $data = $this->ai->generateJson('boss_chat', $content, FeatureSchemas::for('boss_chat'), $session->user);

        $modules = $data['suggested_modules'];
        if (preg_match(self::SENSITIVE, $message) && ! in_array('temu_bos', $modules, true)) {
            $modules[] = 'temu_bos';
        }

        $session->turns()->create(['role' => 'warga', 'content' => $message]);
        $session->turns()->create(['role' => 'bos', 'content' => $data['reply'], 'meta' => ['follow_up' => $data['follow_up'], 'modules' => $modules]]);
        $session->increment('turns_count');
        $session->update(['last_activity_at' => now()]);

        return ['reply' => $data['reply'], 'follow_up' => $data['follow_up'], 'modules' => Modules::links($modules)];
    }

    public function isSensitive(string $text): bool
    {
        return (bool) preg_match(self::SENSITIVE, $text);
    }
}
