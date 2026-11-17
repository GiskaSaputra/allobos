<?php

namespace App\Services\Payments;

use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Services\Programs\ProgramService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/** Pembayaran kelas berbayar. Status lunas hanya ditetapkan oleh webhook SumoPod yang sudah diverifikasi. */
class PaymentService
{
    public function __construct(
        private readonly SumoPodClient $gateway,
        private readonly ProgramService $programs,
    ) {}

    /**
     * Pembayaran yang masih terbuka dipakai ulang supaya warga tidak membayar dua kali.
     *
     * @throws RuntimeException
     */
    public function start(Enrollment $enrollment): Payment
    {
        $open = $enrollment->payments()->where('status', PaymentStatus::Menunggu->value)->latest('id')->first();
        if ($open?->isOpen()) {
            return $open;
        }

        $program = $enrollment->program;
        $orderId = 'KLS-'.$program->id.'-'.$enrollment->user_id.'-'.Str::upper(Str::random(6));
        $payment = $enrollment->payments()->create([
            'user_id' => $enrollment->user_id,
            'order_id' => $orderId,
            'amount' => $program->price,
            'status' => PaymentStatus::Menunggu,
        ]);
        $returnUrl = route('warga.payments.show', $payment);

        try {
            $created = $this->gateway->createPayment($orderId, $program->price, $returnUrl, $returnUrl);
        } catch (RuntimeException $e) {
            $payment->update(['status' => PaymentStatus::Gagal]);
            throw $e;
        }
        $payment->update([
            'provider_payment_id' => $created['payment_id'],
            'payment_url' => $created['payment_link_url'],
            'fee' => $created['fee'],
            'expires_at' => $created['expires_at'] ? CarbonImmutable::parse($created['expires_at']) : now()->addHours(config('services.sumopod.expires_in_hours')),
        ]);

        return $payment;
    }

    /** Menerapkan peristiwa webhook. Aman bila peristiwa yang sama dikirim lebih dari sekali. */
    public function handle(array $event): ?Payment
    {
        $data = $event['data'] ?? [];
        $payment = Payment::query()->where('order_id', (string) ($data['order_id'] ?? ''))->first();
        if (! $payment) {
            return null;
        }

        $type = (string) ($event['event_type'] ?? '');
        $confirmed = false;

        DB::transaction(function () use ($payment, $type, $event, $data, &$confirmed) {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $payment->last_event = $event;

            if ($type === 'payment.completed' && $payment->status !== PaymentStatus::Lunas) {
                if ((int) ($data['amount'] ?? 0) !== $payment->amount) {
                    $payment->save();

                    return;
                }
                $payment->fill(['status' => PaymentStatus::Lunas, 'paid_at' => isset($data['paid_at']) ? CarbonImmutable::parse($data['paid_at']) : now(), 'fee' => $data['fee'] ?? $payment->fee]);
                // Warga yang sudah membayar tetap mendapat kursinya walau batas waktu di sisi kita sempat lewat.
                $payment->enrollment->update(['status' => EnrollmentStatus::Terdaftar]);
                $confirmed = true;
            } elseif (in_array($type, ['payment.failed', 'payment.expired'], true) && $payment->status === PaymentStatus::Menunggu) {
                $payment->status = $type === 'payment.failed' ? PaymentStatus::Gagal : PaymentStatus::Kedaluwarsa;
                $this->programs->release($payment->enrollment);
            }
            $payment->save();
        });

        $payment->refresh();
        if ($confirmed) {
            $this->programs->syncStatus($payment->enrollment->program->fresh());
            $this->programs->confirmed($payment->enrollment);
        }

        return $payment;
    }

    /** Dijalankan terjadwal: pembayaran yang lewat batas waktu melepas kursinya. */
    public function expireStale(): int
    {
        $count = 0;
        Payment::query()->where('status', PaymentStatus::Menunggu->value)->where('expires_at', '<', now())->with('enrollment.program')
            ->each(function (Payment $payment) use (&$count) {
                $payment->update(['status' => PaymentStatus::Kedaluwarsa]);
                $this->programs->release($payment->enrollment);
                $count++;
            });

        return $count;
    }
}
