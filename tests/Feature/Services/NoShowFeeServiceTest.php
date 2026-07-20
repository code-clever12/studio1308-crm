<?php

use App\Models\Appointment;
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
