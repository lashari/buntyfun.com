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

    // Both blank by default — never show ads before AdSense approval, never load
    // analytics against an unconfigured property. Blank = no-op, not a crash.
    'adsense' => [
        'client_id' => env('ADSENSE_CLIENT_ID'),
    ],

    // Meta (Facebook Page + Instagram) publishing. App ID and login configuration ID are
    // public identifiers; the app secret lives only in the server .env.
    'meta' => [
        'app_id' => env('META_APP_ID', '2532222677188710'),
        'app_secret' => env('META_APP_SECRET'),
        'config_id' => env('META_LOGIN_CONFIG_ID', '1909608247125001'),
        'graph_version' => env('META_GRAPH_VERSION', 'v27.0'),
    ],

    'ga4' => [
        'measurement_id' => env('GA4_MEASUREMENT_ID'),
    ],

];
