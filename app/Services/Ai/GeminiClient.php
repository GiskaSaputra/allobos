<?php

namespace App\Services\Ai;

use App\Models\AiRequest;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/** Satu pintu ke Gemini: JSON berskema, kuota, log, penyamaran data, kunci bergiliran. */
class GeminiClient
{
    public function __construct(
        private readonly AiSettings $settings,
        private readonly PromptRegistry $prompts,
        private readonly PiiRedactor $redactor,
    ) {}

    /**
     * @param  array{image?:array{mime:string,data:string},prompt?:string,quota?:bool}  $options
     * @return array<string,mixed> Data sesuai skema. Bila penelusuran web aktif, ditambah kunci _sources.
     *
     * @throws AiException
     */
    public function generateJson(string $feature, string $content, array $schema, ?User $user = null, array $options = []): array
    {
        $config = $this->settings->for($feature);
        $counted = $options['quota'] ?? true;

        if (! $config['enabled']) {
            throw AiException::disabled($feature);
        }
        if (config('ai.keys') === []) {
            throw AiException::noKey();
        }
        if ($counted && $user && $this->quotaLeft($feature, $user) === 0) {
            throw AiException::quota($this->quotaResetsAt());
        }

        $payload = $this->payload($config, $options['prompt'] ?? $this->prompts->active($feature), $this->redactor->redact($content, $user), $schema, $options['image'] ?? null);
        $started = microtime(true);
        $tokens = 0;

        try {
            $data = $this->ask($config['model'], $payload, $schema, $tokens);
        } catch (AiException $first) {
            // Jawaban cacat diulang satu kali. Galat lain langsung diteruskan.
            try {
                $data = $first->reason === 'invalid' ? $this->ask($config['model'], $payload, $schema, $tokens) : throw $first;
            } catch (AiException $e) {
                $this->log($feature, $user, 'gagal', $started, $tokens, $e->reason);
                throw $e;
            }
        }

        $this->log($feature, $user, $counted ? 'ok' : 'uji', $started, $tokens);

        return $data;
    }

    /** Sisa jatah hari ini. null berarti tanpa batas. */
    public function quotaLeft(string $feature, User $user): ?int
    {
        $quota = $this->settings->for($feature)['quota'];
        if ($quota === null) {
            return null;
        }

        $used = AiRequest::query()
            ->where('user_id', $user->id)->where('feature', $feature)->where('status', 'ok')
            ->where('created_at', '>=', CarbonImmutable::now()->startOfDay())
            ->count();

        return max(0, $quota - $used);
    }

    public function quotaResetsAt(): CarbonImmutable
    {
        return CarbonImmutable::now()->addDay()->startOfDay();
    }

    private function payload(array $config, string $system, string $content, array $schema, ?array $image): array
    {
        $parts = [['text' => $content]];
        if ($image) {
            $parts[] = ['inlineData' => ['mimeType' => $image['mime'], 'data' => $image['data']]];
        }

        $payload = [
            'systemInstruction' => ['parts' => [['text' => $system]]],
            'contents' => [['role' => 'user', 'parts' => $parts]],
            'generationConfig' => ['temperature' => $config['temperature']],
        ];

        // Mode JSON berskema tidak bisa digabung dengan alat penelusuran, jadi bentuknya diminta lewat teks.
        if ($config['search']) {
            $payload['tools'] = [['google_search' => (object) []]];
            $payload['contents'][0]['parts'][0]['text'] .= "\n\nJawab hanya dengan satu objek JSON berbentuk: ".json_encode($this->shape($schema));
        } else {
            $payload['generationConfig'] += ['responseMimeType' => 'application/json', 'responseSchema' => $schema];
        }

        return $payload;
    }

    private function ask(string $model, array $payload, array $schema, int &$tokens): array
    {
        $body = $this->send($model, $payload);
        $tokens += (int) data_get($body, 'usageMetadata.totalTokenCount', 0);

        $text = collect(data_get($body, 'candidates.0.content.parts', []))->pluck('text')->implode('');
        $text = trim(preg_replace('/^```(?:json)?|```$/m', '', trim($text)));
        $data = json_decode($text, true);

        if (! is_array($data)) {
            throw AiException::invalid('bukan JSON');
        }
        if ($error = Schema::check($data, $schema)) {
            throw AiException::invalid($error);
        }

        $sources = collect(data_get($body, 'candidates.0.groundingMetadata.groundingChunks', []))
            ->map(fn ($chunk) => ['title' => data_get($chunk, 'web.title'), 'url' => data_get($chunk, 'web.uri')])
            ->filter(fn ($source) => $source['url'])->take(5)->values()->all();

        return $sources ? $data + ['_sources' => $sources] : $data;
    }

    /** Mencoba tiap kunci bergiliran. Kunci yang terkena batas dilewati. */
    private function send(string $model, array $payload): array
    {
        $keys = config('ai.keys');
        $start = (int) Cache::get('ai.key_index', 0);
        $last = 'tidak ada kunci';

        foreach (range(0, count($keys) - 1) as $offset) {
            $index = ($start + $offset) % count($keys);

            try {
                $response = Http::timeout(config('ai.timeout'))
                    ->withHeaders(['x-goog-api-key' => $keys[$index]])
                    ->post(config('ai.endpoint')."/models/{$model}:generateContent", $payload);
            } catch (ConnectionException $e) {
                throw AiException::http('sambungan gagal');
            }

            if ($response->successful()) {
                Cache::forever('ai.key_index', $index);

                return $response->json();
            }

            $last = 'kode '.$response->status();
            if (! in_array($response->status(), [401, 403, 429], true)) {
                break;
            }
        }

        throw AiException::http($last);
    }

    private function shape(array $schema): mixed
    {
        return match ($schema['type']) {
            'OBJECT' => array_map(fn ($child) => $this->shape($child), $schema['properties']),
            'ARRAY' => [$this->shape($schema['items'])],
            'STRING' => isset($schema['enum']) ? implode('|', $schema['enum']) : 'teks',
            default => 0,
        };
    }

    private function log(string $feature, ?User $user, string $status, float $started, int $tokens, ?string $error = null): void
    {
        AiRequest::query()->create([
            'user_id' => $user?->id,
            'feature' => $feature,
            'status' => $status,
            'duration_ms' => (int) round((microtime(true) - $started) * 1000),
            'tokens' => $tokens,
            'error' => $error,
        ]);
    }
}
