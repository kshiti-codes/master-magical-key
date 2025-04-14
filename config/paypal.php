<?php
// config/paypal.php
return [
    'mode'    => env('PAYPAL_MODE', 'sandbox'), // Can be 'sandbox' or 'live'
    
    'sandbox' => [
        'client_id'     => env('PAYPAL_SANDBOX_CLIENT_ID', 'Aau-FeEdkkN5MHOoM0NnMziveBmfmKVzhYD_mu1D8UDUpzQAe7zVWxgqy87Tp3YUSfgJZv0JhqooH6E2'),
        'client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET', 'EJi55I4J4YTYlTgATKgagsN6zBo3tS-A4630ystUYlIJwc3c1--dM1oM6KXMaOIj4P-7z9wfjoFykq22'),
        'app_id'        => env('PAYPAL_SANDBOX_APP_ID', ''),
    ],

    'live' => [
        'client_id'     => env('PAYPAL_LIVE_CLIENT_ID', 'Aagj8q7oz4BGfM3ikPkHQGjS3Kporqv93y6Hz2kDPqqbXXm9_KTmBeFfEvbEqYsHjgqfLDO2-DIu_f2H'),
        'client_secret' => env('PAYPAL_LIVE_CLIENT_SECRET', 'EKHln5GyoivdoIW5U75KauXvnkiKLe0jBhBNg9BTWCME5osDP-fHKNSjO_uezZZR-NI5vQXbSMlAazFj'),
        'app_id'        => env('PAYPAL_LIVE_APP_ID', ''),
        'webhook_id' => env('PAYPAL_WEBHOOK_ID', '15J86786BY168092A'),
        'webhook_secret' => env('PAYPAL_WEBHOOK_SECRET', 'EOOlSBe8QdSvU9w9uFW2VnBktFV6KiUi5gWYLlPRozPrm9ul9itvMS519wr-FMZsiEogtyBJCHf5Ha7b'),
        // 'merchant_id'   => env('PAYPAL_LIVE_MERCHANT_ID', 'T9NJ5KWJGKKL4'),
    ],

    'payment_action' => env('PAYPAL_PAYMENT_ACTION', 'Sale'),
    'currency'       => env('PAYPAL_CURRENCY', 'AUD'),
    'notify_url'     => env('PAYPAL_NOTIFY_URL', ''),
    'locale'         => env('PAYPAL_LOCALE', 'en_AU'),
    'validate_ssl'   => env('PAYPAL_VALIDATE_SSL', true),
];