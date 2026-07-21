<?php

use App\Models\Appointment;
use App\Models\User;

it('redirects an admin visiting the generic dashboard to their real admin dashboard', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertRedirect(route('admin.dashboard'));
});

it('shows staff the generic placeholder dashboard', function () {
    $staffUser = User::factory()->create(['role' => 'staff']);

    $this->actingAs($staffUser)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee("You're logged in as staff.");
});

it('shows a customer their real dashboard with upcoming appointments', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'status' => 'confirmed',
        'appointment_date' => now()->addDays(3)->toDateString(),
    ]);

    $response = $this->actingAs($customer)->get(route('dashboard'));

    $response->assertOk();
    expect($response->viewData('upcoming')->pluck('id'))->toContain($appointment->id);
});
