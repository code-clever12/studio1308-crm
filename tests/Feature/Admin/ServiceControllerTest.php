<?php

use App\Models\Category;
use App\Models\Service;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->category = Category::factory()->create();
});

it('lets an admin create a service', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.services.store'), [
        'category_id' => $this->category->id,
        'name' => 'Deluxe Facial',
        'price' => 120,
        'duration_minutes' => 60,
        'buffer_time_minutes' => 15,
        'is_taxable' => true,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('admin.services.index'));
    expect(Service::where('name', 'Deluxe Facial')->exists())->toBeTrue();
});

it('rejects a service with a deposit larger than its price', function () {
    $this->actingAs($this->admin)->post(route('admin.services.store'), [
        'category_id' => $this->category->id,
        'name' => 'Bad Deposit Service',
        'price' => 50,
        'deposit_amount' => 75,
        'duration_minutes' => 30,
    ])->assertSessionHasErrors('deposit_amount');
});

it('lets an admin update a service', function () {
    $service = Service::factory()->create(['category_id' => $this->category->id, 'price' => 50]);

    $this->actingAs($this->admin)->put(route('admin.services.update', $service), [
        'category_id' => $this->category->id,
        'name' => 'Updated Name',
        'price' => 65,
        'duration_minutes' => 45,
    ])->assertRedirect(route('admin.services.index'));

    expect($service->fresh()->name)->toBe('Updated Name')
        ->and((float) $service->fresh()->price)->toBe(65.0);
});

it('deactivates rather than hard-deletes a service on destroy', function () {
    $service = Service::factory()->create(['category_id' => $this->category->id, 'is_active' => true]);

    $this->actingAs($this->admin)
        ->delete(route('admin.services.destroy', $service))
        ->assertRedirect(route('admin.services.index'));

    expect($service->fresh()->is_active)->toBeFalse();
    $this->assertDatabaseHas('services', ['id' => $service->id]);
});
