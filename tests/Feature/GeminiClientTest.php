<?php

namespace Tests\Feature;

use App\Services\Ai\GeminiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_kunci_terkena_batas_dilewati_lalu_model_cadangan_dicoba(): void
    {
        config(['ai.keys' => ['k1', 'k2'], 'ai.model' => 'utama', 'ai.fallback_models' => ['cadangan']]);
        $ok = ['candidates' => [['content' => ['parts' => [['text' => json_encode(['summary' => 'Ringkas', 'lessons' => ['a', 'b', 'c']])]]]]]];
        Http::fake([
            '*/models/utama:*' => Http::sequence()->push([], 429)->push([], 503),
            '*/models/cadangan:*' => Http::response($ok),
        ]);

        $data = app(GeminiClient::class)->generateJson('podcast_summary', 'isi', [
            'type' => 'OBJECT',
            'properties' => ['summary' => ['type' => 'STRING'], 'lessons' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING']]],
            'required' => ['summary', 'lessons'],
        ]);

        $this->assertSame('Ringkas', $data['summary']);
        $keys = Http::recorded()->map(fn ($pair) => $pair[0]->header('x-goog-api-key')[0])->all();
        $this->assertSame(['k1', 'k2', 'k1'], $keys);
        Http::assertSent(fn (Request $request) => str_contains($request->url(), 'cadangan'));
    }
}
