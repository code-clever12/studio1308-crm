<?php

use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
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

        // Only trusted when explicitly configured (see docs/DEPLOYMENT.md) —
        // trusting X-Forwarded-* headers from an unconfigured source would
        // let a client spoof its own IP/scheme, so this stays off by default
        // rather than defaulting to '*'.
        if ($trustedProxies = env('TRUSTED_PROXIES')) {
            $middleware->trustProxies(at: $trustedProxies === '*' ? '*' : explode(',', $trustedProxies));
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // No-op (reports nothing) whenever SENTRY_LARAVEL_DSN is unset.
        Integration::handles($exceptions);
    })->create();
