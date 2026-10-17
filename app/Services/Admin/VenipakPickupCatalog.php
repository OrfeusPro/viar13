<?php

namespace App\Services\Admin;

use App\Models\VenipakData;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VenipakPickupCatalog
{
    public const COUNTRIES = [
        'EE' => 'Estonia', 'LT' => 'Lithuania', 'LV' => 'Latvia',
        'FI' => 'Finland', 'DK' => 'Denmark', 'PL' => 'Poland', 'SE' => 'Sweden',
    ];

    public function points(?string $country): array
    {
        $country = strtoupper(trim((string) $country));
        if (! isset(self::COUNTRIES[$country])) {
            return [];
        }

        $sender = VenipakData::query()->where('is_active', true)->first();
        if (! $sender || blank($sender->user) || blank($sender->pass) || blank($sender->login_id)) {
            return [];
        }

        // Cache is scoped to the configured account without storing its credentials in a key.
        $key = 'admin.venipak.pickup.'.hash('sha256', $sender->id.'|'.$sender->user.'|'.$sender->login_id).'|'.$country;
        if (Cache::has($key)) {
            return Cache::get($key);
        }

        try {
            $response = Http::connectTimeout(5)->timeout(15)->asMultipart()->post(
                'https://go.venipak.lt/ws/get_pickup_points?'.http_build_query(['country' => $country]),
                ['user' => $sender->user, 'pass' => $sender->pass, 'login_id' => $sender->login_id],
            );
            $rows = $response->successful() ? $response->json() : null;
            if (! is_array($rows) || ! array_is_list($rows)) {
                throw new \UnexpectedValueException('Invalid pickup catalog response.');
            }

            $points = [];
            foreach ($rows as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $point = [];
                foreach (['city', 'address', 'zip', 'name', 'display_name', 'company_code', 'code', 'id'] as $field) {
                    $point[$field] = is_scalar($row[$field] ?? null) ? trim((string) $row[$field]) : '';
                }
                if ($point['city'] === '' || $point['address'] === '') {
                    continue;
                }
                $point['name'] = $point['name'] ?: $point['display_name'];
                $point['company_code'] = $point['company_code'] ?: ($point['code'] ?: $point['id']);
                $id = hash('sha256', $point['city'].'|'.$point['address'].'|'.$point['name'].'|'.$point['company_code']);
                $points[$id] = $point;
            }
            Cache::put($key, $points, $points === [] ? now()->addSeconds(30) : now()->addMinutes(5));

            return $points;
        } catch (\Throwable $exception) {
            // Provider bodies and exception messages may contain credentials or contact data.
            Log::warning('Admin Venipak pickup catalog unavailable.', ['country' => $country, 'exception_type' => $exception::class]);
            Cache::put($key, [], now()->addSeconds(30));

            return [];
        }
    }

    public function cities(?string $country): array
    {
        $cities = [];
        foreach ($this->points($country) as $point) {
            $cities[$point['city']] = $point['city'];
        }

        return $cities;
    }

    public function options(?string $country, ?string $city = null): array
    {
        $options = [];
        foreach ($this->points($country) as $id => $point) {
            if ($city && $point['city'] !== $city) {
                continue;
            }
            $options[$id] = $point['city'].', '.$point['address'].($point['display_name'] !== '' ? ' ('.$point['display_name'].')' : '');
        }

        return $options;
    }

    public function match(?string $country, ?string $desired): ?string
    {
        $desired = mb_strtolower(trim((string) $desired));
        if ($desired === '') {
            return null;
        }
        $labels = $this->options($country);
        foreach ($this->points($country) as $id => $point) {
            foreach ([$point['display_name'], $point['address'], $labels[$id]] as $candidate) {
                if (mb_strtolower($candidate) === $desired) {
                    return $id;
                }
            }
        }

        return null;
    }

    public function selection(?string $country, ?string $id): array
    {
        $point = $this->points($country)[$id ?? ''] ?? [];

        return [
            'g_city_pickup' => $point['city'] ?? '',
            'g_address_pickup' => $point['address'] ?? '',
            'g_post_pickup' => $point['zip'] ?? '',
            'g_name_pickup' => $point['name'] ?? '',
            'g_code_pickup' => $point['company_code'] ?? '',
        ];
    }
}
