<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use RuntimeException;

class NoShowFeeService
{
    /**
     * Appointments still 'pending'/'confirmed' this long after their start
     * time are eligible to be auto-marked as a no-show.
     */
    private const GRACE_PERIOD_MINUTES = 30;

    /**
     * Customers with this many no-shows are auto-blocked from booking.
     */
    private const BLOCK_THRESHOLD = 3;

    /**
     * Appointments whose start time + grace period has passed and are still
     * awaiting completion. Step 4's scheduled job calls this and marks each
     * one as a no-show (and attempts to charge the fee, once Step 8 lands).
     *
     * @return Collection<int, Appointment>
     */
    public function findEligibleAppointments(): Collection
    {
        return Appointment::query()
            ->whereIn('status', ['pending', 'confirmed'])
            ->get()
            ->filter(function (Appointment $appointment) {
                $start = Carbon::parse("{$appointment->appointment_date->toDateString()} {$appointment->start_time}");

                return $start->addMinutes(self::GRACE_PERIOD_MINUTES)->isPast();
            })
            ->values();
    }

    /**
     * Mark an appointment as a no-show and auto-block the customer after
     * repeated offenses. Does NOT charge the fee — that requires the Stripe
     * card-on-file charge implemented in Step 8, orchestrated by the
     * scheduled job in Step 4.
     */
    public function markNoShow(Appointment $appointment): Appointment
    {
        if (! in_array($appointment->status, ['pending', 'confirmed'], true)) {
            throw new InvalidArgumentException('Only pending or confirmed appointments can be marked as a no-show.');
        }

        $appointment->update(['status' => 'no_show']);

        if ($this->shouldBlockCustomer($appointment->customer)) {
            $this->blockCustomer($appointment->customer);
        }

        return $appointment->fresh();
    }

    /**
     * Charges the no-show fee to the customer's card on file. Implemented in Step 8.
     */
    public function chargeFee(Appointment $appointment): Payment
    {
        throw new RuntimeException('Stripe no-show fee charging is implemented in Step 8.');
    }

    public function noShowCountForCustomer(User $customer): int
    {
        return Appointment::query()
            ->where('customer_id', $customer->id)
            ->where('status', 'no_show')
            ->count();
    }

    public function shouldBlockCustomer(User $customer): bool
    {
        return $this->noShowCountForCustomer($customer) >= self::BLOCK_THRESHOLD;
    }

    public function blockCustomer(User $customer): void
    {
        $customer->update(['is_active' => false]);
    }

    public function unblockCustomer(User $customer): void
    {
        $customer->update(['is_active' => true]);
    }
}
