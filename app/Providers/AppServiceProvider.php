<?php

namespace App\Providers;

use App\Enums\RoleName;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale('id');

        RateLimiter::for('ai', fn (Request $request) => Limit::perMinute(12)->by($request->user()?->id ?: $request->ip()));

        // Admin memegang semua hak Pengelola.
        Gate::before(fn ($user, $ability) => $user->active_role === RoleName::Admin && $ability !== 'sign' ? true : null);

        VerifyEmail::toMailUsing(fn ($notifiable, string $url) => (new MailMessage)
            ->subject('Pastikan email kamu untuk alloBos')
            ->greeting('Halo '.$notifiable->firstName())
            ->line('Tekan tombol di bawah untuk memastikan email ini milik kamu.')
            ->action('Pastikan Email', $url)
            ->line('Bila kamu tidak mendaftar di alloBos, abaikan email ini.')
            ->salutation('Salam, tim alloBos'));

        ResetPassword::toMailUsing(fn ($notifiable, string $token) => (new MailMessage)
            ->subject('Atur ulang kata sandi alloBos')
            ->line('Kami menerima permintaan atur ulang kata sandi untuk akun kamu.')
            ->action('Atur Ulang Sandi', route('password.reset', ['token' => $token, 'email' => $notifiable->email]))
            ->line('Tautan ini berlaku 60 menit. Bila kamu tidak memintanya, abaikan email ini.')
            ->salutation('Salam, tim alloBos'));
    }
}
