<?php

use App\Models\Appointment;
use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Models\User;
use Carbon\Carbon;

function adminNextMonday(): string
{
    return Carbon::now()->next(Carbon::MONDAY)->toDateString();
}

beforeEach(function () {
    Salon::factory()->create(['opens_at' => '09:00:00', 'closes_at' => '18:00:00']);

    $this->admin = User::factory()->admin()->create();
    $this->customer = User::factory()->create(['role' => 'customer']);
    $this->service = Service::factory()->create(['price' => 80, 'duration_minutes' => 60, 'buffer_time_minutes' => 15]);
    $this->staff = Staff::factory()->create(['status' => 'active']);
    $this->staff->services()->attach($this->service->id, ['is_available' => true]);

    StaffSchedule::factory()->create([
        'staff_id' => $this->staff->id,
        'day_of_week' => Carbon::parse(adminNextMonday())->dayOfWeek,
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'break_start' => null,
        'break_end' => null,
        'is_working_day' => true,
    ]);
});

it('lets an admin create a confirmed walk-in booking', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.appointments.store'), [
        'customer_id' => $this->customer->id,
        'service_id' => $this->service->id,
        'staff_id' => $this->staff->id,
        'appointment_date' => adminNextMonday(),
        'start_time' => '10:00',
    ]);

    $response->assertRedirect(route('admin.appointments.index'));

    $appointment = Appointment::first();
    expect($appointment->status)->toBe('confirmed')
        ->and($appointment->payment_status)->toBe('paid')
        ->and((float) $appointment->deposit_paid)->toBe((float) $appointment->total_amount);
});

it('blocks a non-admin from creating a walk-in booking', function () {
    $this->actingAs($this->customer)
        ->post(route('admin.appointments.store'), [
            'customer_id' => $this->customer->id,
            'service_id' => $this->service->id,
            'appointment_date' => adminNextMonday(),
            'start_time' => '10:00',
        ])
        ->assertForbidden();
});

it('lets an admin reschedule an appointment to an available slot', function () {
    $appointment = Appointment::factory()->create([
        'staff_id' => $this->staff->id,
        'service_id' => $this->service->id,
        'appointment_date' => adminNextMonday(),
        'start_time' => '10:00:00',
        'end_time' => '11:15:00',
        'status' => 'confirmed',
    ]);

    $response = $this->actingAs($this->admin)->patch(route('admin.appointments.update', $appointment), [
        'appointment_date' => adminNextMonday(),
        'start_time' => '13:00',
    ]);

    $response->assertRedirect(route('admin.appointments.index'));
    expect($appointment->fresh()->start_time)->toBe('13:00:00');
});

it('rejects rescheduling onto an already-booked slot', function () {
    Appointment::factory()->create([
        'staff_id' => $this->staff->id,
        'appointment_date' => adminNextMonday(),
        'start_time' => '13:00:00',
        'end_time' => '14:15:00',
        'status' => 'confirmed',
    ]);

    $appointment = Appointment::factory()->create([
        'staff_id' => $this->staff->id,
        'service_id' => $this->service->id,
        'appointment_date' => adminNextMonday(),
        'start_time' => '10:00:00',
        'end_time' => '11:15:00',
        'status' => 'confirmed',
    ]);

    $response = $this->actingAs($this->admin)->patch(route('admin.appointments.update', $appointment), [
        'appointment_date' => adminNextMonday(),
        'start_time' => '13:00',
    ]);

    $response->assertSessionHasErrors('start_time');
    expect($appointment->fresh()->start_time)->toBe('10:00:00');
});

it('lets an admin cancel an appointment', function () {
    $appointment = Appointment::factory()->create([
        'staff_id' => $this->staff->id,
        'appointment_date' => Carbon::now()->addDays(3)->toDateString(),
        'start_time' => '10:00:00',
        'status' => 'confirmed',
    ]);

    $this->actingAs($this->admin)
        ->delete(route('admin.appointments.destroy', $appointment))
        ->assertRedirect(route('admin.appointments.index'));

    expect($appointment->fresh()->status)->toBe('cancelled');
});
