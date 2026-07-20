<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

/**
 * Admins bypass every check here via Gate::before() in AppServiceProvider.
 * The service catalog is public: anyone can browse it, only admins manage it.
 */
class ServicePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Service $service): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Service $service): bool
    {
        return false;
    }

    public function delete(User $user, Service $service): bool
    {
        return false;
    }

    public function restore(User $user, Service $service): bool
    {
        return false;
    }

    public function forceDelete(User $user, Service $service): bool
    {
        return false;
    }
}
