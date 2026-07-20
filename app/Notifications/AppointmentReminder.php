<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Sent to the customer ~24 hours before their appointment.
 * Delivered via the database channel for now; Step 9 adds mail delivery.
 */
class AppointmentReminder extends Notification implements ShouldQueue
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
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'service_name' => $this->appointment->service->name,
            'appointment_date' => $this->appointment->appointment_date->toDateString(),
            'start_time' => $this->appointment->start_time,
            'message' => "Reminder: your {$this->appointment->service->name} appointment is tomorrow at {$this->appointment->start_time}.",
        ];
    }
}
