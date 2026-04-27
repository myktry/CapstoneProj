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

    'stripe' => [
        'key'    => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'sms' => [
        'driver' => env('SMS_DRIVER', 'log'),
        'from' => env('SMS_FROM', 'BLACKEMBER'),
        'textbee_api_key' => env('SMS_TEXTBEE_API_KEY'),
        'textbee_device_id' => env('SMS_TEXTBEE_DEVICE_ID'),
        'textbee_base_url' => env('SMS_TEXTBEE_BASE_URL', 'https://api.textbee.dev/api/v1'),
        'textbee_sim_subscription_id' => env('SMS_TEXTBEE_SIM_SUBSCRIPTION_ID'),
        'textbee_verify_ssl' => env('SMS_TEXTBEE_VERIFY_SSL', true),
        'vonage_key' => env('SMS_VONAGE_KEY'),
        'vonage_secret' => env('SMS_VONAGE_SECRET'),
        'vonage_url' => env('SMS_VONAGE_URL', 'https://rest.nexmo.com/sms/json'),
        'vonage_verify_ssl' => env('SMS_VONAGE_VERIFY_SSL', true),
    ],

];
