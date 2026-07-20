<?php

use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
});

it('lets an admin block a customer', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.customers.block', $this->customer))
        ->assertRedirect();

    expect($this->customer->fresh()->is_active)->toBeFalse();
});

it('lets an admin unblock a customer', function () {
    $this->customer->update(['is_active' => false]);

    $this->actingAs($this->admin)
        ->post(route('admin.customers.unblock', $this->customer))
        ->assertRedirect();

    expect($this->customer->fresh()->is_active)->toBeTrue();
});

it('404s when trying to block a non-customer user', function () {
    $staffUser = User::factory()->create(['role' => 'staff']);

    $this->actingAs($this->admin)
        ->post(route('admin.customers.block', $staffUser))
        ->assertNotFound();
});
