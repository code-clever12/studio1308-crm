<?php

namespace App\Jobs;

use App\Models\Staff;
use App\Services\ACHPayoutService;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Admin-triggered (Admin\PayoutController) batch payout run for a date
 * range. Creates a pending ACHPayout per eligible staff member; actually
 * transferring funds (ACHPayoutService::initiateTransfer) is implemented
 * in Step 8, so payouts are created here and stay 'pending' until then.
 */
class ProcessACHPayouts implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public CarbonInterface $from,
        public CarbonInterface $to,
    ) {}

    public function handle(ACHPayoutService $achPayoutService): void
    {
        Staff::query()
            ->where('status', 'active')
            ->whereHas('achBankAccount', fn ($query) => $query->where('verification_status', 'verified'))
            ->get()
            ->each(function (Staff $staff) use ($achPayoutService) {
                try {
                    $payout = $achPayoutService->createPayout($staff, $this->from, $this->to);
                    $achPayoutService->initiateTransfer($payout);
                } catch (RuntimeException $e) {
                    Log::info('ACH payout not transferred: '.$e->getMessage(), ['staff_id' => $staff->id]);
                }
            });
    }
}
