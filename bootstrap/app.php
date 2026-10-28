<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Uploaded media through the app (config cms.media_proxy). Registered outside the
            // "web" group: no session cookie, so the CDN can cache the response.
            Route::get('media/{path}', App\Http\Controllers\MediaController::class)
                ->where('path', '.*')
                ->name('media');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Behind Vercel's (or any host's) proxy: honour X-Forwarded-* so URLs use https.
        $middleware->trustProxies(at: '*');
        $middleware->redirectGuestsTo(fn () => route("admin.login"));
        $middleware->redirectUsersTo(fn () => route("admin.dashboard"));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
