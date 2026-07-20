<?php

namespace App\Listeners;

use App\Events\AppointmentCancelled;
use App\Services\NotificationService;

class SendCancellationNotification
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function handle(AppointmentCancelled $event): void
    {
        $this->notificationService->sendCancellationConfirmation($event->appointment);
    }
}
