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

    // Suara Bos Allo (teks ke suara). Tanpa kunci, peramban membacakan dengan suaranya sendiri.
    'speechify' => [
        'key' => env('SPEECHIFY_API_KEY'),
        'voice' => env('SPEECHIFY_VOICE', 'bima'),
        'model' => env('SPEECHIFY_MODEL', 'simba-3.5'),
        'language' => env('SPEECHIFY_LANGUAGE', 'id-ID'),
    ],

    // Pembayaran kelas berbayar lewat SumoPod (QRIS). Bawaan memakai sandbox (uji coba).
    'sumopod' => [
        'key' => env('SUMOPOD_API_KEY'),
        'base_url' => env('SUMOPOD_BASE_URL', 'https://api-pay-sandbox.sumopod.com'),
        'webhook_token' => env('SUMOPOD_WEBHOOK_TOKEN'),
        'webhook_secret' => env('SUMOPOD_WEBHOOK_SECRET'),
        'expires_in_hours' => 1,
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
