<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/** Suara Bos Allo lewat Speechify. Hasil disimpan per teks supaya Ulangi Jawaban tidak membayar dua kali. */
class VoiceClient
{
    private const ENDPOINT = 'https://api.speechify.ai/v1/audio/speech';

    public function configured(): bool
    {
        return filled(config('services.speechify.key'));
    }

    /**
     * @return string isi berkas MP3
     *
     * @throws AiException
     */
    public function speak(string $text): string
    {
        if (! $this->configured()) {
            throw AiException::noKey();
        }

        $config = config('services.speechify');
        $path = 'suara/'.sha1($config['voice'].'|'.$config['model'].'|'.$text).'.mp3';
        $disk = Storage::disk('local');
        if ($disk->exists($path)) {
            return $disk->get($path);
        }

        try {
            $response = Http::timeout(30)->withToken($config['key'])->post(self::ENDPOINT, [
                'input' => mb_substr($text, 0, 2000),
                'voice_id' => $config['voice'],
                'model' => $config['model'],
                'language' => $config['language'],
                'audio_format' => 'mp3',
            ]);
        } catch (ConnectionException) {
            throw AiException::http('sambungan suara gagal');
        }

        $audio = base64_decode((string) $response->json('audio_data'), true);
        if (! $response->successful() || ! $audio) {
            throw AiException::http('suara kode '.$response->status());
        }

        $disk->put($path, $audio);

        return $audio;
    }
}
