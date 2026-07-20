<?php

use App\Models\Appointment;
use App\Models\Staff;
use App\Models\User;
use App\Services\TipService;

beforeEach(function () {
    $this->tipService = app(TipService::class);
});

it('calculates preset tip amounts for standard percentages', function () {
    $options = $this->tipService->calculateTipOptions(75.00);

    expect($options)->toBe([
        15 => 11.25,
        18 => 13.5,
        20 => 15.0,
    ]);
});

it('records a tip and updates the appointment tip_amount', function () {
    $staff = Staff::factory()->create();
    $customer = User::factory()->create(['role' => 'customer']);
    $appointment = Appointment::factory()->create(['staff_id' => $staff->id]);

    $tip = $this->tipService->recordTip($appointment, 15.00, $customer, 20);

    expect($tip)->not->toBeNull()
        ->and((float) $tip->amount)->toBe(15.0)
        ->and($tip->staff_id)->toBe($staff->id)
        ->and((float) $appointment->fresh()->tip_amount)->toBe(15.0);
});

it('sums completed tips for a staff member within a date range', function () {
    $staff = Staff::factory()->create();

    \App\Models\Tip::factory()->count(3)->create([
        'staff_id' => $staff->id,
        'status' => 'completed',
        'amount' => 10,
    ]);

    \App\Models\Tip::factory()->create([
        'staff_id' => $staff->id,
        'status' => 'failed',
        'amount' => 999,
    ]);

    expect($this->tipService->totalTipsForStaff($staff))->toBe(30.0);
});
