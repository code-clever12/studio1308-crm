<?php

use App\Models\Appointment;
use App\Models\Salon;
use App\Models\Service;
use App\Services\SalesTaxService;

beforeEach(function () {
    $this->salesTaxService = app(SalesTaxService::class);
    $this->salon = Salon::factory()->create(['sales_tax_rate' => 10]);
});

it('calculates tax as a percentage of the service price', function () {
    $service = Service::factory()->create(['price' => 100, 'is_taxable' => true]);

    expect($this->salesTaxService->calculateTax($service, $this->salon))->toBe(10.0);
});

it('returns zero tax for non-taxable services', function () {
    $service = Service::factory()->create(['price' => 100, 'is_taxable' => false]);

    expect($this->salesTaxService->calculateTax($service, $this->salon))->toBe(0.0);
});

it('records a sales tax transaction linked to the appointment', function () {
    $appointment = Appointment::factory()->create();

    $transaction = $this->salesTaxService->recordTaxTransaction($appointment, 100, 8.88, $this->salon);

    expect($transaction)->not->toBeNull()
        ->and($transaction->appointment_id)->toBe($appointment->id)
        ->and((float) $transaction->tax_amount)->toBe(8.88)
        ->and($transaction->state)->toBe($this->salon->state);
});

it('skips recording a transaction when the tax amount is zero', function () {
    $appointment = Appointment::factory()->create();

    $transaction = $this->salesTaxService->recordTaxTransaction($appointment, 100, 0, $this->salon);

    expect($transaction)->toBeNull();
});
