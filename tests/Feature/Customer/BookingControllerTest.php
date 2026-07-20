<?php

use App\Models\Appointment;
use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Models\User;
use App\Models\Waitlist;
use Carbon\Carbon;

function customerNextMonday(): string
{
    return Carbon::now()->next(Carbon::MONDAY)->toDateString();
}

beforeEach(function () {
    Salon::factory()->create(['opens_at' => '09:00:00', 'closes_at' => '18:00:00', 'sales_tax_rate' => 10, 'deposit_percentage' => 25]);

    $this->customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
    $this->service = Service::factory()->create(['price' => 100, 'duration_minutes' => 60, 'buffer_time_minutes' => 15, 'is_active' => true]);
    $this->staff = Staff::factory()->create(['status' => 'active']);
    $this->staff->services()->attach($this->service->id, ['is_available' => true]);

    StaffSchedule::factory()->create([
        'staff_id' => $this->staff->id,
        'day_of_week' => Carbon::parse(customerNextMonday())->dayOfWeek,
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'is_working_day' => true,
    ]);
});

it('returns available slots as json for a service and date', function () {
    $response = $this->actingAs($this->customer)->getJson(
        route('customer.booking.slots', ['service_id' => $this->service->id, 'date' => customerNextMonday()])
    );

    $response->assertOk();
    expect($response->json('slots'))->toHaveKey((string) $this->staff->id);
});

it('returns a checkout breakdown as json', function () {
    $response = $this->actingAs($this->customer)->getJson(
        route('customer.booking.breakdown', ['service_id' => $this->service->id, 'tip' => 15])
    );

    $response->assertOk()
        ->assertJson([
            'subtotal' => 100.0,
            'tax' => 10.0,
            'deposit' => 25.0,
            'tip' => 15.0,
        ]);
});

it('lets a customer book an appointment', function () {
    $response = $this->actingAs($this->customer)->post(route('customer.booking.store'), [
        'service_id' => $this->service->id,
        'staff_id' => $this->staff->id,
        'appointment_date' => customerNextMonday(),
        'start_time' => '10:00',
    ]);

    $response->assertRedirect(route('customer.appointments.index'));

    $appointment = Appointment::where('customer_id', $this->customer->id)->first();
    expect($appointment)->not->toBeNull()
        ->and($appointment->status)->toBe('pending');
});

it('rejects booking a slot that is already taken', function () {
    Appointment::factory()->create([
        'staff_id' => $this->staff->id,
        'appointment_date' => customerNextMonday(),
        'start_time' => '10:00:00',
        'end_time' => '11:15:00',
        'status' => 'confirmed',
    ]);

    $this->actingAs($this->customer)->post(route('customer.booking.store'), [
        'service_id' => $this->service->id,
        'staff_id' => $this->staff->id,
        'appointment_date' => customerNextMonday(),
        'start_time' => '10:00',
    ])->assertSessionHasErrors('start_time');
});

it('blocks a customer with booking restrictions from booking', function () {
    $this->customer->update(['is_active' => false]);

    $this->actingAs($this->customer)->post(route('customer.booking.store'), [
        'service_id' => $this->service->id,
        'staff_id' => $this->staff->id,
        'appointment_date' => customerNextMonday(),
        'start_time' => '10:00',
    ])->assertSessionHasErrors('booking');
});

it('lets a customer join the waitlist', function () {
    $this->actingAs($this->customer)->post(route('customer.booking.waitlist'), [
        'service_id' => $this->service->id,
        'staff_id' => $this->staff->id,
        'requested_date' => customerNextMonday(),
    ])->assertRedirect(route('dashboard'));

    expect(Waitlist::where('customer_id', $this->customer->id)->exists())->toBeTrue();
});

it('blocks staff and admin from customer booking routes', function () {
    $staffUser = User::factory()->create(['role' => 'staff']);
    $admin = User::factory()->admin()->create();

    $this->actingAs($staffUser)->getJson(route('customer.booking.slots', ['service_id' => $this->service->id, 'date' => customerNextMonday()]))->assertForbidden();
    $this->actingAs($admin)->getJson(route('customer.booking.slots', ['service_id' => $this->service->id, 'date' => customerNextMonday()]))->assertForbidden();
});
