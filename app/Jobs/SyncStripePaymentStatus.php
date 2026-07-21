<?php

namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;

/**
 * Reconciles a Payment's status against Stripe's PaymentIntent (in case a
 * webhook was missed). Implemented in Step 8 once the Stripe SDK is wired
 * up; this job class exists now so PaymentController can queue it.
 */
class SyncStripePaymentStatus implements ShouldQueue
{
    use Queueable;

    public function __construct(public Payment $payment) {}

    public function handle(): void
    {
        throw new RuntimeException('Stripe payment status sync is implemented in Step 8.');
    }
}
