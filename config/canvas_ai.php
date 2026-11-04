<?php

return [
    'enabled' => (bool) env('CANVAS_AI_ENABLED', false),
    'key' => env('CANVAS_AI_API_KEY', ''),
    'model' => env('CANVAS_AI_MODEL', 'gpt-image-1.5'),
    'daily_limit' => (int) env('CANVAS_AI_DAILY_LIMIT', 20),
    'user_daily_limit' => (int) env('CANVAS_AI_USER_DAILY_LIMIT', 5),
    'storage_path' => storage_path('app/canvas-ai'),
];
