<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /** Peran aktif pengguna harus salah satu dari daftar dan memang dimilikinya. */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $active = $user->active_role->value;

        abort_unless(in_array($active, $roles, true) && $user->hasRole($active), 403);

        return $next($request);
    }
}
