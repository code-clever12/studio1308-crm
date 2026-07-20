<?php

namespace App\Notifications;

use App\Models\Tip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Sent to a staff member when they receive a tip.
 * Delivered via the database channel for now; Step 9 adds mail delivery.
 */
class TipReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Tip $tip)
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
            'tip_id' => $this->tip->id,
            'amount' => (float) $this->tip->amount,
            'appointment_id' => $this->tip->appointment_id,
            'message' => "You received a \${$this->tip->amount} tip!",
        ];
    }
}
