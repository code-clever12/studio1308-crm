<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends push notifications to the mobile app via Firebase Cloud Messaging's
 * HTTP v1 API. Built without the official kreait/laravel-firebase package —
 * its dependency chain requires either PHP 8.3+ or the ext-sodium extension,
 * neither available on this PHP 8.2 XAMPP install (same class of platform
 * gap as spatie/laravel-backup during Step 11 — resolved the same way, with
 * a minimal dependency-free implementation rather than forcing an
 * unverifiable --ignore-platform-req install). RS256 JWT signing only needs
 * PHP's built-in openssl extension, which is present here.
 *
 * Gracefully does nothing until FCM_PROJECT_ID and FCM_CREDENTIALS_PATH are
 * set (see config/services.php) — same "build now, connect later" pattern
 * as Stripe/Sentry.
 */
class PushNotificationService
{
    public function isConfigured(): bool
    {
        $path = config('services.fcm.credentials_path');

        return filled(config('services.fcm.project_id')) && filled($path) && is_readable($path);
    }

    /**
     * @param  array<string, string>  $data
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): void
    {
        if (! $this->isConfigured() || $user->deviceTokens->isEmpty()) {
            return;
        }

        $accessToken = $this->accessToken();

        if ($accessToken === null) {
            return;
        }

        $user->deviceTokens->each(
            fn ($deviceToken) => $this->send($deviceToken->token, $accessToken, $title, $body, $data)
        );
    }

    /**
     * @param  array<string, string>  $data
     */
    protected function send(string $token, string $accessToken, string $title, string $body, array $data): void
    {
        $projectId = config('services.fcm.project_id');

        $response = Http::withToken($accessToken)->post(
            "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
            [
                'message' => [
                    'token' => $token,
                    'notification' => ['title' => $title, 'body' => $body],
                    'data' => $data,
                ],
            ]
        );

        if ($response->failed()) {
            Log::warning('FCM push notification failed.', ['response' => $response->json()]);
        }
    }

    /**
     * Exchanges the Firebase service account's signed JWT for a short-lived
     * OAuth2 access token (Google's JWT-bearer grant), cached just under its
     * 1-hour lifetime so this only round-trips to Google once per hour.
     */
    protected function accessToken(): ?string
    {
        return Cache::remember('fcm_access_token', now()->addMinutes(55), function () {
            $credentials = json_decode((string) file_get_contents(config('services.fcm.credentials_path')), true);

            if (! is_array($credentials) || ! isset($credentials['client_email'], $credentials['private_key'])) {
                Log::warning('FCM credentials file is missing client_email/private_key.');

                return null;
            }

            $now = time();

            $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $claims = $this->base64UrlEncode(json_encode([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ]));

            $signingInput = "{$header}.{$claims}";

            if (! openssl_sign($signingInput, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256)) {
                Log::warning('FCM: failed to sign JWT with the service account private key.');

                return null;
            }

            $jwt = $signingInput.'.'.$this->base64UrlEncode($signature);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->failed()) {
                Log::warning('FCM OAuth2 token exchange failed.', ['response' => $response->json()]);

                return null;
            }

            return $response->json('access_token');
        });
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
