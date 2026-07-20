<?php

use App\Models\ACHBankAccount;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('lets an admin create a staff member with a new user account and assigned services', function () {
    $services = Service::factory()->count(2)->create();

    $response = $this->actingAs($this->admin)->post(route('admin.staff.store'), [
        'name' => 'Jamie Fox',
        'email' => 'jamie@example.test',
        'phone' => '2125551234',
        'bio' => 'Colorist',
        'commission_rate' => 25,
        'status' => 'active',
        'hire_date' => now()->subYear()->toDateString(),
        'service_ids' => $services->pluck('id')->all(),
    ]);

    $user = User::where('email', 'jamie@example.test')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe('staff');

    $staff = Staff::where('user_id', $user->id)->first();
    expect($staff)->not->toBeNull()
        ->and((float) $staff->commission_rate)->toBe(25.0)
        ->and($staff->services()->count())->toBe(2);

    $response->assertRedirect(route('admin.staff.edit', $staff));
});

it('lets an admin update a staff member and resync their services', function () {
    $staff = Staff::factory()->create();
    $oldService = Service::factory()->create();
    $newService = Service::factory()->create();
    $staff->services()->attach($oldService->id, ['is_available' => true]);

    $this->actingAs($this->admin)->put(route('admin.staff.update', $staff), [
        'name' => $staff->user->name,
        'email' => $staff->user->email,
        'commission_rate' => 30,
        'status' => 'active',
        'service_ids' => [$newService->id],
    ])->assertRedirect(route('admin.staff.edit', $staff));

    $staff->refresh();
    expect((float) $staff->commission_rate)->toBe(30.0)
        ->and($staff->services()->pluck('services.id')->all())->toBe([$newService->id]);
});

it('deactivates a staff member and their user account on destroy', function () {
    $staff = Staff::factory()->create(['status' => 'active']);

    $this->actingAs($this->admin)
        ->delete(route('admin.staff.destroy', $staff))
        ->assertRedirect(route('admin.staff.index'));

    expect($staff->fresh()->status)->toBe('inactive')
        ->and($staff->user->fresh()->is_active)->toBeFalse();
});

it('lets an admin verify a staff bank account with Stripe', function () {
    $staff = Staff::factory()->create();
    ACHBankAccount::factory()->create(['staff_id' => $staff->id, 'verification_status' => 'pending']);

    $http = mockStripeHttp();
    $http->shouldReceive('request')->once()
        ->withArgs(fn ($method, $url) => str_contains($url, '/v1/accounts') && ! str_contains($url, 'external_accounts'))
        ->andReturn(stripeHttpResponse(['id' => 'acct_test123', 'object' => 'account']));
    $http->shouldReceive('request')->once()
        ->withArgs(fn ($method, $url) => str_contains($url, '/external_accounts'))
        ->andReturn(stripeHttpResponse(['id' => 'ba_test123', 'object' => 'bank_account', 'status' => 'new']));

    $this->actingAs($this->admin)
        ->post(route('admin.staff.ach-account.verify', $staff))
        ->assertRedirect(route('admin.staff.edit', $staff));

    expect($staff->achBankAccount->fresh()->verification_status)->toBe('verified');
});

it('shows an error when Stripe verification fails', function () {
    $staff = Staff::factory()->create();
    ACHBankAccount::factory()->create(['staff_id' => $staff->id, 'verification_status' => 'pending']);

    $http = mockStripeHttp();
    $http->shouldReceive('request')->once()
        ->andReturn(stripeHttpResponse([
            'error' => ['type' => 'invalid_request_error', 'message' => 'Missing required param.'],
        ], 400));

    $this->actingAs($this->admin)
        ->post(route('admin.staff.ach-account.verify', $staff))
        ->assertSessionHasErrors('ach');

    expect($staff->achBankAccount->fresh()->verification_status)->toBe('failed');
});

it('blocks a non-admin from managing staff', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->get(route('admin.staff.index'))
        ->assertForbidden();
});
