<?php

use App\Jobs\ProcessACHPayouts;
use App\Jobs\SyncStripePaymentStatus;
use App\Models\ACHBankAccount;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Staff;
use App\Services\ACHPayoutService;
use Carbon\Carbon;

it('creates a pending payout for each verified staff member with earnings', function () {
    $verifiedStaff = Staff::factory()->create(['status' => 'active', 'commission_rate' => 20]);
    ACHBankAccount::factory()->create(['staff_id' => $verifiedStaff->id, 'verification_status' => 'verified']);

    $unverifiedStaff = Staff::factory()->create(['status' => 'active']);

    $from = Carbon::now()->subDay();
    $to = Carbon::now()->addDay();

    Appointment::factory()->create([
        'staff_id' => $verifiedStaff->id,
        'service_price' => 100,
        'status' => 'completed',
        'appointment_date' => Carbon::now()->toDateString(),
    ]);

    (new ProcessACHPayouts($from, $to))->handle(app(ACHPayoutService::class));

    $this->assertDatabaseHas('ach_payouts', ['staff_id' => $verifiedStaff->id, 'status' => 'pending']);
    $this->assertDatabaseMissing('ach_payouts', ['staff_id' => $unverifiedStaff->id]);
});

it('throws for the stubbed Stripe payment status sync pending Step 8', function () {
    $payment = Payment::factory()->create();

    (new SyncStripePaymentStatus($payment))->handle();
})->throws(RuntimeException::class);
