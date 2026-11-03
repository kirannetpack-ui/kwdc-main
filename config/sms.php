<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default SMS Driver
    |--------------------------------------------------------------------------
    | Supported: "log", "sparrow", "aakash", "array"
    */
    'default' => env('SMS_DRIVER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | SMS Providers Configuration
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'sparrow' => [
            'endpoint' => env('SPARROW_SMS_ENDPOINT', 'https://api.sparrowsms.com/v2/sms/'),
            'token' => env('SPARROW_SMS_TOKEN', 'mock_sparrow_token'),
            'from' => env('SPARROW_SMS_FROM', 'KTMWDC'),
        ],

        'aakash' => [
            'endpoint' => env('AAKASH_SMS_ENDPOINT', 'https://sms.aakashsms.com/sms/v3/send'),
            'auth_token' => env('AAKASH_SMS_AUTH_TOKEN', 'mock_aakash_token'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Notification Numbers (Nepal Mobile Numbers)
    |--------------------------------------------------------------------------
    */
    'admin_phones' => array_filter(explode(',', env('SMS_ADMIN_PHONES', '9801000000,9841000000'))),
];
