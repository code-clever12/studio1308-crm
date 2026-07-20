<?php

namespace App\Policies;

use App\Models\Staff;
use App\Models\User;

/**
 * Admins bypass every check here via Gate::before() in AppServiceProvider.
 * Staff profiles (photo/bio/ratings) are public for the customer booking flow;
 * only the staff member themselves may edit their own profile, and only
 * admins can create/remove staff or change commission/bank details.
 */
class StaffPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Staff $staff): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Staff $staff): bool
    {
        return $user->isStaff() && $user->staff?->id === $staff->id;
    }

    public function delete(User $user, Staff $staff): bool
    {
        return false;
    }

    public function restore(User $user, Staff $staff): bool
    {
        return false;
    }

    public function forceDelete(User $user, Staff $staff): bool
    {
        return false;
    }
}
