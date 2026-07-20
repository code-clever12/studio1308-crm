<?php

use App\Models\ACHBankAccount;
use App\Models\Appointment;
use App\Models\Staff;
use App\Models\Tip;
use App\Services\ACHPayoutService;
use Carbon\Carbon;

beforeEach(function () {
    $this->achPayoutService = app(ACHPayoutService::class);
    $this->staff = Staff::factory()->create(['commission_rate' => 20]);
});

it('calculates combined commission and tip earnings for a staff member', function () {
    $today = Carbon::today();

    Appointment::factory()->create([
        'staff_id' => $this->staff->id,
        'service_price' => 100,
        'status' => 'completed',
        'appointment_date' => $today->toDateString(),
    ]);

    Tip::factory()->create([
        'staff_id' => $this->staff->id,
        'status' => 'completed',
        'amount' => 15,
    ]);

    $earnings = $this->achPayoutService->calculateEarnings($this->staff, $today->copy()->subDay(), $today->copy()->addDay());

    expect($earnings['commission'])->toBe(20.0)
        ->and($earnings['tips'])->toBe(15.0)
        ->and($earnings['total'])->toBe(35.0);
});

it('refuses to create a payout without a verified ACH bank account', function () {
    $today = Carbon::today();

    $this->achPayoutService->createPayout($this->staff, $today->copy()->subDay(), $today->copy()->addDay());
})->throws(RuntimeException::class);

it('creates a pending payout record when earnings exist and the account is verified', function () {
    ACHBankAccount::factory()->create([
        'staff_id' => $this->staff->id,
        'verification_status' => 'verified',
    ]);

    $today = Carbon::today();

    Appointment::factory()->create([
        'staff_id' => $this->staff->id,
        'service_price' => 200,
        'status' => 'completed',
        'appointment_date' => $today->toDateString(),
    ]);

    $payout = $this->achPayoutService->createPayout($this->staff, $today->copy()->subDay(), $today->copy()->addDay());

    expect($payout->status)->toBe('pending')
        ->and((float) $payout->commission_amount)->toBe(40.0)
        ->and((float) $payout->amount)->toBe(40.0);
});

it('creates a Stripe Connect account and attaches the bank account when verifying', function () {
    $bankAccount = ACHBankAccount::factory()->create([
        'staff_id' => $this->staff->id,
        'verification_status' => 'pending',
        'bank_account_holder_name' => 'Jamie Barber',
        'bank_account_routing_number' => '110000000',
        'bank_account_number' => '000123456789',
    ]);

    $http = mockStripeHttp();
    $http->shouldReceive('request')->once()
        ->withArgs(fn ($method, $url) => $method === 'post' && str_contains($url, '/v1/accounts') && ! str_contains($url, 'external_accounts'))
        ->andReturn(stripeHttpResponse(['id' => 'acct_test123', 'object' => 'account']));
    $http->shouldReceive('request')->once()
        ->withArgs(fn ($method, $url) => $method === 'post' && str_contains($url, '/v1/accounts/acct_test123/external_accounts'))
        ->andReturn(stripeHttpResponse(['id' => 'ba_test123', 'object' => 'bank_account', 'status' => 'new']));

    $updated = $this->achPayoutService->verifyBankAccount($this->staff);

    expect($updated->verification_status)->toBe('verified')
        ->and($updated->stripe_bank_account_token)->toBe('ba_test123')
        ->and($this->staff->fresh()->stripe_connect_account_id)->toBe('acct_test123');
});

it('marks the bank account failed when Stripe rejects the external account', function () {
    ACHBankAccount::factory()->create(['staff_id' => $this->staff->id, 'verification_status' => 'pending']);

    $http = mockStripeHttp();
    $http->shouldReceive('request')->once()
        ->withArgs(fn ($method, $url) => str_contains($url, '/v1/accounts') && ! str_contains($url, 'external_accounts'))
        ->andReturn(stripeHttpResponse(['id' => 'acct_test123', 'object' => 'account']));
    $http->shouldReceive('request')->once()
        ->withArgs(fn ($method, $url) => str_contains($url, '/external_accounts'))
        ->andReturn(stripeHttpResponse([
            'error' => ['type' => 'invalid_request_error', 'message' => 'Invalid routing number.'],
        ], 400));

    $this->achPayoutService->verifyBankAccount($this->staff);
})->throws(RuntimeException::class);

it('initiates a Stripe transfer for an in-transit payout', function () {
    $this->staff->update(['stripe_connect_account_id' => 'acct_test123']);
    $payout = App\Models\ACHPayout::factory()->create([
        'staff_id' => $this->staff->id,
        'status' => 'pending',
        'amount' => 150,
        'stripe_payout_id' => null,
    ]);

    $http = mockStripeHttp();
    $http->shouldReceive('request')->once()
        ->withArgs(fn ($method, $url, $headers, $params) => $method === 'post'
            && str_contains($url, '/v1/transfers')
            && $params['amount'] === 15000
            && $params['destination'] === 'acct_test123')
        ->andReturn(stripeHttpResponse(['id' => 'tr_test123', 'object' => 'transfer']));

    $updated = $this->achPayoutService->initiateTransfer($payout);

    expect($updated->status)->toBe('in_transit')
        ->and($updated->stripe_payout_id)->toBe('tr_test123');
});

it('refuses to initiate a transfer without a Stripe Connect account', function () {
    $payout = App\Models\ACHPayout::factory()->create(['staff_id' => $this->staff->id, 'status' => 'pending']);

    $this->achPayoutService->initiateTransfer($payout);
})->throws(RuntimeException::class);
