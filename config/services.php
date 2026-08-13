<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'facebook' => [
        'enable' => env('FACEBOOK_ENABLE'),
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI')
    ],

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    //    'venipak' => [
    //        'key' => env('VENIPAK_API_KEY')
    //    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),

        // Google Places API (for automatic rating/total updates)
        'places_api_key' => env('GOOGLE_PLACES_API_KEY'),
        'place_id' => env('GOOGLE_PLACE_ID'),
        'maps_url' => env('GOOGLE_MAPS_URL'),
        'places_cache_ttl_minutes' => (int) env('GOOGLE_PLACES_CACHE_TTL_MINUTES', 60),

        // Widget fallback values (shown immediately, also used if Places API is not configured/failed)
        'reviews_widget' => [
            // NOTE: (bool) "0" === true in PHP, so use boolean validator.
            'enabled' => filter_var(env('GOOGLE_REVIEWS_WIDGET_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
            'rating' => env('GOOGLE_REVIEWS_WIDGET_RATING'),
            'total' => env('GOOGLE_REVIEWS_WIDGET_TOTAL'),
            'url' => env('GOOGLE_REVIEWS_WIDGET_URL'),
        ],

        // Reviews section (carousel)
        'reviews_section' => [
            'enabled' => filter_var(env('GOOGLE_REVIEWS_SECTION_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
            'url' => env('GOOGLE_REVIEWS_SECTION_URL'),
            // Filtering. Note: Places API typically returns only a small subset of reviews, so strict filters may yield few items.
            'only_five_stars' => filter_var(env('GOOGLE_REVIEWS_ONLY_FIVE_STARS', false), FILTER_VALIDATE_BOOLEAN),
            'min_stars' => env('GOOGLE_REVIEWS_MIN_STARS', null),
        ],
    ],

    'sa_integration' => [
        'api_key' => env('SA_API_KEY', 'test-key'),
        'attachments' => [
            'max_size' => (int) env('SA_ATTACHMENT_MAX_SIZE', 10 * 1024 * 1024),
            'allowed_mime' => explode(',', env('SA_ATTACHMENT_ALLOWED_MIME', 'image/jpeg,image/png,image/webp,application/pdf')),
        ],
    ],

    'synvolve' => [
        'order_webhook_url' => env('SYNVOLVE_ORDER_WEBHOOK_URL'),
        'manager_message_webhook_url' => env('SYNVOLVE_MANAGER_MESSAGE_WEBHOOK_URL'),
        'bot_status_webhook_url' => env('SYNVOLVE_BOT_STATUS_WEBHOOK_URL'),
        'timeout' => (float) env('SYNVOLVE_WEBHOOK_TIMEOUT', 5),
        'connect_timeout' => (float) env('SYNVOLVE_WEBHOOK_CONNECT_TIMEOUT', 3),
        'verify_ssl' => filter_var(env('SYNVOLVE_WEBHOOK_VERIFY_SSL', true), FILTER_VALIDATE_BOOLEAN),
        'test_receiver_enabled' => filter_var(env('SYNVOLVE_TEST_RECEIVER_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
    ],

];
