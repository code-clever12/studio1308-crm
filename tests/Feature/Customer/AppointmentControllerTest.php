<?php

use App\Models\Appointment;
use App\Models\Salon;
use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    Salon::factory()->create(['no_show_fee' => 25]);

    $this->customer = User::factory()->create(['role' => 'customer']);
    $this->staff = Staff::factory()->create();

    $nextMonday = Carbon::now()->next(Carbon::MONDAY);

    StaffSchedule::factory()->create([
        'staff_id' => $this->staff->id,
        'day_of_week' => $nextMonday->dayOfWeek,
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'is_working_day' => true,
    ]);

    $this->appointment = Appointment::factory()->create([
        'customer_id' => $this->customer->id,
        'staff_id' => $this->staff->id,
        'appointment_date' => $nextMonday->toDateString(),
        'start_time' => '10:00:00',
        'end_time' => '11:00:00',
        'status' => 'confirmed',
    ]);

    $this->nextMonday = $nextMonday->toDateString();
});

it('lets a customer reschedule their own appointment', function () {
    $this->actingAs($this->customer)->patch(route('customer.appointments.update', $this->appointment), [
        'appointment_date' => $this->nextMonday,
        'start_time' => '13:00',
    ])->assertRedirect(route('customer.appointments.index'));

    expect($this->appointment->fresh()->start_time)->toBe('13:00:00');
});

it('prevents a customer from rescheduling someone else\'s appointment', function () {
    $otherCustomer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($otherCustomer)->patch(route('customer.appointments.update', $this->appointment), [
        'appointment_date' => $this->nextMonday,
        'start_time' => '13:00',
    ])->assertForbidden();
});

it('lets a customer cancel their own upcoming appointment', function () {
    $this->actingAs($this->customer)
        ->delete(route('customer.appointments.destroy', $this->appointment))
        ->assertRedirect(route('customer.appointments.index'));

    expect($this->appointment->fresh()->status)->toBe('cancelled');
});

it('prevents cancelling an already-completed appointment', function () {
    $this->appointment->update(['status' => 'completed']);

    $this->actingAs($this->customer)
        ->delete(route('customer.appointments.destroy', $this->appointment))
        ->assertForbidden();
});
