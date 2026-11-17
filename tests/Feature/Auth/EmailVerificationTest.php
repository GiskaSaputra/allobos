<?php

namespace Tests\Feature\Auth;

use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_akun_belum_terverifikasi_diarahkan_ke_halaman_verifikasi(): void
    {
        $user = $this->userWithRole(attributes: ['email_verified_at' => null]);

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('verification.notice'));
        $this->actingAs($user)->get('/verifikasi-email')->assertOk()->assertSee('Pastikan email kamu');
    }

    public function test_tautan_verifikasi_menandai_email(): void
    {
        Event::fake();
        $user = $this->userWithRole(attributes: ['email_verified_at' => null]);

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->actingAs($user)->get($url)->assertRedirect(route('dashboard'));

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
