<?php

namespace App\Notifications;

use App\Mail\NoShowFeeChargedMail;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Sent to the customer when a no-show fee is charged to their card on file.
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
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): NoShowFeeChargedMail
    {
        return (new NoShowFeeChargedMail($this->appointment, $this->feeAmount))->to($notifiable->email);
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
