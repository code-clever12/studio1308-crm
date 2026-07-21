<?php

use App\Models\ACHPayout;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PayoutFailed;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;

function postStripeWebhook(string $route, array $payload, ?string $secret = null): TestResponse
{
    $body = json_encode($payload);
    $secret ??= config('services.stripe.webhook_secret');

    return test()->call('POST', route($route), [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => stripeWebhookSignature($body, $secret),
        'CONTENT_TYPE' => 'application/json',
    ], $body);
}

it('rejects a payment webhook with an invalid signature', function () {
    postStripeWebhook('webhooks.stripe', [
        'id' => 'evt_bad',
        'type' => 'payment_intent.succeeded',
        'data' => ['object' => ['id' => 'pi_bad']],
    ], secret: 'wrong_secret')->assertStatus(400);
});

it('rejects a payment webhook with a missing signature header', function () {
    $this->postJson(route('webhooks.stripe'), ['id' => 'evt_bad'])->assertStatus(400);
});

it('confirms the appointment, records the tip, and saves the card on a successful payment webhook', function () {
    $appointment = Appointment::factory()->create([
        'status' => 'pending',
        'total_amount' => 110,
        'deposit_paid' => 0,
        'remaining_balance' => 110,
    ]);

    $payment = Payment::factory()->create([
        'appointment_id' => $appointment->id,
        'customer_id' => $appointment->customer_id,
        'status' => 'pending',
        'stripe_payment_intent_id' => 'pi_test123',
        'breakdown_json' => ['deposit' => 25, 'tip' => 10, 'total' => 35],
    ]);

    postStripeWebhook('webhooks.stripe', [
        'id' => 'evt_test123',
        'type' => 'payment_intent.succeeded',
        'data' => ['object' => [
            'id' => 'pi_test123',
            'latest_charge' => 'ch_test123',
            'payment_method' => 'pm_test123',
        ]],
    ])->assertOk();

    expect($payment->fresh()->status)->toBe('succeeded')
        ->and($payment->fresh()->stripe_charge_id)->toBe('ch_test123')
        ->and($appointment->fresh()->status)->toBe('confirmed')
        ->and((float) $appointment->fresh()->deposit_paid)->toBe(25.0)
        ->and((float) $appointment->fresh()->remaining_balance)->toBe(85.0)
        ->and($appointment->customer->fresh()->stripe_payment_method_id)->toBe('pm_test123');

    if ($appointment->staff_id) {
        expect((float) $appointment->tips()->sum('amount'))->toBe(10.0);
    }
});

it('does not reprocess an already-succeeded payment webhook', function () {
    $appointment = Appointment::factory()->create(['status' => 'confirmed', 'deposit_paid' => 25]);
    $payment = Payment::factory()->create([
        'appointment_id' => $appointment->id,
        'customer_id' => $appointment->customer_id,
        'status' => 'succeeded',
        'stripe_payment_intent_id' => 'pi_test123',
        'breakdown_json' => ['deposit' => 25, 'tip' => 10],
    ]);

    postStripeWebhook('webhooks.stripe', [
        'id' => 'evt_test123',
        'type' => 'payment_intent.succeeded',
        'data' => ['object' => ['id' => 'pi_test123', 'latest_charge' => 'ch_new', 'payment_method' => null]],
    ])->assertOk();

    expect($payment->fresh()->stripe_charge_id)->not->toBe('ch_new');
});

it('marks a payment refunded on a charge.refunded webhook', function () {
    $payment = Payment::factory()->create([
        'status' => 'succeeded',
        'stripe_payment_intent_id' => 'pi_test123',
    ]);

    postStripeWebhook('webhooks.stripe', [
        'id' => 'evt_refund',
        'type' => 'charge.refunded',
        'data' => ['object' => ['payment_intent' => 'pi_test123', 'amount_refunded' => 2500]],
    ])->assertOk();

    expect($payment->fresh()->status)->toBe('refunded')
        ->and((float) $payment->fresh()->refund_amount)->toBe(25.0);
});

it('rejects a payouts webhook with an invalid signature', function () {
    postStripeWebhook('webhooks.stripe.payouts', [
        'id' => 'evt_bad',
        'type' => 'payout.paid',
        'data' => ['object' => ['id' => 'po_bad']],
    ], secret: 'wrong_secret')->assertStatus(400);
});

it('marks an ACH payout completed on a payout.paid webhook', function () {
    $payout = ACHPayout::factory()->create(['status' => 'in_transit', 'stripe_payout_id' => 'po_test123']);

    postStripeWebhook('webhooks.stripe.payouts', [
        'id' => 'evt_payout_paid',
        'type' => 'payout.paid',
        'data' => ['object' => ['id' => 'po_test123']],
    ])->assertOk();

    expect($payout->fresh()->status)->toBe('completed');
});

it('marks an ACH payout failed and alerts admins on a payout.failed webhook', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $payout = ACHPayout::factory()->create(['status' => 'in_transit', 'stripe_payout_id' => 'po_test123']);

    postStripeWebhook('webhooks.stripe.payouts', [
        'id' => 'evt_payout_failed',
        'type' => 'payout.failed',
        'data' => ['object' => ['id' => 'po_test123', 'failure_message' => 'Account closed']],
    ])->assertOk();

    expect($payout->fresh()->status)->toBe('failed')
        ->and($payout->fresh()->failure_reason)->toBe('Account closed');

    Notification::assertSentTo($admin, PayoutFailed::class);
});
