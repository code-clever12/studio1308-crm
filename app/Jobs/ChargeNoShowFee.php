<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Services\NoShowFeeService;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Charges the no-show fee to the customer's card on file. Dispatched by
 * MarkNoShowAppointments. NoShowFeeService::chargeFee() wraps any Stripe
 * decline/API error (or a missing card on file) as a RuntimeException, so
 * this job logs and exits cleanly rather than failing the queue for a
 * legitimately-declined charge.
 */
class ChargeNoShowFee implements ShouldQueue
{
    use Queueable;

    public function __construct(public Appointment $appointment)
    {
    }

    public function handle(NoShowFeeService $noShowFeeService, NotificationService $notificationService): void
    {
        try {
            $payment = $noShowFeeService->chargeFee($this->appointment);

            $this->appointment->update(['no_show_fee_charged' => true]);

            $notificationService->sendNoShowFeeReceipt($this->appointment, (float) $payment->amount);
        } catch (RuntimeException $e) {
            Log::warning('No-show fee charge failed: '.$e->getMessage(), [
                'appointment_id' => $this->appointment->id,
            ]);
        }
    }
}
