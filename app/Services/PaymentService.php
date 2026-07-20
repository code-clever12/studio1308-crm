<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Salon;
use App\Models\Service;
use App\Models\User;
use Stripe\StripeClient;

/**
 * Payment breakdown/deposit math, plus the real Stripe integration: creating
 * deposit+tip PaymentIntents and processing refunds. Stripe Connect (ACH
 * staff payouts) lives in ACHPayoutService; no-show off-session charges live
 * in NoShowFeeService — both reuse the Stripe customer/payment-method saved
 * here.
 */
class PaymentService
{
    public function __construct(private readonly StripeClient $stripe)
    {
    }

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
     * Finds or creates the Stripe Customer backing this user, so their card
     * can be saved and reused for no-show fees.
     */
    public function ensureStripeCustomer(User $user): string
    {
        if ($user->stripe_customer_id) {
            return $user->stripe_customer_id;
        }

        $customer = $this->stripe->customers->create([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'metadata' => ['user_id' => $user->id],
        ]);

        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer->id;
    }

    /**
     * Creates (or, if the customer changed their tip on a page refresh,
     * updates) a Stripe PaymentIntent for the deposit + optional tip,
     * charged as a single transaction. Saves the card on file
     * (setup_future_usage) so NoShowFeeService can charge it later
     * off-session. Creates/updates a pending Payment record;
     * PaymentController's webhook confirms it on success.
     *
     * @return array{client_secret: string, payment: Payment}
     */
    public function createDepositPaymentIntent(Appointment $appointment, float $tipAmount = 0): array
    {
        $customer = $appointment->customer;
        $customerId = $this->ensureStripeCustomer($customer);

        $depositDue = $this->calculateDeposit($appointment->service);
        $amount = round($depositDue + $tipAmount, 2);
        $breakdown = ['deposit' => $depositDue, 'tip' => $tipAmount, 'total' => $amount];

        $existing = $appointment->payments()
            ->where('status', 'pending')
            ->whereNotNull('stripe_payment_intent_id')
            ->latest()
            ->first();

        if ($existing) {
            $intent = $this->stripe->paymentIntents->update($existing->stripe_payment_intent_id, [
                'amount' => $this->toCents($amount),
                'metadata' => ['appointment_id' => $appointment->id, 'tip_amount' => $tipAmount],
            ]);

            $existing->update(['amount' => $amount, 'breakdown_json' => $breakdown]);

            return ['client_secret' => $intent->client_secret, 'payment' => $existing->fresh()];
        }

        $intent = $this->stripe->paymentIntents->create([
            'amount' => $this->toCents($amount),
            'currency' => 'usd',
            'customer' => $customerId,
            'setup_future_usage' => 'off_session',
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => [
                'appointment_id' => $appointment->id,
                'tip_amount' => $tipAmount,
            ],
        ]);

        $payment = Payment::create([
            'appointment_id' => $appointment->id,
            'customer_id' => $customer->id,
            'amount' => $amount,
            'breakdown_json' => $breakdown,
            'payment_method' => 'stripe_card',
            'stripe_payment_intent_id' => $intent->id,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        return [
            'client_secret' => $intent->client_secret,
            'payment' => $payment,
        ];
    }

    /**
     * Refunds a payment (in full or in part) via Stripe.
     */
    public function refund(Payment $payment, float $amount, string $reason): Payment
    {
        $refund = $this->stripe->refunds->create([
            'payment_intent' => $payment->stripe_payment_intent_id,
            'amount' => $this->toCents($amount),
            'metadata' => ['reason' => $reason],
        ]);

        $payment->update([
            'status' => 'refunded',
            'refund_amount' => $amount,
            'refund_reason' => $reason,
            'refund_date' => now(),
            'refund_stripe_id' => $refund->id,
        ]);

        return $payment->fresh();
    }

    private function toCents(float $amount): int
    {
        return (int) round($amount * 100);
    }
}
