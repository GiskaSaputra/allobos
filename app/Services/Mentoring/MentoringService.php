<?php

namespace App\Services\Mentoring;

use App\Enums\BossStatus;
use App\Enums\MentoringStatus;
use App\Enums\StepOrigin;
use App\Models\BossProfile;
use App\Models\BossSlot;
use App\Models\ConsultNote;
use App\Models\MentoringSession;
use App\Models\User;
use App\Notifications\Pemberitahuan;
use App\Services\Plans\StepService;
use App\Services\Readiness\ReadinessCalculator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MentoringService
{
    public function __construct(
        private readonly ReadinessCalculator $readiness,
        private readonly StepService $steps,
    ) {}

    public function book(User $user, BossSlot $slot, string $topic, ?int $noteId): MentoringSession
    {
        $session = DB::transaction(function () use ($user, $slot, $topic, $noteId) {
            $slot = BossSlot::query()->with('boss')->lockForUpdate()->findOrFail($slot->id);

            if ($slot->boss->status !== BossStatus::Aktif) {
                throw new RuntimeException('Bos ini sedang tidak menerima sesi.');
            }
            if ($slot->boss->user_id === $user->id) {
                throw new RuntimeException('Kamu tidak bisa memesan slot milikmu sendiri.');
            }
            if (! $slot->isBookable()) {
                throw new RuntimeException('Slot ini sudah terisi atau kurang dari 12 jam lagi. Pilih slot lain.');
            }
            $pending = MentoringSession::query()->where('user_id', $user->id)->where('boss_profile_id', $slot->boss_profile_id)
                ->where('status', MentoringStatus::Menunggu->value)->exists();
            if ($pending) {
                throw new RuntimeException('Kamu masih punya permintaan yang menunggu jawaban Bos ini. Tunggu jawabannya dulu.');
            }
            $note = $noteId ? ConsultNote::query()->where('user_id', $user->id)->find($noteId) : null;

            return MentoringSession::query()->create([
                'boss_slot_id' => $slot->id,
                'boss_profile_id' => $slot->boss_profile_id,
                'user_id' => $user->id,
                'topic' => $topic,
                'consult_note_id' => $note?->id,
                'status' => MentoringStatus::Menunggu,
            ]);
        });

        $session->load('slot', 'boss.user');
        $session->boss->user->notify(new Pemberitahuan(
            'Permintaan sesi baru',
            $user->name.' mengajukan sesi '.$session->slot->starts_at->translatedFormat('l, d F H:i').' WIB tentang: '.$topic,
            route('bos.sessions.show', $session),
            mail: true,
        ));

        return $session;
    }

    public function approve(MentoringSession $session, ?string $link, ?string $address): void
    {
        $this->expect($session, MentoringStatus::Menunggu);
        $session->load('slot');
        if ($session->slot->mode === 'daring' && ! $link) {
            throw new RuntimeException('Isi tautan rapat untuk sesi daring.');
        }
        if ($session->slot->mode === 'tatap_muka' && ! $address) {
            throw new RuntimeException('Isi alamat pertemuan untuk sesi tatap muka.');
        }

        $session->update(['status' => MentoringStatus::Disetujui, 'meeting_link' => $link, 'address' => $address]);
        $this->notifyWarga($session, 'Sesi Temu Bos disetujui', 'Sesi '.$session->slot->starts_at->translatedFormat('l, d F H:i').' WIB sudah disetujui. Detail pertemuan ada di halaman sesi.');
    }

    public function reject(MentoringSession $session, string $reason): void
    {
        $this->expect($session, MentoringStatus::Menunggu);
        $session->update(['status' => MentoringStatus::Ditolak, 'reason' => $reason]);
        $this->notifyWarga($session, 'Sesi Temu Bos ditolak', 'Alasan: '.$reason.'. Slot lain masih bisa kamu pilih.');
    }

    public function cancel(MentoringSession $session, User $by, ?string $reason = null): void
    {
        if (! in_array($session->status, [MentoringStatus::Menunggu, MentoringStatus::Disetujui], true)) {
            throw new RuntimeException('Sesi ini sudah tidak bisa dibatalkan.');
        }
        $session->load('slot', 'boss.user', 'user');
        $byWarga = $by->id === $session->user_id;

        if ($session->slot->starts_at->isPast()) {
            throw new RuntimeException('Waktu sesi sudah lewat, jadi tidak bisa dibatalkan.');
        }
        if ($byWarga && $session->slot->starts_at->lt(now()->addHours(6))) {
            throw new RuntimeException('Pembatalan oleh warga paling lambat 6 jam sebelum sesi. Hubungi Bos lewat pesan bila ada keperluan mendesak.');
        }

        $session->update(['status' => MentoringStatus::Dibatalkan, 'reason' => $reason ?: ($byWarga ? 'Dibatalkan warga' : 'Dibatalkan Bos')]);
        $other = $byWarga ? $session->boss->user : $session->user;
        $other->notify(new Pemberitahuan(
            'Sesi Temu Bos dibatalkan',
            'Sesi '.$session->slot->starts_at->translatedFormat('l, d F H:i').' WIB dibatalkan oleh '.$by->name.($reason ? '. Alasan: '.$reason : '.'),
            $byWarga ? route('bos.sessions.show', $session) : route('warga.mentoring.show', $session),
            mail: true,
        ));
    }

    public function complete(MentoringSession $session, string $notes, array $steps): void
    {
        $this->expect($session, MentoringStatus::Disetujui);
        $session->update(['status' => MentoringStatus::Selesai, 'boss_notes' => $notes, 'boss_steps' => array_values(array_filter(array_map('trim', $steps)))]);

        $url = route('warga.mentoring.show', $session);
        $this->notifyWarga($session, 'Catatan sesi Temu Bos', 'Bos sudah menulis catatan dan saran langkah. Jangan lupa beri penilaian.');
        $this->readiness->record($session->user, 'temu_bos', 'Sesi Temu Bos dengan '.$session->boss->user->name, $url);
    }

    public function noShow(MentoringSession $session): void
    {
        $this->expect($session, MentoringStatus::Disetujui);
        if ($session->slot->starts_at->isFuture()) {
            throw new RuntimeException('Tandai tidak hadir hanya setelah waktu sesi lewat.');
        }
        $session->update(['status' => MentoringStatus::TidakHadir]);
    }

    public function rate(MentoringSession $session, int $rating, ?string $comment): void
    {
        $this->expect($session, MentoringStatus::Selesai);
        if ($session->rating) {
            throw new RuntimeException('Sesi ini sudah kamu nilai.');
        }

        $session->update(['rating' => $rating, 'rating_comment' => $comment]);
        $this->refreshRating($session->boss);
    }

    public function addSteps(MentoringSession $session): int
    {
        $titles = $session->boss_steps ?? [];
        $this->steps->addMany($session->user, $titles, StepOrigin::TemuBos);

        return count($titles);
    }

    public function refreshRating(BossProfile $boss): void
    {
        $stats = MentoringSession::query()->where('boss_profile_id', $boss->id)->whereNotNull('rating')
            ->selectRaw('avg(rating) as avg, count(*) as total')->first();
        $boss->update(['rating_avg' => round((float) $stats->avg, 2), 'rating_count' => (int) $stats->total]);
    }

    /** Menunggu lebih dari 48 jam atau slotnya lewat menjadi Kedaluwarsa. */
    public function expire(): int
    {
        $sessions = MentoringSession::query()->with('slot', 'user', 'boss.user')
            ->where('status', MentoringStatus::Menunggu->value)
            ->where(fn ($q) => $q->where('created_at', '<', now()->subHours(48))->orWhereHas('slot', fn ($s) => $s->where('starts_at', '<', now())))
            ->get();

        foreach ($sessions as $session) {
            $session->update(['status' => MentoringStatus::Kedaluwarsa]);
            $this->notifyWarga($session, 'Permintaan Temu Bos kedaluwarsa', 'Bos belum menjawab dalam 48 jam, jadi permintaanmu ditutup. Kamu bisa memilih slot lain.');
        }

        return $sessions->count();
    }

    /** Pengingat sehari dan sejam sebelum sesi untuk kedua pihak. */
    public function remind(): int
    {
        $sent = 0;
        $approved = MentoringSession::query()->with('slot', 'user', 'boss.user')->where('status', MentoringStatus::Disetujui->value)
            ->whereHas('slot', fn ($q) => $q->whereBetween('starts_at', [now(), now()->addDay()]))->get();

        foreach ($approved as $session) {
            $start = $session->slot->starts_at;
            $field = $start->lte(now()->addHour()) ? 'reminded_hour_at' : 'reminded_day_at';
            if ($session->$field) {
                continue;
            }
            $when = $field === 'reminded_hour_at' ? 'sejam lagi' : 'besok';
            foreach ([[$session->user, route('warga.mentoring.show', $session)], [$session->boss->user, route('bos.sessions.show', $session)]] as [$person, $url]) {
                $person->notify(new Pemberitahuan('Pengingat Temu Bos', 'Sesi Temu Bos '.$when.', '.$start->translatedFormat('l, d F H:i').' WIB.', $url, mail: true));
            }
            $session->update([$field => now()]);
            $sent++;
        }

        return $sent;
    }

    private function expect(MentoringSession $session, MentoringStatus $status): void
    {
        if ($session->status !== $status) {
            throw new RuntimeException('Status sesi sudah berubah menjadi '.$session->status->label().'. Muat ulang halaman.');
        }
    }

    private function notifyWarga(MentoringSession $session, string $title, string $body): void
    {
        $session->user->notify(new Pemberitahuan($title, $body, route('warga.mentoring.show', $session), mail: true));
    }
}
