<?php

namespace App\Notifications\Channels;

use App\Services\PushNotificationService;
use Illuminate\Notifications\Notification;

class FcmChannel
{
    public function __construct(private readonly PushNotificationService $pushNotificationService) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toFcm')) {
            return;
        }

        $payload = $notification->toFcm($notifiable);

        $this->pushNotificationService->sendToUser(
            $notifiable,
            $payload['title'],
            $payload['body'],
            $payload['data'] ?? []
        );
    }
}
