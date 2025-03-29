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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'omdb' => [
        'base_url' => env('OMDB_BASE_URL', 'https://www.omdbapi.com/'),
        'api_key' => env('OMDB_API_KEY'),
        'daily_limit' => env('OMDB_DAILY_LIMIT', 1000),
        'requests_used' => env('OMDB_REQUESTS_USED', 0),
        'reset_time' => env('OMDB_RESET_TIME', '00:00'),
    ],
    'cache_time_movie' => [env('CACHE_TIME_MOVIE')],

];
