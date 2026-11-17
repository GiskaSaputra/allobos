<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Enums\RoleName;
use App\Models\BusinessField;
use App\Models\Podcast;
use App\Models\PodcastListen;
use App\Services\Podcasts\PodcastService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PodcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_url_youtube_dikenali(): void
    {
        foreach (['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'https://youtu.be/dQw4w9WgXcQ?t=3', 'youtube.com/shorts/dQw4w9WgXcQ', 'https://m.youtube.com/watch?feature=share&v=dQw4w9WgXcQ'] as $url) {
            $this->assertSame('dQw4w9WgXcQ', PodcastService::youtubeId($url), $url);
        }
        $this->assertNull(PodcastService::youtubeId('https://vimeo.com/12345'));
    }

    public function test_pengelola_menambah_meringkas_menerbitkan_lalu_warga_mendengar(): void
    {
        $field = BusinessField::where('slug', 'kuliner-dan-makanan-olahan')->first();
        $warga = $this->onboardedWarga();
        config(['ai.keys' => ['kunci-uji']]);
        Http::fake([
            'www.youtube.com/oembed*' => Http::response(['title' => 'Merintis Keripik dari Dapur', 'thumbnail_url' => 'https://i.ytimg.com/vi/x/hq.jpg']),
            'generativelanguage.googleapis.com/*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => json_encode(['summary' => 'Ringkasan episode.', 'lessons' => ['Satu', 'Dua', 'Tiga']])]]]]]]),
        ]);

        $this->actingAs($this->userWithRole(RoleName::Pengelola));
        $this->post(route('pengelola.podcasts.fetch'), ['url' => 'https://vimeo.com/1'])->assertSessionHasErrors('url');
        $this->post(route('pengelola.podcasts.fetch'), ['url' => 'https://youtu.be/dQw4w9WgXcQ'])->assertRedirect();
        $podcast = Podcast::firstOrFail();
        $this->assertSame('Merintis Keripik dari Dapur', $podcast->title);

        $this->post(route('pengelola.podcasts.publish', $podcast))->assertSessionHas('error');
        $this->put(route('pengelola.podcasts.update', $podcast), ['title' => $podcast->title, 'business_field_id' => $field->id, 'duration_minutes' => 25])->assertSessionHas('status');
        $this->post(route('pengelola.podcasts.summarize', $podcast));
        $podcast->refresh();
        $this->assertSame('draf', $podcast->summary_state);
        $this->assertCount(3, $podcast->lessons);
        Http::assertSent(fn ($r) => str_contains(json_encode($r->data()), 'youtube.com\/watch?v=dQw4w9WgXcQ'));

        $this->get(route('podcasts.show', $podcast))->assertNotFound();
        $this->post(route('pengelola.podcasts.publish', $podcast))->assertSessionHas('status');
        $this->assertSame(ContentStatus::Terbit, $podcast->fresh()->status);
        $this->assertCount(1, $warga->notifications);

        $this->actingAs($warga)->get(route('podcasts.show', $podcast))->assertOk()->assertSee('Tandai Sudah Didengar')->assertSee('Ringkasan episode.');
        $this->post(route('warga.podcasts.listen', $podcast))->assertSessionHas('status');
        $this->post(route('warga.podcasts.note', $podcast), ['note' => 'Coba kemasan baru'])->assertSessionHas('status');
        $listen = PodcastListen::firstOrFail();
        $this->assertNotNull($listen->listened_at);
        $this->assertSame('Coba kemasan baru', $listen->note);
        $this->get(route('warga.dashboard'))->assertOk();
    }
}
