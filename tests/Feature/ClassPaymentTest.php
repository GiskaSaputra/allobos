<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProgramKind;
use App\Enums\ProgramStatus;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Program;
use App\Services\Payments\SumoPodClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ClassPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function program(int $price, int $quota = 10): Program
    {
        $program = Program::create(['title' => 'Kelas Uji '.$price, 'slug' => 'kelas-uji-'.$price, 'kind' => ProgramKind::Workshop, 'description' => 'Uji', 'location' => 'Wonosari', 'quota' => $quota, 'price' => $price, 'status' => ProgramStatus::Dibuka]);
        $program->sessions()->create(['title' => 'Sesi 1', 'starts_at' => now()->addDays(3), 'ends_at' => now()->addDays(3)->addHours(2)]);

        return $program;
    }

    private function webhook(array $event, string $token = 'whtok_uji')
    {
        return $this->call('POST', route('webhooks.sumopod'), [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_WEBHOOK_TOKEN' => $token], json_encode($event));
    }

    public function test_kelas_gratis_perlu_konfirmasi_lalu_langsung_terdaftar(): void
    {
        $program = $this->program(0);
        $warga = $this->onboardedWarga();

        $this->actingAs($warga)->get(route('warga.programs.confirm', $program))->assertOk()->assertSee('Gratis')->assertSee('Konfirmasi Pendaftaran');
        $this->post(route('warga.programs.enroll', $program))->assertSessionHasErrors('agree');
        $this->assertSame(0, Enrollment::count());

        $this->post(route('warga.programs.enroll', $program), ['agree' => 1])->assertRedirect(route('warga.programs.index'));
        $this->assertSame(EnrollmentStatus::Terdaftar, Enrollment::first()->status);
        $this->assertSame(0, Payment::count());
    }

    public function test_kelas_berbayar_menahan_kursi_lalu_lunas_lewat_webhook(): void
    {
        config(['services.sumopod.key' => 'kunci-uji', 'services.sumopod.webhook_token' => 'whtok_uji']);
        Http::fake(['api-pay-sandbox.sumopod.com/*' => Http::response(['payment_id' => 'pay-1', 'payment_link_url' => 'https://pay.sumopod.com/uji', 'status' => 'pending', 'fee' => 650, 'expires_at' => now()->addHour()->toIso8601String()])]);
        $program = $this->program(50000);
        $warga = $this->onboardedWarga();

        $this->actingAs($warga)->post(route('warga.programs.enroll', $program), ['agree' => 1])->assertRedirect('https://pay.sumopod.com/uji');
        $enrollment = Enrollment::firstOrFail();
        $payment = Payment::firstOrFail();
        $this->assertSame(EnrollmentStatus::MenungguBayar, $enrollment->status);
        $this->assertSame(PaymentStatus::Menunggu, $payment->status);
        $this->assertSame(9, $program->fresh()->seatsLeft());
        Http::assertSent(fn ($request) => $request['amount'] === 50000 && $request['payment_method_type_code'] === 'QRIS' && $request->hasHeader('X-Api-Key', 'kunci-uji'));

        // Mendaftar lagi sebelum membayar memakai tautan yang sama, tidak membuat pembayaran baru.
        $this->post(route('warga.programs.enroll', $program), ['agree' => 1])->assertRedirect('https://pay.sumopod.com/uji');
        $this->assertSame(1, Payment::count());

        $this->webhook(['event_type' => 'payment.completed', 'data' => ['order_id' => $payment->order_id, 'amount' => 50000]], 'token-salah')->assertStatus(401);
        $this->assertSame(EnrollmentStatus::MenungguBayar, $enrollment->fresh()->status);

        $event = ['event_type' => 'payment.completed', 'data' => ['order_id' => $payment->order_id, 'amount' => 50000, 'status' => 'completed', 'paid_at' => now()->toIso8601String()]];
        $this->webhook($event)->assertOk()->assertJsonPath('status', 'lunas');
        $this->webhook($event)->assertOk();
        $this->assertSame(EnrollmentStatus::Terdaftar, $enrollment->fresh()->status);
        $this->assertSame(PaymentStatus::Lunas, $payment->fresh()->status);
        $this->assertSame(1, $warga->notifications()->where('data->title', 'Pendaftaran kelas berhasil')->count());

        $this->get(route('warga.payments.show', $payment))->assertOk()->assertSee('Pembayaran berhasil');
        $this->actingAs($this->onboardedWarga())->get(route('warga.payments.show', $payment))->assertForbidden();
    }

    public function test_jumlah_tidak_cocok_tidak_melunasi(): void
    {
        config(['services.sumopod.key' => 'kunci-uji', 'services.sumopod.webhook_token' => 'whtok_uji']);
        Http::fake(['api-pay-sandbox.sumopod.com/*' => Http::response(['payment_link_url' => 'https://pay.sumopod.com/uji'])]);
        $program = $this->program(50000);
        $this->actingAs($this->onboardedWarga())->post(route('warga.programs.enroll', $program), ['agree' => 1]);
        $payment = Payment::firstOrFail();

        $this->webhook(['event_type' => 'payment.completed', 'data' => ['order_id' => $payment->order_id, 'amount' => 1000]])->assertOk();
        $this->assertSame(PaymentStatus::Menunggu, $payment->fresh()->status);
    }

    public function test_pembayaran_kedaluwarsa_atau_gagal_dibuat_melepas_kursi(): void
    {
        config(['services.sumopod.key' => 'kunci-uji']);
        Http::fake(['api-pay-sandbox.sumopod.com/*' => Http::sequence()
            ->push(['payment_link_url' => 'https://pay.sumopod.com/uji', 'expires_at' => now()->subMinute()->toIso8601String()])
            ->push(['message' => 'error'], 500)]);
        $program = $this->program(25000, 1);
        $this->actingAs($this->onboardedWarga())->post(route('warga.programs.enroll', $program), ['agree' => 1]);
        $this->assertSame(ProgramStatus::Penuh, $program->fresh()->status);

        $this->artisan('allobos:pembayaran-kedaluwarsa')->assertSuccessful();
        $this->assertSame(PaymentStatus::Kedaluwarsa, Payment::first()->status);
        $this->assertSame(EnrollmentStatus::Batal, Enrollment::first()->status);
        $this->assertSame(ProgramStatus::Dibuka, $program->fresh()->status);

        $this->actingAs($this->onboardedWarga())->post(route('warga.programs.enroll', $program), ['agree' => 1])->assertSessionHas('error');
        $this->assertSame(1, $program->fresh()->seatsLeft());
    }

    public function test_tanda_tangan_svix_diverifikasi(): void
    {
        $secret = 'whsec_'.base64_encode('rahasia-uji-yang-cukup-panjang');
        config(['services.sumopod.webhook_token' => null, 'services.sumopod.webhook_secret' => $secret]);
        $client = app(SumoPodClient::class);
        $body = '{"event_type":"payment.test"}';
        $time = (string) time();
        $sig = base64_encode(hash_hmac('sha256', "msg_1.{$time}.{$body}", 'rahasia-uji-yang-cukup-panjang', true));

        $this->assertTrue($client->verifyWebhook($body, ['svix-id' => 'msg_1', 'svix-timestamp' => $time, 'svix-signature' => "v1,salah v1,{$sig}"]));
        $this->assertFalse($client->verifyWebhook($body.' ', ['svix-id' => 'msg_1', 'svix-timestamp' => $time, 'svix-signature' => "v1,{$sig}"]));
        $this->assertFalse($client->verifyWebhook($body, ['svix-id' => 'msg_1', 'svix-timestamp' => (string) (time() - 3600), 'svix-signature' => "v1,{$sig}"]));
    }
}
