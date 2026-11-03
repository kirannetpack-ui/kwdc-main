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
        'company_name' => 'Kathmandu Warehousing & Distribution Center Pvt. Ltd.',
        'company_pan' => '609823412',
        'accounts' => [
            [
                'id' => 'nabil',
                'bank_name' => 'Nabil Bank Ltd.',
                'account_name' => 'Kathmandu Warehousing & Distribution Center Pvt. Ltd.',
                'account_number' => '01100175023910',
                'branch' => 'Kathmandu Main / Durbar Marg',
                'swift_code' => 'NARBNPKA',
            ],
            [
                'id' => 'global_ime',
                'bank_name' => 'Global IME Bank Ltd.',
                'account_name' => 'KTM-WDC Logistics & Cargo Hub',
                'account_number' => '00101010045623',
                'branch' => 'New Road / Birgunj Terminal',
                'swift_code' => 'GLBLNPKA',
            ],
            [
                'id' => 'nic_asia',
                'bank_name' => 'NIC Asia Bank Ltd.',
                'account_name' => 'KTM-WDC Logistics Nepal',
                'account_number' => '109200548123901',
                'branch' => 'Thamel Corporate Branch',
                'swift_code' => 'NICAIPKA',
            ],
        ],
        'qr' => [
            'merchant_name' => 'KTM-WDC LOGISTICS PVT LTD',
            'merchant_pan' => '609823412',
            'terminal_id' => 'KTM-WDC-01',
            'networks' => ['Fonepay', 'NepalPay', 'connectIPS', 'SmartQR'],
        ],
    ],
];
