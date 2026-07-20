<?php

use App\Models\ACHBankAccount;
use App\Models\Appointment;
use App\Models\Staff;
use App\Models\Tip;
use App\Services\ACHPayoutService;
use Carbon\Carbon;

beforeEach(function () {
    $this->achPayoutService = app(ACHPayoutService::class);
    $this->staff = Staff::factory()->create(['commission_rate' => 20]);
});

it('calculates combined commission and tip earnings for a staff member', function () {
    $today = Carbon::today();

    Appointment::factory()->create([
        'staff_id' => $this->staff->id,
        'service_price' => 100,
        'status' => 'completed',
        'appointment_date' => $today->toDateString(),
    ]);

    Tip::factory()->create([
        'staff_id' => $this->staff->id,
        'status' => 'completed',
        'amount' => 15,
    ]);

    $earnings = $this->achPayoutService->calculateEarnings($this->staff, $today->copy()->subDay(), $today->copy()->addDay());

    expect($earnings['commission'])->toBe(20.0)
        ->and($earnings['tips'])->toBe(15.0)
        ->and($earnings['total'])->toBe(35.0);
});

it('refuses to create a payout without a verified ACH bank account', function () {
    $today = Carbon::today();

    $this->achPayoutService->createPayout($this->staff, $today->copy()->subDay(), $today->copy()->addDay());
})->throws(RuntimeException::class);

it('creates a pending payout record when earnings exist and the account is verified', function () {
    ACHBankAccount::factory()->create([
        'staff_id' => $this->staff->id,
        'verification_status' => 'verified',
    ]);

    $today = Carbon::today();

    Appointment::factory()->create([
        'staff_id' => $this->staff->id,
        'service_price' => 200,
        'status' => 'completed',
        'appointment_date' => $today->toDateString(),
    ]);

    $payout = $this->achPayoutService->createPayout($this->staff, $today->copy()->subDay(), $today->copy()->addDay());

    expect($payout->status)->toBe('pending')
        ->and((float) $payout->commission_amount)->toBe(40.0)
        ->and((float) $payout->amount)->toBe(40.0);
});
