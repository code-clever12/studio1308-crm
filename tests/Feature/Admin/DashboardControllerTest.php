<?php

use App\Models\Appointment;
use App\Models\User;

it('renders the admin dashboard with real KPI data', function () {
    $admin = User::factory()->admin()->create();

    Appointment::factory()->create([
        'appointment_date' => today()->toDateString(),
        'status' => 'confirmed',
        'deposit_paid' => 40,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertSee("Today's Appointments")
        ->assertSee('1')
        ->assertSee('$40.00');
});

it('blocks a customer from viewing the admin dashboard', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
});
