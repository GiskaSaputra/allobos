<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboarded
{
    /** Modul warga terkunci sampai Profil Usaha selesai (AB-01). */
    public function handle(Request $request, Closure $next): Response
    {
        $profile = $request->user()->profile;

        if (! $profile?->onboarded_at) {
            return redirect()->route('warga.onboarding.show', ['step' => $profile?->nextStep() ?? 1]);
        }

        return $next($request);
    }
}
