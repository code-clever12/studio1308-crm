<?php

namespace App\Http\Controllers;

use App\Events\PaymentSucceeded;
use App\Models\ACHPayout;
use App\Models\Payment;
use App\Services\NotificationService;
use App\Services\TipService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

/**
 * Stripe webhook endpoints. Configure both URLs in the Stripe Dashboard
 * (or via the CLI for local testing) once real API keys are available —
 * see STRIPE_WEBHOOK_SECRET in .env.
 */
class PaymentController extends Controller
{
    public function __construct(
        private readonly TipService $tipService,
        private readonly NotificationService $notificationService,
    ) {
    }

    /**
     * payment_intent.succeeded, charge.refunded.
     */
    public function webhook(Request $request): Response
    {
        $event = $this->verifyWebhook($request, config('services.stripe.webhook_secret'));

        if ($event === null) {
            return response('Invalid signature', 400);
        }

        match ($event->type) {
            'payment_intent.succeeded' => $this->handlePaymentSucceeded($event->data->object),
            'charge.refunded' => $this->handleChargeRefunded($event->data->object),
            default => null,
        };

        return response('OK', 200);
    }

    /**
     * payout.paid, payout.failed — ACH transfer status for staff payouts.
     */
    public function payoutWebhook(Request $request): Response
    {
        $event = $this->verifyWebhook($request, config('services.stripe.webhook_secret'));

        if ($event === null) {
            return response('Invalid signature', 400);
        }

        match ($event->type) {
            'payout.paid' => $this->handlePayoutPaid($event->data->object),
            'payout.failed' => $this->handlePayoutFailed($event->data->object),
            default => null,
        };

        return response('OK', 200);
    }

    private function verifyWebhook(Request $request, ?string $secret): ?object
    {
        try {
            return Webhook::constructEvent(
                $request->getContent(),
                (string) $request->header('Stripe-Signature'),
                (string) $secret,
            );
        } catch (UnexpectedValueException|SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed.', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * @param  object{id: string, payment_method: ?string, latest_charge: ?string}  $paymentIntent
     */
    private function handlePaymentSucceeded(object $paymentIntent): void
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if (! $payment || $payment->status === 'succeeded') {
            return;
        }

        $payment->update([
            'status' => 'succeeded',
            'stripe_charge_id' => $paymentIntent->latest_charge,
            'payment_date' => now(),
        ]);

        $appointment = $payment->appointment;
        $deposit = (float) ($payment->breakdown_json['deposit'] ?? $payment->amount);
        $tipAmount = (float) ($payment->breakdown_json['tip'] ?? 0);

        $appointment->update([
            'deposit_paid' => $deposit,
            'remaining_balance' => max(round((float) $appointment->total_amount - $deposit, 2), 0),
        ]);

        if ($paymentIntent->payment_method) {
            $appointment->customer->update(['stripe_payment_method_id' => $paymentIntent->payment_method]);
        }

        if ($tipAmount > 0) {
            $tip = $this->tipService->recordTip($appointment, $tipAmount, $appointment->customer);

            if ($tip) {
                $this->notificationService->sendTipReceipt($tip);
            }
        }

        PaymentSucceeded::dispatch($appointment->fresh(), $payment);
    }

    /**
     * @param  object{payment_intent: ?string, amount_refunded: int}  $charge
     */
    private function handleChargeRefunded(object $charge): void
    {
        $payment = Payment::where('stripe_payment_intent_id', $charge->payment_intent)->first();

        if (! $payment || $payment->status === 'refunded') {
            return;
        }

        $payment->update([
            'status' => 'refunded',
            'refund_amount' => round($charge->amount_refunded / 100, 2),
            'refund_date' => now(),
        ]);
    }

    /**
     * @param  object{id: string}  $payout
     */
    private function handlePayoutPaid(object $payout): void
    {
        ACHPayout::where('stripe_payout_id', $payout->id)->first()?->update(['status' => 'completed']);
    }

    /**
     * @param  object{id: string, failure_message: ?string}  $payout
     */
    private function handlePayoutFailed(object $payout): void
    {
        $achPayout = ACHPayout::where('stripe_payout_id', $payout->id)->first();

        if (! $achPayout) {
            return;
        }

        $achPayout->update([
            'status' => 'failed',
            'failure_reason' => $payout->failure_message ?? 'Unknown failure reported by Stripe.',
        ]);

        $this->notificationService->sendPayoutFailedAlert($achPayout->fresh());
    }
}
