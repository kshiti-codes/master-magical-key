<?php

return [
    'enabled' => env('GMAIL_API_ENABLED', false),
    'from_address' => env('GMAIL_FROM_ADDRESS', env('MAIL_FROM_ADDRESS')),
    'from_name' => env('GMAIL_FROM_NAME', env('MAIL_FROM_NAME')),
    'credentials_path' => storage_path('app/google-credentials.json'),
    'token_path' => storage_path('app/gmail-token.json'),
    'rate_limits' => [
        'per_minute' => env('GMAIL_RATE_LIMIT_PER_MINUTE', 60),
        'per_day' => env('GMAIL_RATE_LIMIT_PER_DAY', 1000),
    ],
];