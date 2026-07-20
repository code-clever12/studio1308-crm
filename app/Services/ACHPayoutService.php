<?php

namespace App\Services;

use App\Models\ACHBankAccount;
use App\Models\ACHPayout;
use App\Models\Staff;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;
use RuntimeException;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class ACHPayoutService
{
    public function __construct(
        private readonly CommissionService $commissionService,
        private readonly TipService $tipService,
        private readonly StripeClient $stripe,
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
     * Requires a verified ACH bank account on file — see verifyBankAccount().
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
     * Creates the staff member's Stripe Connect "Custom" account (if they
     * don't already have one) and attaches their bank details as an
     * external account. Separate from initiateTransfer() so admins verify a
     * bank account once, independent of any specific payout run — resolves
     * the chicken-and-egg problem of createPayout() requiring a verified
     * account before a payout (and therefore a transfer) can exist.
     *
     * Note: Stripe Custom accounts normally require additional identity/
     * compliance fields (SSN last-4, DOB, address, TOS acceptance) beyond
     * what this app collects — this uses the minimal documented fields and
     * is the piece most likely to need adjustment once tested against a
     * real Stripe test account.
     */
    public function verifyBankAccount(Staff $staff): ACHBankAccount
    {
        $bankAccount = $staff->achBankAccount;

        if (! $bankAccount) {
            throw new RuntimeException("Staff #{$staff->id} has no bank account on file to verify.");
        }

        try {
            $connectAccountId = $staff->stripe_connect_account_id
                ?? $this->createConnectAccount($staff);

            $externalAccount = $this->stripe->accounts->createExternalAccount($connectAccountId, [
                'external_account' => [
                    'object' => 'bank_account',
                    'country' => 'US',
                    'currency' => 'usd',
                    'account_holder_name' => $bankAccount->bank_account_holder_name,
                    'account_holder_type' => 'individual',
                    'routing_number' => $bankAccount->bank_account_routing_number,
                    'account_number' => $bankAccount->bank_account_number,
                ],
            ]);
        } catch (ApiErrorException $e) {
            $bankAccount->update(['verification_status' => 'failed']);

            throw new RuntimeException(
                "Failed to verify bank account for staff #{$staff->id}: {$e->getMessage()}",
                previous: $e,
            );
        }

        // Stripe-hosted bank accounts start 'new' and move to 'validated'/
        // 'verified' asynchronously (micro-deposits) or instantly depending
        // on the bank; treat anything other than 'errored' as good enough
        // for this platform to consider the account usable for payouts.
        $bankAccount->update([
            'verification_status' => $externalAccount->status === 'errored' ? 'failed' : 'verified',
            'stripe_bank_account_token' => $externalAccount->id,
        ]);

        return $bankAccount->fresh();
    }

    /**
     * Initiates the actual Stripe Connect transfer for an already-created
     * payout. Requires the staff to have completed verifyBankAccount() first.
     */
    public function initiateTransfer(ACHPayout $payout): ACHPayout
    {
        $staff = $payout->staff;

        if (! $staff->stripe_connect_account_id) {
            throw new RuntimeException("Staff #{$staff->id} has no Stripe Connect account — call verifyBankAccount() first.");
        }

        try {
            $transfer = $this->stripe->transfers->create([
                'amount' => (int) round((float) $payout->amount * 100),
                'currency' => 'usd',
                'destination' => $staff->stripe_connect_account_id,
                'metadata' => [
                    'payout_id' => $payout->id,
                    'staff_id' => $staff->id,
                ],
            ]);
        } catch (ApiErrorException $e) {
            $payout->update([
                'status' => 'failed',
                'failure_reason' => $e->getMessage(),
            ]);

            throw new RuntimeException(
                "Failed to initiate ACH transfer for payout #{$payout->id}: {$e->getMessage()}",
                previous: $e,
            );
        }

        $payout->update([
            'status' => 'in_transit',
            'stripe_payout_id' => $transfer->id,
            'payout_date' => now()->toDateString(),
            'expected_arrival_date' => now()->addWeekdays(2)->toDateString(),
        ]);

        return $payout->fresh();
    }

    private function createConnectAccount(Staff $staff): string
    {
        [$firstName, $lastName] = $this->splitName($staff->user->name);

        $account = $this->stripe->accounts->create([
            'type' => 'custom',
            'country' => 'US',
            'email' => $staff->user->email,
            'capabilities' => [
                'transfers' => ['requested' => true],
            ],
            'business_type' => 'individual',
            'individual' => [
                'email' => $staff->user->email,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ],
            'business_profile' => [
                'mcc' => '7230', // Beauty/Barber Shops
                'product_description' => 'Salon staff commission and tip payouts',
            ],
            'tos_acceptance' => [
                'date' => now()->timestamp,
                'ip' => request()?->ip() ?? '127.0.0.1',
            ],
        ]);

        $staff->update(['stripe_connect_account_id' => $account->id]);

        return $account->id;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $fullName): array
    {
        $firstName = Str::before($fullName, ' ');
        $lastName = Str::after($fullName, ' ');

        return [$firstName, $lastName !== $fullName ? $lastName : $firstName];
    }
}
