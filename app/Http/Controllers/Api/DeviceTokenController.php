<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Registers/unregisters the mobile app's FCM device token against the
 * authenticated admin — see App\Services\PushNotificationService.
 */
class DeviceTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:255'],
            'platform' => ['required', 'in:'.implode(',', DeviceToken::PLATFORMS)],
        ]);

        // updateOrCreate on the token itself: reinstalling the app (or a
        // token refresh) issues a new FCM token, but the same physical
        // token should never end up owned by two different rows.
        $deviceToken = DeviceToken::updateOrCreate(
            ['token' => $data['token']],
            ['user_id' => $request->user()->id, 'platform' => $data['platform']]
        );

        return response()->json(['success' => true, 'id' => $deviceToken->id], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:255'],
        ]);

        $request->user()->deviceTokens()->where('token', $data['token'])->delete();

        return response()->json(['success' => true]);
    }
}
