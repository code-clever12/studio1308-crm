<?php

use App\Models\DeviceToken;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('registers a device token for the authenticated admin', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->postJson(route('api.v1.device-tokens.store'), [
        'token' => 'fcm-token-abc123',
        'platform' => 'android',
    ]);

    $response->assertCreated()->assertJson(['success' => true]);

    expect(DeviceToken::where('token', 'fcm-token-abc123')->first()->user_id)->toBe($this->admin->id);
});

it('reassigns an existing token to whoever registers it again (reinstall/refresh)', function () {
    $other = User::factory()->admin()->create();
    DeviceToken::factory()->create(['token' => 'fcm-token-abc123', 'user_id' => $other->id]);

    Sanctum::actingAs($this->admin);

    $this->postJson(route('api.v1.device-tokens.store'), [
        'token' => 'fcm-token-abc123',
        'platform' => 'android',
    ])->assertCreated();

    expect(DeviceToken::where('token', 'fcm-token-abc123')->count())->toBe(1)
        ->and(DeviceToken::where('token', 'fcm-token-abc123')->first()->user_id)->toBe($this->admin->id);
});

it('rejects an invalid platform', function () {
    Sanctum::actingAs($this->admin);

    $this->postJson(route('api.v1.device-tokens.store'), [
        'token' => 'fcm-token-abc123',
        'platform' => 'windows-phone',
    ])->assertStatus(422)->assertJsonValidationErrors('platform');
});

it('unregisters a device token', function () {
    DeviceToken::factory()->create(['token' => 'fcm-token-abc123', 'user_id' => $this->admin->id]);
    Sanctum::actingAs($this->admin);

    $this->deleteJson(route('api.v1.device-tokens.destroy'), ['token' => 'fcm-token-abc123'])
        ->assertOk()->assertJson(['success' => true]);

    expect(DeviceToken::where('token', 'fcm-token-abc123')->exists())->toBeFalse();
});

it('requires authentication', function () {
    $this->postJson(route('api.v1.device-tokens.store'), [
        'token' => 'fcm-token-abc123',
        'platform' => 'android',
    ])->assertUnauthorized();
});
