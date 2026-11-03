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

'kataho' => [
    'api_url' => env('KATAHO_API_URL', 'https://api.kataho.app/v1'),
    'api_key' => env('KATAHO_API_KEY'),
    'app_url' => env('KATAHO_APP_URL', 'https://kataho.app'),
],

'google_maps' => [
    'api_key' => env('GOOGLE_MAPS_API_KEY'),
],

'ai' => [
    'provider' => env('AI_PROVIDER', 'free'),
],

'gemini' => [
    'api_key' => env('GEMINI_API_KEY'),
    'model' => env('GEMINI_MODEL', 'gemini-3.5-flash'),
],

'openai' => [
    'api_key' => env('OPENAI_API_KEY'),
    'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
],

'groq' => [
    'api_key' => env('GROQ_API_KEY'),
    'model' => env('GROQ_MODEL', 'mixtral-8x7b-32768'),
],

];
