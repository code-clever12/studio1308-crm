<?php

use App\Models\Salon;
use App\Models\Service;
use App\Services\PaymentService;

beforeEach(function () {
    $this->paymentService = app(PaymentService::class);
    $this->salon = Salon::factory()->create([
        'deposit_percentage' => 25,
        'sales_tax_rate' => 10,
    ]);
});

it('uses the service deposit_amount override when set', function () {
    $service = Service::factory()->create(['price' => 100, 'deposit_amount' => 40]);

    expect($this->paymentService->calculateDeposit($service, $this->salon))->toBe(40.0);
});

it('falls back to the salon deposit percentage when no override is set', function () {
    $service = Service::factory()->create(['price' => 100, 'deposit_amount' => null]);

    expect($this->paymentService->calculateDeposit($service, $this->salon))->toBe(25.0);
});

it('computes a full checkout breakdown including tax, deposit, and tip', function () {
    $service = Service::factory()->create(['price' => 100, 'deposit_amount' => null, 'is_taxable' => true]);

    $breakdown = $this->paymentService->calculateBreakdown($service, 15, $this->salon);

    expect($breakdown['subtotal'])->toBe(100.0)
        ->and($breakdown['tax'])->toBe(10.0)
        ->and($breakdown['total_amount'])->toBe(110.0)
        ->and($breakdown['deposit'])->toBe(25.0)
        ->and($breakdown['tip'])->toBe(15.0)
        ->and($breakdown['charge_today'])->toBe(40.0)
        ->and($breakdown['remaining_balance'])->toBe(85.0);
});

it('throws for stubbed Stripe methods pending Step 8', function () {
    $appointment = App\Models\Appointment::factory()->create();

    $this->paymentService->createDepositPaymentIntent($appointment);
})->throws(RuntimeException::class);
