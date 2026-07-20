<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Sent to the customer when a no-show fee is charged to their card on file.
 * Delivered via the database channel for now; Step 9 adds mail delivery.
 */
class NoShowFeeCharged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Appointment $appointment, public float $feeAmount)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'fee_amount' => $this->feeAmount,
            'message' => "A \${$this->feeAmount} no-show fee was charged for your missed appointment on {$this->appointment->appointment_date->toFormattedDateString()}.",
        ];
    }
}
