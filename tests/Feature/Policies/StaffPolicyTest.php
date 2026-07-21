<?php

use App\Models\Staff;
use App\Models\User;
use App\Policies\StaffPolicy;

/*
|--------------------------------------------------------------------------
| No staff self-service routes exist yet, so StaffPolicy::update()'s
| self-service branch is untested via HTTP — verified directly here.
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    $this->policy = new StaffPolicy;
});

it('lets a staff member update their own profile', function () {
    $staff = Staff::factory()->create();

    expect($this->policy->update($staff->user, $staff))->toBeTrue();
});

it('blocks a staff member from updating another staff member\'s profile', function () {
    $staff = Staff::factory()->create();
    $otherStaff = Staff::factory()->create();

    expect($this->policy->update($staff->user, $otherStaff))->toBeFalse();
});

it('blocks a customer from updating any staff profile', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $staff = Staff::factory()->create();

    expect($this->policy->update($customer, $staff))->toBeFalse();
});
