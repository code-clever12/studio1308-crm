<?php

namespace App\Notifications;

use App\Mail\CancellationNoticeMail;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Sent to the customer when their appointment is cancelled, with refund details.
 */
class AppointmentCancelled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Appointment $appointment)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): CancellationNoticeMail
    {
        return (new CancellationNoticeMail($this->appointment))->to($notifiable->email);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $refundAmount = round((float) $this->appointment->deposit_paid - (float) ($this->appointment->cancellation_fee ?? 0), 2);

        return [
            'appointment_id' => $this->appointment->id,
            'service_name' => $this->appointment->service->name,
            'cancellation_fee' => (float) ($this->appointment->cancellation_fee ?? 0),
            'refund_amount' => max($refundAmount, 0),
            'message' => "Your {$this->appointment->service->name} appointment on {$this->appointment->appointment_date->toFormattedDateString()} has been cancelled.",
        ];
    }
}
