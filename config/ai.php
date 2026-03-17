<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Driver
    |--------------------------------------------------------------------------
    | The driver used for AI completions. Supported: "openrouter", "null".
    */
    'default_driver' => env('AI_DRIVER', 'openrouter'),

    /*
    |--------------------------------------------------------------------------
    | Default Model
    |--------------------------------------------------------------------------
    | The model used when none is specified per-request or per-prompt.
    */
    'default_model' => env('AI_DEFAULT_MODEL', 'openai/gpt-4o-mini'),

    /*
    |--------------------------------------------------------------------------
    | Usage Tracking
    |--------------------------------------------------------------------------
    | Whether to persist AI usage logs to the database.
    */
    'track_usage' => env('AI_TRACK_USAGE', true),

    /*
    |--------------------------------------------------------------------------
    | Drivers
    |--------------------------------------------------------------------------
    */
    'drivers' => [
        'openrouter' => [
            'api_key'  => env('OPENROUTER_API_KEY'),
            'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
        ],
        'null' => [],
    ],
];
