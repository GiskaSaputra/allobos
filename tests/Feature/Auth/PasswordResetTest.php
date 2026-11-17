<?php

namespace Tests\Feature\Auth;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_tautan_atur_ulang_dikirim_dan_dipakai(): void
    {
        Notification::fake();
        $user = $this->userWithRole();

        $this->get('/lupa-sandi')->assertOk();
        $this->post('/lupa-sandi', ['email' => $user->email])->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $this->get('/reset-sandi/'.$notification->token)->assertOk();
            $this->post('/reset-sandi', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'sandi-baru-123',
                'password_confirmation' => 'sandi-baru-123',
            ])->assertRedirect(route('login'));

            return Hash::check('sandi-baru-123', $user->fresh()->password);
        });
    }
}
