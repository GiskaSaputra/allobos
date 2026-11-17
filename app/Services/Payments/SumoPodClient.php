<?php

namespace App\Services\Payments;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/** Satu pintu ke SumoPod Managed Payment (QRIS). */
class SumoPodClient
{
    public function configured(): bool
    {
        return filled(config('services.sumopod.key'));
    }

    /**
     * Membuat tautan pembayaran QRIS.
     *
     * @return array{payment_id:?string, payment_link_url:string, status:?string, expires_at:?string, fee:?int}
     *
     * @throws RuntimeException
     */
    public function createPayment(string $orderId, int $amount, string $successUrl, string $cancelUrl): array
    {
        if (! $this->configured()) {
            throw new RuntimeException('Pembayaran online belum diatur. Hubungi pengelola kelas.');
        }

        try {
            $response = Http::timeout(20)
                ->withHeaders(['X-Api-Key' => config('services.sumopod.key')])
                ->acceptJson()
                ->post(rtrim(config('services.sumopod.base_url'), '/').'/api/v1/payments', [
                    'order_id' => $orderId,
                    'amount' => $amount,
                    'currency' => 'IDR',
                    'expires_in_hours' => config('services.sumopod.expires_in_hours'),
                    'success_return_url' => $successUrl,
                    'cancel_return_url' => $cancelUrl,
                    'payment_method_type_code' => 'QRIS',
                ]);
        } catch (ConnectionException) {
            throw new RuntimeException('Layanan pembayaran tidak bisa dihubungi. Coba lagi beberapa saat lagi.');
        }

        $data = $response->json();
        if (! $response->successful() || empty($data['payment_link_url'])) {
            throw new RuntimeException('Pembayaran belum bisa dibuat (kode '.$response->status().'). Coba lagi beberapa saat lagi.');
        }

        return [
            'payment_id' => $data['payment_id'] ?? null,
            'payment_link_url' => $data['payment_link_url'],
            'status' => $data['status'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'fee' => isset($data['fee']) ? (int) $data['fee'] : null,
        ];
    }

    /**
     * Webhook SumoPod sah bila token X-Webhook-Token cocok, atau tanda tangan Svix (HMAC SHA-256) cocok.
     * Paling tidak salah satu rahasia harus diatur, tanpa itu semua webhook ditolak.
     */
    public function verifyWebhook(string $rawBody, array $headers): bool
    {
        $token = config('services.sumopod.webhook_token');
        if ($token && hash_equals($token, (string) ($headers['x-webhook-token'] ?? ''))) {
            return true;
        }

        $secret = config('services.sumopod.webhook_secret');
        $id = (string) ($headers['svix-id'] ?? '');
        $timestamp = (string) ($headers['svix-timestamp'] ?? '');
        $signature = (string) ($headers['svix-signature'] ?? '');
        if (! $secret || $id === '' || $timestamp === '' || $signature === '' || abs(time() - (int) $timestamp) > 300) {
            return false;
        }

        $key = base64_decode(str_replace('whsec_', '', $secret));
        $expected = base64_encode(hash_hmac('sha256', "{$id}.{$timestamp}.{$rawBody}", $key, true));
        foreach (explode(' ', $signature) as $part) {
            $value = str_contains($part, ',') ? explode(',', $part, 2)[1] : $part;
            if (hash_equals($expected, $value)) {
                return true;
            }
        }

        return false;
    }
}
