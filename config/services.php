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

    'gcash' => [
        'merchant_number' => env('GCASH_MERCHANT_NUMBER'),
        'merchant_name' => env('GCASH_MERCHANT_NAME', 'Merchant'),
        'merchant_city' => env('GCASH_MERCHANT_CITY', 'Manila'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/auth/google/callback'),
    ],

    'paymaya' => [
        'public_key' => env('PAYMAYA_PUBLIC_KEY'),
        'secret_key' => env('PAYMAYA_SECRET_KEY'),
        'environment' => env('PAYMAYA_ENVIRONMENT', 'sandbox'), // 'sandbox' or 'production'
        'webhook_secret' => env('PAYMAYA_WEBHOOK_SECRET'),
    ],

];
