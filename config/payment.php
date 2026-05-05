<?php

return [
    'vnpay' => [
        'tmn_code' => env('VNPAY_TMN_CODE', 'YOUR_TMN_CODE'), // Sandbox vnp_TmnCode
        'hash_secret' => env('VNPAY_HASH_SECRET', 'YOUR_HASH_SECRET'), // Sandbox vnp_HashSecret
        'url' => env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
        'return_url' => env('VNPAY_RETURN_URL', '/thanh-toan/vnpay-callback'),
    ],

    'momo' => [
        'partner_code' => env('MOMO_PARTNER_CODE', 'MOMOBKUN20180529'), // Default Sandbox MoMo partner code
        'access_key' => env('MOMO_ACCESS_KEY', 'klm05TvNCpe7cgZ9'),
        'secret_key' => env('MOMO_SECRET_KEY', 'at67qH6mk8w5Y1nSAbLSf4pO1RmJMOA'),
        'endpoint' => env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create'),
        'return_url' => env('MOMO_RETURN_URL', '/thanh-toan/momo-callback'),
    ]
];
