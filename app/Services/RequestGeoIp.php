<?php

namespace App\Services;

use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;

/** Reuse one lookup for the same IP within a request. */
class RequestGeoIp
{
    private const ATTRIBUTE = '_viar_geoip_positions';

    public function get(Request $request): object|false
    {
        $ip = $request->ip();
        $key = hash('sha256', serialize($ip));
        $positions = $request->attributes->get(self::ATTRIBUTE, []);
        if (array_key_exists($key, $positions)) {
            return $positions[$key];
        }

        $position = config('geoip.cache_enabled', false)
            ? app(CachedGeoIpCountry::class)->get($ip)
            : Location::get($ip);
        $positions[$key] = $position;
        $request->attributes->set(self::ATTRIBUTE, $positions);

        return $position;
    }
}
