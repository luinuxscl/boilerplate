<?php

return [
    'default_rate_limit' => env('API_KEY_RATE_LIMIT', 60),
    'default_expiry_days' => env('API_KEY_EXPIRY_DAYS', null),
    'prefix' => env('API_KEY_PREFIX', 'sk_'),
];
