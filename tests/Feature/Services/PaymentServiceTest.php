<?php

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Salon;
use App\Models\Service;
use App\Models\User;
use App\Services\PaymentService;

beforeEach(function () {
    $this->salon = Salon::factory()->create([
        'deposit_percentage' => 25,
        'sales_tax_rate' => 10,
    ]);
});

it('uses the service deposit_amount override when set', function () {
    $service = Service::factory()->create(['price' => 100, 'deposit_amount' => 40]);

    expect(app(PaymentService::class)->calculateDeposit($service, $this->salon))->toBe(40.0);
});

it('falls back to the salon deposit percentage when no override is set', function () {
    $service = Service::factory()->create(['price' => 100, 'deposit_amount' => null]);

    expect(app(PaymentService::class)->calculateDeposit($service, $this->salon))->toBe(25.0);
});

it('computes a full checkout breakdown including tax, deposit, and tip', function () {
    $service = Service::factory()->create(['price' => 100, 'deposit_amount' => null, 'is_taxable' => true]);

    $breakdown = app(PaymentService::class)->calculateBreakdown($service, 15, $this->salon);

    expect($breakdown['subtotal'])->toBe(100.0)
        ->and($breakdown['tax'])->toBe(10.0)
        ->and($breakdown['total_amount'])->toBe(110.0)
        ->and($breakdown['deposit'])->toBe(25.0)
        ->and($breakdown['tip'])->toBe(15.0)
        ->and($breakdown['charge_today'])->toBe(40.0)
        ->and($breakdown['remaining_balance'])->toBe(85.0);
});

it('creates a Stripe customer and deposit PaymentIntent, saving a pending Payment', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $service = Service::factory()->create(['price' => 100, 'deposit_amount' => null]);
    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'service_id' => $service->id,
    ]);

    $http = mockStripeHttp();
    $http->shouldReceive('request')->once()
        ->withArgs(fn ($method, $url) => $method === 'post' && str_contains($url, '/v1/customers'))
        ->andReturn(stripeHttpResponse(['id' => 'cus_test123', 'object' => 'customer']));
    $http->shouldReceive('request')->once()
        ->withArgs(fn ($method, $url, $headers, $params) => $method === 'post'
            && str_contains($url, '/v1/payment_intents')
            && $params['amount'] === 3000
            && $params['customer'] === 'cus_test123')
        ->andReturn(stripeHttpResponse([
            'id' => 'pi_test123',
            'object' => 'payment_intent',
            'client_secret' => 'pi_test123_secret_abc',
            'status' => 'requires_payment_method',
        ]));

    $result = app(PaymentService::class)->createDepositPaymentIntent($appointment, tipAmount: 5);

    expect($result['client_secret'])->toBe('pi_test123_secret_abc')
        ->and($result['payment']->status)->toBe('pending')
        ->and((float) $result['payment']->amount)->toBe(30.0)
        ->and($customer->fresh()->stripe_customer_id)->toBe('cus_test123');
});

it('updates the existing PaymentIntent instead of creating a duplicate when called again', function () {
    $customer = User::factory()->create(['role' => 'customer', 'stripe_customer_id' => 'cus_existing']);
    $service = Service::factory()->create(['price' => 100, 'deposit_amount' => null]);
    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'service_id' => $service->id,
    ]);

    Payment::factory()->create([
        'appointment_id' => $appointment->id,
        'customer_id' => $customer->id,
        'status' => 'pending',
        'stripe_payment_intent_id' => 'pi_existing123',
    ]);

    $http = mockStripeHttp();
    $http->shouldReceive('request')->once()
        ->withArgs(fn ($method, $url, $headers, $params) => $method === 'post'
            && str_contains($url, '/v1/payment_intents/pi_existing123')
            && $params['amount'] === 3500)
        ->andReturn(stripeHttpResponse([
            'id' => 'pi_existing123',
            'object' => 'payment_intent',
            'client_secret' => 'pi_existing123_secret',
            'status' => 'requires_payment_method',
        ]));

    $result = app(PaymentService::class)->createDepositPaymentIntent($appointment, tipAmount: 10);

    expect($result['client_secret'])->toBe('pi_existing123_secret')
        ->and(Payment::where('appointment_id', $appointment->id)->count())->toBe(1);
});

it('refunds a payment via Stripe and updates the Payment record', function () {
    $payment = Payment::factory()->create([
        'status' => 'succeeded',
        'amount' => 25,
        'stripe_payment_intent_id' => 'pi_test123',
    ]);

    $http = mockStripeHttp();
    $http->shouldReceive('request')->once()
        ->withArgs(fn ($method, $url, $headers, $params) => $method === 'post'
            && str_contains($url, '/v1/refunds')
            && $params['payment_intent'] === 'pi_test123'
            && $params['amount'] === 2500)
        ->andReturn(stripeHttpResponse(['id' => 're_test123', 'object' => 'refund']));

    $refunded = app(PaymentService::class)->refund($payment, 25, 'Customer cancelled');

    expect($refunded->status)->toBe('refunded')
        ->and((float) $refunded->refund_amount)->toBe(25.0)
        ->and($refunded->refund_stripe_id)->toBe('re_test123');
});
