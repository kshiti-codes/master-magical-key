<?php

return [
    'key'            => env('STRIPE_KEY'),
    'secret'         => env('STRIPE_SECRET'),
    'currency'       => env('STRIPE_CURRENCY', 'aud'),
];
