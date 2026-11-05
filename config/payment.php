<?php

return [
    'http' => [
        'timeout' => (int) env('PAYMENT_HTTP_TIMEOUT', 10),
        'retry_times' => (int) env('PAYMENT_HTTP_RETRY_TIMES', 2),
        'retry_sleep_ms' => (int) env('PAYMENT_HTTP_RETRY_SLEEP_MS', 200),
    ],
    'khalti' => [
        'secret_key' => env('KHALTI_SECRET_KEY', ''),
        'public_key' => env('KHALTI_PUBLIC_KEY', ''),
        'base_url' => env('KHALTI_BASE_URL', 'https://a.khalti.com/api/v2/'),
        'verification_url' => env('KHALTI_VERIFICATION_URL', 'https://a.khalti.com/api/v2/epayment/lookup/'),
    ],
    'esewa' => [
        'merchant_code' => env('ESEWA_MERCHANT_CODE', ''),
        'secret_key' => env('ESEWA_SECRET_KEY', ''),
        'payment_url' => env('ESEWA_PAYMENT_URL', 'https://rc.esewa.com.np/epay/main'),
        'verification_url' => env('ESEWA_VERIFICATION_URL', 'https://rc.esewa.com.np/epay/transrec'),
        'success_url' => env('APP_URL') . '/payment/esewa/success',
        'failure_url' => env('APP_URL') . '/payment/esewa/failure',
    ],
    'stripe' => [
        'key' => env('STRIPE_KEY', ''),
        'secret' => env('STRIPE_SECRET', ''),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),
        'currency' => env('STRIPE_CURRENCY', 'npr'),
    ],
    'banking' => [
        'accounts' => env('BANK_TRANSFER_ENABLED', false) && env('BANK_NAME') && env('BANK_ACCOUNT_HOLDER') && env('BANK_ACCOUNT_NUMBER') ? [[
            'bank_name' => env('BANK_NAME'),
            'account_name' => env('BANK_ACCOUNT_HOLDER'),
            'account_number' => env('BANK_ACCOUNT_NUMBER'),
            'branch' => env('BANK_BRANCH'),
        ]] : [],
        'qr' => [
            'image_url' => env('BANK_QR_ENABLED', false) && env('BANK_QR_MERCHANT_NAME') ? env('BANK_QR_IMAGE_URL') : null,
            'merchant_name' => env('BANK_QR_MERCHANT_NAME'),
        ],
    ],
];
