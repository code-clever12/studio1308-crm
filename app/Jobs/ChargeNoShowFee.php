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
 * MarkNoShowAppointments. The actual Stripe charge
 * (NoShowFeeService::chargeFee) is implemented in Step 8 — until then this
 * job logs and exits cleanly rather than failing the queue.
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
            Log::info('No-show fee charge skipped (Stripe not yet integrated): '.$e->getMessage(), [
                'appointment_id' => $this->appointment->id,
            ]);
        }
    }
}
