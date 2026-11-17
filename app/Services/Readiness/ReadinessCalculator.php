<?php

namespace App\Services\Readiness;

use App\Enums\CertificateStatus;
use App\Enums\GameStatus;
use App\Enums\MentoringStatus;
use App\Enums\SessionStatus;
use App\Enums\ShopStatus;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\BossSession;
use App\Models\Certificate;
use App\Models\MentoringSession;
use App\Models\PodcastListen;
use App\Models\ReadinessSnapshot;
use App\Models\Setting;
use App\Models\ShopGame;
use App\Models\User;

/** Skor Siap Usaha dihitung dari bukti di basis data, bukan dari klaim pengguna (Konsep 4.10, AB-13). */
class ReadinessCalculator
{
    /** @return array<string,int> */
    public function weights(): array
    {
        return Setting::read('readiness.weights') ?: config('readiness.weights');
    }

    /** @return array<string,array{label:string,weight:int,fraction:float,points:float,detail:string}> */
    public function breakdown(User $user): array
    {
        $targets = config('readiness.targets');
        $parts = [];

        $profile = $user->profile;
        $filled = $profile ? collect([$profile->district_id, $profile->business_status, $profile->interests, $profile->capital_range, $profile->free_time, $profile->skills])->filter(fn ($v) => filled($v))->count() : 0;
        $parts['profil'] = [$profile?->onboarded_at ? 1.0 : $filled / 6, $profile?->onboarded_at ? 'Profil Usaha lengkap' : "{$filled} dari 6 isian terisi"];

        $consults = BossSession::query()->where('user_id', $user->id)->where('status', SessionStatus::Selesai->value)->count();
        $mentoring = MentoringSession::query()->where('user_id', $user->id)->where('status', MentoringStatus::Selesai->value)->count();
        $parts['konsultasi'] = [min(1, ($consults + $mentoring) / $targets['konsultasi_sesi']), "{$consults} sesi Ruang Bos, {$mentoring} sesi Temu Bos"];

        $attended = Attendance::query()->where('user_id', $user->id)->count();
        $certs = Certificate::query()->where('user_id', $user->id)->where('status', CertificateStatus::Tercatat->value)->count();
        $listened = PodcastListen::query()->where('user_id', $user->id)->whereNotNull('listened_at')->count();
        $parts['belajar'] = [min(1, ($attended + 2 * $certs + $listened) / $targets['belajar_butir']), "{$attended} kehadiran kelas, {$certs} sertifikat tercatat, {$listened} episode siniar"];

        $best = (int) ShopGame::query()->where('user_id', $user->id)->where('status', GameStatus::Selesai->value)->max('score');
        $parts['simulasi'] = [$best / 100, $best ? "Nilai terbaik Warung Bos {$best}" : 'Belum menamatkan Warung Bos 3D'];

        $plan = $user->plan()->whereNotNull('built_at')->exists();
        $steps = $user->steps()->count();
        $done = $user->steps()->whereNotNull('done_at')->count();
        $stepRatio = $steps ? $done / $steps : 0;
        $parts['rencana'] = [($plan ? 0.4 : 0) + 0.6 * $stepRatio, ($plan ? 'Rencana tersimpan' : 'Belum ada rencana').", {$done} dari {$steps} langkah selesai"];

        $shop = $user->shop;
        $published = $shop && $shop->status === ShopStatus::Terbit && $shop->products()->exists();
        $parts['lapak'] = [$published ? 1.0 : 0.0, $published ? 'Lapak terbit' : ($shop ? 'Lapak belum terbit' : 'Belum punya lapak')];

        $weights = $this->weights();
        $labels = config('readiness.labels');

        return collect($parts)->map(fn ($part, $key) => [
            'label' => $labels[$key],
            'weight' => (int) $weights[$key],
            'fraction' => round($part[0], 4),
            'points' => round($part[0] * $weights[$key], 2),
            'detail' => $part[1],
        ])->all();
    }

    public function score(array $breakdown): int
    {
        return (int) round(collect($breakdown)->sum('points'));
    }

    public function level(int $score): string
    {
        return collect(config('readiness.levels'))->filter(fn ($label, $min) => $score >= $min)->last();
    }

    /** Menghitung ulang dan menyimpan cuplikan bila nilainya berubah. */
    public function refresh(User $user, ?string $reason = null): int
    {
        $breakdown = $this->breakdown($user);
        $score = $this->score($breakdown);
        $last = ReadinessSnapshot::query()->where('user_id', $user->id)->latest('id')->value('score');

        if ($last === null || (int) $last !== $score) {
            ReadinessSnapshot::query()->create(['user_id' => $user->id, 'score' => $score, 'breakdown' => $breakdown, 'reason' => $reason]);
        }

        return $score;
    }

    /** Mencatat aktivitas lintas modul lalu menghitung ulang skor. */
    public function record(User $user, string $module, string $description, ?string $url = null): int
    {
        ActivityLog::query()->create(['user_id' => $user->id, 'module' => $module, 'description' => $description, 'url' => $url]);

        return $this->refresh($user, $description);
    }

    /** Langkah berikutnya: komponen dengan poin hilang terbesar (JU-01). */
    public function nextAction(User $user, array $breakdown): array
    {
        $key = collect($breakdown)->sortByDesc(fn ($part) => $part['weight'] * (1 - $part['fraction']))->keys()->first();

        if ($breakdown[$key]['fraction'] >= 1) {
            return ['key' => 'selesai', 'title' => 'Semua komponen sudah penuh', 'body' => 'Pertahankan dengan terus mencentang Langkah 30 Hari dan menjawab pesanan lapak.', 'button' => 'Lihat Jejak Usaha', 'route' => 'warga.journey'];
        }

        $hasPlan = $user->plan()->whereNotNull('built_at')->exists();

        return ['key' => $key] + match ($key) {
            'profil' => ['title' => 'Lengkapi Profil Usaha', 'body' => 'Profil membuat saran Bos Allo sesuai modal dan kapanewon kamu.', 'button' => 'Lengkapi Profil', 'route' => 'warga.onboarding.show'],
            'konsultasi' => ['title' => 'Ceritakan usahamu ke Bos Allo', 'body' => 'Satu sesi singkat menghasilkan Catatan Konsultasi dan langkah yang bisa langsung dikerjakan.', 'button' => 'Bicara dengan Bos Allo', 'route' => 'boss-room.setup'],
            'belajar' => ['title' => 'Ikut kelas dari pengusaha asli', 'body' => 'Hadir di kelas Bos atau dengarkan siniar untuk menambah nilai belajar.', 'button' => 'Lihat Kelas', 'route' => 'warga.programs.index'],
            'simulasi' => ['title' => 'Latihan di Warung Bos 3D', 'body' => 'Jalankan warung tujuh hari tanpa risiko modal sungguhan.', 'button' => 'Main Warung Bos 3D', 'route' => 'game.index'],
            'rencana' => $hasPlan
                ? ['title' => 'Kerjakan Langkah 30 Hari', 'body' => 'Centang langkah yang sudah selesai supaya skormu naik.', 'button' => 'Buka Langkah 30 Hari', 'route' => 'warga.steps.index']
                : ['title' => 'Temukan usaha yang cocok', 'body' => 'Bos Allo menyusun tiga peluang usaha dari profil dan riwayat belajarmu.', 'button' => 'Buka Peluang', 'route' => 'warga.opportunities.index'],
            'lapak' => ['title' => 'Buka lapak online', 'body' => 'Pajang produkmu dan terima pesanan lewat WhatsApp.', 'button' => 'Buka Lapak', 'route' => 'warga.shop.edit'],
        };
    }
}
