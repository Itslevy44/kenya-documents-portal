<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Kenya Docs Third-Party Services
    |--------------------------------------------------------------------------
    */

    'africastalking' => [
        'username'  => env('AT_USERNAME'),
        'api_key'   => env('AT_API_KEY'),
        'sender_id' => env('AT_SENDER_ID'),
    ],

    'payhero' => [
        'basic_auth'     => env('PAYHERO_BASIC_AUTH'),
        'channel_id'     => env('PAYHERO_CHANNEL_ID'),
        'account_id'     => env('PAYHERO_ACCOUNT_ID'),
        'webhook_secret' => env('PAYHERO_WEBHOOK_SECRET'),
    ],

    'telegram' => [
        'bot_token'  => env('TELEGRAM_BOT_TOKEN'),
        'channel_id' => env('TELEGRAM_CHANNEL_ID'),
    ],

    'admin_phone' => env('ADMIN_PHONE'),

];
