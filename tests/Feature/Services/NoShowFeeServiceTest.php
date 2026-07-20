<?php

use App\Models\Appointment;
use App\Models\Salon;
use App\Models\User;
use App\Services\NoShowFeeService;
use Carbon\Carbon;

beforeEach(function () {
    $this->noShowFeeService = app(NoShowFeeService::class);
});

function appointmentThatStarted(int $minutesAgo, array $overrides = []): Appointment
{
    $start = Carbon::now()->subMinutes($minutesAgo);

    return Appointment::factory()->create(array_merge([
        'appointment_date' => $start->toDateString(),
        'start_time' => $start->format('H:i:s'),
        'status' => 'confirmed',
    ], $overrides));
}

it('finds appointments past the grace period as eligible for no-show', function () {
    $eligible = appointmentThatStarted(45);
    $notYetEligible = appointmentThatStarted(10);

    $found = $this->noShowFeeService->findEligibleAppointments()->pluck('id');

    expect($found)->toContain($eligible->id)
        ->and($found)->not->toContain($notYetEligible->id);
});

it('marks an appointment as a no-show', function () {
    $appointment = appointmentThatStarted(45);

    $updated = $this->noShowFeeService->markNoShow($appointment);

    expect($updated->status)->toBe('no_show');
});

it('auto-blocks a customer after their third no-show', function () {
    $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

    Appointment::factory()->count(2)->create(['customer_id' => $customer->id, 'status' => 'no_show']);
    $thirdStrike = appointmentThatStarted(45, ['customer_id' => $customer->id]);

    $this->noShowFeeService->markNoShow($thirdStrike);

    expect($customer->fresh()->is_active)->toBeFalse();
});

it('does not block a customer with fewer than three no-shows', function () {
    $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

    $appointment = appointmentThatStarted(45, ['customer_id' => $customer->id]);

    $this->noShowFeeService->markNoShow($appointment);

    expect($customer->fresh()->is_active)->toBeTrue();
});

it('refuses to charge a no-show fee when the customer has no card on file', function () {
    $appointment = Appointment::factory()->create(['status' => 'no_show']);

    $this->noShowFeeService->chargeFee($appointment);
})->throws(RuntimeException::class);

it('charges the saved card off-session for the salon no-show fee', function () {
    Salon::factory()->create(['no_show_fee' => 35]);

    $customer = User::factory()->create([
        'role' => 'customer',
        'stripe_customer_id' => 'cus_test123',
        'stripe_payment_method_id' => 'pm_test123',
    ]);
    $appointment = Appointment::factory()->create(['status' => 'no_show', 'customer_id' => $customer->id]);

    $http = mockStripeHttp();
    $http->shouldReceive('request')->once()
        ->withArgs(fn ($method, $url, $headers, $params) => $method === 'post'
            && str_contains($url, '/v1/payment_intents')
            && $params['amount'] === 3500
            && $params['customer'] === 'cus_test123'
            && $params['payment_method'] === 'pm_test123'
            && $params['off_session'] === 'true'
            && $params['confirm'] === 'true')
        ->andReturn(stripeHttpResponse([
            'id' => 'pi_noshow123',
            'object' => 'payment_intent',
            'status' => 'succeeded',
            'latest_charge' => 'ch_noshow123',
        ]));

    $payment = $this->noShowFeeService->chargeFee($appointment);

    expect($payment->status)->toBe('succeeded')
        ->and((float) $payment->amount)->toBe(35.0)
        ->and($payment->stripe_payment_intent_id)->toBe('pi_noshow123');
});

it('wraps a declined off-session charge as a RuntimeException', function () {
    Salon::factory()->create(['no_show_fee' => 35]);

    $customer = User::factory()->create([
        'role' => 'customer',
        'stripe_customer_id' => 'cus_test123',
        'stripe_payment_method_id' => 'pm_test123',
    ]);
    $appointment = Appointment::factory()->create(['status' => 'no_show', 'customer_id' => $customer->id]);

    $http = mockStripeHttp();
    $http->shouldReceive('request')->once()
        ->andReturn(stripeHttpResponse([
            'error' => ['type' => 'card_error', 'code' => 'card_declined', 'message' => 'Your card was declined.'],
        ], 402));

    $this->noShowFeeService->chargeFee($appointment);
})->throws(RuntimeException::class);
