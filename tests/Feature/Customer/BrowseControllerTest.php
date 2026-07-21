<?php

use App\Models\Category;
use App\Models\Review;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;

beforeEach(function () {
    $this->customer = User::factory()->create(['role' => 'customer']);
});

it('only lists active services', function () {
    $active = Service::factory()->create(['is_active' => true]);
    $inactive = Service::factory()->create(['is_active' => false]);

    $response = $this->actingAs($this->customer)->get(route('customer.browse'));

    $response->assertOk();
    $services = $response->viewData('services');
    expect($services->pluck('id'))->toContain($active->id)
        ->and($services->pluck('id'))->not->toContain($inactive->id);
});

it('filters services by search term', function () {
    $match = Service::factory()->create(['name' => 'Signature Facial', 'is_active' => true]);
    $other = Service::factory()->create(['name' => 'Swedish Massage', 'is_active' => true]);

    $response = $this->actingAs($this->customer)->get(route('customer.browse', ['search' => 'Facial']));

    $services = $response->viewData('services');
    expect($services->pluck('id'))->toContain($match->id)
        ->and($services->pluck('id'))->not->toContain($other->id);
});

it('filters services by category', function () {
    $categoryA = Category::factory()->create();
    $categoryB = Category::factory()->create();
    $inCategoryA = Service::factory()->create(['category_id' => $categoryA->id, 'is_active' => true]);
    $inCategoryB = Service::factory()->create(['category_id' => $categoryB->id, 'is_active' => true]);

    $response = $this->actingAs($this->customer)->get(route('customer.browse', ['category_id' => $categoryA->id]));

    $services = $response->viewData('services');
    expect($services->pluck('id'))->toContain($inCategoryA->id)
        ->and($services->pluck('id'))->not->toContain($inCategoryB->id);
});

it('filters services by max price', function () {
    $cheap = Service::factory()->create(['price' => 40, 'is_active' => true]);
    $expensive = Service::factory()->create(['price' => 200, 'is_active' => true]);

    $response = $this->actingAs($this->customer)->get(route('customer.browse', ['max_price' => 100]));

    $services = $response->viewData('services');
    expect($services->pluck('id'))->toContain($cheap->id)
        ->and($services->pluck('id'))->not->toContain($expensive->id);
});

it('only lists active staff with their average rating', function () {
    $activeStaff = Staff::factory()->create(['status' => 'active']);
    $inactiveStaff = Staff::factory()->create(['status' => 'inactive']);
    Review::factory()->create(['staff_id' => $activeStaff->id, 'rating' => 5]);

    $response = $this->actingAs($this->customer)->get(route('customer.browse'));

    $staff = $response->viewData('staff');
    expect($staff->pluck('id'))->toContain($activeStaff->id)
        ->and($staff->pluck('id'))->not->toContain($inactiveStaff->id);

    $matched = $staff->firstWhere('id', $activeStaff->id);
    expect((float) $matched->reviews_avg_rating)->toBe(5.0);
});

it('blocks staff and admin from the customer browse route', function () {
    $staffUser = User::factory()->create(['role' => 'staff']);
    $admin = User::factory()->admin()->create();

    $this->actingAs($staffUser)->get(route('customer.browse'))->assertForbidden();
    $this->actingAs($admin)->get(route('customer.browse'))->assertForbidden();
});
