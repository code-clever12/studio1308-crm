<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Salon;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use RuntimeException;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

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

    public function __construct(private readonly StripeClient $stripe)
    {
    }

    /**
     * Appointments whose start time + grace period has passed and are still
     * awaiting completion. Step 4's scheduled job calls this and marks each
     * one as a no-show (and attempts to charge the fee).
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
     * repeated offenses. Does NOT charge the fee — see chargeFee().
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
     * Charges the no-show fee to the customer's card on file (saved via
     * setup_future_usage during their deposit payment). Throws a
     * RuntimeException — wrapping any Stripe decline/API error — if there's
     * no card on file or the off-session charge fails, so callers (the
     * ChargeNoShowFee job) can handle it uniformly without depending on
     * Stripe's exception types directly.
     */
    public function chargeFee(Appointment $appointment): Payment
    {
        $customer = $appointment->customer;

        if (! $customer->stripe_customer_id || ! $customer->stripe_payment_method_id) {
            throw new RuntimeException("No card on file for customer #{$customer->id}; cannot charge the no-show fee.");
        }

        $salon = Salon::query()->firstOrFail();
        $feeAmount = round((float) $salon->no_show_fee, 2);

        try {
            $intent = $this->stripe->paymentIntents->create([
                'amount' => (int) round($feeAmount * 100),
                'currency' => 'usd',
                'customer' => $customer->stripe_customer_id,
                'payment_method' => $customer->stripe_payment_method_id,
                'off_session' => true,
                'confirm' => true,
                'metadata' => [
                    'appointment_id' => $appointment->id,
                    'reason' => 'no_show_fee',
                ],
            ]);
        } catch (ApiErrorException $e) {
            throw new RuntimeException(
                "Failed to charge no-show fee for appointment #{$appointment->id}: {$e->getMessage()}",
                previous: $e,
            );
        }

        return Payment::create([
            'appointment_id' => $appointment->id,
            'customer_id' => $customer->id,
            'amount' => $feeAmount,
            'breakdown_json' => ['no_show_fee' => $feeAmount],
            'payment_method' => 'stripe_card',
            'stripe_payment_intent_id' => $intent->id,
            'stripe_charge_id' => $intent->latest_charge,
            'currency' => 'USD',
            'status' => $intent->status === 'succeeded' ? 'succeeded' : 'pending',
            'payment_date' => now(),
        ]);
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
