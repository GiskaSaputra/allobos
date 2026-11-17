<?php

namespace App\Http\Middleware;

use App\Enums\BossStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveBoss
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->bossProfile?->status !== BossStatus::Aktif) {
            return redirect()->route('bos.dashboard')->with('error', 'Profil Bos kamu belum aktif. Tunggu kurasi Pengelola IPI sebelum membuka slot.');
        }

        return $next($request);
    }
}
