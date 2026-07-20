<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

/**
 * Admins bypass every check here via Gate::before() in AppServiceProvider.
 */
class AppointmentPolicy
{
    /**
     * Staff can list their own appointments; customers can list their own bookings.
     */
    public function viewAny(User $user): bool
    {
        return $user->isStaff() || $user->isCustomer();
    }

    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->isCustomer()) {
            return $appointment->customer_id === $user->id;
        }

        if ($user->isStaff()) {
            return $user->staff?->id === $appointment->staff_id;
        }

        return false;
    }

    /**
     * Customers book their own appointments; staff/admin can create walk-ins
     * (admin handled by Gate::before, staff walk-in booking may be enabled later).
     */
    public function create(User $user): bool
    {
        return $user->isCustomer();
    }

    /**
     * Customers may reschedule their own upcoming appointment; staff may update
     * (e.g. mark in-progress/completed) appointments assigned to them.
     */
    public function update(User $user, Appointment $appointment): bool
    {
        if ($user->isCustomer()) {
            return $appointment->customer_id === $user->id
                && in_array($appointment->status, ['pending', 'confirmed'], true);
        }

        if ($user->isStaff()) {
            return $user->staff?->id === $appointment->staff_id;
        }

        return false;
    }

    /**
     * "Delete" here means cancel — customers can cancel their own upcoming appointment.
     */
    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->isCustomer()
            && $appointment->customer_id === $user->id
            && in_array($appointment->status, ['pending', 'confirmed'], true);
    }

    public function restore(User $user, Appointment $appointment): bool
    {
        return false;
    }

    public function forceDelete(User $user, Appointment $appointment): bool
    {
        return false;
    }
}
