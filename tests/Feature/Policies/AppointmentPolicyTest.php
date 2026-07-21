<?php

use App\Models\Appointment;
use App\Models\Staff;
use App\Models\User;
use App\Policies\AppointmentPolicy;

/*
|--------------------------------------------------------------------------
| No staff-facing routes exist yet (only customer and admin route groups),
| so the staff branches of AppointmentPolicy are currently dead code from
| an HTTP standpoint — tested directly against the policy here so they
| don't silently rot before staff self-service routes ship.
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    $this->policy = new AppointmentPolicy;
});

it('lets a staff member view their own assigned appointment', function () {
    $staff = Staff::factory()->create();
    $appointment = Appointment::factory()->create(['staff_id' => $staff->id]);

    expect($this->policy->view($staff->user, $appointment))->toBeTrue();
});

it('blocks a staff member from viewing another staff member\'s appointment', function () {
    $staff = Staff::factory()->create();
    $otherStaff = Staff::factory()->create();
    $appointment = Appointment::factory()->create(['staff_id' => $otherStaff->id]);

    expect($this->policy->view($staff->user, $appointment))->toBeFalse();
});

it('lets a staff member update their own assigned appointment regardless of status', function () {
    $staff = Staff::factory()->create();
    $appointment = Appointment::factory()->create(['staff_id' => $staff->id, 'status' => 'completed']);

    expect($this->policy->update($staff->user, $appointment))->toBeTrue();
});

it('blocks a staff member from updating another staff member\'s appointment', function () {
    $staff = Staff::factory()->create();
    $otherStaff = Staff::factory()->create();
    $appointment = Appointment::factory()->create(['staff_id' => $otherStaff->id]);

    expect($this->policy->update($staff->user, $appointment))->toBeFalse();
});

it('blocks a staff member with no linked Staff row from viewing any appointment', function () {
    $staffUser = User::factory()->create(['role' => 'staff']);
    $appointment = Appointment::factory()->create();

    expect($this->policy->view($staffUser, $appointment))->toBeFalse();
});
