<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleWrites
{
    private const PER_MINUTE = 120;

    /** Batas laju untuk semua permintaan yang mengubah data. */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe()) {
            return $next($request);
        }

        $key = 'tulis:'.($request->user()?->id ?? $request->ip());

        if (RateLimiter::tooManyAttempts($key, self::PER_MINUTE)) {
            abort(429, 'Terlalu banyak permintaan. Coba lagi dalam '.RateLimiter::availableIn($key).' detik.');
        }

        RateLimiter::hit($key, 60);

        return $next($request);
    }
}
