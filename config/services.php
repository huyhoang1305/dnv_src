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

    'ghn' => [
        'token' => env('GHN_TOKEN', '4366d497-afec-11f1-8cda-3204dc9bded5'),
        'shop_id' => (int) env('GHN_SHOP_ID', 6665569),
        'api_url' => env('GHN_API_URL', 'https://online-gateway.ghn.vn/shiip/public-api/'),
        'from_district_id' => (int) env('GHN_FROM_DISTRICT_ID', 1454),
        'from_ward_code' => (string) env('GHN_FROM_WARD_CODE', '21201'),
        'from_province_id' => (int) env('GHN_FROM_PROVINCE_ID', 202),
    ],

];
