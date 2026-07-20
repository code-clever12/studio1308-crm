<?php

namespace App\Notifications;

use App\Models\ACHPayout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Sent to admin users when a Stripe ACH payout fails.
 * Delivered via the database channel for now; Step 9 adds mail delivery.
 */
class PayoutFailed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ACHPayout $payout)
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
            'payout_id' => $this->payout->id,
            'staff_id' => $this->payout->staff_id,
            'amount' => (float) $this->payout->amount,
            'failure_reason' => $this->payout->failure_reason,
            'message' => "ACH payout of \${$this->payout->amount} to {$this->payout->staff->user->name} failed: {$this->payout->failure_reason}",
        ];
    }
}
