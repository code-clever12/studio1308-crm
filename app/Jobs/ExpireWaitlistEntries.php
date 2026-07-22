<?php

namespace App\Jobs;

use App\Models\Waitlist;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Scheduled hourly (see routes/console.php). A waitlist offer is only good
 * for 48 hours after the customer is notified — anything still sitting in
 * 'notified' past that window is marked 'expired' so the slot reads as
 * available to notify again rather than looking permanently claimed.
 */
class ExpireWaitlistEntries implements ShouldQueue
{
    use Queueable;

    private const OFFER_WINDOW_HOURS = 48;

    public function handle(): void
    {
        Waitlist::query()
            ->where('status', 'notified')
            ->where('notification_sent_at', '<=', now()->subHours(self::OFFER_WINDOW_HOURS))
            ->update(['status' => 'expired']);
    }
}
