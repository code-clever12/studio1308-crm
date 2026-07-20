<?php

namespace App\Listeners;

use App\Events\AppointmentBooked;
use App\Services\NotificationService;

class NotifyStaffOfNewAppointment
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function handle(AppointmentBooked $event): void
    {
        if ($event->appointment->staff_id) {
            $this->notificationService->sendStaffAssignment($event->appointment);
        }
    }
}
