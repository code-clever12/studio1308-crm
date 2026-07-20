<?php

use App\Jobs\ProcessACHPayouts;
use App\Models\ACHBankAccount;
use App\Models\Appointment;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('queues a batch payout run for a date range', function () {
    Queue::fake();

    $this->actingAs($this->admin)->post(route('admin.payouts.store'), [
        'from' => now()->subWeek()->toDateString(),
        'to' => now()->toDateString(),
    ])->assertRedirect(route('admin.payouts.index'));

    Queue::assertPushed(ProcessACHPayouts::class);
});

it('creates a payout for a single verified staff member with earnings', function () {
    $staff = Staff::factory()->create(['commission_rate' => 20]);
    ACHBankAccount::factory()->create(['staff_id' => $staff->id, 'verification_status' => 'verified']);

    Appointment::factory()->create([
        'staff_id' => $staff->id,
        'service_price' => 100,
        'status' => 'completed',
        'appointment_date' => now()->toDateString(),
    ]);

    $this->actingAs($this->admin)->post(route('admin.payouts.store-for-staff', $staff), [
        'from' => now()->subDay()->toDateString(),
        'to' => now()->addDay()->toDateString(),
    ])->assertRedirect(route('admin.payouts.index'));

    $this->assertDatabaseHas('ach_payouts', ['staff_id' => $staff->id, 'status' => 'pending']);
});
