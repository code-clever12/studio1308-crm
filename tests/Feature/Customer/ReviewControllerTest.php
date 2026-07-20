<?php

use App\Models\Appointment;
use App\Models\Review;
use App\Models\Staff;
use App\Models\User;

beforeEach(function () {
    $this->customer = User::factory()->create(['role' => 'customer']);
    $this->staff = Staff::factory()->create();
});

it('lets a customer review a completed appointment', function () {
    $appointment = Appointment::factory()->create([
        'customer_id' => $this->customer->id,
        'staff_id' => $this->staff->id,
        'status' => 'completed',
    ]);

    $this->actingAs($this->customer)->post(route('customer.appointments.review', $appointment), [
        'rating' => 5,
        'comment' => 'Loved it!',
    ])->assertRedirect(route('customer.appointments.index'));

    $this->assertDatabaseHas('reviews', [
        'appointment_id' => $appointment->id,
        'rating' => 5,
    ]);
});

it('rejects reviewing an appointment that is not completed', function () {
    $appointment = Appointment::factory()->create([
        'customer_id' => $this->customer->id,
        'staff_id' => $this->staff->id,
        'status' => 'confirmed',
    ]);

    $this->actingAs($this->customer)->post(route('customer.appointments.review', $appointment), [
        'rating' => 5,
    ])->assertStatus(422);
});

it('rejects a second review of the same appointment', function () {
    $appointment = Appointment::factory()->create([
        'customer_id' => $this->customer->id,
        'staff_id' => $this->staff->id,
        'status' => 'completed',
    ]);

    Review::factory()->create(['appointment_id' => $appointment->id, 'customer_id' => $this->customer->id, 'staff_id' => $this->staff->id]);

    $this->actingAs($this->customer)->post(route('customer.appointments.review', $appointment), [
        'rating' => 4,
    ])->assertStatus(422);
});

it('prevents reviewing someone else\'s appointment', function () {
    $otherCustomer = User::factory()->create(['role' => 'customer']);
    $appointment = Appointment::factory()->create([
        'customer_id' => $otherCustomer->id,
        'staff_id' => $this->staff->id,
        'status' => 'completed',
    ]);

    $this->actingAs($this->customer)->post(route('customer.appointments.review', $appointment), [
        'rating' => 5,
    ])->assertForbidden();
});
