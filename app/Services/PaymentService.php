<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Salon;
use App\Models\Service;
use RuntimeException;

/**
 * Payment breakdown/deposit math is fully implemented here. The methods that
 * actually talk to Stripe (createDepositPaymentIntent, refund) are stubbed —
 * Step 8 ("Stripe Integration for USA") fills those in once stripe/stripe-php
 * is installed and API keys are configured.
 */
class PaymentService
{
    /**
     * Deposit amount owed for a service: the service's own deposit_amount if
     * set, otherwise the salon's default deposit_percentage of the price.
     */
    public function calculateDeposit(Service $service, ?Salon $salon = null): float
    {
        if ($service->deposit_amount !== null) {
            return round((float) $service->deposit_amount, 2);
        }

        $salon ??= Salon::query()->firstOrFail();

        return round((float) $service->price * ((float) $salon->deposit_percentage / 100), 2);
    }

    /**
     * Full checkout breakdown: service price, tax, deposit, optional tip, and
     * the amount actually charged today (deposit + tip) vs. the remaining balance.
     *
     * @return array{subtotal: float, tax: float, total_amount: float, deposit: float, tip: float, charge_today: float, remaining_balance: float}
     */
    public function calculateBreakdown(Service $service, float $tipAmount = 0, ?Salon $salon = null): array
    {
        $salon ??= Salon::query()->firstOrFail();

        $subtotal = round((float) $service->price, 2);
        $tax = app(SalesTaxService::class)->calculateTax($service, $salon);
        $totalAmount = round($subtotal + $tax, 2);
        $deposit = $this->calculateDeposit($service, $salon);
        $chargeToday = round($deposit + $tipAmount, 2);

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total_amount' => $totalAmount,
            'deposit' => $deposit,
            'tip' => round($tipAmount, 2),
            'charge_today' => $chargeToday,
            'remaining_balance' => round($totalAmount - $deposit, 2),
        ];
    }

    /**
     * Creates a Stripe PaymentIntent for the deposit + optional tip, charged as
     * a single transaction. Implemented in Step 8.
     */
    public function createDepositPaymentIntent(Appointment $appointment, float $tipAmount = 0): array
    {
        throw new RuntimeException('Stripe payment intent creation is implemented in Step 8.');
    }

    /**
     * Refunds a payment (in full or in part) via Stripe. Implemented in Step 8.
     */
    public function refund(Payment $payment, float $amount, string $reason): Payment
    {
        throw new RuntimeException('Stripe refunds are implemented in Step 8.');
    }
}
