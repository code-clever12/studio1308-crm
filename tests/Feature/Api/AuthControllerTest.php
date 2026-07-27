<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('logs in an admin and returns a usable token', function () {
    $admin = User::factory()->admin()->create(['password' => bcrypt('password123')]);

    $response = $this->postJson(route('api.v1.auth.login'), [
        'email' => $admin->email,
        'password' => 'password123',
        'device_name' => 'Pixel 6',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']]);

    expect($admin->tokens()->count())->toBe(1)
        ->and($admin->tokens()->first()->name)->toBe('Pixel 6');
});

it('rejects login with a wrong password', function () {
    $admin = User::factory()->admin()->create(['password' => bcrypt('password123')]);

    $this->postJson(route('api.v1.auth.login'), [
        'email' => $admin->email,
        'password' => 'wrong-password',
        'device_name' => 'Pixel 6',
    ])->assertStatus(422)->assertJsonValidationErrors('email');
});

it('rejects login from a non-admin account', function () {
    $customer = User::factory()->create(['role' => 'customer', 'password' => bcrypt('password123')]);

    $this->postJson(route('api.v1.auth.login'), [
        'email' => $customer->email,
        'password' => 'password123',
        'device_name' => 'Pixel 6',
    ])->assertStatus(422)->assertJsonValidationErrors('email');
});

it('returns the authenticated admin on /auth/me', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $this->getJson(route('api.v1.auth.me'))
        ->assertOk()
        ->assertJsonPath('user.email', $admin->email);
});

it('revokes the current token on logout', function () {
    $admin = User::factory()->admin()->create();
    $token = $admin->createToken('Pixel 6');

    // A real Bearer token (not Sanctum::actingAs, which bypasses token
    // resolution entirely) so currentAccessToken()->delete() has a real
    // token record to revoke.
    $this->withHeader('Authorization', "Bearer {$token->plainTextToken}")
        ->postJson(route('api.v1.auth.logout'))
        ->assertOk();

    expect($admin->tokens()->count())->toBe(0);
});

it('blocks unauthenticated access to protected endpoints', function () {
    $this->getJson(route('api.v1.auth.me'))->assertUnauthorized();
});
