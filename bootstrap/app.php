<?php

use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);

        // Stripe's webhook calls are server-to-server — they can never carry
        // a Laravel session or CSRF token, so these routes must be exempt.
        $middleware->validateCsrfTokens(except: [
            'webhooks/stripe',
            'webhooks/stripe/payouts',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
