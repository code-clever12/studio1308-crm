<?php

use App\Models\DeviceToken;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Http;

afterEach(function () {
    if (isset($this->fcmCredentialsPath) && file_exists($this->fcmCredentialsPath)) {
        unlink($this->fcmCredentialsPath);
    }
});

it('does nothing when FCM credentials are not configured', function () {
    config(['services.fcm.project_id' => null, 'services.fcm.credentials_path' => null]);
    Http::fake();

    $user = User::factory()->admin()->create();
    DeviceToken::factory()->create(['user_id' => $user->id]);

    app(PushNotificationService::class)->sendToUser($user, 'Title', 'Body');

    Http::assertNothingSent();
});

it('does nothing when the user has no registered devices', function () {
    $this->fcmCredentialsPath = tempnam(sys_get_temp_dir(), 'fcm');
    file_put_contents($this->fcmCredentialsPath, json_encode([
        'client_email' => 'test@example.iam.gserviceaccount.com',
        'private_key' => generateTestRsaPrivateKey(),
    ]));

    config([
        'services.fcm.project_id' => 'test-project',
        'services.fcm.credentials_path' => $this->fcmCredentialsPath,
    ]);

    Http::fake();

    $user = User::factory()->admin()->create();

    app(PushNotificationService::class)->sendToUser($user, 'Title', 'Body');

    Http::assertNothingSent();
});

it('exchanges the service account JWT for a token and sends to every registered device', function () {
    $this->fcmCredentialsPath = tempnam(sys_get_temp_dir(), 'fcm');
    file_put_contents($this->fcmCredentialsPath, json_encode([
        'client_email' => 'test@example.iam.gserviceaccount.com',
        'private_key' => generateTestRsaPrivateKey(),
    ]));

    config([
        'services.fcm.project_id' => 'test-project',
        'services.fcm.credentials_path' => $this->fcmCredentialsPath,
    ]);

    Http::fake([
        'oauth2.googleapis.com/*' => Http::response(['access_token' => 'fake-access-token'], 200),
        'fcm.googleapis.com/*' => Http::response([], 200),
    ]);

    $user = User::factory()->admin()->create();
    DeviceToken::factory()->create(['user_id' => $user->id, 'token' => 'device-token-1']);
    DeviceToken::factory()->create(['user_id' => $user->id, 'token' => 'device-token-2']);

    app(PushNotificationService::class)->sendToUser($user, 'New Lead', 'New lead from Hero Form', ['submission_id' => '5']);

    Http::assertSent(fn ($request) => str_contains($request->url(), 'oauth2.googleapis.com'));
    Http::assertSent(fn ($request) => str_contains($request->url(), 'fcm.googleapis.com')
        && $request['message']['token'] === 'device-token-1'
        && $request['message']['notification']['title'] === 'New Lead');
    Http::assertSent(fn ($request) => str_contains($request->url(), 'fcm.googleapis.com')
        && $request['message']['token'] === 'device-token-2');
});

it('caches the access token instead of re-authenticating for every send', function () {
    $this->fcmCredentialsPath = tempnam(sys_get_temp_dir(), 'fcm');
    file_put_contents($this->fcmCredentialsPath, json_encode([
        'client_email' => 'test@example.iam.gserviceaccount.com',
        'private_key' => generateTestRsaPrivateKey(),
    ]));

    config([
        'services.fcm.project_id' => 'test-project',
        'services.fcm.credentials_path' => $this->fcmCredentialsPath,
    ]);

    Http::fake([
        'oauth2.googleapis.com/*' => Http::response(['access_token' => 'fake-access-token'], 200),
        'fcm.googleapis.com/*' => Http::response([], 200),
    ]);

    $userA = User::factory()->admin()->create();
    DeviceToken::factory()->create(['user_id' => $userA->id]);
    $userB = User::factory()->admin()->create();
    DeviceToken::factory()->create(['user_id' => $userB->id]);

    $service = app(PushNotificationService::class);
    $service->sendToUser($userA, 'Title', 'Body');
    $service->sendToUser($userB, 'Title', 'Body');

    Http::assertSentCount(3); // one OAuth exchange (cached) + two FCM sends
});
