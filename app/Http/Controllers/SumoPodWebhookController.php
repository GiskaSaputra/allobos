<?php

namespace App\Http\Controllers;

use App\Services\Payments\PaymentService;
use App\Services\Payments\SumoPodClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SumoPodWebhookController extends Controller
{
    public function __invoke(Request $request, SumoPodClient $gateway, PaymentService $payments): JsonResponse
    {
        $raw = $request->getContent();
        $headers = array_map(fn ($values) => $values[0] ?? '', array_change_key_case($request->headers->all(), CASE_LOWER));

        if (! $gateway->verifyWebhook($raw, $headers)) {
            return response()->json(['message' => 'Tanda tangan webhook tidak sah.'], 401);
        }

        $event = json_decode($raw, true);
        if (! is_array($event)) {
            return response()->json(['message' => 'Isi webhook tidak bisa dibaca.'], 422);
        }

        $payment = $payments->handle($event);

        return response()->json(['ok' => true, 'status' => $payment?->status->value]);
    }
}
