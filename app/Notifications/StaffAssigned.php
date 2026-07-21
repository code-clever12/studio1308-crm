<?php

namespace App\Notifications;

use App\Mail\StaffAssignedMail;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Sent to a staff member when a new appointment is assigned to them.
 */
class StaffAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Appointment $appointment) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): StaffAssignedMail
    {
        return (new StaffAssignedMail($this->appointment))->to($notifiable->email);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'customer_name' => $this->appointment->customer->name,
            'service_name' => $this->appointment->service->name,
            'appointment_date' => $this->appointment->appointment_date->toDateString(),
            'start_time' => $this->appointment->start_time,
            'message' => "New appointment: {$this->appointment->customer->name} booked {$this->appointment->service->name} on {$this->appointment->appointment_date->toFormattedDateString()} at {$this->appointment->start_time}.",
        ];
    }
}
