<?php

use App\Models\Tip;
use App\Models\User;

beforeEach(function () {
    $this->customer = User::factory()->create(['role' => 'customer']);
});

it('lists only the authenticated customer\'s own tips', function () {
    $ownTip = Tip::factory()->create(['customer_id' => $this->customer->id, 'amount' => 15]);
    $otherCustomer = User::factory()->create(['role' => 'customer']);
    Tip::factory()->create(['customer_id' => $otherCustomer->id, 'amount' => 20]);

    $response = $this->actingAs($this->customer)->get(route('customer.tips.index'));

    $response->assertOk();
    $tips = $response->viewData('tips');
    expect($tips)->toHaveCount(1)
        ->and($tips->first()->id)->toBe($ownTip->id);
});

it('shows an empty state when the customer has never tipped', function () {
    $response = $this->actingAs($this->customer)->get(route('customer.tips.index'));

    $response->assertOk()->assertSee("You haven't left any tips yet.");
});

it('blocks staff and admin from the customer tips route', function () {
    $staffUser = User::factory()->create(['role' => 'staff']);
    $admin = User::factory()->admin()->create();

    $this->actingAs($staffUser)->get(route('customer.tips.index'))->assertForbidden();
    $this->actingAs($admin)->get(route('customer.tips.index'))->assertForbidden();
});
