<?php

use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\HandleRedirects;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // anonymous deal-alert sign-ups; no account or session data involved
        $middleware->validateCsrfTokens(except: ['push/subscribe', 'push/unsubscribe']);

        $middleware->web(prepend: [
            HandleRedirects::class,
            CheckMaintenanceMode::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
