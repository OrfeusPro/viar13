<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class GoogleRatingController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $debug = (bool) config('app.debug');

        $cfg = (array) config('services.google', []);

        $widget = (array) ($cfg['reviews_widget'] ?? []);
        $enabled = (bool) ($widget['enabled'] ?? false);
        if (!$enabled) {
            return response()->json(['enabled' => false]);
        }

        $fallbackUrl = $widget['url'] ?? ($cfg['maps_url'] ?? null);
        $fallbackRating = isset($widget['rating']) ? (float) $widget['rating'] : null;
        $fallbackTotal = isset($widget['total']) ? (int) $widget['total'] : null;

        $key = $cfg['places_api_key'] ?? null;
        $placeId = $cfg['place_id'] ?? null;
        $ttlMinutes = (int) ($cfg['places_cache_ttl_minutes'] ?? 60);
        $ttlMinutes = max(1, min(24 * 60, $ttlMinutes));

        // If Places is not configured, just return the fallback values.
        if (!$key || !$placeId) {
            return response()->json([
                'enabled' => true,
                'source' => 'fallback',
                'rating' => $fallbackRating,
                'user_ratings_total' => $fallbackTotal,
                'url' => $fallbackUrl,
            ]);
        }

        $cacheKey = 'google_place_rating_v1:' . md5($placeId);

        $data = Cache::get($cacheKey);
        $placesError = null;
        $placesStatus = null;
        if (!is_array($data)) {
            $data = null;
            try {
                $client = new Client([
                    'timeout' => 3.5,
                    'connect_timeout' => 2.0,
                ]);

                // Places API (New): https://places.googleapis.com/v1/places/{placeId}
                $res = $client->get('https://places.googleapis.com/v1/places/' . rawurlencode($placeId), [
                    'http_errors' => false,
                    'headers' => [
                        'X-Goog-Api-Key' => $key,
                        'X-Goog-FieldMask' => 'rating,userRatingCount,googleMapsUri',
                    ],
                    'query' => [
                        // Optional, helps localized strings in some fields.
                        'languageCode' => app()->getLocale(),
                    ],
                ]);

                $placesStatus = $res->getStatusCode();
                $body = (string) $res->getBody();
                $json = json_decode($body, true);
                if (is_array($json)) {
                    if ($placesStatus >= 200 && $placesStatus < 300) {
                        $data = [
                            'rating' => isset($json['rating']) ? (float) $json['rating'] : null,
                            'user_ratings_total' => isset($json['userRatingCount']) ? (int) $json['userRatingCount'] : null,
                            'url' => $json['googleMapsUri'] ?? $fallbackUrl,
                        ];
                    } else {
                        $placesError = $json['error']['message'] ?? ('HTTP ' . $placesStatus);
                    }
                } else {
                    $placesError = 'invalid_json';
                }
            } catch (\Throwable $e) {
                $data = null;
                $placesError = $e->getMessage();
            }

            // Only cache successful responses. Failures fall back but will retry on next request.
            if (is_array($data)) {
                Cache::put($cacheKey, $data, Carbon::now()->addMinutes($ttlMinutes));
            }
        }

        // If Places API failed, return fallback values so the widget still shows.
        if (!is_array($data)) {
            $payload = [
                'enabled' => true,
                'source' => 'fallback',
                'rating' => $fallbackRating,
                'user_ratings_total' => $fallbackTotal,
                'url' => $fallbackUrl,
            ];

            if ($debug) {
                $payload['places_http_status'] = $placesStatus;
                $payload['places_error'] = $placesError;
                $payload['hint'] = 'GOOGLE_PLACE_ID must look like ChIJ... (Place ID from Google Maps), not a project/app id.';
            }

            return response()->json($payload);
        }

        return response()->json([
            'enabled' => true,
            'source' => 'places',
            'rating' => $data['rating'] ?? $fallbackRating,
            'user_ratings_total' => $data['user_ratings_total'] ?? $fallbackTotal,
            'url' => $data['url'] ?? $fallbackUrl,
        ]);
    }
}
