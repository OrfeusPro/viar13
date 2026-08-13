<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class GoogleReviewsController extends Controller
{
    private function normalizeMinStars(array $section): ?float
    {
        $onlyFiveStars = (bool) ($section['only_five_stars'] ?? false);
        if ($onlyFiveStars) {
            return 5.0;
        }

        if (isset($section['min_stars']) && $section['min_stars'] !== '' && $section['min_stars'] !== null) {
            return (float) $section['min_stars'];
        }

        return null;
    }

    private function filterByMinStars(array $reviews, ?float $minStars): array
    {
        if (!is_float($minStars)) {
            return $reviews;
        }

        return array_values(array_filter($reviews, function ($r) use ($minStars) {
            return isset($r['rating']) && (float) $r['rating'] >= $minStars;
        }));
    }

    public function __invoke(): JsonResponse
    {
        $debug = (bool) config('app.debug');

        $cfg = (array) config('services.google', []);

        $section = (array) ($cfg['reviews_section'] ?? []);
        $enabled = (bool) ($section['enabled'] ?? false);
        $minStars = $this->normalizeMinStars($section);
        if (!$enabled) {
            return response()->json(['enabled' => false]);
        }

        $fallbackUrl = $section['url'] ?? ($cfg['maps_url'] ?? null);

        $key = $cfg['places_api_key'] ?? null;
        $placeId = $cfg['place_id'] ?? null;
        $ttlMinutes = (int) ($cfg['places_cache_ttl_minutes'] ?? 60);
        $ttlMinutes = max(1, min(24 * 60, $ttlMinutes));

        if (!$key || !$placeId) {
            return response()->json([
                'enabled' => true,
                'source' => 'fallback',
                'error' => 'places_not_configured',
                'url' => $fallbackUrl,
                'write_review_url' => null,
                'name' => null,
                'rating' => null,
                'user_ratings_total' => null,
                'reviews' => [],
            ]);
        }

        $locale = (string) app()->getLocale();
        $cacheKey = 'google_place_reviews_v1:' . md5($placeId . '|' . $locale);

        $data = Cache::get($cacheKey);
        $placesError = null;
        $placesStatus = null;
        if (!is_array($data)) {
            $data = null;
            try {
                $client = new Client([
                    'timeout' => 4.5,
                    'connect_timeout' => 2.0,
                ]);

                // Places API (New): https://places.googleapis.com/v1/places/{placeId}
                $res = $client->get('https://places.googleapis.com/v1/places/' . rawurlencode($placeId), [
                    'http_errors' => false,
                    'headers' => [
                        'X-Goog-Api-Key' => $key,
                        // Ask explicitly for review publishTime so we can localize relative time on frontend.
                        'X-Goog-FieldMask' => implode(',', [
                            'displayName',
                            'rating',
                            'userRatingCount',
                            'googleMapsUri',
                            'googleMapsLinks',
                            'reviews.rating',
                            'reviews.publishTime',
                            'reviews.relativePublishTimeDescription',
                            'reviews.text',
                            'reviews.authorAttribution',
                            'reviews.googleMapsUri',
                        ]),
                    ],
                    'query' => [
                        'languageCode' => $locale,
                    ],
                ]);

                $placesStatus = $res->getStatusCode();
                $body = (string) $res->getBody();
                $json = json_decode($body, true);
                if (is_array($json) && $placesStatus >= 200 && $placesStatus < 300) {
                    $reviews = (array) ($json['reviews'] ?? []);

                    // Normalize reviews to a predictable subset for the frontend.
                    $normalized = [];
                    foreach ($reviews as $r) {
                        if (!is_array($r)) {
                            continue;
                        }
                        $author = (array) ($r['authorAttribution'] ?? []);
                        $text = (array) ($r['text'] ?? []);
                        $publishTime = $r['publishTime'] ?? null;
                        $unixTime = null;
                        if (is_string($publishTime) && $publishTime !== '') {
                            // Places API can return RFC3339 with nanoseconds (9 digits). PHP/Carbon typically supports up to microseconds.
                            $normalizedTime = preg_replace('/\\.(\\d{6})\\d+(Z|[+-]\\d{2}:\\d{2})$/', '.$1$2', $publishTime);
                            if (!is_string($normalizedTime) || $normalizedTime === '') {
                                $normalizedTime = $publishTime;
                            }
                            try {
                                $unixTime = Carbon::parse($normalizedTime)->timestamp;
                            } catch (\Throwable $e) {
                                $unixTime = null;
                            }
                        }
                        $normalized[] = [
                            'author_name' => $author['displayName'] ?? null,
                            'profile_photo_url' => $author['photoUri'] ?? null,
                            'rating' => isset($r['rating']) ? (float) $r['rating'] : null,
                            'relative_time_description' => $r['relativePublishTimeDescription'] ?? null,
                            'time' => $unixTime,
                            'text' => $text['text'] ?? null,
                            'author_url' => $r['googleMapsUri'] ?? ($author['uri'] ?? null),
                        ];
                    }

                    if (is_float($minStars)) {
                        $normalized = array_values(array_filter($normalized, function ($r) use ($minStars) {
                            return isset($r['rating']) && (float) $r['rating'] >= $minStars;
                        }));
                    }

                    $displayName = (array) ($json['displayName'] ?? []);
                    $links = (array) ($json['googleMapsLinks'] ?? []);

                    $data = [
                        'name' => $displayName['text'] ?? null,
                        'rating' => isset($json['rating']) ? (float) $json['rating'] : null,
                        'user_ratings_total' => isset($json['userRatingCount']) ? (int) $json['userRatingCount'] : null,
                        'url' => $json['googleMapsUri'] ?? $fallbackUrl,
                        'write_review_url' => $links['writeAReviewUri'] ?? ('https://search.google.com/local/writereview?placeid=' . rawurlencode($placeId)),
                        'reviews' => $this->filterByMinStars($normalized, $minStars),
                    ];
                } elseif (is_array($json)) {
                    $placesError = $json['error']['message'] ?? ('HTTP ' . $placesStatus);
                } else {
                    $placesError = 'invalid_json';
                }
            } catch (\Throwable $e) {
                $data = null;
                $placesError = $e->getMessage();
            }

            if (is_array($data)) {
                Cache::put($cacheKey, $data, Carbon::now()->addMinutes($ttlMinutes));
            }
        }

        if (!is_array($data)) {
            $payload = [
                'enabled' => true,
                'source' => 'fallback',
                'error' => 'places_failed',
                'url' => $fallbackUrl,
                'write_review_url' => $placeId ? ('https://search.google.com/local/writereview?placeid=' . rawurlencode($placeId)) : null,
                'name' => null,
                'rating' => null,
                'user_ratings_total' => null,
                'reviews' => [],
            ];

            if ($debug) {
                $payload['places_http_status'] = $placesStatus;
                $payload['places_error'] = $placesError;
                $payload['hint'] = 'GOOGLE_PLACE_ID must look like ChIJ... (Place ID from Google Maps), not a project/app id.';
            }

            return response()->json($payload);
        }

        $data['enabled'] = true;
        $data['source'] = 'places';

        return response()->json($data);
    }
}
