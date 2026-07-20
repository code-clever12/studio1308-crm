<?php

use App\Models\Appointment;
use App\Models\Staff;
use App\Services\CommissionService;
use Carbon\Carbon;

beforeEach(function () {
    $this->commissionService = app(CommissionService::class);
});

it('calculates commission only for completed appointments', function () {
    $staff = Staff::factory()->create(['commission_rate' => 20]);

    $completed = Appointment::factory()->create([
        'staff_id' => $staff->id,
        'service_price' => 100,
        'status' => 'completed',
    ]);

    $pending = Appointment::factory()->create([
        'staff_id' => $staff->id,
        'service_price' => 100,
        'status' => 'pending',
    ]);

    expect($this->commissionService->calculateCommission($completed))->toBe(20.0)
        ->and($this->commissionService->calculateCommission($pending))->toBe(0.0);
});

it('builds a commission report totalling revenue and commission for a date range', function () {
    $staff = Staff::factory()->create(['commission_rate' => 25]);
    $today = Carbon::today();

    Appointment::factory()->count(2)->create([
        'staff_id' => $staff->id,
        'service_price' => 100,
        'status' => 'completed',
        'appointment_date' => $today->toDateString(),
    ]);

    Appointment::factory()->create([
        'staff_id' => $staff->id,
        'service_price' => 500,
        'status' => 'cancelled',
        'appointment_date' => $today->toDateString(),
    ]);

    $report = $this->commissionService->commissionReport($staff, $today->copy()->subDay(), $today->copy()->addDay());

    expect($report['appointments_completed'])->toBe(2)
        ->and($report['total_revenue'])->toBe(200.0)
        ->and($report['total_commission'])->toBe(50.0);
});
