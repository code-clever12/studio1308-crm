<?php

use App\Jobs\ChargeNoShowFee;
use App\Jobs\MarkNoShowAppointments;
use App\Models\Appointment;
use App\Services\NoShowFeeService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;

it('marks eligible appointments as no-shows and queues the fee charge', function () {
    Queue::fake();

    $start = Carbon::now()->subMinutes(45);

    $appointment = Appointment::factory()->create([
        'appointment_date' => $start->toDateString(),
        'start_time' => $start->format('H:i:s'),
        'status' => 'confirmed',
    ]);

    $notEligible = Appointment::factory()->create([
        'appointment_date' => Carbon::now()->toDateString(),
        'start_time' => Carbon::now()->format('H:i:s'),
        'status' => 'confirmed',
    ]);

    (new MarkNoShowAppointments)->handle(app(NoShowFeeService::class));

    expect($appointment->fresh()->status)->toBe('no_show')
        ->and($notEligible->fresh()->status)->toBe('confirmed');

    Queue::assertPushed(ChargeNoShowFee::class, fn ($job) => $job->appointment->id === $appointment->id);
});

it('logs and exits cleanly when charging the no-show fee since Stripe is not yet wired up', function () {
    $appointment = Appointment::factory()->create(['status' => 'no_show']);

    (new ChargeNoShowFee($appointment))->handle(
        app(NoShowFeeService::class),
        app(NotificationService::class),
    );

    expect($appointment->fresh()->no_show_fee_charged)->toBeFalse();
});
