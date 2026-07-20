<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
