<?php

namespace App\Services\Podcasts;

use App\Enums\ContentStatus;
use App\Enums\RoleName;
use App\Models\AuditLog;
use App\Models\Podcast;
use App\Models\PodcastListen;
use App\Models\ResidentProfile;
use App\Models\User;
use App\Notifications\Pemberitahuan;
use App\Services\Ai\AiException;
use App\Services\Ai\GeminiClient;
use App\Services\Ai\Schema;
use App\Services\Readiness\ReadinessCalculator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use RuntimeException;

class PodcastService
{
    public function __construct(
        private readonly GeminiClient $ai,
        private readonly ReadinessCalculator $readiness,
    ) {}

    /** Id video dari tautan youtube.com/watch, youtu.be, shorts, live, atau embed. */
    public static function youtubeId(string $url): ?string
    {
        $pattern = '~^(?:https?://)?(?:www\.|m\.)?(?:youtube\.com/(?:watch\?(?:.*&)?v=|shorts/|live/|embed/)|youtu\.be/)([A-Za-z0-9_-]{11})(?:[?&#/].*)?$~';

        return preg_match($pattern, trim($url), $m) ? $m[1] : null;
    }

    /** Membuat draf episode dari URL. Judul dan sampul diambil dari oEmbed YouTube, boleh gagal. */
    public function fetch(string $url): Podcast
    {
        $id = self::youtubeId($url) ?? throw new RuntimeException('Alamat ini bukan tautan video YouTube yang dikenali. Contoh: https://www.youtube.com/watch?v=xxxxxxxxxxx');

        if ($existing = Podcast::query()->where('youtube_id', $id)->first()) {
            throw new RuntimeException('Video ini sudah ada sebagai episode "'.$existing->title.'".');
        }

        $meta = [];
        try {
            $response = Http::timeout(8)->get('https://www.youtube.com/oembed', ['url' => 'https://www.youtube.com/watch?v='.$id, 'format' => 'json']);
            $meta = $response->successful() ? $response->json() : [];
        } catch (\Throwable) {
            $meta = [];
        }

        $title = Str::limit($meta['title'] ?? 'Episode baru', 250, '');

        return Podcast::query()->create([
            'title' => $title,
            'slug' => $this->slug($title),
            'youtube_id' => $id,
            'thumbnail_url' => $meta['thumbnail_url'] ?? null,
            'status' => ContentStatus::Draf,
            'summary_state' => 'kosong',
        ]);
    }

    /** Ringkasan dan tiga pelajaran. Dicoba dari video, lalu dari judul dan catatan pengelola. */
    public function summarize(Podcast $podcast, ?User $user = null): void
    {
        $schema = Schema::obj(['summary' => Schema::str(), 'lessons' => Schema::arr(Schema::str(), 3, 3)]);
        $content = "Judul episode: {$podcast->title}\nBidang: ".($podcast->field?->name ?? '-')."\nCatatan pengelola:\n".($podcast->source_notes ?: '-');

        try {
            try {
                $data = $this->ai->generateJson('podcast_summary', $content."\n\nRingkas video terlampir.", $schema, $user, ['video_url' => 'https://www.youtube.com/watch?v='.$podcast->youtube_id]);
            } catch (AiException $e) {
                if ($e->isQuota() || ! $podcast->source_notes) {
                    throw $e;
                }
                $data = $this->ai->generateJson('podcast_summary', $content, $schema, $user);
            }
        } catch (AiException $e) {
            $podcast->update(['summary_state' => 'gagal']);

            return;
        }

        $podcast->update([
            'summary' => $data['summary'],
            'lessons' => array_slice($data['lessons'], 0, 3),
            'summary_is_ai' => true,
            'summary_state' => 'draf',
        ]);
    }

    public function publish(Podcast $podcast): void
    {
        if (! $podcast->summary || count($podcast->lessons ?? []) < 3) {
            throw new RuntimeException('Lengkapi ringkasan dan tiga pelajaran sebelum menerbitkan episode.');
        }
        if ($podcast->status === ContentStatus::Terbit) {
            throw new RuntimeException('Episode ini sudah terbit.');
        }

        $podcast->update(['status' => ContentStatus::Terbit, 'published_at' => now(), 'summary_state' => 'disetujui']);
        AuditLog::record('siniar.terbitkan', 'podcast:'.$podcast->id);

        if ($podcast->business_field_id) {
            $userIds = ResidentProfile::query()->whereJsonContains('interests', $podcast->business_field_id)->pluck('user_id');
            Notification::send(
                User::query()->whereIn('id', $userIds)->role(RoleName::Warga->value)->get(),
                new Pemberitahuan('Episode siniar baru sesuai minatmu', $podcast->title, route('podcasts.show', $podcast)),
            );
        }
    }

    public function markListened(User $user, Podcast $podcast): void
    {
        $listen = PodcastListen::query()->firstOrNew(['podcast_id' => $podcast->id, 'user_id' => $user->id]);
        if ($listen->listened_at) {
            return;
        }
        $listen->listened_at = now();
        $listen->save();

        $this->readiness->record($user, 'siniar', 'Mendengar siniar: '.$podcast->title, route('podcasts.show', $podcast));
    }

    public function saveNote(User $user, Podcast $podcast, ?string $note): void
    {
        PodcastListen::query()->updateOrCreate(['podcast_id' => $podcast->id, 'user_id' => $user->id], ['note' => $note]);
    }

    private function slug(string $title): string
    {
        $base = Str::slug(Str::limit($title, 80, '')) ?: 'episode';
        $slug = $base;
        for ($i = 2; Podcast::query()->where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }
}
