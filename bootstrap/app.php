<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureActiveBoss;
use App\Http\Middleware\EnsureOnboarded;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\ThrottleWrites;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [ThrottleWrites::class, EnsureAccountIsActive::class]);
        $middleware->alias([
            'role' => EnsureRole::class,
            'onboarded' => EnsureOnboarded::class,
            'boss.active' => EnsureActiveBoss::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
