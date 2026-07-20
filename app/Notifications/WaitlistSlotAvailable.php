<?php

namespace App\Notifications;

use App\Models\Waitlist;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Sent when a slot matching a customer's waitlist request opens up.
 * Delivered via the database channel for now; Step 9 adds mail delivery.
 */
class WaitlistSlotAvailable extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Waitlist $waitlist)
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
        $staffName = $this->waitlist->staff?->user?->name ?? 'A staff member';

        return [
            'waitlist_id' => $this->waitlist->id,
            'service_id' => $this->waitlist->service_id,
            'staff_id' => $this->waitlist->staff_id,
            'requested_date' => $this->waitlist->requested_date->toDateString(),
            'message' => "A slot just opened up! {$staffName} is available on {$this->waitlist->requested_date->toFormattedDateString()}. Book now before it expires.",
        ];
    }
}
