<?php

namespace App\Listeners;

use App\Events\PaymentSucceeded;
use App\Services\NotificationService;

/**
 * Confirms the appointment once its deposit payment succeeds. Not yet
 * triggered anywhere — PaymentSucceeded is dispatched by Step 8's Stripe
 * webhook handler, which doesn't exist until that step lands.
 */
class RecordBookingConfirmation
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function handle(PaymentSucceeded $event): void
    {
        $event->appointment->update([
            'status' => 'confirmed',
            'payment_status' => $event->appointment->remaining_balance > 0 ? 'partial' : 'paid',
        ]);

        $this->notificationService->sendBookingConfirmation($event->appointment);
    }
}
