<?php

namespace App\Events;

use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched once Step 8's Stripe webhook handler confirms a deposit
 * (+ optional tip) charge succeeded. Not yet dispatched anywhere until
 * that webhook handler lands — see RecordBookingConfirmation listener.
 */
class PaymentSucceeded
{
    use Dispatchable, SerializesModels;

    public function __construct(public Appointment $appointment, public Payment $payment) {}
}
