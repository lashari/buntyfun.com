<?php

return [
    // Start with one provider — don't run two paid integrations without a reason.
    'provider' => env('AI_PROVIDER', 'gemini'),

    'daily_free_limit' => (int) env('AI_DAILY_FREE_LIMIT', 10),

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
    ],
];
