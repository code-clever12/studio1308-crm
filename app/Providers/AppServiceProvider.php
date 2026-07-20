<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bound via the container (rather than `new StripeClient(...)` inline
        // in each service) so tests can swap in a mock without a real API key.
        // Falls back to a placeholder key when Stripe isn't configured yet —
        // StripeClient's constructor requires a non-empty string, but nothing
        // should actually call the API in that state; callers check
        // filled(config('services.stripe.key')) first (see BookingController).
        $this->app->singleton(
            StripeClient::class,
            fn () => new StripeClient(config('services.stripe.secret') ?: 'sk_test_not_configured')
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('is-admin', fn (User $user) => $user->isAdmin());
        Gate::define('is-staff', fn (User $user) => $user->isStaff());
        Gate::define('is-customer', fn (User $user) => $user->isCustomer());

        // Admins manage the whole platform: short-circuit every ability check.
        Gate::before(fn (User $user, string $ability) => $user->isAdmin() ? true : null);
    }
}
