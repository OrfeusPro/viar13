<?php

return [
    'cache_enabled' => env('GEOIP_CACHE_ENABLED', false),
    'success_ttl' => (int) env('GEOIP_CACHE_SUCCESS_TTL', 3600),
    'failure_ttl' => (int) env('GEOIP_CACHE_FAILURE_TTL', 30),
];
