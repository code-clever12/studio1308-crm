<?php

namespace App\Listeners;

use App\Events\PaymentSucceeded;
use App\Services\NotificationService;

/**
 * Confirms the appointment once its deposit payment succeeds. Triggered by
 * PaymentController's payment_intent.succeeded webhook handler.
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
