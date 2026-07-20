<?php

use App\Models\Appointment;
use App\Models\Salon;
use App\Models\Service;
use App\Models\Waitlist;
use App\Services\CancellationService;
use Carbon\Carbon;

beforeEach(function () {
    $this->cancellationService = app(CancellationService::class);
    $this->salon = Salon::factory()->create(['no_show_fee' => 25]);
});

function appointmentStartingIn(int $hours, array $overrides = []): Appointment
{
    $start = Carbon::now()->addHours($hours);

    return Appointment::factory()->create(array_merge([
        'appointment_date' => $start->toDateString(),
        'start_time' => $start->format('H:i:s'),
        'status' => 'confirmed',
        'deposit_paid' => 25,
    ], $overrides));
}

it('waives the fee when cancelling 24+ hours before the appointment', function () {
    $appointment = appointmentStartingIn(48);

    expect($this->cancellationService->calculateCancellationFee($appointment, $this->salon))->toBe(0.0);
});

it('charges the fee when cancelling within 24 hours of the appointment', function () {
    $appointment = appointmentStartingIn(5);

    expect($this->cancellationService->calculateCancellationFee($appointment, $this->salon))->toBe(25.0);
});

it('cancels an appointment and records the fee, reason, and cancelled_at', function () {
    $appointment = appointmentStartingIn(48);

    $cancelled = $this->cancellationService->cancel($appointment, 'Change of plans');

    expect($cancelled->status)->toBe('cancelled')
        ->and($cancelled->cancellation_reason)->toBe('Change of plans')
        ->and($cancelled->cancellation_fee)->not->toBeNull()
        ->and($cancelled->cancelled_at)->not->toBeNull();
});

it('rejects cancelling an appointment that is already completed', function () {
    $appointment = appointmentStartingIn(-2, ['status' => 'completed']);

    $this->cancellationService->cancel($appointment);
})->throws(InvalidArgumentException::class);

it('notifies matching waitlist entries when a slot frees up', function () {
    $service = Service::factory()->create();
    $appointment = appointmentStartingIn(48, ['service_id' => $service->id]);

    $matching = Waitlist::factory()->create([
        'service_id' => $service->id,
        'requested_date' => $appointment->appointment_date,
        'status' => 'waiting',
    ]);

    $nonMatching = Waitlist::factory()->create([
        'service_id' => Service::factory()->create()->id,
        'requested_date' => $appointment->appointment_date,
        'status' => 'waiting',
    ]);

    $this->cancellationService->cancel($appointment);

    expect($matching->fresh()->status)->toBe('notified')
        ->and($matching->fresh()->notification_sent_at)->not->toBeNull()
        ->and($nonMatching->fresh()->status)->toBe('waiting');
});
