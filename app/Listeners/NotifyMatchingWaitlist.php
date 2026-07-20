<?php

namespace App\Listeners;

use App\Events\AppointmentCancelled;
use App\Models\Waitlist;
use App\Services\NotificationService;

/**
 * Notifies waiting customers whose requested date is on or before the
 * freed-up date, for the same service, per the spec's matching rule.
 */
class NotifyMatchingWaitlist
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function handle(AppointmentCancelled $event): void
    {
        Waitlist::query()
            ->where('service_id', $event->appointment->service_id)
            ->where('status', 'waiting')
            ->where('requested_date', '<=', $event->appointment->appointment_date)
            ->get()
            ->each(function (Waitlist $entry) {
                $entry->update([
                    'status' => 'notified',
                    'notification_sent_at' => now(),
                ]);

                $this->notificationService->sendWaitlistNotification($entry);
            });
    }
}
