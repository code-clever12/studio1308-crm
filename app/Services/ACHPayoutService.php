<?php

namespace App\Services;

use App\Models\ACHPayout;
use App\Models\Staff;
use Carbon\CarbonInterface;
use RuntimeException;

class ACHPayoutService
{
    public function __construct(
        private readonly CommissionService $commissionService,
        private readonly TipService $tipService,
    ) {
    }

    /**
     * @return array{commission: float, tips: float, total: float}
     */
    public function calculateEarnings(Staff $staff, CarbonInterface $from, CarbonInterface $to): array
    {
        $commission = $this->commissionService->totalCommissionForStaff($staff, $from, $to);
        $tips = $this->tipService->totalTipsForStaff($staff, $from, $to);

        return [
            'commission' => $commission,
            'tips' => $tips,
            'total' => round($commission + $tips, 2),
        ];
    }

    /**
     * Create a pending payout record for a staff member covering a date range.
     * Requires a verified ACH bank account on file. Actually initiating the
     * Stripe Connect transfer happens via initiateTransfer() in Step 8.
     */
    public function createPayout(Staff $staff, CarbonInterface $from, CarbonInterface $to, ?float $adjustments = null): ACHPayout
    {
        if (! $staff->achBankAccount || $staff->achBankAccount->verification_status !== 'verified') {
            throw new RuntimeException("Staff #{$staff->id} has no verified ACH bank account on file.");
        }

        $earnings = $this->calculateEarnings($staff, $from, $to);
        $total = round($earnings['total'] + ($adjustments ?? 0), 2);

        if ($total <= 0) {
            throw new RuntimeException("Staff #{$staff->id} has no earnings to pay out for this period.");
        }

        return ACHPayout::create([
            'staff_id' => $staff->id,
            'amount' => $total,
            'status' => 'pending',
            'commission_amount' => $earnings['commission'],
            'tips_amount' => $earnings['tips'],
            'adjustments_amount' => $adjustments,
        ]);
    }

    /**
     * Initiates the actual Stripe Connect transfer. Implemented in Step 8
     * once the Stripe SDK and Connect accounts are wired up.
     */
    public function initiateTransfer(ACHPayout $payout): ACHPayout
    {
        throw new RuntimeException('Stripe Connect payout transfers are implemented in Step 8.');
    }
}
