<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Stevebauman\Location\Facades\Location;
use Throwable;

/** Cache only the country; delivery prices still come from the live database. */
class CachedGeoIpCountry
{
    public function get(?string $ip): object|false
    {
        $key = $this->key($ip);
        if ($key !== null) {
            try {
                $cached = Cache::get($key);
                if (is_array($cached) && ($cached['version'] ?? null) === 1
                    && array_key_exists('country', $cached)
                    && ($cached['country'] === null || $this->validCountry($cached['country']))) {
                    return $this->position($cached['country']);
                }
            } catch (Throwable) {
                Log::warning('geoip.cache_read_failed');
            }
        }

        try {
            $position = Location::get($ip);
            $country = $position ? $position->countryCode : null;
            $country = $this->validCountry($country) ? $country : null;
        } catch (Throwable) {
            Log::warning('geoip.lookup_failed');
            $country = null;
        }

        if ($key !== null) {
            try {
                $ttl = $country === null ? config('geoip.failure_ttl', 30) : config('geoip.success_ttl', 3600);
                if ((int) $ttl > 0) {
                    Cache::put($key, ['version' => 1, 'country' => $country], (int) $ttl);
                }
            } catch (Throwable) {
                Log::warning('geoip.cache_write_failed');
            }
        }

        return $this->position($country);
    }

    private function key(?string $ip): ?string
    {
        $effectiveIp = $ip ?: (config('location.testing.enabled', true)
            ? config('location.testing.ip', '66.102.0.0') : $ip);
        if (!is_string($effectiveIp) || !filter_var($effectiveIp, FILTER_VALIDATE_IP)) {
            return null;
        }
        $secret = (string) config('app.key');
        if ($secret === '') {
            return null;
        }

        $identity = serialize([bin2hex(inet_pton($effectiveIp)), config('location.driver'), config('location.fallbacks')]);

        return 'geoip:country:v1:'.hash_hmac('sha256', $identity, $secret);
    }

    private function validCountry(mixed $country): bool
    {
        return is_string($country) && preg_match('/^[A-Z]{2,3}$/D', $country) === 1;
    }

    private function position(?string $country): object|false
    {
        return $country === null ? false : (object) ['countryCode' => $country];
    }
}
