<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | USA payment processing: deposits, tips, no-show fee charges, and
    | ACH Connect payouts to staff. Wired up in Step 8 — see
    | App\Services\PaymentService, NoShowFeeService::chargeFee(), and
    | ACHPayoutService::initiateTransfer() for the stubbed call sites.
    */
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    /*
    | Push notifications to the mobile app (new lead alerts) via Firebase
    | Cloud Messaging's HTTP v1 API. "credentials_path" points at a Firebase
    | service-account JSON key file (Firebase Console > Project Settings >
    | Service Accounts > Generate new private key). See App\Services\
    | PushNotificationService — gracefully does nothing until both values
    | are set, same "build now, connect later" pattern as Stripe above.
    */
    'fcm' => [
        'project_id' => env('FCM_PROJECT_ID'),
        'credentials_path' => env('FCM_CREDENTIALS_PATH'),
    ],

];
