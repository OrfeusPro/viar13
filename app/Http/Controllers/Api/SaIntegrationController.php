<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SaBotControl;
use App\Models\SaConversation;
use App\Models\SaEscalation;
use App\Models\SaEvent;
use App\Models\SaMessage;
use App\Models\Orders;
use App\Models\HeaderMenu;
use App\Models\AProductionTime;
use App\Models\CanvasPhotoImprove;
use App\Models\CanvasRam;
use App\Models\GalleryBox;
use App\Models\GalleryDecoration;
use App\Models\GalleryHolst;
use App\Entity\GalleryExecutionType;
use App\Services\SynvolveWebhookService;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class SaIntegrationController extends Controller
{
    public function messagesWebhook(Request $request): JsonResponse
    {
        $request = $this->normalizePhoneFields($request, [
            'data.message.from.phone',
        ]);

        $validator = Validator::make($request->all(), [
            'event_id' => 'required|uuid',
            'event_type' => 'required|in:message.created,message.status',
            'idempotency_key' => 'required|string|max:255',
            'occurred_at' => 'required|date',
            'source' => 'required|string|in:SA',
            'data' => 'required|array',
            'data.lead_id' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $eventType = $request->input('event_type');

        if ($eventType === 'message.created') {
            $messageValidator = Validator::make($request->all(), [
                'data.conversation_id' => 'required|string|max:255',
                'data.channel' => 'required|string|in:whatsapp',
                'data.message' => 'required|array',
                'data.message.message_id' => 'required|string|max:255',
                'data.message.direction' => 'required|in:inbound,outbound',
                'data.message.from' => 'required|array',
                'data.message.from.phone' => ['nullable', 'string', 'regex:/^\+?\d{7,15}$/'],
                'data.message.to' => 'required|array',
                'data.message.sent_at' => 'required|date',
                'data.message.status' => 'required|in:received,sent,delivered,read,failed',
            ]);

            if ($messageValidator->fails()) {
                return $this->validationError($messageValidator);
            }
        }

        if ($eventType === 'message.status') {
            $statusValidator = Validator::make($request->all(), [
                'data.conversation_id' => 'nullable|string|max:255',
                'data.channel' => 'nullable|string|in:whatsapp',
                'data.message_id' => 'required|string|max:255',
                'data.status' => 'required|in:received,sent,delivered,read,failed',
                'data.status_at' => 'required|date',
            ]);

            if ($statusValidator->fails()) {
                return $this->validationError($statusValidator);
            }
        }

        $eventId = (string) $request->input('event_id');
        if ($this->isDuplicateEvent('sa:webhooks:messages:event', $eventId, [
            'event_id' => $eventId,
            'idempotency_key' => (string) $request->input('idempotency_key'),
            'event_type' => $eventType,
            'source' => (string) $request->input('source'),
            'payload' => $request->all(),
        ])) {
            return response()->json(['status' => 'duplicate'], 200);
        }

        $this->persistMessagePayload($request, $eventType, $eventId);

        $result = ['stored' => true];
        if ($eventType === 'message.status') {
            $result['status_updated'] = true;
        }

        return response()->json([
            'status' => 'ok',
            'result' => $result,
        ], 200);
    }

    public function sendMessageWebhook(Request $request): JsonResponse
    {
        $request = $this->normalizePhoneFields($request, [
            'data.client.phone',
        ]);

        $validator = Validator::make($request->all(), [
            'event_id' => 'required|uuid',
            'event_type' => 'required|in:crm.message.send',
            'idempotency_key' => 'required|string|max:255',
            'occurred_at' => 'required|date',
            'source' => 'required|string|in:CRM',
            'data' => 'required|array',
            'data.lead_id' => 'nullable',
            'data.conversation_id' => 'nullable|string|max:255',
            'data.channel' => 'required|string|in:whatsapp',
            'data.client.phone' => ['required', 'string', 'regex:/^\+?\d{7,15}$/'],
            'data.manager.id' => 'required|string|max:255',
            'data.message.client_visible_sender' => 'required|in:manager,agent,system',
            'data.message.text' => 'required|string',
            'data.bot_control.mode_after_send' => 'nullable|in:active,paused,handoff_to_manager',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $idempotencyKey = (string) $request->input('idempotency_key');
        if ($this->isDuplicateEvent('crm:webhooks:send-message:key', $idempotencyKey, [
            'event_id' => (string) $request->input('event_id'),
            'idempotency_key' => $idempotencyKey,
            'event_type' => (string) $request->input('event_type'),
            'source' => (string) $request->input('source'),
            'payload' => $request->all(),
        ])) {
            return response()->json(['status' => 'duplicate'], 200);
        }

        $outboundMessage = $this->persistOutboundMessageRequest($request);
        $providerMeta = [];

        if ($outboundMessage && !empty($outboundMessage->provider_meta_json)) {
            $decodedMeta = json_decode((string) $outboundMessage->provider_meta_json, true);
            $providerMeta = is_array($decodedMeta) ? $decodedMeta : [];
        }

        app(SynvolveWebhookService::class)->notifyManagerMessageForOrderOrPhone(
            $request->input('data.lead_id') !== null ? (int) $request->input('data.lead_id') : null,
            (string) $request->input('data.client.phone', ''),
            (string) $request->input('data.message.text', ''),
            [
                'trigger' => 'sa_manager_message',
                'lead_id' => $request->input('data.lead_id'),
                'conversation_id' => $request->input('data.conversation_id'),
                'manager_id' => $request->input('data.manager.id'),
                'channel' => $request->input('data.channel'),
            ]
        );

        return response()->json([
            'status' => 'ok',
            'result' => [
                'accepted' => true,
                'message_id' => $outboundMessage ? $outboundMessage->message_id : null,
                'sa_message_id' => $outboundMessage ? $outboundMessage->message_id : null,
                'provider_message_id' => data_get($providerMeta, 'provider_message_id'),
                'queued' => true,
                'bot_mode' => $request->input('data.bot_control.mode_after_send'),
            ],
        ], 200);
    }

    public function servicesCatalog(Request $request): JsonResponse
    {
        $request->merge([
            'phone' => $this->sanitizePhoneValue((string) $request->input('phone', '')),
            'client_phone' => $this->sanitizePhoneValue((string) $request->input('client_phone', '')),
        ]);

        $supportedLocales = $this->supportedCatalogLocales();

        $validator = Validator::make($request->all(), [
            'lang' => 'nullable|in:' . implode(',', $supportedLocales),
            'updated_since' => 'nullable|date',
            'include_inactive' => 'nullable|in:true,false,1,0',
            'country' => 'nullable|string|min:2|max:3',
            'country_code' => 'nullable|string|min:2|max:3',
            'phone' => ['nullable', 'string', 'regex:/^\+?\d{7,15}$/'],
            'client_phone' => ['nullable', 'string', 'regex:/^\+?\d{7,15}$/'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $lang = (string) $request->query('lang', 'ru');
        $updatedSince = $request->query('updated_since');
        $includeInactive = filter_var((string) $request->query('include_inactive', 'false'), FILTER_VALIDATE_BOOLEAN);
        $countryContext = $this->resolveCountryPricingContext($request);

        $catalog = $this->buildCatalogFromRealSources(
            $lang,
            $countryContext['country_code'],
            (float) $countryContext['multiplier']
        );
        $services = collect($catalog['services']);
        $bundles = collect($catalog['bundles']);

        if (!$includeInactive) {
            $services = $services->where('is_active', true)->values();
            $bundles = $bundles->where('is_active', true)->values();
        }

        if ($updatedSince) {
            $updatedAt = Carbon::parse($updatedSince);
            $services = $services
                ->filter(function (array $service) use ($updatedAt) {
                    return Carbon::parse($service['updated_at'])->greaterThan($updatedAt);
                })
                ->values();

            $bundles = $bundles
                ->filter(function (array $bundle) use ($updatedAt) {
                    return Carbon::parse($bundle['updated_at'])->greaterThan($updatedAt);
                })
                ->values();
        }

        $categories = $this->filterCategoriesByServices(collect($catalog['categories']), $services);

        return response()->json([
            'status' => 'ok',
            'meta' => [
                'currency' => 'EUR',
                'generated_at' => gmdate('Y-m-d\TH:i:s\Z'),
                'version' => gmdate('Y-m-d') . '_1',
                'country_code' => $countryContext['country_code'],
                'country_multiplier' => (float) $countryContext['multiplier'],
            ],
            'data' => [
                'categories' => $categories->values()->all(),
                'services' => $services->values()->all(),
                'bundles' => $bundles->values()->all(),
            ],
        ], 200);
    }

    public function catalogFull(Request $request): JsonResponse
    {
        $request->merge([
            'phone' => $this->sanitizePhoneValue((string) $request->input('phone', '')),
            'client_phone' => $this->sanitizePhoneValue((string) $request->input('client_phone', '')),
        ]);

        $supportedLocales = $this->supportedCatalogLocales();

        $validator = Validator::make($request->all(), [
            'lang' => 'nullable|in:' . implode(',', $supportedLocales),
            'updated_since' => 'nullable|date',
            'include_inactive' => 'nullable|in:true,false,1,0',
            'country' => 'nullable|string|min:2|max:3',
            'country_code' => 'nullable|string|min:2|max:3',
            'phone' => ['nullable', 'string', 'regex:/^\+?\d{7,15}$/'],
            'client_phone' => ['nullable', 'string', 'regex:/^\+?\d{7,15}$/'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $lang = (string) $request->query('lang', 'ru');
        $updatedSince = $request->query('updated_since');
        $includeInactive = filter_var((string) $request->query('include_inactive', 'false'), FILTER_VALIDATE_BOOLEAN);
        $countryContext = $this->resolveCountryPricingContext($request);

        $catalog = $this->buildCatalogFromRealSources(
            $lang,
            $countryContext['country_code'],
            (float) $countryContext['multiplier']
        );

        $services = collect($catalog['services']);
        $bundles = collect($catalog['bundles']);

        if (!$includeInactive) {
            $services = $services->where('is_active', true)->values();
            $bundles = $bundles->where('is_active', true)->values();
        }

        if ($updatedSince) {
            $updatedAt = Carbon::parse($updatedSince);
            $services = $services
                ->filter(function (array $service) use ($updatedAt) {
                    return Carbon::parse($service['updated_at'])->greaterThan($updatedAt);
                })
                ->values();

            $bundles = $bundles
                ->filter(function (array $bundle) use ($updatedAt) {
                    return Carbon::parse($bundle['updated_at'])->greaterThan($updatedAt);
                })
                ->values();
        }

        $enrichedServices = $services
            ->map(function (array $service) use ($countryContext) {
                return $this->buildFullCatalogServicePayload($service, $countryContext);
            })
            ->values();

        $categories = $this->filterCategoriesByServices(collect($catalog['categories']), $enrichedServices);

        return response()->json([
            'status' => 'ok',
            'meta' => [
                'currency' => 'EUR',
                'generated_at' => gmdate('Y-m-d\TH:i:s\Z'),
                'version' => gmdate('Y-m-d') . '_1',
                'country_code' => $countryContext['country_code'],
                'country_multiplier' => (float) $countryContext['multiplier'],
            ],
            'data' => [
                'categories' => $categories->values()->all(),
                'services' => $enrichedServices->values()->all(),
                'bundles' => $bundles->values()->all(),
            ],
        ], 200);
    }

    public function serviceSizes(Request $request, string $serviceId): JsonResponse
    {
        $request->merge([
            'phone' => $this->sanitizePhoneValue((string) $request->input('phone', '')),
            'client_phone' => $this->sanitizePhoneValue((string) $request->input('client_phone', '')),
        ]);

        $validator = Validator::make(
            array_merge($request->all(), ['service_id' => $serviceId]),
            [
                'service_id' => ['required', 'regex:/^(HM-\d+|GC-5|FC-1)$/'],
                'country' => 'nullable|string|min:2|max:3',
                'country_code' => 'nullable|string|min:2|max:3',
                'phone' => ['nullable', 'string', 'regex:/^\+?\d{7,15}$/'],
                'client_phone' => ['nullable', 'string', 'regex:/^\+?\d{7,15}$/'],
                'gallery_item_id' => 'nullable|integer|min:1',
                'item_id' => 'nullable|integer|min:1',
                'product_id' => 'nullable|integer|min:1',
            ]
        );

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $countryContext = $this->resolveCountryPricingContext($request);
        $menuItem = $this->resolveHeaderMenuItemByServiceId($serviceId);
        if (!$menuItem) {
            return response()->json([
                'status' => 'error',
                'error' => [
                    'code' => 'SERVICE_NOT_FOUND',
                    'message' => 'Service not found',
                    'details' => [
                        ['field' => 'service_id', 'issue' => 'not_found'],
                    ],
                ],
            ], 404);
        }

        $path = $this->normalizeMenuPath((string) $menuItem->link);
        $galleryItem = null;
        if ($path === '/new/gallery') {
            $galleryItem = $this->resolveRequestedGalleryCatalogItem($request->query());
            if ($this->hasRequestedGalleryCatalogItem($request->query()) && !$galleryItem) {
                return response()->json([
                    'status' => 'error',
                    'error' => [
                        'code' => 'GALLERY_ITEM_NOT_FOUND',
                        'message' => 'Gallery item not found',
                        'details' => [
                            ['field' => 'gallery_item_id', 'issue' => 'not_found'],
                        ],
                    ],
                ], 404);
            }
        }

        $sizes = $this->buildServiceSizesPayload($path, $countryContext, $galleryItem);
        if (empty($sizes)) {
            $data = [
                'service_id' => $serviceId,
                'service_path' => $path,
                'sizes' => [],
            ];
            if ($galleryItem) {
                $data['gallery_item_id'] = (int) $galleryItem->id;
            }

            return response()->json([
                'status' => 'ok',
                'meta' => [
                    'currency' => 'EUR',
                    'country_code' => $countryContext['country_code'],
                    'country_multiplier' => (float) $countryContext['multiplier'],
                ],
                'data' => $data,
            ], 200);
        }

        return response()->json([
            'status' => 'ok',
            'meta' => [
                'currency' => 'EUR',
                'country_code' => $countryContext['country_code'],
                'country_multiplier' => (float) $countryContext['multiplier'],
            ],
            'data' => [
                'service_id' => $serviceId,
                'service_path' => $path,
                'gallery_item_id' => $galleryItem ? (int) $galleryItem->id : null,
                'sizes' => $sizes,
            ],
        ], 200);
    }

    public function servicePriceBySize(Request $request, string $serviceId): JsonResponse
    {
        $request->merge([
            'phone' => $this->sanitizePhoneValue((string) $request->input('phone', '')),
            'client_phone' => $this->sanitizePhoneValue((string) $request->input('client_phone', '')),
        ]);

        $sizeRule = $this->isGiftCardServiceId($serviceId)
            ? ['required', 'string', 'max:32']
            : ['required', 'regex:/^\d+x\d+$/'];

        $validator = Validator::make(
            array_merge($request->all(), ['service_id' => $serviceId]),
            [
                'service_id' => ['required', 'regex:/^(HM-\d+|GC-5|FC-1)$/'],
                'size' => $sizeRule,
                'country' => 'nullable|string|min:2|max:3',
                'country_code' => 'nullable|string|min:2|max:3',
                'phone' => ['nullable', 'string', 'regex:/^\+?\d{7,15}$/'],
                'client_phone' => ['nullable', 'string', 'regex:/^\+?\d{7,15}$/'],
                'gallery_item_id' => 'nullable|integer|min:1',
                'item_id' => 'nullable|integer|min:1',
                'product_id' => 'nullable|integer|min:1',
            ]
        );

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $countryContext = $this->resolveCountryPricingContext($request);
        $menuItem = $this->resolveHeaderMenuItemByServiceId($serviceId);
        if (!$menuItem) {
            return response()->json([
                'status' => 'error',
                'error' => [
                    'code' => 'SERVICE_NOT_FOUND',
                    'message' => 'Service not found',
                    'details' => [
                        ['field' => 'service_id', 'issue' => 'not_found'],
                    ],
                ],
            ], 404);
        }

        $path = $this->normalizeMenuPath((string) $menuItem->link);
        $size = strtolower((string) $request->query('size'));
        if ($path === '/new/gift-card') {
            $normalizedAmount = $this->extractGiftCardNominalAmount($size);
            $size = $normalizedAmount !== null ? $this->normalizeGiftCardNominalKey($normalizedAmount) : trim($size);
        }

        $galleryItem = null;
        if ($path === '/new/gallery') {
            $galleryItem = $this->resolveRequestedGalleryCatalogItem($request->query());
            if ($this->hasRequestedGalleryCatalogItem($request->query()) && !$galleryItem) {
                return response()->json([
                    'status' => 'error',
                    'error' => [
                        'code' => 'GALLERY_ITEM_NOT_FOUND',
                        'message' => 'Gallery item not found',
                        'details' => [
                            ['field' => 'gallery_item_id', 'issue' => 'not_found'],
                        ],
                    ],
                ], 404);
            }
        }

        $sizePriceDetailsMap = $galleryItem
            ? $this->resolveSizePriceDetailsByGalleryItemRow($galleryItem)
            : $this->resolveServiceSizePriceDetailsByPath($path);
        if (!array_key_exists($size, $sizePriceDetailsMap)) {
            return response()->json([
                'status' => 'error',
                'error' => [
                    'code' => 'SIZE_NOT_FOUND',
                    'message' => 'Requested size is not available for service',
                    'details' => [
                        ['field' => 'size', 'issue' => 'not_available'],
                    ],
                ],
            ], 404);
        }

        if ($path === '/new/gift-card') {
            $details = $sizePriceDetailsMap[$size];
            $amount = (float) round((float) ($details['current'] ?? 0), 2);
            $originalAmount = isset($details['original']) ? (float) round((float) $details['original'], 2) : null;

            return response()->json([
                'status' => 'ok',
                'meta' => [
                    'currency' => 'EUR',
                    'country_code' => $countryContext['country_code'],
                    'country_multiplier' => (float) $countryContext['multiplier'],
                ],
                'data' => [
                    'service_id' => $serviceId,
                    'service_path' => $path,
                    'gallery_item_id' => $galleryItem ? (int) $galleryItem->id : null,
                    'size' => $size,
                    'width' => null,
                    'height' => null,
                    'format' => 'nominal',
                    'price' => [
                        'type' => 'fixed',
                        'amount' => $amount,
                        'original_amount' => $originalAmount,
                        'is_discounted' => (bool) ($details['is_discounted'] ?? false),
                    ],
                ],
            ], 200);
        }

        [$width, $height] = array_map('intval', explode('x', $size));
        $details = $sizePriceDetailsMap[$size];
        $amount = (float) round((float) ($details['current'] ?? 0) * (float) $countryContext['multiplier'], 2);
        $originalAmount = isset($details['original']) ? (float) round((float) $details['original'] * (float) $countryContext['multiplier'], 2) : null;

        return response()->json([
            'status' => 'ok',
            'meta' => [
                'currency' => 'EUR',
                'country_code' => $countryContext['country_code'],
                'country_multiplier' => (float) $countryContext['multiplier'],
            ],
            'data' => [
                'service_id' => $serviceId,
                'service_path' => $path,
                'gallery_item_id' => $galleryItem ? (int) $galleryItem->id : null,
                'size' => $size,
                'width' => $width,
                'height' => $height,
                'format' => $this->resolveSizeFormat($width, $height),
                'price' => [
                    'type' => 'fixed',
                    'amount' => $amount,
                    'original_amount' => $originalAmount,
                    'is_discounted' => (bool) ($details['is_discounted'] ?? false),
                ],
            ],
        ], 200);
    }

    private function buildFullCatalogServicePayload(array $service, array $countryContext): array
    {
        $serviceId = (string) ($service['id'] ?? '');
        $menuItem = $this->resolveHeaderMenuItemByServiceId($serviceId);
        $path = $this->normalizeMenuPath((string) ($menuItem->link ?? ($service['short_description'] ?? '')));
        $media = $this->resolveCatalogServiceMedia($serviceId, $path);

        $service['sizes'] = $this->buildServiceSizesPayload($path, $countryContext);
        $service = $this->enrichCatalogServicePriceFromSizes($service);
        $service['photo'] = $media['primary'] ?? null;
        $service['photos'] = $media['photos'] ?? [];

        return $service;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildServiceSizesPayload(string $path, array $countryContext, ?object $galleryItem = null): array
    {
        $sizePriceDetailsMap = $galleryItem
            ? $this->resolveSizePriceDetailsByGalleryItemRow($galleryItem)
            : $this->resolveServiceSizePriceDetailsByPath($path);

        if (empty($sizePriceDetailsMap)) {
            return [];
        }

        $sizes = [];
        if ($path === '/new/gift-card') {
            foreach ($sizePriceDetailsMap as $nominal => $details) {
                $amount = (float) ($details['current'] ?? 0);
                $sizes[] = [
                    'size' => $nominal,
                    'width' => null,
                    'height' => null,
                    'format' => 'nominal',
                    'price' => [
                        'type' => 'fixed',
                        'amount' => (float) round($amount, 2),
                        'original_amount' => isset($details['original']) ? (float) round((float) $details['original'], 2) : null,
                        'is_discounted' => (bool) ($details['is_discounted'] ?? false),
                    ],
                ];
            }

            usort($sizes, function (array $a, array $b) {
                return (float) ($a['price']['amount'] ?? 0) <=> (float) ($b['price']['amount'] ?? 0);
            });

            return $sizes;
        }

        foreach ($sizePriceDetailsMap as $size => $details) {
            [$width, $height] = array_map('intval', explode('x', $size));
            $current = (float) ($details['current'] ?? 0);
            $original = isset($details['original']) ? (float) $details['original'] : null;
            $sizes[] = [
                'size' => $size,
                'width' => $width,
                'height' => $height,
                'format' => $this->resolveSizeFormat($width, $height),
                'price' => [
                    'type' => 'fixed',
                    'amount' => (float) round($current * (float) $countryContext['multiplier'], 2),
                    'original_amount' => $original !== null ? (float) round($original * (float) $countryContext['multiplier'], 2) : null,
                    'is_discounted' => (bool) ($details['is_discounted'] ?? false),
                ],
            ];
        }

        usort($sizes, function (array $a, array $b) {
            if ($a['width'] === $b['width']) {
                return $a['height'] <=> $b['height'];
            }
            return $a['width'] <=> $b['width'];
        });

        return $sizes;
    }

    /**
     * @return array{primary: ?array<string,mixed>, photos: array<int, array<string,mixed>>}
     */
    private function resolveCatalogServiceMedia(string $serviceId, string $path): array
    {
        if ($path === '/new/canvas') {
            return $this->resolveCatalogSiteImagesMedia('canvas', ['mcard_about_block_1', 'canvas_1_size'], 'images/canvas.png');
        }

        if ($path === '/collage') {
            return $this->resolveCatalogSiteImagesMedia('collage', ['collage_f_order_1'], 'images/collage/vcollage-image.jpg');
        }

        if ($path === '/new/gift-card') {
            return $this->resolveCatalogSiteImagesMedia('gift-card', ['gift_card_slide', 'gift_card_gift_card'], 'images/gift.png');
        }

        if ($path === '/family-constructor') {
            return $this->buildStaticCatalogMediaSet('img/family-picture-img.png', 'static_asset');
        }

        if ($path === '/new/caricature') {
            return $this->preferCatalogMedia(
                $this->resolveCatalogGalleryMediaBySlug('caricature', function ($query) {
                    $query->where('is_sharj', 1);
                }),
                $this->resolveCatalogSiteImagesMedia('caricature', ['caricature_ordering_step_1', 'caricature_on_canvas'])
            );
        }

        if ($path === '/simpsons') {
            return $this->preferCatalogMedia(
                $this->resolveCatalogGalleryMediaBySlug('simpsons-portrait', function ($query) {
                    $query->where('slug', 'simpsons');
                }),
                $this->resolveCatalogSiteImagesMedia('simpsons', ['simpsons_service_1', 'simpsons_on_canvas'])
            );
        }

        if ($path === '/new/graphic-portrait/portrait-historical') {
            return $this->preferCatalogMedia(
                $this->resolveCatalogGalleryMediaBySlug('portrait-historical'),
                $this->resolveCatalogSiteImagesMedia('portrait-historical', ['portrait_historical_example', 'portrait_historical_on_canvas'])
            );
        }

        if (strpos($path, '/new/graphic-portrait/') === 0) {
            $slug = trim((string) substr($path, strlen('/new/graphic-portrait/')));
            return $this->preferCatalogMedia(
                $this->resolveCatalogGalleryMediaBySlug($slug),
                $this->resolveCatalogSiteImagesMedia('portrait', ['portrait_about_tab_about_deadline_1', 'portait-why'])
            );
        }

        if ($path === '/modular-generator') {
            return $this->resolveCatalogGalleryMediaByQuery(function ($query) {
                $query->where('id_type', 2);
            });
        }

        if ($path === '/new/gallery') {
            return $this->resolveCatalogGalleryMediaByQuery(function ($query) {
                $query->whereIn('id_type', [2, 3, 4]);
            });
        }

        // Keep payload stable even when no real image source exists.
        return [
            'primary' => null,
            'photos' => [],
        ];
    }

    /**
     * @param array{primary: ?array<string,mixed>, photos: array<int, array<string,mixed>>} $preferred
     * @param array{primary: ?array<string,mixed>, photos: array<int, array<string,mixed>>} $fallback
     * @return array{primary: ?array<string,mixed>, photos: array<int, array<string,mixed>>}
     */
    private function preferCatalogMedia(array $preferred, array $fallback): array
    {
        if (!empty($preferred['primary'])) {
            return $preferred;
        }

        return $fallback;
    }

    /**
     * @param array<int, string> $preferredPositions
     * @return array{primary: ?array<string,mixed>, photos: array<int, array<string,mixed>>}
     */
    private function resolveCatalogSiteImagesMedia(string $page, array $preferredPositions = [], ?string $fallbackPublicAsset = null): array
    {
        if (!Schema::hasTable('site_images')) {
            return $fallbackPublicAsset ? $this->buildStaticCatalogMediaSet($fallbackPublicAsset, 'static_asset') : ['primary' => null, 'photos' => []];
        }

        $query = DB::table('site_images')
            ->where('page', $page)
            ->whereNotNull('img')
            ->where('img', '!=', '')
            ->orderBy('id');

        $rows = $query->get(['id', 'position_name', 'img', 'updated_at']);
        if ($rows->isEmpty()) {
            return $fallbackPublicAsset ? $this->buildStaticCatalogMediaSet($fallbackPublicAsset, 'static_asset') : ['primary' => null, 'photos' => []];
        }

        $ordered = collect();
        foreach ($preferredPositions as $positionName) {
            $match = $rows->firstWhere('position_name', $positionName);
            if ($match) {
                $ordered->push($match);
            }
        }
        foreach ($rows as $row) {
            if (!$ordered->contains(function ($item) use ($row) {
                return (int) $item->id === (int) $row->id;
            })) {
                $ordered->push($row);
            }
        }

        $photos = $ordered
            ->map(function ($row) {
                return $this->buildCatalogMediaEntry((string) $row->img, 'site_images', [
                    'position_name' => (string) ($row->position_name ?? ''),
                ]);
            })
            ->filter()
            ->take(5)
            ->values()
            ->all();

        if (empty($photos) && $fallbackPublicAsset) {
            return $this->buildStaticCatalogMediaSet($fallbackPublicAsset, 'static_asset');
        }

        return [
            'primary' => $photos[0] ?? null,
            'photos' => $photos,
        ];
    }

    /**
     * @return array{primary: ?array<string,mixed>, photos: array<int, array<string,mixed>>}
     */
    private function resolveCatalogGalleryMediaBySlug(string $slug, ?callable $fallbackQuery = null): array
    {
        if ($slug !== '') {
            $row = $this->resolveGalleryMediaRowBySlug($slug);
            if ($row) {
                return $this->buildCatalogGalleryMediaSetFromRow($row);
            }
        }

        if ($fallbackQuery) {
            return $this->resolveCatalogGalleryMediaByQuery($fallbackQuery);
        }

        return ['primary' => null, 'photos' => []];
    }

    /**
     * @return array{primary: ?array<string,mixed>, photos: array<int, array<string,mixed>>}
     */
    private function resolveCatalogGalleryMediaByQuery(callable $applier): array
    {
        if (!Schema::hasTable('gallery_items')) {
            return ['primary' => null, 'photos' => []];
        }

        $columns = $this->galleryMediaSelectableColumns();
        $query = DB::table('gallery_items')->where('active', 1);
        $applier($query);
        $row = $query->orderBy('id')->first($columns);

        if (!$row) {
            return ['primary' => null, 'photos' => []];
        }

        return $this->buildCatalogGalleryMediaSetFromRow($row);
    }

    private function resolveGalleryMediaRowBySlug(string $slug): ?object
    {
        if ($slug === '' || !Schema::hasTable('gallery_items')) {
            return null;
        }

        return DB::table('gallery_items')
            ->where('active', 1)
            ->where('slug', $slug)
            ->orderBy('id')
            ->first($this->galleryMediaSelectableColumns());
    }

    /**
     * @return array<int, string>
     */
    private function galleryMediaSelectableColumns(): array
    {
        $columns = ['id', 'slug', 'name', 'updated_at'];
        foreach (['images', 'new_main_image', 'new_image1', 'new_image2', 'new_image1_inner', 'new_image2_inner', 'new_image3_inner'] as $column) {
            if (Schema::hasColumn('gallery_items', $column)) {
                $columns[] = $column;
            }
        }

        return array_values(array_unique($columns));
    }

    /**
     * @return array{primary: ?array<string,mixed>, photos: array<int, array<string,mixed>>}
     */
    private function buildCatalogGalleryMediaSetFromRow(object $row): array
    {
        $rawPaths = [];
        foreach (['new_main_image', 'new_image1', 'new_image2', 'new_image1_inner', 'new_image2_inner', 'new_image3_inner'] as $column) {
            if (!empty($row->{$column})) {
                $rawPaths[] = (string) $row->{$column};
            }
        }

        if (!empty($row->images)) {
            $decoded = json_decode((string) $row->images, true);
            if (is_array($decoded)) {
                foreach ($decoded as $imagePath) {
                    if (is_string($imagePath) && trim($imagePath) !== '') {
                        $rawPaths[] = trim($imagePath);
                    }
                }
            } elseif (is_string($row->images) && trim((string) $row->images) !== '') {
                $rawPaths[] = trim((string) $row->images);
            }
        }

        $rawPaths = array_values(array_unique(array_filter($rawPaths)));
        $photos = collect($rawPaths)
            ->map(function (string $rawPath) use ($row) {
                return $this->buildCatalogMediaEntry($rawPath, 'gallery_items', [
                    'gallery_item_id' => (int) ($row->id ?? 0),
                    'slug' => (string) ($row->slug ?? ''),
                    'name' => (string) ($row->name ?? ''),
                ]);
            })
            ->filter()
            ->take(5)
            ->values()
            ->all();

        return [
            'primary' => $photos[0] ?? null,
            'photos' => $photos,
        ];
    }

    /**
     * @return array{primary: array<string,mixed>, photos: array<int, array<string,mixed>>}
     */
    private function buildStaticCatalogMediaSet(string $publicRelativePath, string $source): array
    {
        $photo = $this->buildCatalogMediaEntry($publicRelativePath, $source);

        return [
            'primary' => $photo,
            'photos' => $photo ? [$photo] : [],
        ];
    }

    /**
     * @param array<string, mixed> $meta
     * @return array<string, mixed>|null
     */
    private function buildCatalogMediaEntry(string $rawPath, string $source, array $meta = []): ?array
    {
        $rawPath = trim($rawPath);
        if ($rawPath === '') {
            return null;
        }

        $url = $this->normalizeCatalogMediaUrl($rawPath, $source);
        if ($url === '') {
            return null;
        }

        return array_merge([
            'url' => $url,
            'source' => $source,
            'path' => $rawPath,
        ], $meta);
    }

    private function normalizeCatalogMediaUrl(string $rawPath, string $source): string
    {
        $rawPath = trim($rawPath);
        if ($rawPath === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $rawPath) || strpos($rawPath, '//') === 0) {
            return $rawPath;
        }

        $normalized = ltrim($rawPath, '/');

        if ($source === 'gallery_items') {
            if (strpos($normalized, 'storage/') === 0) {
                return asset($normalized);
            }

            return asset('storage/' . $normalized);
        }

        if (strpos($normalized, 'storage/') === 0) {
            return asset($normalized);
        }

        if ($source === 'site_images') {
            return asset('storage/' . $normalized);
        }

        return asset($normalized);
    }

    public function pipelineChangedWebhook(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|uuid',
            'event_type' => 'required|in:crm.lead.stage_changed',
            'idempotency_key' => 'required|string|max:255',
            'occurred_at' => 'required|date',
            'source' => 'required|string|in:CRM',
            'data' => 'required|array',
            'data.lead_id' => 'required',
            'data.pipeline.id' => 'required|string|max:255',
            'data.stage' => 'required|array',
            'data.stage.from.id' => 'nullable|string|in:' . implode(',', $this->supportedLeadStageStatuses()),
            'data.stage.to.id' => 'required|string|in:' . implode(',', $this->supportedLeadStageStatuses()),
            'data.changed_by' => 'required|array',
            'data.changed_by.type' => 'required|string|in:manager,system,agent',
            'data.changed_by.id' => 'required|string|max:255',
            'data.bot_control.mode' => 'nullable|in:active,paused,handoff_to_manager',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $eventId = (string) $request->input('event_id');
        if ($this->isDuplicateEvent('crm:webhooks:pipeline-changed:event', $eventId, [
            'event_id' => $eventId,
            'idempotency_key' => (string) $request->input('idempotency_key'),
            'event_type' => (string) $request->input('event_type'),
            'source' => (string) $request->input('source'),
            'payload' => $request->all(),
        ])) {
            return response()->json(['status' => 'duplicate'], 200);
        }

        $fromStage = (string) $request->input('data.stage.from.id', '');
        $toStage = (string) $request->input('data.stage.to.id', '');
        $normalizedFromStage = $this->normalizeStageId($fromStage);
        $normalizedToStage = $this->normalizeStageId($toStage);

        $mappedOrderStatus = $this->mapStageIdToOrderStatus($toStage);
        $updatedOrder = false;
        $leadId = $request->input('data.lead_id');
        $resolvedOrderId = $this->ensureOrderExistsForLead($leadId, [
            'source' => 'pipeline_changed',
        ]);

        if ($mappedOrderStatus !== null && Schema::hasTable('orders') && $resolvedOrderId !== null) {
            $updatedOrder = DB::table('orders')
                    ->where('id', $resolvedOrderId)
                    ->update([
                        'status' => $mappedOrderStatus,
                        'updated_at' => now(),
                    ]) > 0;
        }

        return response()->json([
            'status' => 'ok',
            'data' => [
                'lead_id' => $leadId,
                'stage' => [
                    'from' => ['id' => $normalizedFromStage],
                    'to' => ['id' => $normalizedToStage],
                ],
                'order_status' => $mappedOrderStatus,
                'order_updated' => $updatedOrder,
                'bot_mode' => $request->input('data.bot_control.mode'),
            ],
        ], 200);
    }

    public function lookupOrders(Request $request): JsonResponse
    {
        if (!Schema::hasTable('orders')) {
            return response()->json([
                'status' => 'error',
                'error' => [
                    'code' => 'ORDERS_TABLE_NOT_FOUND',
                    'message' => 'Orders table is not available.',
                ],
            ], 500);
        }

        $orderId = trim((string) $request->query('order_id', $request->query('lead_id', '')));
        $rawPhone = (string) $request->query('phone', $request->query('client_phone', ''));
        $phone = $this->sanitizePhoneValue($rawPhone);
        $phoneDigits = preg_replace('/\D+/', '', $phone);
        $phoneDigits = is_string($phoneDigits) ? $phoneDigits : '';
        $lang = strtolower((string) $request->query('lang', 'ru'));

        $details = [];
        if ($orderId === '' && $phone === '') {
            $details[] = [
                'field' => 'order_id|lead_id|phone|client_phone',
                'issue' => 'Передайте order_id/lead_id или phone/client_phone.',
            ];
        }
        if ($orderId !== '' && !ctype_digit($orderId)) {
            $details[] = [
                'field' => 'order_id',
                'issue' => 'order_id/lead_id должен быть положительным числом.',
            ];
        }
        if ($rawPhone !== '' && ($phone === '' || !preg_match('/^\+?\d{7,15}$/', $phone))) {
            $details[] = [
                'field' => 'phone',
                'issue' => 'phone/client_phone должен содержать 7-15 цифр; лишние символы очищаются автоматически.',
            ];
        }
        if (!in_array($lang, $this->supportedCatalogLocales(), true)) {
            $details[] = [
                'field' => 'lang',
                'issue' => 'lang должен быть одним из: ' . implode(',', $this->supportedCatalogLocales()),
            ];
        }

        if (!empty($details)) {
            return $this->validationErrorFromDetails($details);
        }

        $select = [
            'orders.*',
            'users.phone as lookup_user_phone',
            'users.email as lookup_user_email',
        ];
        $select[] = Schema::hasColumn('users', 'first_name')
            ? 'users.first_name as lookup_user_name'
            : DB::raw("'' as lookup_user_name");
        $select[] = Schema::hasColumn('users', 'last_name')
            ? 'users.last_name as lookup_user_last_name'
            : DB::raw("'' as lookup_user_last_name");

        $query = DB::table('orders')
            ->leftJoin('users', 'users.id', '=', 'orders.user_id')
            ->select($select);

        if ($orderId !== '') {
            $query->where('orders.id', (int) $orderId);
        }

        if ($phoneDigits !== '') {
            $query->where(function ($phoneQuery) use ($phone, $phoneDigits) {
                $phoneQuery->where('orders.delivery', 'like', '%' . $phoneDigits . '%')
                    ->orWhere('orders.delivery', 'like', '%' . $phone . '%')
                    ->orWhere('users.phone', 'like', '%' . $phoneDigits . '%')
                    ->orWhere('users.phone', 'like', '%' . $phone . '%');

                if (Schema::hasColumn('orders', 'sa_client_phone')) {
                    $phoneQuery->orWhere('orders.sa_client_phone', 'like', '%' . $phoneDigits . '%')
                        ->orWhere('orders.sa_client_phone', 'like', '%' . $phone . '%');
                }
            });
        }

        $orders = $query
            ->orderBy('orders.id', 'desc')
            ->get()
            ->map(function ($order) use ($lang) {
                return $this->buildOrderLookupPayload($order, $lang);
            })
            ->values()
            ->all();

        return response()->json([
            'status' => 'ok',
            'meta' => [
                'count' => count($orders),
                'lookup' => [
                    'order_id' => $orderId !== '' ? (int) $orderId : null,
                    'phone' => $phone !== '' ? $phone : null,
                    'lang' => $lang,
                ],
            ],
            'data' => [
                'orders' => $orders,
            ],
        ], 200);
    }

    public function createLead(Request $request): JsonResponse
    {
        $request = $this->normalizePhoneFields($request, [
            'lead.client.phone',
            'lead.recipient.phone',
        ]);

        $validator = Validator::make($request->all(), [
            'idempotency_key' => 'required|string|max:255',
            'source' => 'required|string|in:SA',
            'lead' => 'required|array',
            'lead.external_ids.conversation_id' => 'nullable|string|max:255',
            'lead.client.phone' => ['required', 'string', 'regex:/^\+?\d{7,15}$/'],
            'lead.client.name' => 'nullable|string|max:255',
            'lead.channel' => 'required|string|in:whatsapp,telegram,viber,facebook,instagram,webchat',
            'lead.initial_message' => 'nullable|string',
            'lead.pipeline.id' => 'nullable|string|max:255',
            'lead.stage.id' => 'nullable|string|in:' . implode(',', $this->supportedLeadStageStatuses()),
            'lead.service_request' => 'nullable|array',
            'lead.service_request.service_id' => 'nullable|string|max:255',
            'lead.service_request.country_code' => 'nullable|string|max:3',
            'lead.service_request.options' => 'nullable|array',
            'lead.service_request.options.size' => 'nullable|string|max:32',
            'lead.service_request.options.canvas_id' => 'nullable|integer|min:1',
            'lead.service_request.options.holst_id' => 'nullable|integer|min:1',
            'lead.service_request.options.decoration_id' => 'nullable|integer|min:1',
            'lead.service_request.options.decor_id' => 'nullable|integer|min:1',
            'lead.service_request.options.packaging_id' => 'nullable|integer|min:1',
            'lead.service_request.options.compl_id' => 'nullable|integer|min:1',
            'lead.service_request.options.ram_id' => 'nullable|integer|min:1',
            'lead.service_request.options.form_id' => 'nullable|integer|min:1',
            'lead.service_request.options.formId' => 'nullable|integer|min:1',
            'lead.service_request.options.execution_id' => 'nullable|integer|min:1',
            'lead.service_request.options.executionId' => 'nullable|integer|min:1',
            'lead.service_request.options.photo_improvement_id' => 'nullable|integer|min:1',
            'lead.service_request.options.gallery_item_id' => 'nullable|integer|min:1',
            'lead.service_request.options.item_id' => 'nullable|integer|min:1',
            'lead.service_request.options.product_id' => 'nullable|integer|min:1',
            'lead.service_request.options.production_mode' => 'nullable|string|in:standard,express',
            'lead.service_request.options.layout_svg' => 'nullable|string',
            'lead.service_request.options.collageSvgImage' => 'nullable|string',
            'lead.service_request.options.layout_blocks' => 'nullable|array',
            'lead.service_request.options.layout_blocks.*.width' => 'required_with:lead.service_request.options.layout_blocks|numeric|min:0.1',
            'lead.service_request.options.layout_blocks.*.height' => 'required_with:lead.service_request.options.layout_blocks|numeric|min:0.1',
            'lead.service_request.options.amount' => 'nullable|numeric|min:1',
            'lead.service_request.options.nominal' => 'nullable|string|max:32',
            'lead.service_request.options.card_type' => 'nullable|string|in:online,offline',
            'lead.service_request.options.whom' => 'nullable|string|max:255',
            'lead.service_request.options.hide_nom' => 'nullable|boolean',
            'lead.service_request.layout_svg' => 'nullable|string',
            'lead.service_request.collageSvgImage' => 'nullable|string',
            'lead.service_request.layout_blocks' => 'nullable|array',
            'lead.service_request.layout_blocks.*.width' => 'required_with:lead.service_request.layout_blocks|numeric|min:0.1',
            'lead.service_request.layout_blocks.*.height' => 'required_with:lead.service_request.layout_blocks|numeric|min:0.1',
            'lead.service_request.amount' => 'nullable|numeric|min:1',
            'lead.service_request.nominal' => 'nullable|string|max:32',
            'lead.service_request.card_type' => 'nullable|string|in:online,offline',
            'lead.service_request.whom' => 'nullable|string|max:255',
            'lead.service_request.hide_nom' => 'nullable|boolean',
            'lead.service_request.gallery_item_id' => 'nullable|integer|min:1',
            'lead.service_request.item_id' => 'nullable|integer|min:1',
            'lead.service_request.product_id' => 'nullable|integer|min:1',
            'lead.recipient' => 'nullable|array',
            'lead.recipient.name' => 'nullable|string|max:255',
            'lead.recipient.last_name' => 'nullable|string|max:255',
            'lead.recipient.phone' => ['nullable', 'string', 'regex:/^\+?\d{7,15}$/'],
            'lead.recipient.address' => 'nullable|string|max:500',
            'lead.recipient.postal_index' => 'nullable|string|max:20',
            'lead.pricing' => 'nullable|array',
            'lead.pricing.coupon_code' => 'nullable|string|max:255',
            'lead.pricing.use_bonus' => 'nullable|boolean',
            'lead.delivery' => 'nullable|array',
            'lead.delivery.method' => 'nullable|string|in:' . implode(',', $this->supportedLeadDeliveryMethods()),
            'lead.delivery.payment' => 'nullable|string|in:' . implode(',', $this->acceptedLeadPaymentMethods()),
            'lead.delivery.deliv_price' => 'nullable|numeric|min:0',
            'lead.delivery.country' => 'nullable|string|max:3',
            'lead.delivery.city' => 'nullable|string|max:255',
            'lead.delivery.address' => 'nullable|string|max:500',
            'lead.delivery.postal_index' => 'nullable|string|max:20',
            'lead.delivery.pickup_workshop_id' => 'nullable|integer|min:1',
            'lead.delivery.delivery_town_id' => 'nullable|integer|min:1',
            'lead.delivery.delivery_photo_short_code' => 'nullable|string|max:64',
            'lead.billing' => 'nullable|array',
            'lead.billing.company' => 'nullable|array',
            'lead.billing.company.name' => 'nullable|string|max:255',
            'lead.billing.company.name_l' => 'nullable|string|max:255',
            'lead.billing.company.registration_number' => 'nullable|string|max:255',
            'lead.billing.company.legal_address' => 'nullable|string|max:500',
            'lead.billing.company.pnr_nr' => 'nullable|string|max:255',
            'lead.billing.company.bank_name' => 'nullable|string|max:255',
            'lead.billing.company.bank_code' => 'nullable|string|max:255',
            'lead.billing.company.bank_account_code' => 'nullable|string|max:255',
            'lead.fields' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        if ($request->boolean('lead.pricing.use_bonus') && trim((string) $request->input('lead.pricing.coupon_code', '')) !== '') {
            return $this->validationErrorFromDetails([
                [
                    'field' => 'lead.pricing',
                    'issue' => 'Нельзя одновременно передавать coupon_code и use_bonus=true',
                ],
            ]);
        }

        $giftCardValidationErrors = $this->validateGiftCardLeadPayload((array) $request->input('lead', []));
        if (!empty($giftCardValidationErrors)) {
            return $this->validationErrorFromDetails($giftCardValidationErrors);
        }

        $galleryValidationErrors = $this->validateGalleryCatalogLeadPayload((array) $request->input('lead', []));
        if (!empty($galleryValidationErrors)) {
            return $this->validationErrorFromDetails($galleryValidationErrors);
        }

        $idempotencyKey = (string) $request->input('idempotency_key');
        if ($this->isDuplicateEvent('sa:leads:create:key', $idempotencyKey, [
            'idempotency_key' => $idempotencyKey,
            'event_type' => 'sa.lead.create',
            'source' => (string) $request->input('source'),
            'payload' => $request->all(),
        ])) {
            return response()->json(['status' => 'duplicate'], 200);
        }

        $lead = (array) $request->input('lead', []);
        try {
            $leadId = $this->resolveOrCreateLeadId($lead, $idempotencyKey);
        } catch (\InvalidArgumentException $e) {
            $decoded = json_decode($e->getMessage(), true);
            if (is_array($decoded) && isset($decoded['details']) && is_array($decoded['details'])) {
                return $this->validationErrorFromDetails(
                    $decoded['details'],
                    (string) ($decoded['message'] ?? '')
                );
            }

            return $this->validationErrorFromDetails([
                [
                    'field' => 'lead.pricing',
                    'issue' => $e->getMessage(),
                ],
            ]);
        }
        if ($leadId === null) {
            return response()->json([
                'status' => 'error',
                'error' => [
                    'code' => 'USER_CREATION_FAILED',
                    'message' => 'Не удалось создать клиента. Укажите корректный номер телефона или email.',
                ],
            ], 422);
        }
        try {
            $resolvedOrderId = $this->ensureOrderExistsForLead($leadId, $lead);
        } catch (\InvalidArgumentException $e) {
            $decoded = json_decode($e->getMessage(), true);
            if (is_array($decoded) && isset($decoded['details']) && is_array($decoded['details'])) {
                return $this->validationErrorFromDetails(
                    $decoded['details'],
                    (string) ($decoded['message'] ?? '')
                );
            }

            return $this->validationErrorFromDetails([
                [
                    'field' => 'lead.pricing',
                    'issue' => $e->getMessage(),
                ],
            ]);
        }
        if ($resolvedOrderId !== null) {
            $leadId = (string) $resolvedOrderId;
            $this->bindConversationToOrder(
                $resolvedOrderId,
                (string) data_get($lead, 'external_ids.conversation_id', ''),
                (string) data_get($lead, 'client.phone', '')
            );

            $requestedStageId = $this->normalizeStageId((string) data_get($lead, 'stage.id', ''));
            if ($requestedStageId !== null && Schema::hasTable('orders')) {
                DB::table('orders')
                    ->where('id', $resolvedOrderId)
                    ->update([
                        'status' => $requestedStageId,
                        'updated_at' => now(),
                    ]);
            }
        }

        $responseStageId = $this->normalizeStageId((string) data_get($lead, 'stage.id', '')) ?? 'watching';

        return response()->json([
            'status' => 'ok',
            'data' => [
                'lead_id' => $leadId,
                'contact_id' => 'CRM-CONTACT-' . $leadId,
                'deal_id' => 'CRM-DEAL-' . $leadId,
                'pipeline' => [
                    'id' => (string) data_get($lead, 'pipeline.id', 'PIPE-1'),
                ],
                'stage' => [
                    'id' => $responseStageId,
                ],
                'links' => [
                    'lead_url' => 'https://crm.example.com/leads/' . $leadId,
                ],
            ],
        ], 200);
    }

    public function updateLead(Request $request, string $leadId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'idempotency_key' => 'required|string|max:255',
            'source' => 'required|string|in:SA',
            'update' => 'required|array',
            'update.fields' => 'nullable|array',
            'update.stage' => 'nullable|array',
            'update.stage.id' => 'nullable|string|in:' . implode(',', $this->supportedLeadStageStatuses()),
            'update.stage.from.id' => 'nullable|string|in:' . implode(',', $this->supportedLeadStageStatuses()),
            'update.tags_add' => 'nullable|array',
            'update.tags_add.*' => 'string|max:100',
            'update.tags_remove' => 'nullable|array',
            'update.tags_remove.*' => 'string|max:100',
            'update.notes_append' => 'nullable|array',
            'update.notes_append.*.text' => 'required_with:update.notes_append|string',
            'update.notes_append.*.created_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $idempotencyKey = (string) $request->input('idempotency_key');
        if ($this->isDuplicateEvent('sa:leads:update:key', $idempotencyKey, [
            'idempotency_key' => $idempotencyKey,
            'event_type' => 'sa.lead.update',
            'source' => (string) $request->input('source'),
            'payload' => $request->all(),
        ])) {
            return response()->json(['status' => 'duplicate'], 200);
        }

        $newStageId = (string) $request->input('update.stage.id', '');
        $fromStageId = (string) $request->input('update.stage.from.id', '');

        // If from.id not provided, try to detect current stage from order
        if ($newStageId !== '' && $fromStageId === '' && Schema::hasTable('orders')) {
            $currentStatus = DB::table('orders')->where('id', (int) $leadId)->value('status');
            if ($currentStatus) {
                $fromStageId = $this->mapOrderStatusToStageId($currentStatus);
            }
        }

        $leadExists = true;
        $orderUpdated = false;
        if (Schema::hasTable('orders')) {
            // Check if lead exists WITHOUT auto-creating
            $resolvedOrderId = DB::table('orders')->where('id', (int) $leadId)->value('id');
            if ($resolvedOrderId === null) {
                $resolvedOrderId = DB::table('orders')->where('sa_conversation_id', $leadId)->value('id');
            }
            $leadExists = $resolvedOrderId !== null;

            if (!$leadExists) {
                return response()->json([
                    'status' => 'error',
                    'error' => [
                        'code' => 'LEAD_NOT_FOUND',
                        'message' => 'Lead not found',
                        'details' => [['field' => 'lead_id', 'issue' => 'not_found']],
                    ],
                ], 404);
            }

            $mappedOrderStatus = $this->mapStageIdToOrderStatus($newStageId);
            if ($resolvedOrderId !== null && $mappedOrderStatus !== null) {
                $orderUpdated = DB::table('orders')
                        ->where('id', $resolvedOrderId)
                        ->update([
                            'status' => $mappedOrderStatus,
                            'updated_at' => now(),
                        ]) > 0;
            }
        } else {
            $leadExists = false;
        }

        $tagsAdd = (array) $request->input('update.tags_add', []);
        $tagsRemove = (array) $request->input('update.tags_remove', []);
        $notesAppend = (array) $request->input('update.notes_append', []);

        return response()->json([
            'status' => 'ok',
            'data' => [
                'lead_id' => $leadId,
                'updated' => true,
                'stage' => ['id' => $this->normalizeStageId($newStageId)],
                'fields_updated' => array_keys((array) $request->input('update.fields', [])),
                'tags' => [
                    'added' => $tagsAdd,
                    'removed' => $tagsRemove,
                ],
                'notes_appended' => count($notesAppend),
                'order_updated' => $orderUpdated,
                // Policy-based behavior for unknown lead_id in MVP.
                'temporary_lead_created' => !$leadExists,
            ],
        ], 200);
    }

    public function createEscalation(Request $request): JsonResponse
    {
        $request = $this->normalizePhoneFields($request, [
            'escalation.dialog.client.phone',
        ]);

        $validator = Validator::make($request->all(), [
            'idempotency_key' => 'required|string|max:255',
            'source' => 'required|string|in:SA',
            'escalation' => 'required|array',
            'escalation.lead_id' => 'required',
            'escalation.conversation_id' => 'required|string|max:255',
            'escalation.priority' => 'required|in:low,normal,high,urgent',
            'escalation.reason_code' => 'required|string|max:255',
            'escalation.reason_text' => 'nullable|string',
            'escalation.confidence' => 'nullable|numeric|min:0|max:1',
            'escalation.suggested_next' => 'nullable|array',
            'escalation.dialog' => 'required|array',
            'escalation.dialog.channel' => 'required|string|in:whatsapp,telegram,viber,facebook,instagram,webchat',
            'escalation.dialog.client' => 'required|array',
            'escalation.dialog.client.phone' => ['nullable', 'string', 'regex:/^\+?\d{7,15}$/'],
            'escalation.dialog.messages' => 'required|array|min:1',
            'escalation.bot_control' => 'nullable|array',
            'escalation.bot_control.set_mode' => 'nullable|in:active,paused,handoff_to_manager',
            'escalation.bot_control.allow_manager_takeover' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $idempotencyKey = (string) $request->input('idempotency_key');
        if ($this->isDuplicateEvent('sa:escalations:create:key', $idempotencyKey, [
            'idempotency_key' => $idempotencyKey,
            'event_type' => 'sa.escalation.create',
            'source' => (string) $request->input('source'),
            'payload' => $request->all(),
        ])) {
            return response()->json(['status' => 'duplicate'], 200);
        }

        $escalation = (array) $request->input('escalation', []);
        $leadId = (string) data_get($escalation, 'lead_id');
        $conversationId = (string) data_get($escalation, 'conversation_id');
        $priority = (string) data_get($escalation, 'priority', 'normal');
        $taskId = $this->buildEscalationTaskId($leadId, $conversationId, $idempotencyKey);

        $this->persistEscalation($escalation, $taskId);

        return response()->json([
            'status' => 'ok',
            'data' => [
                'task_id' => $taskId,
                'lead_id' => $leadId,
                'priority' => $priority,
                'bot_mode' => data_get($escalation, 'bot_control.set_mode'),
                'links' => [
                    'task_url' => 'https://crm.example.com/tasks/' . $taskId,
                    'lead_url' => 'https://crm.example.com/leads/' . $leadId,
                ],
            ],
        ], 200);
    }

    public function botControlWebhook(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|uuid',
            'event_type' => 'required|in:crm.bot_control',
            'idempotency_key' => 'required|string|max:255',
            'occurred_at' => 'required|date',
            'source' => 'required|string|in:CRM',
            'data' => 'required|array',
            'data.lead_id' => 'nullable',
            'data.conversation_id' => 'nullable|string|max:255',
            'data.action' => 'required|in:pause_bot,resume_bot,handoff_to_manager',
            'data.changed_by' => 'required|array',
            'data.changed_by.type' => 'required|string|in:manager,system,agent',
            'data.changed_by.id' => 'required|string|max:255',
            'data.changed_by.name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        if (!$request->filled('data.lead_id') && !$request->filled('data.conversation_id')) {
            return $this->validationErrorFromDetails(
                [
                    [
                        'field' => 'data.lead_id',
                        'issue' => 'The data.lead_id field is required when data.conversation_id is not present.',
                    ],
                    [
                        'field' => 'data.conversation_id',
                        'issue' => 'The data.conversation_id field is required when data.lead_id is not present.',
                    ],
                ],
                'Either data.lead_id or data.conversation_id is required.'
            );
        }

        $eventId = (string) $request->input('event_id');
        if ($this->isDuplicateEvent('crm:webhooks:bot-control:event', $eventId, [
            'event_id' => $eventId,
            'idempotency_key' => (string) $request->input('idempotency_key'),
            'event_type' => (string) $request->input('event_type'),
            'source' => (string) $request->input('source'),
            'payload' => $request->all(),
        ])) {
            return response()->json(['status' => 'duplicate'], 200);
        }

        $action = (string) $request->input('data.action');
        $conversationId = (string) $request->input('data.conversation_id', '');
        if ($conversationId === '') {
            $conversationId = $this->resolveConversationIdForLead($request->input('data.lead_id'));
        }
        $resolvedLeadId = $this->resolveLinkedOrderId($request->input('data.lead_id'), $conversationId);
        $modeMap = [
            'pause_bot' => 'paused',
            'resume_bot' => 'active',
            'handoff_to_manager' => 'handoff_to_manager',
        ];

        $this->persistBotControl(
            $request->input('data.lead_id'),
            $conversationId,
            $action,
            $modeMap[$action] ?? null,
            (array) $request->input('data.changed_by', []),
            $request->all()
        );

        return response()->json([
            'status' => 'ok',
            'data' => [
                'lead_id' => $request->input('data.lead_id'),
                'resolved_lead_id' => $resolvedLeadId,
                'conversation_id' => $conversationId,
                'action' => $action,
                'bot_mode' => $modeMap[$action] ?? null,
                'changed_by' => $request->input('data.changed_by'),
            ],
        ], 200);
    }

    private function isDuplicateEvent(string $prefix, string $identifier, array $context = []): bool
    {
        $cacheKey = $prefix . ':' . sha1($identifier);

        if (Schema::hasTable('sa_events')) {
            try {
                SaEvent::create([
                    'dedupe_key' => $cacheKey,
                    'event_id' => $context['event_id'] ?? null,
                    'idempotency_key' => $context['idempotency_key'] ?? null,
                    'event_type' => $context['event_type'] ?? null,
                    'source' => $context['source'] ?? null,
                    'payload' => isset($context['payload']) ? json_encode($context['payload']) : null,
                    'status' => 'received',
                    'processed_at' => now(),
                ]);

                return false;
            } catch (QueryException $e) {
                // Duplicate key -> idempotent duplicate.
                if ((int) $e->getCode() === 23000) {
                    return true;
                }

                // Fall back to cache if DB write fails for non-duplicate reason.
            }
        }

        // Cache::add returns false when key already exists.
        return !Cache::add($cacheKey, 1, now()->addDay());
    }

    private function persistMessagePayload(Request $request, string $eventType, string $eventId): void
    {
        if (!Schema::hasTable('sa_messages') || !Schema::hasTable('sa_conversations')) {
            return;
        }

        $data = (array) $request->input('data', []);
        $messageId = $eventType === 'message.created'
            ? (string) data_get($data, 'message.message_id')
            : (string) data_get($data, 'message_id');
        $existingMessage = $messageId !== ''
            ? SaMessage::query()->where('message_id', $messageId)->first()
            : null;
        $conversationId = (string) data_get($data, 'conversation_id', (string) ($existingMessage->conversation_id ?? ''));
        $leadId = data_get($data, 'lead_id');
        $resolvedOrderId = $this->resolveLinkedOrderId($leadId, $conversationId);
        if ($resolvedOrderId === null && $existingMessage && $existingMessage->orders_id) {
            $resolvedOrderId = (int) $existingMessage->orders_id;
        }

        $this->upsertSaConversation(
            $conversationId,
            $resolvedOrderId,
            (string) data_get($data, 'message.from.phone', ''),
            (string) data_get($data, 'message.from.name', ''),
            (string) data_get($data, 'channel', ''),
            null,
            now(),
            (string) data_get($data, 'message.direction', ''),
            (string) data_get($data, 'message.from.type', '')
        );
        $this->syncOrderIntegrationFields(
            $resolvedOrderId,
            $conversationId,
            (string) data_get($data, 'message.from.phone', ''),
            null
        );

        if ($eventType === 'message.created') {
            $direction = (string) data_get($data, 'message.direction', 'inbound');
            $storedAttachments = $this->processMessageAttachments(
                (array) data_get($data, 'message.attachments', []),
                $messageId
            );
            $chatText = trim((string) data_get($data, 'message.text', ''));
            $chatText = $this->buildChatTextWithAttachments($chatText, $storedAttachments);
            $messageModel = SaMessage::query()->updateOrCreate(
                ['message_id' => $messageId],
                [
                    'event_id' => $eventId,
                    'orders_id' => $resolvedOrderId,
                    'conversation_id' => $conversationId,
                    'direction' => (string) data_get($data, 'message.direction'),
                    'status' => (string) data_get($data, 'message.status'),
                    'from_json' => json_encode(data_get($data, 'message.from', [])),
                    'to_json' => json_encode(data_get($data, 'message.to', [])),
                    'text' => $chatText,
                    'attachments_json' => json_encode($storedAttachments),
                    'sent_at' => $this->normalizeDateTime(data_get($data, 'message.sent_at')),
                    'provider_meta_json' => json_encode(data_get($data, 'message.provider_meta', [])),
                ]
            );

            if ($messageModel->wasRecentlyCreated) {
                $this->syncClientChatMessage(
                    $resolvedOrderId,
                    $chatText,
                    $direction === 'outbound',
                    (string) data_get($data, 'message.sent_at', ''),
                    $messageId
                );
            }
        }

        if ($eventType === 'message.status') {
            SaMessage::query()
                ->where('message_id', (string) data_get($data, 'message_id'))
                ->update([
                    'status' => (string) data_get($data, 'status'),
                    'updated_at' => now(),
                ]);
        }
    }

    private function persistOutboundMessageRequest(Request $request): ?SaMessage
    {
        if (!Schema::hasTable('sa_messages')) {
            return null;
        }

        $data = (array) $request->input('data', []);
        $messageId = 'OUT-' . substr(sha1((string) $request->input('idempotency_key')), 0, 16);
        $leadId = data_get($data, 'lead_id');
        $conversationId = (string) data_get($data, 'conversation_id', '');
        $resolvedOrderId = $this->resolveLinkedOrderId($leadId, $conversationId);

        $this->upsertSaConversation(
            $conversationId,
            $resolvedOrderId,
            (string) data_get($data, 'client.phone', ''),
            (string) data_get($data, 'client.name', ''),
            (string) data_get($data, 'channel', ''),
            (string) data_get($data, 'bot_control.mode_after_send', ''),
            null,
            'outbound'
        );

        $messageModel = SaMessage::query()->updateOrCreate(
            ['message_id' => $messageId],
            [
                'event_id' => (string) $request->input('event_id'),
                'orders_id' => $resolvedOrderId,
                'conversation_id' => $conversationId,
                'direction' => 'outbound',
                'status' => 'sent',
                'from_json' => json_encode(data_get($data, 'manager', [])),
                'to_json' => json_encode(data_get($data, 'client', [])),
                'text' => data_get($data, 'message.text'),
                'provider_meta_json' => json_encode([
                    'provider_message_id' => null,
                    'queued' => true,
                ]),
                'sent_at' => $this->normalizeDateTime(now()->toIso8601String()),
            ]
        );

        if ($messageModel->wasRecentlyCreated) {
            $this->syncClientChatMessage(
                $resolvedOrderId,
                (string) data_get($data, 'message.text', ''),
                true,
                '',
                $messageId
            );
        }

        return $messageModel;
    }

    private function persistEscalation(array $escalation, string $taskId): void
    {
        if (!Schema::hasTable('sa_escalations')) {
            return;
        }

        $leadId = data_get($escalation, 'lead_id');
        $conversationId = (string) data_get($escalation, 'conversation_id', '');
        $resolvedOrderId = $this->ensureOrderExistsForLead($leadId, $escalation);

        SaEscalation::query()->create([
            'orders_id' => $resolvedOrderId,
            'conversation_id' => $conversationId,
            'priority' => (string) data_get($escalation, 'priority', 'normal'),
            'reason_code' => (string) data_get($escalation, 'reason_code', ''),
            'reason_text' => data_get($escalation, 'reason_text'),
            'confidence' => data_get($escalation, 'confidence'),
            'suggested_next_json' => json_encode(data_get($escalation, 'suggested_next', [])),
            'dialog_json' => json_encode(data_get($escalation, 'dialog', [])),
            'task_ref' => $taskId,
        ]);

        $modeAfter = data_get($escalation, 'bot_control.set_mode');
        if ($modeAfter) {
            $this->persistBotControl(
                $leadId,
                $conversationId,
                'escalation_bot_control',
                (string) $modeAfter,
                ['type' => 'system', 'id' => 'sa-escalation'],
                $escalation
            );
        }
    }

    private function persistBotControl(
        $leadId,
        string $conversationId,
        string $action,
        ?string $modeAfter,
        array $changedBy,
        array $payload
    ): void {
        if (!Schema::hasTable('sa_bot_controls')) {
            return;
        }

        $resolvedOrderId = $this->resolveLinkedOrderId($leadId, $conversationId);

        SaBotControl::query()->create([
            'orders_id' => $resolvedOrderId,
            'conversation_id' => $conversationId,
            'action' => $action,
            'mode_after' => $modeAfter,
            'changed_by_json' => json_encode($changedBy),
            'payload' => json_encode($payload),
        ]);

        if (Schema::hasTable('sa_conversations') && $conversationId !== '') {
            SaConversation::query()
                ->where('conversation_id', $conversationId)
                ->update([
                    'bot_mode' => $modeAfter,
                    'updated_at' => now(),
                ]);
        }

        $this->syncOrderIntegrationFields($resolvedOrderId, $conversationId, '', $modeAfter);
    }

    private function ensureOrderExistsForLead($leadId, array $context = []): ?int
    {
        if (!Schema::hasTable('orders') || !is_numeric($leadId)) {
            return null;
        }

        $orderId = (int) $leadId;
        if ($orderId <= 0) {
            return null;
        }

        if (DB::table('orders')->where('id', $orderId)->exists()) {
            return $orderId;
        }

        $basket = [];
        $checkoutParams = [];
        $adminCommentAppend = '';

        if (isset($context['service_request']['service_id'])) {
            $basket = $this->resolveServiceToBasket($context['service_request']);

            if (!empty($basket)) {
                $checkoutParams = $this->buildCheckoutParamsFromLead($context);
            } else {
                $adminCommentAppend = "\n[SA] Внимание: не удалось найти услугу по code/ID: " . $context['service_request']['service_id'];
            }
        }

        if (!empty($basket) && !empty($checkoutParams)) {
            try {
                $leadUser = $this->resolveLeadUserFromContext($context);
                if ($leadUser) {
                    $pricingResult = $this->applyLeadPricingToOrderPayload($basket, $checkoutParams, $context, $leadUser);
                    $basket = $pricingResult['basket'];
                    $checkoutParams = $pricingResult['checkout_params'];
                }
                $newOrderId = $leadUser ? $this->saveOrderAsUser($leadUser, $basket, $checkoutParams) : null;
                if ($newOrderId) {
                    return $newOrderId;
                }
            } catch (\Exception $e) {
                Log::error('SA Integration saveOrder error: ' . $e->getMessage());
                $adminCommentAppend = "\n[SA] Ошибка автосоздания реального заказа: " . $e->getMessage();
            }
        }

        $context['admin_comment_append'] = $adminCommentAppend;

        if ($this->createTemporaryOrder($orderId, $context)) {
            return $orderId;
        }

        return null;
    }

    private function resolveLinkedOrderId($leadId, string $conversationId = ''): ?int
    {
        if (Schema::hasTable('orders') && is_numeric($leadId)) {
            $orderId = (int) $leadId;
            if ($orderId > 0 && DB::table('orders')->where('id', $orderId)->exists()) {
                return $orderId;
            }
        }

        if ($conversationId !== '') {
            if (Schema::hasTable('sa_conversations')) {
                $conversationOrderId = SaConversation::query()
                    ->where('conversation_id', $conversationId)
                    ->value('orders_id');

                if ($conversationOrderId !== null && (int) $conversationOrderId > 0) {
                    return (int) $conversationOrderId;
                }
            }

            if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'sa_conversation_id')) {
                $orderId = DB::table('orders')
                    ->where('sa_conversation_id', $conversationId)
                    ->value('id');

                if ($orderId !== null && (int) $orderId > 0) {
                    return (int) $orderId;
                }
            }
        }

        return null;
    }

    private function resolveConversationIdForLead($leadId): string
    {
        if (!is_numeric($leadId) || !Schema::hasTable('orders')) {
            return '';
        }

        $orderId = (int) $leadId;
        if ($orderId <= 0) {
            return '';
        }

        if (Schema::hasColumn('orders', 'sa_conversation_id')) {
            $conversationId = DB::table('orders')
                ->where('id', $orderId)
                ->value('sa_conversation_id');

            if ($conversationId !== null && (string) $conversationId !== '') {
                return (string) $conversationId;
            }
        }

        if (Schema::hasTable('sa_conversations')) {
            $conversationId = SaConversation::query()
                ->where('orders_id', $orderId)
                ->orderByDesc('id')
                ->value('conversation_id');

            if ($conversationId !== null && (string) $conversationId !== '') {
                return (string) $conversationId;
            }
        }

        return '';
    }

    private function upsertSaConversation(
        string $conversationId,
        ?int $orderId,
        string $clientPhone = '',
        string $clientName = '',
        string $channel = '',
        ?string $botMode = null,
        ?Carbon $lastMessageAt = null,
        ?string $lastDirection = null,
        ?string $lastSenderType = null
    ): void {
        if ($conversationId === '' || !Schema::hasTable('sa_conversations')) {
            return;
        }

        $existing = SaConversation::query()
            ->where('conversation_id', $conversationId)
            ->first();

        $attributes = [
            'orders_id' => $orderId ?? ($existing->orders_id ?? null),
            'client_phone' => $clientPhone !== '' ? $clientPhone : ($existing->client_phone ?? null),
            'client_name' => $clientName !== '' ? $clientName : ($existing->client_name ?? null),
            'channel' => $channel !== '' ? $channel : ($existing->channel ?? null),
            'bot_mode' => ($botMode !== null && $botMode !== '') ? $botMode : ($existing->bot_mode ?? null),
            'last_message_at' => $lastMessageAt ?: ($existing->last_message_at ?? now()),
        ];

        if (Schema::hasColumn('sa_conversations', 'unread_for_manager')) {
            $lastSenderType = strtolower((string) $lastSenderType);
            if ($lastDirection === 'inbound') {
                $attributes['unread_for_manager'] = 1;
            } elseif ($lastDirection === 'outbound' && $lastSenderType === 'bot') {
                $attributes['unread_for_manager'] = 1;
            } elseif ($lastDirection === 'outbound') {
                $attributes['unread_for_manager'] = 0;
            } else {
                $attributes['unread_for_manager'] = $existing->unread_for_manager ?? 0;
            }
        }

        SaConversation::query()->updateOrCreate(
            ['conversation_id' => $conversationId],
            $attributes
        );
    }

    private function bindConversationToOrder(?int $orderId, string $conversationId, string $clientPhone = ''): void
    {
        if ($orderId === null || $conversationId === '') {
            return;
        }

        $pendingMessages = collect();
        if (Schema::hasTable('sa_messages')) {
            $pendingMessages = SaMessage::query()
                ->where('conversation_id', $conversationId)
                ->whereNull('orders_id')
                ->orderByRaw('COALESCE(sent_at, created_at) asc')
                ->get(['message_id', 'text', 'direction', 'sent_at']);

            SaMessage::query()
                ->where('conversation_id', $conversationId)
                ->whereNull('orders_id')
                ->update([
                    'orders_id' => $orderId,
                    'updated_at' => now(),
                ]);
        }

        if (Schema::hasTable('sa_conversations')) {
            SaConversation::query()
                ->where('conversation_id', $conversationId)
                ->update([
                    'orders_id' => $orderId,
                    'updated_at' => now(),
                ]);
        }

        if (Schema::hasTable('sa_escalations')) {
            DB::table('sa_escalations')
                ->where('conversation_id', $conversationId)
                ->whereNull('orders_id')
                ->update([
                    'orders_id' => $orderId,
                    'updated_at' => now(),
                ]);
        }

        if (Schema::hasTable('sa_bot_controls')) {
            DB::table('sa_bot_controls')
                ->where('conversation_id', $conversationId)
                ->whereNull('orders_id')
                ->update([
                    'orders_id' => $orderId,
                    'updated_at' => now(),
                ]);
        }

        foreach ($pendingMessages as $pendingMessage) {
            $this->syncClientChatMessage(
                $orderId,
                (string) ($pendingMessage->text ?? ''),
                (string) ($pendingMessage->direction ?? '') === 'outbound',
                optional($pendingMessage->sent_at)->toIso8601String() ?: '',
                (string) ($pendingMessage->message_id ?? '')
            );
        }

        $this->syncOrderIntegrationFields($orderId, $conversationId, $clientPhone, null);
    }

    private function resolveServiceToBasket(?array $serviceRequest): array
    {
        if (empty($serviceRequest) || empty($serviceRequest['service_id'])) {
            return [];
        }

        $serviceId = (string) $serviceRequest['service_id'];
        $countryCode = strtoupper((string) ($serviceRequest['country_code'] ?? $serviceRequest['country'] ?? 'LV'));
        $country = $this->resolveCountryRowByCode($countryCode);
        $countryCode = (string) ($country->country_code ?? 'LV');
        $multiplier = (float) ($country->price_country_mltpr ?? 1);
        $displayLocale = $this->resolveLocaleByCountryCode($countryCode);
        if ($multiplier <= 0) {
            $multiplier = 1.0;
        }

        $servicePath = $this->resolveServicePathByServiceId($serviceId);
        if ($servicePath === '/new/gift-card') {
            return $this->buildGiftCardBasketFromServiceRequest(
                $serviceId,
                $servicePath,
                $serviceRequest,
                $displayLocale
            );
        }

        if ($servicePath === '/new/canvas') {
            return $this->buildCanvasBasketFromServiceRequest($serviceId, $serviceRequest, $countryCode, $multiplier, $displayLocale);
        }

        $options = (array) ($serviceRequest['options'] ?? []);
        $requestedSize = strtolower(str_replace(' ', '', (string) ($options['size'] ?? ($serviceRequest['size'] ?? ''))));
        $resolvedSize = null;
        $resolvedSizePrice = null;
        if ($servicePath) {
            $sizePriceMap = $this->resolveServiceSizePriceMapByPath($servicePath);
            if (!empty($sizePriceMap) && $requestedSize !== '' && isset($sizePriceMap[$requestedSize])) {
                $resolvedSize = $requestedSize;
                $resolvedSizePrice = (float) $sizePriceMap[$requestedSize];
            }
        }

        $catalog = $this->buildCatalogFromRealSources('ru', $countryCode, $multiplier);

        foreach ($catalog['services'] as $svc) {
            if ($svc['id'] !== $serviceId) {
                continue;
            }

            $basePrice = (float) ($svc['price']['amount'] ?? 0);
            $effectivePrice = $resolvedSizePrice !== null ? $resolvedSizePrice : $basePrice;

            if ($servicePath && $this->isPortraitServicePath($servicePath)) {
                return $this->buildPortraitBasketFromServiceRequest(
                    $serviceId,
                    $servicePath,
                    $serviceRequest,
                    $effectivePrice,
                    $displayLocale,
                    $svc
                );
            }

            if ($servicePath === '/collage') {
                return $this->buildCollageBasketFromServiceRequest(
                    $serviceId,
                    $servicePath,
                    $serviceRequest,
                    $effectivePrice,
                    $displayLocale,
                    $svc
                );
            }

            if ($servicePath === '/family-constructor') {
                return $this->buildFamilyConstructorBasketFromServiceRequest(
                    $serviceId,
                    $servicePath,
                    $serviceRequest,
                    $effectivePrice,
                    $displayLocale,
                    $svc
                );
            }

            if ($servicePath === '/modular-generator') {
                return $this->buildModularBasketFromServiceRequest(
                    $serviceId,
                    $servicePath,
                    $serviceRequest,
                    $effectivePrice,
                    $displayLocale,
                    $svc
                );
            }

            if ($servicePath === '/new/gallery') {
                return $this->buildGalleryCatalogBasketFromServiceRequest(
                    $serviceId,
                    $servicePath,
                    $serviceRequest,
                    $effectivePrice,
                    $displayLocale,
                    $svc
                );
            }

            $payload = [
                'service' => [
                    'count' => 1,
                    'id' => $svc['id'],
                    'price' => $effectivePrice,
                    'sumPrice' => $effectivePrice,
                    'terms_price' => 0,
                    'name' => $svc['name'],
                    'description' => $svc['short_description'] ?? '',
                    'type' => 'sa_service',
                ],
                'totalPrice' => $effectivePrice,
            ];

            if ($servicePath) {
                $payload['service']['service_path'] = $servicePath;
            }
            if ($resolvedSize !== null) {
                $payload['service']['size'] = $resolvedSize;
                $payload['service']['size_name'] = $resolvedSize;
            }

            return $payload;
        }

        foreach ($catalog['bundles'] as $bnd) {
            if ($bnd['id'] !== $serviceId) {
                continue;
            }

            return [
                'bundle' => [
                    'count' => 1,
                    'id' => $bnd['id'],
                    'price' => $bnd['price']['amount'] ?? 0,
                    'sumPrice' => $bnd['price']['amount'] ?? 0,
                    'terms_price' => 0,
                    'name' => $bnd['name'],
                    'type' => 'sa_bundle',
                ],
                'totalPrice' => $bnd['price']['amount'] ?? 0,
            ];
        }

        return [];
    }

    private function isPortraitServicePath(?string $servicePath): bool
    {
        if (!$servicePath) {
            return false;
        }

        return in_array($servicePath, [
            '/new/caricature',
            '/new/graphic-portrait/portrait-dream-art',
            '/new/graphic-portrait/graphic-portrait',
            '/new/graphic-portrait/pop-art-portrait',
            '/new/graphic-portrait/kartiny',
            '/simpsons',
            '/new/graphic-portrait/portrait-historical',
            '/new/graphic-portrait/pet-portrait',
        ], true);
    }

    /**
     * @return array<int, string>
     */
    private function supportedLeadDeliveryMethods(): array
    {
        return [
            'to_the_door',
            'pickup_at_viar_workshop',
            'city_delivery',
            'venipak',
            'pickup_Riga',
            'pickup_Daugavplis',
            'pickup_Daugavpils',
            'email',
        ];
    }

    /**
     * Canonical payment methods used by the current checkout.
     *
     * @return array<int, string>
     */
    private function supportedLeadPaymentMethods(): array
    {
        return [
            'on_delivery',
            'online_paysera',
            'creditcart',
            'google_pay',
            'apple_pay',
            'paypalOnetimePayment',
            'transfer',
            'prepayment',
            'cash_in_office',
        ];
    }

    /**
     * Legacy aliases preserved for backward compatibility with old docs/data.
     *
     * @return array<string, string>
     */
    private function leadPaymentMethodAliases(): array
    {
        return [
            'online' => 'online_paysera',
            'online_banking' => 'online_paysera',
            'bank' => 'transfer',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function acceptedLeadPaymentMethods(): array
    {
        return array_values(array_unique(array_merge(
            $this->supportedLeadPaymentMethods(),
            array_keys($this->leadPaymentMethodAliases())
        )));
    }

    private function normalizeLeadPaymentMethod($paymentMethod): string
    {
        $paymentMethod = trim((string) $paymentMethod);
        if ($paymentMethod === '') {
            return 'transfer';
        }

        $aliases = $this->leadPaymentMethodAliases();
        if (isset($aliases[$paymentMethod])) {
            return $aliases[$paymentMethod];
        }

        if (in_array($paymentMethod, $this->supportedLeadPaymentMethods(), true)) {
            return $paymentMethod;
        }

        return 'transfer';
    }

    /**
     * @return array{size: string|null, price: float}
     */
    private function resolveRequestedSizeAndPriceForPath(
        string $servicePath,
        array $serviceRequest,
        float $fallbackPrice
    ): array {
        $options = (array) ($serviceRequest['options'] ?? []);
        $requestedSize = strtolower(str_replace(' ', '', (string) ($options['size'] ?? ($serviceRequest['size'] ?? ''))));
        $sizeMap = $this->resolveServiceSizePriceMapByPath($servicePath);

        if (!empty($sizeMap) && $requestedSize !== '' && isset($sizeMap[$requestedSize])) {
            return [
                'size' => $requestedSize,
                'price' => (float) $sizeMap[$requestedSize],
            ];
        }

        if (!empty($sizeMap)) {
            $firstSize = (string) array_key_first($sizeMap);
            if ($firstSize !== '' && isset($sizeMap[$firstSize])) {
                return [
                    'size' => $firstSize,
                    'price' => (float) $sizeMap[$firstSize],
                ];
            }
        }

        return [
            'size' => null,
            'price' => $fallbackPrice,
        ];
    }

    private function buildPortraitBasketFromServiceRequest(
        string $serviceId,
        string $servicePath,
        array $serviceRequest,
        float $fallbackPrice,
        string $displayLocale,
        array $serviceMeta
    ): array {
        $sizeInfo = $this->resolveRequestedSizeAndPriceForPath($servicePath, $serviceRequest, $fallbackPrice);
        $price = round(max(0, (float) ($sizeInfo['price'] ?? $fallbackPrice)), 2);
        if ($price <= 0) {
            $price = round(max(0, $fallbackPrice), 2);
        }

        $options = (array) ($serviceRequest['options'] ?? []);
        $packagingId = (int) ($options['packaging_id'] ?? $serviceRequest['packaging_id'] ?? 3);
        if ($packagingId <= 0) {
            $packagingId = 3;
        }
        $decorationId = (int) ($options['decoration_id'] ?? $serviceRequest['decoration_id'] ?? 5);
        if ($decorationId <= 0) {
            $decorationId = 5;
        }
        $holstId = (int) ($options['holst_id'] ?? $serviceRequest['holst_id'] ?? 5);
        if ($holstId <= 0) {
            $holstId = 5;
        }

        $usersCount = (int) ($options['users_count'] ?? $serviceRequest['users_count'] ?? 1);
        if ($usersCount <= 0) {
            $usersCount = 1;
        }
        $formaId = (int) ($options['forma_id'] ?? $serviceRequest['forma_id'] ?? 1);
        if ($formaId <= 0) {
            $formaId = 1;
        }

        $termsMode = strtolower((string) ($options['production_mode'] ?? $serviceRequest['production_mode'] ?? 'standard'));
        $termsPrice = $termsMode === 'express' ? 5.0 : 0.0;
        $termsBase = trim((string) \App\Models\GalleryItem::getTermsByPriceLocaled($termsPrice > 0 ? 1 : 0, $displayLocale));
        $terms = trim($termsBase . ' ' . rtrim(rtrim(number_format($termsPrice, 2, '.', ''), '0'), '.') . ' EUR');

        $packagingName = trim((string) $this->resolveGalleryBoxLocalizedName($packagingId, $displayLocale, 'Regular packing'));
        if ($packagingName === '') {
            $packagingName = 'Regular packing';
        }
        $decorationName = trim((string) $this->resolveGalleryDecorationLocalizedName($decorationId, $displayLocale, 'Standard. Without additions'));
        if ($decorationName === '') {
            $decorationName = 'Standard. Without additions';
        }

        $portraitType = trim((string) ($options['type'] ?? $serviceRequest['type'] ?? 'Digital'));
        if ($portraitType === '') {
            $portraitType = 'Digital';
        }

        $sizeName = (string) ($sizeInfo['size'] ?? '');
        $itemName = (string) ($serviceMeta['name'] ?? $serviceId);
        $comment = (string) ($serviceRequest['notes'] ?? '');

        return [
            'portrait_item' => [
                'count' => 1,
                'id' => $serviceId,
                'name' => $itemName,
                'price' => $price,
                'sumPrice' => $price,
                'terms_price' => $termsPrice,
                'type' => $portraitType,
                'sa_item_type' => 'sa_service',
                'description' => $servicePath,
                'service_path' => $servicePath,
                'is_port_product' => 1,
                'is_gall_with_img' => 1,
                'is_def_product' => 1,
                'basket_type' => '1',
                'forma_id' => $formaId,
                'users_count' => $usersCount,
                'holst_id' => $holstId,
                'hud_of' => $decorationName,
                'decor_id' => $decorationId,
                'compl_id' => $packagingId,
                'pack' => $packagingName,
                'terms' => $terms,
                'size_name' => $sizeName,
                'size' => $sizeName,
                'orig_images' => [],
                'photo_ex' => '',
                'userComment' => $comment,
                'total_item_price' => $price,
            ],
            'totalPrice' => $price,
        ];
    }

    private function buildGalleryCatalogBasketFromServiceRequest(
        string $serviceId,
        string $servicePath,
        array $serviceRequest,
        float $fallbackPrice,
        string $displayLocale,
        array $serviceMeta
    ): array {
        $exactGalleryItem = $this->resolveRequestedGalleryCatalogItem($serviceRequest);
        if ($exactGalleryItem) {
            return $this->buildExactGalleryCatalogBasketFromServiceRequest(
                $serviceId,
                $servicePath,
                $serviceRequest,
                $fallbackPrice,
                $displayLocale,
                $serviceMeta,
                $exactGalleryItem
            );
        }

        $sizeInfo = $this->resolveRequestedSizeAndPriceForPath($servicePath, $serviceRequest, $fallbackPrice);
        $price = round(max(0, (float) ($sizeInfo['price'] ?? $fallbackPrice)), 2);
        if ($price <= 0) {
            $price = round(max(0, $fallbackPrice), 2);
        }

        $options = (array) ($serviceRequest['options'] ?? []);
        $packagingId = (int) ($options['packaging_id'] ?? $serviceRequest['packaging_id'] ?? 3);
        if ($packagingId <= 0) {
            $packagingId = 3;
        }
        $decorationId = (int) ($options['decoration_id'] ?? $serviceRequest['decoration_id'] ?? 5);
        if ($decorationId <= 0) {
            $decorationId = 5;
        }
        $holstId = (int) ($options['holst_id'] ?? $serviceRequest['holst_id'] ?? 5);
        if ($holstId <= 0) {
            $holstId = 5;
        }

        $termsMode = strtolower((string) ($options['production_mode'] ?? $serviceRequest['production_mode'] ?? 'standard'));
        $termsPrice = $termsMode === 'express' ? 5.0 : 0.0;
        $termsBase = trim((string) \App\Models\GalleryItem::getTermsByPriceLocaled($termsPrice > 0 ? 1 : 0, $displayLocale));
        $terms = trim($termsBase . ' ' . rtrim(rtrim(number_format($termsPrice, 2, '.', ''), '0'), '.') . ' EUR');

        $packagingName = trim((string) $this->resolveGalleryBoxLocalizedName($packagingId, $displayLocale, 'Regular packing'));
        if ($packagingName === '') {
            $packagingName = 'Regular packing';
        }
        $decorationName = trim((string) $this->resolveGalleryDecorationLocalizedName($decorationId, $displayLocale, 'Standard. Without additions'));
        if ($decorationName === '') {
            $decorationName = 'Standard. Without additions';
        }

        $sizeName = (string) ($sizeInfo['size'] ?? '');
        $itemName = (string) ($serviceMeta['name'] ?? $serviceId);
        $comment = (string) ($serviceRequest['notes'] ?? '');

        return [
            'gallery_item' => [
                'count' => 1,
                'id' => $serviceId,
                'name' => $itemName,
                'price' => $price,
                'sumPrice' => $price,
                'terms_price' => $termsPrice,
                'type' => 'sa_service',
                'description' => $servicePath,
                'service_path' => $servicePath,
                'basketType' => '1',
                'is_def_product' => 1,
                'is_construct' => 1,
                'size' => $sizeName,
                'size_name' => $sizeName,
                'formId' => (int) ($options['form_id'] ?? $serviceRequest['form_id'] ?? 1),
                'holst_id' => $holstId,
                'hud_of' => $decorationName,
                'pack' => $packagingName,
                'boxIds' => [$packagingId],
                'terms' => $terms,
                'orig_images' => [],
                'photo_ex' => '',
                'userComment' => $comment,
                'show' => [
                    'size' => $sizeName,
                    'box' => [$packagingName],
                    'decoration' => $decorationName,
                ],
                'total_item_price' => $price,
            ],
            'totalPrice' => $price,
        ];
    }

    private function buildExactGalleryCatalogBasketFromServiceRequest(
        string $serviceId,
        string $servicePath,
        array $serviceRequest,
        float $fallbackPrice,
        string $displayLocale,
        array $serviceMeta,
        object $galleryItem
    ): array {
        $options = (array) ($serviceRequest['options'] ?? []);
        $sizeMap = $this->resolveSizeMapByGalleryItemRow($galleryItem);
        $requestedSize = strtolower(str_replace(' ', '', (string) ($options['size'] ?? $serviceRequest['size'] ?? '')));
        $resolvedSize = $requestedSize !== '' && isset($sizeMap[$requestedSize])
            ? $requestedSize
            : (string) array_key_first($sizeMap);
        $basePrice = ($resolvedSize !== '' && isset($sizeMap[$resolvedSize]))
            ? (float) $sizeMap[$resolvedSize]
            : round(max(0, $fallbackPrice), 2);
        $basePrice = round(max(0, $basePrice), 2);

        $canvasType = $this->resolveCanvasTypeOption($options, $serviceRequest);
        $canvasCoef = (float) ($canvasType['price'] ?? 1.0);
        if ($canvasCoef <= 0) {
            $canvasCoef = 1.0;
        }
        $priceWithCanvas = round($basePrice * $canvasCoef, 2);

        $area = $this->resolveAreaSquareMetersBySize($resolvedSize);
        $decoration = $this->resolveCanvasDecorationOption($options, $serviceRequest);
        $decorationPrice = 0.0;
        if ($area > 0) {
            if ($area <= 0.4) {
                $decorationPrice = $area * (float) ($decoration['coef_sm'] ?? 0);
            } elseif ($area <= 1) {
                $decorationPrice = $area * (float) ($decoration['coef_md'] ?? 0);
            } else {
                $decorationPrice = $area * (float) ($decoration['coef_lg'] ?? 0);
            }
        }
        if ($decorationPrice <= 0) {
            $decorationPrice = (float) ($decoration['price'] ?? 0);
        }
        $decorationPrice = round(max(0, $decorationPrice), 2);

        $packaging = $this->resolveCanvasPackagingOption($options, $serviceRequest);
        $packagingId = (int) ($packaging['id'] ?? 3);
        $packagingPrice = round(max(0, (float) ($packaging['price'] ?? 0)), 2);

        $ram = $this->resolveCanvasRamOption($options, $serviceRequest);
        $ramId = (int) ($ram['id'] ?? 0);
        $ramPrice = 0.0;
        if ($ramId > 0 && preg_match('/^(\d+)x(\d+)$/', $resolvedSize, $matches)) {
            $sizeW = (int) $matches[1];
            $sizeH = (int) $matches[2];
            $ramPerimeterMeters = ($sizeW + $sizeW + $sizeH + $sizeH) / 100;
            $ramPrice = round(max(0, $ramPerimeterMeters * (float) ($ram['price'] ?? 0)), 2);
        }

        $execution = $this->resolveGalleryExecutionOption($options, $serviceRequest, $displayLocale);
        $executionId = (int) ($execution['id'] ?? 2);
        $executionPrice = $this->resolveGalleryExecutionPrice($area, $executionId);

        $terms = $this->resolveCanvasTermsOption($options, $displayLocale);
        $termsPrice = round(max(0, (float) ($terms['price'] ?? 0)), 2);

        $price = round($priceWithCanvas + $decorationPrice + $packagingPrice + $executionPrice + $ramPrice, 2);
        $totalItemPrice = round($price + $termsPrice, 2);

        $packagingName = trim((string) $this->resolveGalleryBoxLocalizedName($packagingId, $displayLocale, (string) ($packaging['name'] ?? 'Regular packing')));
        if ($packagingName === '') {
            $packagingName = 'Regular packing';
        }

        $decorationId = (int) ($decoration['id'] ?? 5);
        $decorationName = trim((string) $this->resolveGalleryDecorationLocalizedName($decorationId, $displayLocale, (string) ($decoration['name'] ?? 'Standard. Without additions')));
        if ($decorationName === '') {
            $decorationName = 'Standard. Without additions';
        }

        $canvasDisplayName = trim((string) $this->resolveGalleryHolstLocalizedField((int) ($canvasType['id'] ?? 0), 'name', $displayLocale, (string) ($canvasType['name'] ?? '')));
        if ($canvasDisplayName === '') {
            $canvasDisplayName = 'Interior';
        }

        $formId = (int) ($options['form_id'] ?? $options['formId'] ?? $serviceRequest['form_id'] ?? $serviceRequest['formId'] ?? 1);
        if ($formId <= 0) {
            $formId = 1;
        }

        $usersCount = (int) ($options['users_count'] ?? $serviceRequest['users_count'] ?? 1);
        if ($usersCount <= 0) {
            $usersCount = 1;
        }

        $itemName = trim((string) ($galleryItem->name ?? ''));
        if ($itemName === '') {
            $itemName = trim((string) ($serviceMeta['name'] ?? $serviceId));
        }

        $comment = (string) ($serviceRequest['notes'] ?? '');

        return [
            'gallery_item' => [
                'count' => 1,
                'id' => $serviceId,
                'pid' => (int) $galleryItem->id,
                'gallery_item_id' => (int) $galleryItem->id,
                'name' => $itemName,
                'price' => $price,
                'sumPrice' => $price,
                'terms_price' => $termsPrice,
                'type' => (string) ($execution['name'] ?? 'Print'),
                'sa_item_type' => 'sa_service',
                'description' => $servicePath,
                'service_path' => $servicePath,
                'basketType' => '1',
                'is_port_product' => 1,
                'is_def_product' => 1,
                'is_construct' => 1,
                'is_gall_with_img' => 0,
                'size' => $resolvedSize,
                'size_name' => $resolvedSize,
                'forma_id' => $formId,
                'formId' => $formId,
                'users_count' => $usersCount,
                'holst_id' => (int) ($canvasType['id'] ?? 0),
                'hud_of' => $decorationName,
                'decor_id' => $decorationId,
                'ram_id' => $ramId,
                'compl_id' => $packagingId,
                'pack' => $packagingName,
                'boxIds' => $packagingId > 0 ? [$packagingId] : [],
                'terms' => (string) ($terms['text'] ?? ''),
                'orig_images' => [],
                'photo_ex' => '',
                'userComment' => $comment,
                'show' => [
                    'size' => $resolvedSize,
                    'box' => $packagingName !== '' ? [$packagingName] : [],
                    'decoration' => $decorationName,
                    'canvas' => $canvasDisplayName,
                ],
                'total_item_price' => $totalItemPrice,
            ],
            'totalPrice' => $price,
        ];
    }

    private function buildGiftCardBasketFromServiceRequest(
        string $serviceId,
        string $servicePath,
        array $serviceRequest,
        string $displayLocale
    ): array {
        $resolvedNominal = $this->resolveRequestedGiftCardNominal($serviceRequest);
        $nominalKey = (string) ($resolvedNominal['key'] ?? '');
        $amount = (float) ($resolvedNominal['amount'] ?? 0);
        if ($nominalKey === '' || $amount <= 0) {
            return [];
        }

        $options = (array) ($serviceRequest['options'] ?? []);
        $cardType = strtolower(trim((string) ($options['card_type'] ?? $serviceRequest['card_type'] ?? 'online')));
        if (!in_array($cardType, ['online', 'offline'], true)) {
            $cardType = 'online';
        }

        $whom = trim((string) ($options['whom'] ?? $serviceRequest['whom'] ?? ''));
        if ($whom === '') {
            $whom = $this->resolveGiftCardWhomLabel($cardType, $displayLocale);
        }

        $hideNom = filter_var(
            $options['hide_nom'] ?? $serviceRequest['hide_nom'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );
        $sender = trim((string) ($options['sender'] ?? $serviceRequest['sender'] ?? data_get($serviceRequest, 'fields.sender', '')));
        $receiver = trim((string) ($options['reseiver'] ?? $options['receiver'] ?? $options['res'] ?? $serviceRequest['reseiver'] ?? $serviceRequest['receiver'] ?? $serviceRequest['res'] ?? data_get($serviceRequest, 'fields.reseiver', data_get($serviceRequest, 'fields.receiver', ''))));
        $giftDate = trim((string) ($options['date'] ?? $serviceRequest['date'] ?? data_get($serviceRequest, 'fields.date', '')));
        $occasionTitle = trim((string) ($options['torjname'] ?? $serviceRequest['torjname'] ?? data_get($serviceRequest, 'fields.torjname', '')));
        $occasionText = trim((string) ($options['torjtext'] ?? $serviceRequest['torjtext'] ?? data_get($serviceRequest, 'fields.torjtext', (string) ($serviceRequest['notes'] ?? ''))));
        $itemName = trim((string) trans('gift_card.gift_cart', [], $displayLocale));
        if ($itemName === '') {
            $itemName = trim((string) trans('pages.gift_card', [], $displayLocale));
        }
        if ($itemName === '') {
            $itemName = 'Gift card';
        }

        return [
            'gift_card_item' => [
                'count' => 1,
                'id' => $serviceId,
                'pid' => 5,
                'basketType' => '5',
                'name' => $itemName,
                'type' => 'sa_service',
                'sa_item_type' => 'sa_service',
                'service_path' => $servicePath,
                'service_request_id' => (string) ($serviceRequest['request_id'] ?? ''),
                'is_gift_card' => 1,
                'price' => $amount,
                'sumPrice' => $amount,
                'total_item_price' => $amount,
                'terms' => 0,
                'terms_price' => 0,
                'nominal' => $nominalKey,
                'size' => $nominalKey,
                'size_name' => $nominalKey,
                'whom' => $whom,
                'card_type' => $cardType,
                'hide_nom' => $hideNom ? 'true' : 'false',
                'sender' => $sender,
                'reseiver' => $receiver,
                'date' => $giftDate,
                'torjname' => $occasionTitle,
                'torjtext' => $occasionText,
                'userComment' => (string) ($serviceRequest['notes'] ?? ''),
            ],
            'totalPrice' => $amount,
        ];
    }

    private function buildCollageBasketFromServiceRequest(
        string $serviceId,
        string $servicePath,
        array $serviceRequest,
        float $fallbackPrice,
        string $displayLocale,
        array $serviceMeta
    ): array {
        $sizeInfo = $this->resolveRequestedSizeAndPriceForPath($servicePath, $serviceRequest, $fallbackPrice);
        $basePrice = round(max(0, (float) ($sizeInfo['price'] ?? $fallbackPrice)), 2);
        if ($basePrice <= 0) {
            $basePrice = round(max(0, $fallbackPrice), 2);
        }

        $options = (array) ($serviceRequest['options'] ?? []);
        $packaging = $this->resolveCanvasPackagingOption($options, $serviceRequest);
        $decoration = $this->resolveCanvasDecorationOption($options, $serviceRequest);
        $holst = $this->resolveGalleryHolstOption($options, $serviceRequest);
        $ram = $this->resolveCanvasRamOption($options, $serviceRequest);
        $terms = $this->resolveCollageTermsOption($options, $displayLocale);

        $packagingId = (int) ($packaging['id'] ?? 3);
        $packagingPrice = round(max(0, (float) ($packaging['price'] ?? 0)), 2);
        $decorationId = (int) ($decoration['id'] ?? 5);
        $holstId = (int) ($holst['id'] ?? 0);
        $ramId = (int) ($ram['id'] ?? 0);
        $termsPrice = round(max(0, (float) ($terms['price'] ?? 0)), 2);

        $sizeName = (string) ($sizeInfo['size'] ?? '');
        $ramPrice = 0.0;
        if ($sizeName !== '' && preg_match('/^(\d+)x(\d+)$/', $sizeName, $matches)) {
            $sizeW = (int) $matches[1];
            $sizeH = (int) $matches[2];
            $ramPerimeterMeters = ($sizeW + $sizeW + $sizeH + $sizeH) / 100;
            $ramPrice = round(max(0, $ramPerimeterMeters * (float) ($ram['price'] ?? 0)), 2);
        }

        $price = round($basePrice + $packagingPrice + $ramPrice, 2);
        $totalItemPrice = round($price + $termsPrice, 2);

        $packagingName = trim((string) $this->resolveGalleryBoxLocalizedName($packagingId, $displayLocale, (string) ($packaging['name'] ?? 'Regular packing')));
        if ($packagingName === '') {
            $packagingName = 'Regular packing';
        }
        $decorationName = trim((string) $this->resolveGalleryDecorationLocalizedName($decorationId, $displayLocale, (string) ($decoration['name'] ?? 'Standard. Without additions')));
        if ($decorationName === '') {
            $decorationName = 'Standard. Without additions';
        }
        $canvasDisplayName = trim((string) $this->resolveGalleryHolstLocalizedField($holstId, 'name', $displayLocale, (string) ($holst['name'] ?? '')));

        $comment = (string) ($serviceRequest['notes'] ?? '');
        $formId = (int) ($options['form_id'] ?? $options['formId'] ?? $serviceRequest['form_id'] ?? $serviceRequest['formId'] ?? 1);
        if ($formId <= 0) {
            $formId = 1;
        }

        $itemType = trim((string) ($options['type'] ?? $serviceRequest['type'] ?? 'undefined'));
        if ($itemType === '') {
            $itemType = 'undefined';
        }

        return [
            'collage_item' => [
                'count' => 1,
                'id' => $serviceId,
                'pid' => 2,
                'name' => 'Collage',
                'price' => $price,
                'sumPrice' => $price,
                'terms_price' => $termsPrice,
                'type' => $itemType,
                'sa_item_type' => 'sa_service',
                'description' => $servicePath,
                'service_path' => $servicePath,
                'basketType' => '1',
                'is_port_product' => 1,
                'is_def_product' => 1,
                'is_construct' => 1,
                'size' => $sizeName,
                'sizeId' => $sizeName,
                'size_name' => $sizeName,
                'formId' => $formId,
                'holst_id' => $holstId,
                'decor_id' => $decorationId,
                'compl_id' => $packagingId,
                'ram_id' => $ramId,
                'hud_of' => $decorationName,
                'pack' => $packagingName,
                'boxIds' => [$packagingId],
                'wall_size_mod' => (string) ($options['wall_size_mod'] ?? $serviceRequest['wall_size_mod'] ?? ''),
                'orig_images' => [],
                'photo_ex' => '',
                'userComment' => $comment,
                'terms' => (string) ($terms['text'] ?? 'Standard - 3 working days 0 EUR'),
                'show' => [
                    'size' => $sizeName,
                    'box' => [$packagingName],
                    'decoration' => $decorationName,
                    'canvas' => $canvasDisplayName,
                ],
                'total_item_price' => $totalItemPrice,
            ],
            'totalPrice' => $price,
        ];
    }

    private function buildFamilyConstructorBasketFromServiceRequest(
        string $serviceId,
        string $servicePath,
        array $serviceRequest,
        float $fallbackPrice,
        string $displayLocale,
        array $serviceMeta
    ): array {
        $sizeInfo = $this->resolveRequestedSizeAndPriceForPath($servicePath, $serviceRequest, $fallbackPrice);
        $basePrice = round(max(0, (float) ($sizeInfo['price'] ?? $fallbackPrice)), 2);
        if ($basePrice <= 0) {
            $basePrice = round(max(0, $fallbackPrice), 2);
        }

        $options = (array) ($serviceRequest['options'] ?? []);
        $canvasType = $this->resolveCanvasTypeOption($options, $serviceRequest);
        $packaging = $this->resolveCanvasPackagingOption($options, $serviceRequest);
        $terms = $this->resolveCanvasTermsOption($options, $displayLocale);

        $requestedRamId = (int) ($options['ram_id'] ?? $serviceRequest['ram_id'] ?? 0);
        $ram = $requestedRamId > 0
            ? $this->resolveCanvasRamOption(['ram_id' => $requestedRamId], ['ram_id' => $requestedRamId])
            : ['id' => 0, 'price' => 0.0];

        $canvasCoef = (float) ($canvasType['price'] ?? 1.0);
        if ($canvasCoef <= 0) {
            $canvasCoef = 1.0;
        }

        $sizeName = (string) ($sizeInfo['size'] ?? '');
        $priceWithCanvas = round($basePrice * $canvasCoef, 2);
        $packagingId = (int) ($packaging['id'] ?? 3);
        $packagingPrice = round(max(0, (float) ($packaging['price'] ?? 0)), 2);
        $termsPrice = round(max(0, (float) ($terms['price'] ?? 0)), 2);
        $ramId = (int) ($ram['id'] ?? 0);
        $ramPrice = 0.0;

        if ($ramId > 0 && $sizeName !== '' && preg_match('/^(\d+)x(\d+)$/', $sizeName, $matches)) {
            $sizeW = (int) $matches[1];
            $sizeH = (int) $matches[2];
            $ramPerimeterMeters = ($sizeW + $sizeW + $sizeH + $sizeH) / 100;
            $ramPrice = round(max(0, $ramPerimeterMeters * (float) ($ram['price'] ?? 0)), 2);
        }

        $price = round($priceWithCanvas + $packagingPrice + $ramPrice, 2);
        $totalItemPrice = round($price + $termsPrice, 2);

        $comment = (string) ($serviceRequest['notes'] ?? '');
        $serviceName = trim((string) ($serviceMeta['name'] ?? 'Family constructor'));
        if ($serviceName === '') {
            $serviceName = 'Family constructor';
        }

        $canvasId = (int) ($canvasType['id'] ?? 0);
        $canvasDisplayName = trim((string) $this->resolveGalleryHolstLocalizedField($canvasId, 'name', $displayLocale, (string) ($canvasType['name'] ?? '')));
        $packagingDisplayName = trim((string) $this->resolveGalleryBoxLocalizedName($packagingId, $displayLocale, (string) ($packaging['name'] ?? 'Regular packing')));

        return [
            'family_constructor_item' => [
                'count' => 1,
                'id' => $serviceId,
                'name' => $serviceName,
                'price' => $price,
                'sumPrice' => $price,
                'terms_price' => $termsPrice,
                'type' => 'sa_service',
                'sa_item_type' => 'sa_service',
                'description' => $servicePath,
                'service_path' => $servicePath,
                'basketType' => '1',
                'is_port_product' => 1,
                'is_def_product' => 1,
                'is_construct' => 1,
                'size' => $sizeName,
                'sizeId' => $sizeName,
                'size_name' => $sizeName,
                'holst_id' => $canvasId,
                'pack' => $packagingDisplayName,
                'boxIds' => $packagingId > 0 ? [$packagingId] : [],
                'terms' => (string) ($terms['text'] ?? ''),
                'photo_ex' => '',
                'orig_images' => [],
                'userComment' => $comment,
                'total_item_price' => $totalItemPrice,
                'show' => [
                    'size' => $sizeName,
                    'box' => $packagingDisplayName !== '' ? [$packagingDisplayName] : [],
                    'canvas' => $canvasDisplayName,
                ],
            ],
            'totalPrice' => $price,
            'total_terms_price' => $termsPrice,
        ];
    }

    private function buildModularBasketFromServiceRequest(
        string $serviceId,
        string $servicePath,
        array $serviceRequest,
        float $fallbackPrice,
        string $displayLocale,
        array $serviceMeta
    ): array {
        $sizeInfo = $this->resolveRequestedSizeAndPriceForPath($servicePath, $serviceRequest, $fallbackPrice);
        $basePrice = round(max(0, (float) ($sizeInfo['price'] ?? $fallbackPrice)), 2);
        if ($basePrice <= 0) {
            $basePrice = round(max(0, $fallbackPrice), 2);
        }

        $options = (array) ($serviceRequest['options'] ?? []);
        $modularDecoration = $this->resolveModularDecorationOption($options, $serviceRequest);
        $defaultDecoration = $this->resolveModularDecorationOption([], []);
        $packaging = $this->resolveCanvasPackagingOption($options, $serviceRequest);
        $defaultPackaging = $this->resolveCanvasPackagingOption([], []);
        $terms = $this->resolveCanvasTermsOption($options, $displayLocale);

        $requestedSize = strtolower(str_replace(' ', '', (string) ($options['size'] ?? $serviceRequest['size'] ?? '')));
        $sizeName = $requestedSize !== ''
            ? $requestedSize
            : (string) ($sizeInfo['size'] ?? '');
        if ($sizeName === '') {
            $sizeName = '90x90';
        }

        $wholeArea = $this->resolveAreaSquareMetersBySize($sizeName);
        $layoutArea = $this->resolveModularLayoutAreaSquareMeters($options, $serviceRequest);
        $termsPriceBase = round(max(0, (float) ($terms['price'] ?? 0)), 2);

        $packagingId = (int) ($packaging['id'] ?? 3);
        $decorationId = (int) ($modularDecoration['id'] ?? 5);
        $executionId = (int) ($options['execution_id'] ?? $options['executionId'] ?? $serviceRequest['execution_id'] ?? $serviceRequest['executionId'] ?? 1);
        if ($executionId <= 0) {
            $executionId = 1;
        }
        $formId = (int) ($options['form_id'] ?? $options['formId'] ?? $serviceRequest['form_id'] ?? $serviceRequest['formId'] ?? 1);
        if ($formId <= 0) {
            $formId = 1;
        }

        $countryCode = strtoupper((string) ($serviceRequest['country_code'] ?? $serviceRequest['country'] ?? 'LV'));
        $country = $this->resolveCountryRowByCode($countryCode);
        $countryMultiplier = (float) ($country->price_country_mltpr ?? 1.0);
        if ($countryMultiplier <= 0) {
            $countryMultiplier = 1.0;
        }

        $priceMode = 'fallback';
        $price = 0.0;
        $termsPrice = 0.0;
        $totalItemPrice = 0.0;

        if ($layoutArea !== null) {
            $executionPrice = $this->resolveModularExecutionPrice($layoutArea, $executionId);
            $decorationPrice = round(max(0, (float) ($modularDecoration['price'] ?? 0)), 2);
            $decorationAreaPrice = $this->resolveDecorationAreaPrice($modularDecoration, $wholeArea);
            $packagingPrice = round(max(0, (float) ($packaging['price'] ?? 0)), 2);

            $baseBeforeMultiplier = round($decorationPrice + $executionPrice + $decorationAreaPrice + $packagingPrice, 2);
            $price = round($baseBeforeMultiplier * $countryMultiplier, 2);
            $termsPrice = round($termsPriceBase * $countryMultiplier, 2);
            $totalItemPrice = round($price + $termsPrice, 2);
            $priceMode = $this->hasModularLayoutSvg($options, $serviceRequest) ? 'layout_svg' : 'layout_blocks';
        } else {
            $decorationPriceDelta = round(max(0, (float) ($modularDecoration['price'] ?? 0) - (float) ($defaultDecoration['price'] ?? 0)), 2);
            $decorationCoefDelta = round(max(0, $this->resolveDecorationAreaPrice($modularDecoration, $wholeArea) - $this->resolveDecorationAreaPrice($defaultDecoration, $wholeArea)), 2);
            $packagingPriceDelta = round(max(0, (float) ($packaging['price'] ?? 0) - (float) ($defaultPackaging['price'] ?? 0)), 2);

            $price = round($basePrice + $decorationPriceDelta + $decorationCoefDelta + $packagingPriceDelta, 2);
            $termsPrice = $termsPriceBase;
            $totalItemPrice = round($price + $termsPrice, 2);
        }

        $packagingName = trim((string) $this->resolveGalleryBoxLocalizedName($packagingId, $displayLocale, (string) ($packaging['name'] ?? 'Regular packing')));
        if ($packagingName === '') {
            $packagingName = 'Regular packing';
        }
        $decorationName = trim((string) $this->resolveGalleryDecorationLocalizedName($decorationId, $displayLocale, (string) ($modularDecoration['name'] ?? 'Standard. Without additions')));
        if ($decorationName === '') {
            $decorationName = 'Standard. Without additions';
        }

        $giftCode = 'G0';
        if ($packagingId === 1) {
            $giftCode = 'G2';
        } elseif ($packagingId === 2) {
            $giftCode = 'G1';
        }

        $manualLacCode = 'L0';
        $manualBrushCode = 'P0';
        if ($decorationId === 1) {
            $manualLacCode = 'L2';
        } elseif ($decorationId === 2) {
            $manualBrushCode = 'P1';
        } elseif ($decorationId === 3) {
            $manualLacCode = 'L1';
        }

        $comment = (string) ($serviceRequest['notes'] ?? '');
        $wallSize = trim((string) ($options['wall_size_mod'] ?? $serviceRequest['wall_size_mod'] ?? ''));
        $itemType = trim((string) ($options['type'] ?? $serviceRequest['type'] ?? 'sa_service'));
        if ($itemType === '') {
            $itemType = 'sa_service';
        }

        return [
            'modular_item' => [
                'count' => 1,
                'id' => $serviceId,
                'name' => 'Modular pictures',
                'price' => $price,
                'sumPrice' => $price,
                'terms_price' => $termsPrice,
                'type' => $itemType,
                'sa_item_type' => 'sa_service',
                'description' => $servicePath,
                'service_path' => $servicePath,
                'basketType' => '1',
                'is_port_product' => 1,
                'is_def_product' => 1,
                'is_construct' => 1,
                'size' => $sizeName,
                'size_name' => $sizeName,
                'sizeId' => $sizeName,
                'formId' => $formId,
                // Legacy-compatible key used by current modular page for the selected finish option.
                'holst_id' => $decorationId,
                'hud_of' => $decorationName,
                'pack' => $packagingName,
                'boxIds' => [$packagingId],
                'executionId' => $executionId,
                'wall_size_mod' => $wallSize,
                'orig_images' => [],
                'photo_ex' => '',
                'userComment' => $comment,
                'terms' => (string) ($terms['text'] ?? 'Standard - 3 working days 0 EUR'),
                'manual_canvas_id' => 2,
                'manual_gift_code' => $giftCode,
                'manual_decoration_id' => $decorationId,
                'manual_lac_code' => $manualLacCode,
                'manual_brushstrokes_code' => $manualBrushCode,
                'manual_orientation_code' => 'V0',
                'sa_modular_price_mode' => $priceMode,
                'sa_modular_layout_area' => $layoutArea,
                'show' => [
                    'size' => $sizeName,
                    'box' => [$packagingName],
                    'decoration' => $decorationName,
                    'canvas' => 'Interior',
                ],
                'total_item_price' => $totalItemPrice,
            ],
            'totalPrice' => $totalItemPrice,
        ];
    }

    private function buildCheckoutParamsFromLead(array $context): array
    {
        $serviceRequest = (array) data_get($context, 'service_request', []);
        $recipient = (array) data_get($context, 'recipient', []);
        $delivery = (array) data_get($context, 'delivery', []);
        $billingCompany = (array) data_get($context, 'billing.company', []);
        $serviceId = (string) ($serviceRequest['service_id'] ?? '');
        $servicePath = $this->resolveServicePathByServiceId($serviceId);
        $countryCode = strtoupper((string) ($serviceRequest['country_code'] ?? $serviceRequest['country'] ?? data_get($context, 'fields.country_code', 'LV')));
        if ($countryCode === '') {
            $countryCode = strtoupper((string) ($delivery['country'] ?? 'LV'));
        }
        $country = $this->resolveCountryRowByCode($countryCode);
        $resolvedCountryCode = strtoupper((string) ($country->country_code ?? ($countryCode !== '' ? $countryCode : 'LV')));
        $deliveryPrice = isset($delivery['deliv_price']) && is_numeric($delivery['deliv_price'])
            ? (float) $delivery['deliv_price']
            : ($country ? (float) (($country->delivery_venipak ?? null) ?? ($country->deliv_price ?? 0)) : 0.0);
        if ($deliveryPrice < 0) {
            $deliveryPrice = 0.0;
        }

        $deliveryMethod = (string) ($delivery['method'] ?? $delivery['sposob'] ?? 'to_the_door');
        if (!in_array($deliveryMethod, $this->supportedLeadDeliveryMethods(), true)) {
            $deliveryMethod = 'to_the_door';
        }

        $paymentMethod = $this->normalizeLeadPaymentMethod($delivery['payment'] ?? 'transfer');

        $giftCardCardType = strtolower(trim((string) data_get($serviceRequest, 'options.card_type', $serviceRequest['card_type'] ?? '')));
        if ($servicePath === '/new/gift-card' && $giftCardCardType === 'online') {
            $deliveryMethod = 'email';
            $deliveryPrice = 0.0;
        }

        $city = (string) ($delivery['city'] ?? data_get($context, 'fields.city', 'N/A'));
        $address = (string) ($delivery['address'] ?? data_get($context, 'fields.address', ($city !== '' ? $city : 'N/A')));
        $postalIndex = (string) ($delivery['postal_index'] ?? data_get($context, 'fields.postal_index', '00000'));
        $recipientAddress = trim((string) ($recipient['address'] ?? ''));
        if ($recipientAddress === '') {
            $recipientAddress = $address !== '' ? $address : 'N/A';
        }
        $recipientPostalIndex = trim((string) ($recipient['postal_index'] ?? ''));
        if ($recipientPostalIndex === '') {
            $recipientPostalIndex = $postalIndex !== '' ? $postalIndex : '00000';
        }
        $recipientName = trim((string) ($recipient['name'] ?? ''));
        if ($recipientName === '') {
            $recipientName = (string) (data_get($context, 'client.name') ?: 'Integration');
        }
        $recipientLastName = trim((string) ($recipient['last_name'] ?? ''));
        if ($recipientLastName === '') {
            $recipientLastName = 'SA';
        }
        $recipientPhone = trim((string) ($recipient['phone'] ?? ''));
        if ($recipientPhone === '') {
            $recipientPhone = (string) (data_get($context, 'client.phone') ?: '');
        }

        $payload = [
            'name' => data_get($context, 'client.name') ?: 'Integration',
            'last_name' => 'SA',
            'phone' => data_get($context, 'client.phone') ?: '+0000000000',
            'email' => data_get($context, 'fields.email') ?: 'integration_' . time() . '@example.com',
            'address' => $address !== '' ? $address : 'N/A',
            'postal_index' => $postalIndex !== '' ? $postalIndex : '00000',
            'country' => $resolvedCountryCode,
            'city' => $city !== '' ? $city : 'N/A',
            'delivery' => $deliveryMethod,
            'payment' => $paymentMethod,
            'comment' => data_get($context, 'service_request.notes') ?: 'Created by SA Integration',
            'deliv_price' => $deliveryPrice,
            'when_send' => data_get($context, 'service_request.preferred_time') ?: '',
            // Keep recipient keys present for Orders::saveOrder().
            'phone_rec' => $recipientPhone,
            'name_rec' => $recipientName,
            'last_name_rec' => $recipientLastName,
            'address_rec' => $recipientAddress,
            'postal_index_rec' => $recipientPostalIndex,
        ];

        $companyName = trim((string) ($billingCompany['name'] ?? ''));
        $companyRegistrationNumber = trim((string) ($billingCompany['registration_number'] ?? ''));
        $companyNameL = trim((string) ($billingCompany['name_l'] ?? ''));
        $companyLegalAddress = trim((string) ($billingCompany['legal_address'] ?? ''));
        $companyPnrNr = trim((string) ($billingCompany['pnr_nr'] ?? ''));
        $companyBankName = trim((string) ($billingCompany['bank_name'] ?? ''));
        $companyBankCode = trim((string) ($billingCompany['bank_code'] ?? ''));
        $companyBankAccountCode = trim((string) ($billingCompany['bank_account_code'] ?? ''));

        if (
            $companyName !== '' ||
            $companyRegistrationNumber !== '' ||
            $companyNameL !== '' ||
            $companyLegalAddress !== '' ||
            $companyPnrNr !== '' ||
            $companyBankName !== '' ||
            $companyBankCode !== '' ||
            $companyBankAccountCode !== ''
        ) {
            $payload['ur_name'] = $companyName;
            $payload['ur_reg_num'] = $companyRegistrationNumber;
            $payload['ur_name_l'] = $companyNameL;
            $payload['ur_legal_addr'] = $companyLegalAddress;
            $payload['ur_pnr_nr'] = $companyPnrNr;
            $payload['ur_bank_name'] = $companyBankName;
            $payload['ur_bank_code'] = $companyBankCode;
            $payload['ur_bank_acc_code'] = $companyBankAccountCode;
        }

        $pickupWorkshopId = (int) ($delivery['pickup_workshop_id'] ?? 0);
        if ($pickupWorkshopId > 0) {
            $payload['pickup_workshop_id'] = $pickupWorkshopId;
        }

        $deliveryTownId = (int) ($delivery['delivery_town_id'] ?? 0);
        if ($deliveryTownId > 0) {
            $payload['delivery_town_id'] = $deliveryTownId;
        }

        $deliveryPhotoShortCode = trim((string) ($delivery['delivery_photo_short_code'] ?? ''));
        if ($deliveryPhotoShortCode !== '') {
            $payload['delivery_photo_short_code'] = $deliveryPhotoShortCode;
        }

        return $payload;
    }

    private function resolveLeadUserFromContext(array $context): ?\App\Models\User
    {
        $email = (string) data_get($context, 'client.email', data_get($context, 'fields.email', data_get($context, 'emails.0', '')));
        $phone = (string) data_get($context, 'client.phone', '');
        $name = (string) data_get($context, 'client.name', '');

        $resolvedUser = null;
        if ($email !== '') {
            $resolvedUser = \App\Models\User::where('email', $email)->first();
        }

        if (!$resolvedUser && $phone !== '') {
            $resolvedUser = \App\Models\User::where('phone', $phone)->first();
        }

        if (!$resolvedUser) {
            $resolvedUser = $this->createSaLeadUser($email, $phone, $name);
        }

        return $resolvedUser;
    }

    private function saveOrderAsUser(\App\Models\User $user, array $basket, array $checkoutParams): ?int
    {
        $guard = Auth::guard('web');
        $previousUser = $guard->user();
        $previousDriver = Auth::getDefaultDriver();

        try {
            Auth::shouldUse('web');
            $guard->setUser($user);

            $newOrderId = (new Orders())->saveOrder($basket, $checkoutParams);
            if (!empty($newOrderId) && is_numeric($newOrderId)) {
                return (int) $newOrderId;
            }
        } finally {
            if ($previousUser) {
                $guard->setUser($previousUser);
            } else {
                $guard->logout();
            }
            Auth::shouldUse($previousDriver);
        }

        return null;
    }

    private function resolveCountryRowByCode(string $countryCode): ?object
    {
        $normalized = strtoupper(trim($countryCode));

        if (Schema::hasTable('country_tels') && $normalized !== '') {
            $row = DB::table('country_tels')
                ->whereRaw('UPPER(country_code) = ?', [$normalized])
                ->first();
            if ($row) {
                return $row;
            }
        }

        if (Schema::hasTable('country_tels')) {
            $fallback = DB::table('country_tels')
                ->whereRaw('UPPER(country_code) = ?', ['LV'])
                ->first();
            if ($fallback) {
                return $fallback;
            }

            return DB::table('country_tels')->orderBy('sort')->orderBy('id')->first();
        }

        return null;
    }

    private function resolveServicePathByServiceId(string $serviceId): ?string
    {
        $menuItem = $this->resolveHeaderMenuItemByServiceId($serviceId);
        if (!$menuItem) {
            return null;
        }

        return $this->normalizeMenuPath((string) $menuItem->link);
    }

    private function buildCanvasBasketFromServiceRequest(
        string $serviceId,
        array $serviceRequest,
        string $countryCode,
        float $countryMultiplier,
        string $displayLocale = 'ru'
    ): array {
        $sizes = $this->resolveCanvasSizePriceMapWithLabels($countryMultiplier);
        if (empty($sizes)) {
            return [];
        }

        $options = (array) ($serviceRequest['options'] ?? []);
        $requestedSize = strtolower(str_replace(' ', '', (string) ($options['size'] ?? ($serviceRequest['size'] ?? ''))));
        $resolvedSize = isset($sizes[$requestedSize]) ? $requestedSize : (string) array_key_first($sizes);
        if ($resolvedSize === '' || !isset($sizes[$resolvedSize])) {
            return [];
        }

        $sizePayload = $sizes[$resolvedSize];
        $sizePrice = (float) ($sizePayload['price'] ?? 0);
        $priceLabel = (string) ($sizePayload['label'] ?? '');
        [$sizeW, $sizeH] = array_map('intval', explode('x', $resolvedSize));
        $area = ($sizeW > 0 && $sizeH > 0) ? (($sizeW * $sizeH) / 10000) : 0.0;

        $canvasType = $this->resolveCanvasTypeOption($options, $serviceRequest);
        $canvasCoef = (float) ($canvasType['price'] ?? 1.0);
        if ($canvasCoef <= 0) {
            $canvasCoef = 1.0;
        }
        $priceWithCanvas = round($sizePrice * $canvasCoef, 2);

        $decoration = $this->resolveCanvasDecorationOption($options, $serviceRequest);
        $decorationPrice = 0.0;
        if ($area > 0) {
            if ($area <= 0.4) {
                $decorationPrice = $area * (float) ($decoration['coef_sm'] ?? 0);
            } elseif ($area <= 1) {
                $decorationPrice = $area * (float) ($decoration['coef_md'] ?? 0);
            } else {
                $decorationPrice = $area * (float) ($decoration['coef_lg'] ?? 0);
            }
        }
        if ($decorationPrice <= 0) {
            $decorationPrice = (float) ($decoration['price'] ?? 0);
        }
        $decorationPrice = round(max(0, $decorationPrice), 2);

        $packaging = $this->resolveCanvasPackagingOption($options, $serviceRequest);
        $packagingPrice = round(max(0, (float) ($packaging['price'] ?? 0)), 2);

        $improvement = $this->resolveCanvasPhotoImprovementOption($options, $serviceRequest, $displayLocale);
        $improvementPrice = round(max(0, (float) ($improvement['price'] ?? 0)), 2);

        $terms = $this->resolveCanvasTermsOption($options, $displayLocale);
        $termsPrice = round(max(0, (float) ($terms['price'] ?? 0)), 2);

        $total = round($priceWithCanvas + $decorationPrice + $packagingPrice + $improvementPrice + $termsPrice, 2);

        $canvasDisplayName = trim((string) $this->resolveGalleryHolstLocalizedField((int) ($canvasType['id'] ?? 0), 'name', $displayLocale, (string) ($canvasType['name'] ?? '')));
        if ($canvasDisplayName === '') {
            $canvasDisplayName = 'Interior';
        }

        $decorationDisplayName = trim((string) $this->resolveGalleryDecorationLocalizedName((int) ($decoration['id'] ?? 0), $displayLocale, (string) ($decoration['name'] ?? 'Standard. Without additions')));
        $packagingDisplayName = trim((string) $this->resolveGalleryBoxLocalizedName((int) ($packaging['id'] ?? 0), $displayLocale, (string) ($packaging['name'] ?? 'Regular packing')));
        $executionDisplay = trim((string) trans('modular_pictures.index45', [], $displayLocale));
        if ($executionDisplay === '') {
            $executionDisplay = GalleryExecutionType::getName(GalleryExecutionType::PRINT_TYPE);
        }

        $giftCode = 'G0';
        $packagingId = (int) ($packaging['id'] ?? 0);
        if ($packagingId === 1) {
            $giftCode = 'G2';
        } elseif ($packagingId === 2) {
            $giftCode = 'G1';
        }

        $manualDecorationId = (int) ($decoration['id'] ?? 5);
        $manualLacCode = 'L0';
        $manualBrushCode = 'P0';
        if ($manualDecorationId === 1) {
            $manualLacCode = 'L2';
        } elseif ($manualDecorationId === 2) {
            $manualBrushCode = 'P1';
        } elseif ($manualDecorationId === 3) {
            $manualLacCode = 'L1';
        }

        return [
            'canvas_item' => [
                'count' => 1,
                'id' => $serviceId,
                'pid' => 1,
                'name' => 'Canvas',
                'type' => 'sa_service',
                'country_code' => strtoupper($countryCode),
                'service_request_id' => (string) ($serviceRequest['request_id'] ?? ''),
                'service_path' => '/new/canvas',
                'formId' => 1,
                'sizeId' => $resolvedSize,
                'size' => $resolvedSize,
                'size_name' => $resolvedSize,
                'price_label' => $priceLabel !== '' ? $priceLabel : null,
                'canvasId' => (int) ($canvasType['id'] ?? 2),
                'decorationId' => (int) ($decoration['id'] ?? 5),
                'executionId' => 2,
                'boxIds' => [(int) ($packaging['id'] ?? 3)],
                'manual_canvas_id' => (int) ($canvasType['id'] ?? 2),
                'manual_gift_code' => $giftCode,
                'manual_decoration_id' => $manualDecorationId,
                'manual_lac_code' => $manualLacCode,
                'manual_brushstrokes_code' => $manualBrushCode,
                'manual_orientation_code' => 'V0',
                'improve_photo' => (string) ($improvement['name'] ?? 'Basic Enhancement 0 EUR'),
                'execution' => $executionDisplay,
                'pack' => $packagingDisplayName,
                'hud_of' => $decorationDisplayName,
                'show' => [
                    'size' => $resolvedSize,
                    'canvas' => $canvasDisplayName,
                    'box' => [$packagingDisplayName],
                    'decoration' => $decorationDisplayName,
                ],
                'terms' => (string) ($terms['text'] ?? 'Standard - 3 working days 0 EUR'),
                'terms_price' => $termsPrice,
                'photo_ex' => '',
                'is_canvas_collage' => 1,
                'is_with_orig_image' => 1,
                'price' => $total,
                'sumPrice' => $total,
            ],
            'totalPrice' => $total,
        ];
    }

    /**
     * @return array<string, array{price: float, label: string}>
     */
    private function resolveCanvasSizePriceMapWithLabels(float $countryMultiplier): array
    {
        if (!Schema::hasTable('canvas_header')) {
            return [];
        }

        $row = DB::table('canvas_header')
            ->orderBy('id')
            ->first(['sizes_30x40', 'sizes_38x38', 'sizes_40x30', 'sizes_60x30']);
        if (!$row) {
            return [];
        }

        $mergedRaw = implode(',', array_filter([
            (string) ($row->sizes_30x40 ?? ''),
            (string) ($row->sizes_38x38 ?? ''),
            (string) ($row->sizes_40x30 ?? ''),
            (string) ($row->sizes_60x30 ?? ''),
        ]));

        $parsed = $this->parseSizePriceMapWithLabels($mergedRaw);
        if (empty($parsed)) {
            return [];
        }

        $result = [];
        foreach ($parsed as $size => $payload) {
            $basePrice = (float) ($payload['price'] ?? 0);
            if ($basePrice <= 0) {
                continue;
            }

            $result[$size] = [
                'price' => (float) round($basePrice * $countryMultiplier, 2),
                'label' => (string) ($payload['label'] ?? ''),
            ];
        }

        return $result;
    }

    /**
     * @return array<string, array{price: float, label: string}>
     */
    private function parseSizePriceMapWithLabels(string $raw): array
    {
        if ($raw === '') {
            return [];
        }

        $result = [];
        $parts = explode(',', $raw);
        foreach ($parts as $part) {
            $chunk = trim((string) $part);
            if ($chunk === '') {
                continue;
            }

            if (!preg_match('/^([0-9]+\s*x\s*[0-9]+)\s*\[(.*?)\]\s*([hstHST])?$/', $chunk, $matches)) {
                continue;
            }

            $size = strtolower(str_replace(' ', '', (string) $matches[1]));
            if (!preg_match('/^\d+x\d+$/', $size)) {
                continue;
            }

            preg_match_all('/\d+(?:[.,]\d+)?/', (string) $matches[2], $numMatches);
            $numbers = $numMatches[0] ?? [];
            if (empty($numbers)) {
                continue;
            }

            $pickedRaw = end($numbers);
            $picked = (float) str_replace(',', '.', (string) $pickedRaw);
            if ($picked <= 0) {
                continue;
            }

            $label = strtolower((string) ($matches[3] ?? ''));
            if (!isset($result[$size]) || $picked < (float) $result[$size]['price']) {
                $result[$size] = [
                    'price' => $picked,
                    'label' => $label,
                ];
            }
        }

        return $result;
    }

    private function resolveCanvasTypeOption(array $options, array $serviceRequest): array
    {
        if (!Schema::hasTable('gallery_holsts')) {
            return ['id' => 2, 'price' => 1.0, 'name' => 'Interior'];
        }

        $requestedId = (int) ($options['canvas_id']
            ?? $options['holst_id']
            ?? $serviceRequest['canvas_id']
            ?? $serviceRequest['canvasId']
            ?? $serviceRequest['holst_id']
            ?? 0);
        if ($requestedId > 0) {
            $row = DB::table('gallery_holsts')->where('id', $requestedId)->first(['id', 'price', 'name', 'density']);
            if ($row) {
                return (array) $row;
            }
        }

        $default = DB::table('gallery_holsts')->where('default', 1)->orderBy('id')->first(['id', 'price', 'name', 'density']);
        if ($default) {
            return (array) $default;
        }

        $fallback = DB::table('gallery_holsts')->orderBy('id')->first(['id', 'price', 'name', 'density']);
        return (array) ($fallback ?: ['id' => 2, 'price' => 1.0, 'name' => 'Interior', 'density' => '(High-quality)']);
    }

    private function resolveCanvasDecorationOption(array $options, array $serviceRequest): array
    {
        if (!Schema::hasTable('gallery_decorations')) {
            return ['id' => 5, 'price' => 0.0, 'coef_sm' => 0, 'coef_md' => 0, 'coef_lg' => 0, 'name' => 'Standard. Without additions'];
        }

        $requestedId = (int) ($options['decoration_id']
            ?? $options['decor_id']
            ?? $serviceRequest['decoration_id']
            ?? $serviceRequest['decorationId']
            ?? $serviceRequest['decor_id']
            ?? 0);
        if ($requestedId > 0) {
            $row = DB::table('gallery_decorations')
                ->where('id', $requestedId)
                ->first(['id', 'price', 'coef_sm', 'coef_md', 'coef_lg', 'name']);
            if ($row) {
                return (array) $row;
            }
        }

        $namedDefault = DB::table('gallery_decorations')
            ->whereRaw('LOWER(name) LIKE ?', ['%standard%'])
            ->orderBy('id')
            ->first(['id', 'price', 'coef_sm', 'coef_md', 'coef_lg', 'name']);
        if ($namedDefault) {
            return (array) $namedDefault;
        }

        $default = DB::table('gallery_decorations')
            ->where('price', 0)
            ->orderBy('id')
            ->first(['id', 'price', 'coef_sm', 'coef_md', 'coef_lg', 'name']);
        if ($default) {
            return (array) $default;
        }

        $fallback = DB::table('gallery_decorations')
            ->orderBy('id')
            ->first(['id', 'price', 'coef_sm', 'coef_md', 'coef_lg', 'name']);

        return (array) ($fallback ?: ['id' => 5, 'price' => 0.0, 'coef_sm' => 0, 'coef_md' => 0, 'coef_lg' => 0, 'name' => 'Standard. Without additions']);
    }

    private function resolveModularDecorationOption(array $options, array $serviceRequest): array
    {
        $normalizedOptions = $options;
        $normalizedRequest = $serviceRequest;

        if (!isset($normalizedOptions['decoration_id']) && isset($options['holst_id'])) {
            $normalizedOptions['decoration_id'] = $options['holst_id'];
        }
        if (!isset($normalizedOptions['decor_id']) && isset($options['holst_id'])) {
            $normalizedOptions['decor_id'] = $options['holst_id'];
        }
        if (!isset($normalizedRequest['decoration_id']) && isset($serviceRequest['holst_id'])) {
            $normalizedRequest['decoration_id'] = $serviceRequest['holst_id'];
        }
        if (!isset($normalizedRequest['decor_id']) && isset($serviceRequest['holst_id'])) {
            $normalizedRequest['decor_id'] = $serviceRequest['holst_id'];
        }

        return $this->resolveCanvasDecorationOption($normalizedOptions, $normalizedRequest);
    }

    private function resolveCanvasPackagingOption(array $options, array $serviceRequest): array
    {
        if (!Schema::hasTable('gallery_boxes')) {
            return ['id' => 3, 'price' => 0.0, 'name' => 'Regular packing'];
        }

        $requestedId = (int) ($options['packaging_id']
            ?? $options['compl_id']
            ?? $serviceRequest['packaging_id']
            ?? $serviceRequest['box_id']
            ?? $serviceRequest['compl_id']
            ?? 0);
        if ($requestedId > 0) {
            $row = DB::table('gallery_boxes')->where('id', $requestedId)->first(['id', 'price', 'name']);
            if ($row) {
                return (array) $row;
            }
        }

        $namedDefault = DB::table('gallery_boxes')
            ->whereRaw('LOWER(name) LIKE ?', ['%regular%'])
            ->orderBy('id')
            ->first(['id', 'price', 'name']);
        if ($namedDefault) {
            return (array) $namedDefault;
        }

        $zeroPriceDefault = DB::table('gallery_boxes')
            ->where('price', 0)
            ->orderBy('id')
            ->first(['id', 'price', 'name']);
        if ($zeroPriceDefault) {
            return (array) $zeroPriceDefault;
        }

        $fallback = DB::table('gallery_boxes')->orderBy('id')->first(['id', 'price', 'name']);
        return (array) ($fallback ?: ['id' => 3, 'price' => 0.0, 'name' => 'Regular packing']);
    }

    private function resolveCanvasRamOption(array $options, array $serviceRequest): array
    {
        if (!Schema::hasTable('canvas_rams')) {
            return ['id' => 0, 'price' => 0.0];
        }

        $requestedId = (int) ($options['ram_id'] ?? $serviceRequest['ram_id'] ?? 12);
        if ($requestedId > 0) {
            $row = CanvasRam::query()->where('id', $requestedId)->first(['id', 'price']);
            if ($row) {
                return [
                    'id' => (int) $row->id,
                    'price' => (float) $row->price,
                ];
            }
        }

        $default = CanvasRam::query()
            ->orderByRaw('CASE WHEN id = 12 THEN 0 ELSE 1 END')
            ->orderBy('price')
            ->orderBy('id')
            ->first(['id', 'price']);

        return $default
            ? ['id' => (int) $default->id, 'price' => (float) $default->price]
            : ['id' => 0, 'price' => 0.0];
    }

    private function resolveAreaSquareMetersBySize(?string $size): float
    {
        $size = strtolower(str_replace(' ', '', (string) $size));
        if ($size === '' || !preg_match('/^(\d+(?:\.\d+)?)x(\d+(?:\.\d+)?)$/', $size, $matches)) {
            return 0.0;
        }

        $width = (float) $matches[1];
        $height = (float) $matches[2];
        if ($width <= 0 || $height <= 0) {
            return 0.0;
        }

        return round(($width * $height) / 10000, 4);
    }

    private function resolveDecorationAreaPrice(array $decoration, float $areaSquareMeters): float
    {
        if ($areaSquareMeters <= 0) {
            return 0.0;
        }

        if ($areaSquareMeters <= 0.4) {
            $coef = (float) ($decoration['coef_sm'] ?? 0);
        } elseif ($areaSquareMeters <= 1.0) {
            $coef = (float) ($decoration['coef_md'] ?? 0);
        } else {
            $coef = (float) ($decoration['coef_lg'] ?? 0);
        }

        if ($coef <= 0) {
            return 0.0;
        }

        return round($areaSquareMeters * $coef, 2);
    }

    private function hasModularLayoutSvg(array $options, array $serviceRequest): bool
    {
        $svg = (string) ($options['layout_svg']
            ?? $options['collageSvgImage']
            ?? $serviceRequest['layout_svg']
            ?? $serviceRequest['collageSvgImage']
            ?? '');

        return trim($svg) !== '';
    }

    private function resolveModularLayoutAreaSquareMeters(array $options, array $serviceRequest): ?float
    {
        $svg = (string) ($options['layout_svg']
            ?? $options['collageSvgImage']
            ?? $serviceRequest['layout_svg']
            ?? $serviceRequest['collageSvgImage']
            ?? '');

        $svgArea = $this->parseModularLayoutSvgAreaSquareMeters($svg);
        if ($svgArea !== null) {
            return $svgArea;
        }

        $blocks = $options['layout_blocks'] ?? $serviceRequest['layout_blocks'] ?? null;
        if (!is_array($blocks) || empty($blocks)) {
            return null;
        }

        $sum = 0.0;
        foreach ($blocks as $block) {
            if (!is_array($block)) {
                continue;
            }

            $width = isset($block['width']) ? (float) $block['width'] : 0.0;
            $height = isset($block['height']) ? (float) $block['height'] : 0.0;
            if ($width <= 0 || $height <= 0) {
                continue;
            }

            $sum += ($width * $height) / 10000;
        }

        return $sum > 0 ? round($sum, 4) : null;
    }

    private function parseModularLayoutSvgAreaSquareMeters(string $svg): ?float
    {
        $svg = trim($svg);
        if ($svg === '') {
            return null;
        }

        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($svg, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($xml === false) {
            return null;
        }

        $viewBox = preg_split('/[\s,]+/', trim((string) ($xml['viewBox'] ?? '')));
        $viewBoxWidth = isset($viewBox[2]) ? (float) $viewBox[2] : 0.0;
        $viewBoxHeight = isset($viewBox[3]) ? (float) $viewBox[3] : 0.0;
        if ($viewBoxWidth <= 0 || $viewBoxHeight <= 0) {
            return null;
        }

        $sum = 0.0;
        $rects = $xml->xpath('//*[local-name()="rect"]');
        if (!is_array($rects)) {
            return null;
        }

        foreach ($rects as $rect) {
            $width = $this->resolveSvgLengthToViewBoxUnits((string) ($rect['width'] ?? ''), $viewBoxWidth);
            $height = $this->resolveSvgLengthToViewBoxUnits((string) ($rect['height'] ?? ''), $viewBoxHeight);
            if ($width <= 0 || $height <= 0) {
                continue;
            }

            $sum += ($width * $height) / 10000;
        }

        return $sum > 0 ? round($sum, 4) : null;
    }

    private function resolveSvgLengthToViewBoxUnits(string $raw, float $viewBoxDimension): float
    {
        $raw = trim($raw);
        if ($raw === '' || $viewBoxDimension <= 0) {
            return 0.0;
        }

        if (substr($raw, -1) === '%') {
            $percent = (float) rtrim($raw, '%');
            return round($viewBoxDimension * $percent / 100, 4);
        }

        if (preg_match('/-?\d+(?:\.\d+)?/', $raw, $matches)) {
            return (float) $matches[0];
        }

        return 0.0;
    }

    private function resolveModularExecutionPrice(float $layoutAreaSquareMeters, int $executionId): float
    {
        if ($layoutAreaSquareMeters <= 0) {
            return 0.0;
        }

        if ($layoutAreaSquareMeters <= 0.4) {
            $executionPrice = $layoutAreaSquareMeters * (float) setting('modulnye-kartiny.area_less_04', 2);
        } elseif ($layoutAreaSquareMeters <= 1.0) {
            $executionPrice = $layoutAreaSquareMeters * (float) setting('modulnye-kartiny.area_less_1', 1.5);
        } else {
            $executionPrice = $layoutAreaSquareMeters * (float) setting('modulnye-kartiny.area_more_01', 1.35);
        }

        if ($executionId === 1) {
            $executionPrice *= (float) setting('modulnye-kartiny.area_is_1', 55);
        }

        return round($executionPrice, 2);
    }

    private function resolveGalleryExecutionOption(array $options, array $serviceRequest, string $locale = 'ru'): array
    {
        $executionId = (int) ($options['execution_id']
            ?? $options['executionId']
            ?? $serviceRequest['execution_id']
            ?? $serviceRequest['executionId']
            ?? 2);

        if ($executionId <= 0) {
            $executionId = 2;
        }

        if ($executionId === 1) {
            $name = trim((string) trans('modular_pictures.index46', [], $locale));
            if ($name === '') {
                $name = GalleryExecutionType::getName(GalleryExecutionType::OIL_TYPE);
            }

            return [
                'id' => 1,
                'name' => $name !== '' ? $name : 'Oil painting',
            ];
        }

        $name = trim((string) trans('modular_pictures.index45', [], $locale));
        if ($name === '') {
            $name = GalleryExecutionType::getName(GalleryExecutionType::PRINT_TYPE);
        }

        return [
            'id' => 2,
            'name' => $name !== '' ? $name : 'Print',
        ];
    }

    private function resolveGalleryExecutionPrice(float $areaSquareMeters, int $executionId): float
    {
        if ($executionId !== 1 || $areaSquareMeters <= 0) {
            return 0.0;
        }

        if ($areaSquareMeters <= 0.4) {
            return round($areaSquareMeters * 80, 2);
        }

        if ($areaSquareMeters <= 1.0) {
            return round($areaSquareMeters * 60, 2);
        }

        return round($areaSquareMeters * 50, 2);
    }

    private function resolveCanvasPhotoImprovementOption(array $options, array $serviceRequest, string $locale = 'ru'): array
    {
        if (!Schema::hasTable('canvas_photo_improvements')) {
            return ['id' => 1, 'price' => 0.0, 'name' => 'Basic Enhancement 0 EUR'];
        }

        $requestedId = (int) ($options['photo_improvement_id'] ?? $serviceRequest['photo_improvement_id'] ?? 0);
        if ($requestedId > 0) {
            $model = CanvasPhotoImprove::where('id', $requestedId)
                ->where('is_active', 1)
                ->get()
                ->translate($locale, 'ru')
                ->first();
            if ($model) {
                return [
                    'id' => (int) $model->id,
                    'price' => (float) $model->price,
                    'name' => trim((string) $model->name) . ' ' . rtrim(rtrim(number_format((float) $model->price, 2, '.', ''), '0'), '.') . ' EUR',
                ];
            }
        }

        $defaultRow = DB::table('canvas_photo_improvements')
            ->where('is_active', 1)
            ->orderByDesc('is_default')
            ->orderBy('sort')
            ->orderBy('id')
            ->first(['id']);

        if (!$defaultRow) {
            return ['id' => 1, 'price' => 0.0, 'name' => 'Basic Enhancement 0 EUR'];
        }

        $default = CanvasPhotoImprove::where('id', (int) $defaultRow->id)->get()->translate($locale, 'ru')->first();
        if (!$default) {
            return ['id' => 1, 'price' => 0.0, 'name' => 'Basic Enhancement 0 EUR'];
        }

        return [
            'id' => (int) $default->id,
            'price' => (float) $default->price,
            'name' => trim((string) $default->name) . ' ' . rtrim(rtrim(number_format((float) $default->price, 2, '.', ''), '0'), '.') . ' EUR',
        ];
    }

    private function resolveCanvasTermsOption(array $options, string $locale = 'ru'): array
    {
        if (!Schema::hasTable('a_production_time')) {
            return ['mode' => 'standard', 'price' => 0.0, 'text' => 'Standard - 3 working days 0 EUR'];
        }

        $baseRow = DB::table('a_production_time')
            ->where('category', 'canvas')
            ->orderBy('id')
            ->first(['id']);
        if (!$baseRow) {
            return ['mode' => 'standard', 'price' => 0.0, 'text' => 'Standard - 3 working days 0 EUR'];
        }

        $row = AProductionTime::where('id', (int) $baseRow->id)->get()->translate($locale, 'ru')->first();
        if (!$row) {
            return ['mode' => 'standard', 'price' => 0.0, 'text' => 'Standard - 3 working days 0 EUR'];
        }

        $mode = strtolower((string) ($options['production_mode'] ?? 'standard'));
        if ($mode === 'express') {
            $price = (float) ($row->express_price ?? 0);
            $text = trim((string) ($row->express_text ?? 'Express')) . ' ' . rtrim(rtrim(number_format($price, 2, '.', ''), '0'), '.') . ' EUR';
            return ['mode' => 'express', 'price' => $price, 'text' => $text];
        }

        $price = (float) ($row->standart_price ?? 0);
        $text = trim((string) ($row->standart_text ?? 'Standard')) . ' ' . rtrim(rtrim(number_format($price, 2, '.', ''), '0'), '.') . ' EUR';
        return ['mode' => 'standard', 'price' => $price, 'text' => $text];
    }

    private function resolveCollageTermsOption(array $options, string $locale = 'ru'): array
    {
        if (!Schema::hasTable('a_production_time')) {
            return ['mode' => 'standard', 'price' => 0.0, 'text' => 'Standard - 3 working days 0 EUR'];
        }

        $baseRow = DB::table('a_production_time')
            ->where('category', 'collage')
            ->orderBy('id')
            ->first(['id']);
        if (!$baseRow) {
            return ['mode' => 'standard', 'price' => 0.0, 'text' => 'Standard - 3 working days 0 EUR'];
        }

        $row = AProductionTime::where('id', (int) $baseRow->id)->get()->translate($locale, 'ru')->first();
        if (!$row) {
            return ['mode' => 'standard', 'price' => 0.0, 'text' => 'Standard - 3 working days 0 EUR'];
        }

        $mode = strtolower((string) ($options['production_mode'] ?? 'standard'));
        if ($mode === 'express') {
            $price = (float) ($row->express_price ?? 0);
            $text = trim((string) ($row->express_text ?? 'Express')) . ' ' . rtrim(rtrim(number_format($price, 2, '.', ''), '0'), '.') . ' EUR';
            return ['mode' => 'express', 'price' => $price, 'text' => $text];
        }

        $price = (float) ($row->standart_price ?? 0);
        $text = trim((string) ($row->standart_text ?? 'Standard')) . ' ' . rtrim(rtrim(number_format($price, 2, '.', ''), '0'), '.') . ' EUR';
        return ['mode' => 'standard', 'price' => $price, 'text' => $text];
    }

    private function resolveLocaleByCountryCode(string $countryCode): string
    {
        $code = strtoupper(trim($countryCode));
        if ($code === 'LV') {
            return 'lv';
        }
        if ($code === 'LT') {
            return 'lt';
        }
        if ($code === 'EE' || $code === 'ET') {
            return 'ee';
        }
        if ($code === 'PL') {
            return 'pl';
        }
        if ($code === 'DE') {
            return 'de';
        }

        return 'ru';
    }

    private function resolveGalleryHolstOption(array $options, array $serviceRequest): array
    {
        if (!Schema::hasTable('gallery_holsts')) {
            return ['id' => 0, 'name' => ''];
        }

        $requestedId = (int) ($options['holst_id']
            ?? $options['canvas_id']
            ?? $serviceRequest['holst_id']
            ?? $serviceRequest['canvas_id']
            ?? $serviceRequest['canvasId']
            ?? 0);
        if ($requestedId > 0) {
            $row = DB::table('gallery_holsts')->where('id', $requestedId)->first(['id', 'name']);
            if ($row) {
                return (array) $row;
            }
        }

        $default = DB::table('gallery_holsts')
            ->where('default', 1)
            ->orderBy('id')
            ->first(['id', 'name']);
        if ($default) {
            return (array) $default;
        }

        $fallback = DB::table('gallery_holsts')->orderBy('id')->first(['id', 'name']);
        return (array) ($fallback ?: ['id' => 0, 'name' => '']);
    }

    private function resolveGalleryHolstLocalizedField(int $id, string $field, string $locale, string $fallback = ''): string
    {
        if ($id <= 0 || !Schema::hasTable('gallery_holsts')) {
            return $fallback;
        }

        $model = GalleryHolst::where('id', $id)->get()->translate($locale, 'ru')->first();
        if (!$model) {
            return $fallback;
        }

        $value = trim((string) data_get($model, $field, ''));
        return $value !== '' ? $value : $fallback;
    }

    private function resolveGalleryBoxLocalizedName(int $id, string $locale, string $fallback = ''): string
    {
        if ($id <= 0 || !Schema::hasTable('gallery_boxes')) {
            return $fallback;
        }

        $model = GalleryBox::where('id', $id)->get()->translate($locale, 'ru')->first();
        if (!$model) {
            return $fallback;
        }

        $value = trim((string) data_get($model, 'name', ''));
        return $value !== '' ? $value : $fallback;
    }

    private function resolveGalleryDecorationLocalizedName(int $id, string $locale, string $fallback = ''): string
    {
        if ($id <= 0 || !Schema::hasTable('gallery_decorations')) {
            return $fallback;
        }

        $model = GalleryDecoration::where('id', $id)->get()->translate($locale, 'ru')->first();
        if (!$model) {
            return $fallback;
        }

        $value = trim((string) data_get($model, 'name', ''));
        return $value !== '' ? $value : $fallback;
    }

    private function createTemporaryOrder(int $orderId, array $context): bool
    {
        $email = (string) data_get($context, 'client.email', data_get($context, 'fields.email', data_get($context, 'emails.0', '')));
        $phone = (string) data_get($context, 'client.phone', '');
        $name  = (string) data_get($context, 'client.name', '');

        $resolvedUser = null;

        if ($email) {
            $resolvedUser = \App\Models\User::where('email', $email)->first();
        }

        if (!$resolvedUser && $phone) {
            $resolvedUser = \App\Models\User::where('phone', $phone)->first();
        }

        if (!$resolvedUser) {
            $resolvedUser = $this->createSaLeadUser($email, $phone, $name);
        }

        if (!$resolvedUser) {
            return false;
        }

        $userId = $resolvedUser->id;

        $defaults = [
            'id' => $orderId,
            'user_id' => $userId,
            'price' => 0,
            'sale_price' => 0,
            'items' => json_encode([]),
            'country' => 'NA',
            'delivery' => json_encode([
                'source' => 'sa_integration',
                'temporary' => true,
                'address' => 'Integration temp address',
                'first_name' => 'Integration',
                'last_name' => 'Temp',
                'phone' => '+0000000000',
                'email' => 'integration@example.com',
                'country' => 'NA',
                'city' => 'N/A',
                'postal_index' => '00000',
                'sposob' => 'api_integration',
                'comment' => 'Temporary integration order',
            ]),
            'payment' => 'unknown',
            'payment_status' => 'not_payed',
            'comment' => 'Temporary order created by SA integration',
            'admin_comment' => (isset($context['source']) ? 'SA temp lead source: ' . $context['source'] : 'SA temp lead') . ($context['admin_comment_append'] ?? ''),
            'status' => 'watching',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $insertData = [];
        foreach ($defaults as $column => $value) {
            if (Schema::hasColumn('orders', $column)) {
                $insertData[$column] = $value;
            }
        }

        try {
            DB::table('orders')->insert($insertData);

            return true;
        } catch (QueryException $e) {
            // If order was created in a parallel request, treat as success.
            if ((int) $e->getCode() === 23000 && DB::table('orders')->where('id', $orderId)->exists()) {
                return true;
            }

            return false;
        }
    }

    private function resolveFallbackUserId(): int
    {
        if (!Schema::hasTable('users')) {
            return 1;
        }

        $firstUserId = DB::table('users')->orderBy('id', 'asc')->value('id');

        return $firstUserId ? (int) $firstUserId : 1;
    }

    /**
     * Создать нового пользователя из данных SA-лида.
     * Определяет страну и locale по префиксу телефона.
     */
    private function createSaLeadUser(string $email, string $phone, string $name): ?\App\Models\User
    {
        $nameParts = explode(' ', trim($name), 2);
        $firstName = !empty($nameParts[0]) ? $nameParts[0] : 'SA';
        $lastName  = $nameParts[1] ?? 'Lead';
        $userEmail = $email ?: ('sa_lead_' . preg_replace('/\D/', '', $phone) . '@noemail.local');

        // Detect country and locale from phone prefix
        $phoneCountry = 'LV';
        $phoneLocale  = 'lv';
        if ($phone) {
            $phoneDigits = preg_replace('/\D/', '', $phone);
            if (str_starts_with($phoneDigits, '370')) {
                $phoneCountry = 'LT'; $phoneLocale = 'lt';
            } elseif (str_starts_with($phoneDigits, '371')) {
                $phoneCountry = 'LV'; $phoneLocale = 'lv';
            } elseif (str_starts_with($phoneDigits, '372')) {
                $phoneCountry = 'EE'; $phoneLocale = 'ee';
            } elseif (str_starts_with($phoneDigits, '375') || str_starts_with($phoneDigits, '380') || str_starts_with($phoneDigits, '7')) {
                $phoneCountry = 'RU'; $phoneLocale = 'ru';
            } elseif (str_starts_with($phoneDigits, '48')) {
                $phoneCountry = 'PL'; $phoneLocale = 'pl';
            } elseif (str_starts_with($phoneDigits, '49')) {
                $phoneCountry = 'DE'; $phoneLocale = 'de';
            }
        }

        // If email already exists - return existing user
        $existing = \App\Models\User::where('email', $userEmail)->first();
        if ($existing) {
            return $existing;
        }

        try {
            $newUser = new \App\Models\User();
            $newUser->email       = $userEmail;
            $newUser->password    = \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16));
            $newUser->first_name  = $firstName;
            $newUser->last_name   = $lastName;
            $newUser->phone       = $phone;
            $newUser->role_id     = 2;
            $newUser->avatar      = 'users/default.png';
            $newUser->news        = 'NO';
            $newUser->ad          = 'NO';
            $newUser->client_data = 'NO';
            $newUser->country     = $phoneCountry;
            $newUser->settings    = collect(['locale' => $phoneLocale]);
            $newUser->save();

            return $newUser;
        } catch (\Throwable $e) {
            Log::warning('SA Integration: failed to create user for lead', [
                'email' => $userEmail,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function syncClientChatMessage(
        ?int $orderId,
        string $text,
        bool $isAdmin,
        string $sentAt = '',
        string $saMessageId = ''
    ): void
    {
        if ($orderId === null || $text === '' || !Schema::hasTable('order_user_comments')) {
            return;
        }

        $timestamp = $sentAt !== '' ? Carbon::parse($sentAt) : now();
        $order = DB::table('orders')->where('id', $orderId)->first(['id', 'user_id']);
        if (!$order) {
            return;
        }

        $senderUserId = $isAdmin
            ? $this->resolveAdminSenderUserId()
            : (int) $order->user_id;

        $insertData = [
            'order_id' => $orderId,
            'user_id' => $senderUserId > 0 ? $senderUserId : null,
            'comment' => $text,
            'is_admin' => $isAdmin ? 1 : 0,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
            'is_img_sketch' => 0,
            'is_img_painter' => 0,
            'order_painter_image_id' => 0,
            'order_user_image_id' => 0,
            // Client-side message flags as used by existing controllers/views:
            // inbound(client->admin): admin unread
            // outbound(admin->client): client unread
            'is_read' => $isAdmin ? 0 : 1,
            'admin_is_read' => $isAdmin ? 1 : 0,
        ];

        if ($saMessageId !== '' && Schema::hasColumn('order_user_comments', 'sa_message_id')) {
            $exists = DB::table('order_user_comments')
                ->where('order_id', $orderId)
                ->where('sa_message_id', $saMessageId)
                ->exists();

            if ($exists) {
                return;
            }

            $insertData['sa_message_id'] = $saMessageId;
        }

        if (Schema::hasColumn('order_user_comments', 'sa_direction')) {
            $insertData['sa_direction'] = $isAdmin ? 'outbound' : 'inbound';
        }

        DB::table('order_user_comments')->insert($insertData);
    }

    private function syncOrderIntegrationFields(?int $orderId, string $conversationId, string $clientPhone, ?string $botMode): void
    {
        if ($orderId === null || !Schema::hasTable('orders')) {
            return;
        }

        $updates = ['updated_at' => now()];
        if ($conversationId !== '' && Schema::hasColumn('orders', 'sa_conversation_id')) {
            $updates['sa_conversation_id'] = $conversationId;
        }
        if ($clientPhone !== '' && Schema::hasColumn('orders', 'sa_client_phone')) {
            $updates['sa_client_phone'] = $clientPhone;
        }
        if ($botMode !== null && Schema::hasColumn('orders', 'sa_bot_mode')) {
            $updates['sa_bot_mode'] = $botMode;
        }

        DB::table('orders')->where('id', $orderId)->update($updates);
    }

    private function normalizeDateTime($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse((string) $value)->format('Y-m-d H:i:s');
    }

    private function resolveAdminSenderUserId(): int
    {
        if (!Schema::hasTable('users')) {
            return $this->resolveFallbackUserId();
        }

        $adminId = DB::table('users')
            ->whereIn('role_id', [1, 4])
            ->orderBy('id', 'asc')
            ->value('id');

        if ($adminId) {
            return (int) $adminId;
        }

        return $this->resolveFallbackUserId();
    }

    /**
     * @param array<int, mixed> $attachments
     * @return array<int, array<string, mixed>>
     */
    private function processMessageAttachments(array $attachments, string $messageId): array
    {
        if (empty($attachments)) {
            return [];
        }

        $maxSize = (int) config('services.sa_integration.attachments.max_size', 10 * 1024 * 1024);
        $allowedMime = (array) config('services.sa_integration.attachments.allowed_mime', [
            'image/jpeg',
            'image/png',
            'image/webp',
            'application/pdf',
        ]);

        $stored = [];
        foreach ($attachments as $index => $attachment) {
            $item = is_array($attachment) ? $attachment : [];
            $sourceUrl = (string) (
                $item['url'] ??
                $item['src'] ??
                $item['file_url'] ??
                ''
            );

            if ($sourceUrl === '') {
                $stored[] = [
                    'status' => 'rejected',
                    'reason' => 'missing_url',
                ];
                continue;
            }

            $download = $this->downloadAttachmentBinary($sourceUrl);
            if ($download['ok'] !== true) {
                $stored[] = [
                    'status' => 'rejected',
                    'reason' => $download['reason'],
                    'source_url' => $sourceUrl,
                ];
                continue;
            }

            $binary = $download['binary'];
            $size = strlen($binary);
            if ($size <= 0 || $size > $maxSize) {
                $stored[] = [
                    'status' => 'rejected',
                    'reason' => 'invalid_size',
                    'source_url' => $sourceUrl,
                    'size' => $size,
                ];
                continue;
            }

            $mime = $this->detectBinaryMime($binary);
            if (!in_array($mime, $allowedMime, true)) {
                $stored[] = [
                    'status' => 'rejected',
                    'reason' => 'mime_not_allowed',
                    'source_url' => $sourceUrl,
                    'mime' => $mime,
                ];
                continue;
            }

            $relativePath = $this->buildAttachmentRelativePath($messageId, (int) $index, $mime, $sourceUrl);
            Storage::disk('public')->put($relativePath, $binary);

            $stored[] = [
                'status' => 'stored',
                'source_url' => $sourceUrl,
                'local_path' => 'storage/' . $relativePath,
                'disk' => 'public',
                'mime' => $mime,
                'size' => $size,
                'name' => (string) ($item['name'] ?? basename(parse_url($sourceUrl, PHP_URL_PATH) ?: $relativePath)),
            ];
        }

        return $stored;
    }

    /**
     * @return array{ok:bool, binary:string, reason:string}
     */
    private function downloadAttachmentBinary(string $url): array
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 15,
                'follow_location' => 1,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $binary = @file_get_contents($url, false, $context);
        if ($binary === false) {
            Log::warning('SA attachment download failed', ['url' => $url]);

            return ['ok' => false, 'binary' => '', 'reason' => 'download_failed'];
        }

        return ['ok' => true, 'binary' => $binary, 'reason' => ''];
    }

    private function detectBinaryMime(string $binary): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->buffer($binary);

        return $mime !== '' ? $mime : 'application/octet-stream';
    }

    private function buildAttachmentRelativePath(string $messageId, int $index, string $mime, string $sourceUrl): string
    {
        $extMap = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
        ];
        $ext = $extMap[$mime] ?? pathinfo(parse_url($sourceUrl, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION);
        $ext = $ext !== '' ? strtolower($ext) : 'bin';

        $safeMessageId = preg_replace('/[^a-zA-Z0-9_-]/', '_', $messageId);
        $fileName = $safeMessageId . '_' . $index . '_' . time() . '.' . $ext;

        return 'sa/attachments/' . date('Y/m') . '/' . $fileName;
    }

    /**
     * @param array<int, array<string, mixed>> $storedAttachments
     */
    private function buildChatTextWithAttachments(string $chatText, array $storedAttachments): string
    {
        if (empty($storedAttachments)) {
            return $chatText;
        }

        $lines = [];
        foreach ($storedAttachments as $attachment) {
            if (($attachment['status'] ?? '') !== 'stored') {
                continue;
            }

            $name = (string) ($attachment['name'] ?? 'attachment');
            $localPath = (string) ($attachment['local_path'] ?? '');
            if ($localPath === '') {
                continue;
            }

            $appUrl = rtrim((string) config('app.url', ''), '/');
            $normalizedLocalPath = '/' . ltrim($localPath, '/');
            $publicUrl = $appUrl !== ''
                ? $appUrl . $normalizedLocalPath
                : url($localPath);
            $lines[] = 'Attachment: ' . $name . ' - ' . $publicUrl;
        }

        if (empty($lines)) {
            return $chatText !== '' ? $chatText : '[Attachment message]';
        }

        if ($chatText === '') {
            return "[Attachment message]\n" . implode("\n", $lines);
        }

        return $chatText . "\n" . implode("\n", $lines);
    }

    private function buildOrderLookupPayload($order, string $lang = 'ru'): array
    {
        $delivery = $this->decodeJsonAssoc($order->delivery ?? null);
        $items = $this->decodeJsonAssoc($order->items ?? null);
        $products = $this->extractLookupProducts($items);
        $itemComments = [];

        foreach ($products as $product) {
            if ((string) ($product['comment'] ?? '') !== '') {
                $itemComments[] = [
                    'product' => $product['name'] ?? null,
                    'comment' => $product['comment'],
                ];
            }
        }

        $deliveryAmount = $this->lookupNumber(data_get($delivery, 'deliv_price'));
        $itemsAmount = $this->lookupNumber($order->price ?? null);
        $saleAmount = $this->lookupNumber($order->sale_price ?? null);
        $totalTermsAmount = $this->lookupNumber(data_get($items, 'total_terms_price'));
        $baseAmount = $saleAmount !== null && $saleAmount > 0 ? $saleAmount : ($itemsAmount ?? 0);
        $status = (string) ($order->status ?? '');
        $paymentStatus = (string) ($order->payment_status ?? '');

        return [
            'lead_id' => (string) ($order->id ?? ''),
            'order_id' => (int) ($order->id ?? 0),
            'created_at' => $this->lookupDate($order->created_at ?? null),
            'updated_at' => $this->lookupDate($order->updated_at ?? null),
            'status' => [
                'id' => $status,
                'title' => $this->lookupOrderStatusTitle($status, $lang),
            ],
            'payment' => [
                'method' => (string) ($order->payment ?? data_get($delivery, 'payment', '')),
                'status' => $paymentStatus,
                'status_title' => $this->lookupPaymentStatusTitle($paymentStatus, $lang),
            ],
            'pricing' => [
                'currency' => 'EUR',
                'items_amount' => $itemsAmount,
                'sale_amount' => $saleAmount,
                'delivery_amount' => $deliveryAmount,
                'production_terms_amount' => $totalTermsAmount,
                'total_amount' => round($baseAmount + ($deliveryAmount ?? 0) + ($totalTermsAmount ?? 0), 2),
            ],
            'client' => [
                'user_id' => isset($order->user_id) ? (int) $order->user_id : null,
                'phone' => (string) ($order->lookup_user_phone ?? data_get($delivery, 'payer_phone', data_get($delivery, 'phone', ''))),
                'email' => (string) ($order->lookup_user_email ?? data_get($delivery, 'email', '')),
                'first_name' => (string) ($order->lookup_user_name ?? data_get($delivery, 'first_name', '')),
                'last_name' => (string) ($order->lookup_user_last_name ?? data_get($delivery, 'last_name', '')),
            ],
            'recipient' => [
                'phone' => (string) data_get($delivery, 'phone', ''),
                'first_name' => (string) data_get($delivery, 'first_name', ''),
                'last_name' => (string) data_get($delivery, 'last_name', ''),
                'country' => (string) ($order->country ?? data_get($delivery, 'country', '')),
                'city' => (string) data_get($delivery, 'city', ''),
                'address' => (string) data_get($delivery, 'address', ''),
                'postal_index' => (string) data_get($delivery, 'postal_index', ''),
            ],
            'delivery' => [
                'method' => (string) data_get($delivery, 'sposob', ''),
                'when_send' => (string) data_get($delivery, 'when_send', ''),
                'price' => $deliveryAmount,
                'raw' => $delivery,
            ],
            'billing' => [
                'is_company' => $this->isLookupCompanyOrder($order),
                'invoice_uuid' => $this->cleanLookupScalar($order->billing_invoice_uuid ?? null),
                'company' => [
                    'name' => $this->cleanLookupScalar($order->ur_name_l ?? null),
                    'registration_number' => $this->cleanLookupScalar($order->ur_reg_num ?? null),
                    'legal_address' => $this->cleanLookupScalar($order->ur_legal_addr ?? null),
                    'vat_number' => $this->cleanLookupScalar($order->ur_pnr_nr ?? null),
                    'bank_name' => $this->cleanLookupScalar($order->ur_bank_name ?? null),
                    'bank_code' => $this->cleanLookupScalar($order->ur_bank_code ?? null),
                    'bank_account_code' => $this->cleanLookupScalar($order->ur_bank_acc_code ?? null),
                ],
            ],
            'comments' => [
                'order_comment' => (string) ($order->comment ?? ''),
                'admin_comment' => (string) ($order->admin_comment ?? ($order->admin_coment ?? '')),
                'client_comment' => (string) ($order->client_comment ?? ''),
                'delivery_comment' => (string) data_get($delivery, 'comment', ''),
                'item_comments' => $itemComments,
            ],
            'products' => $products,
            'artist' => [
                'painter_images' => $this->splitLookupCsvImages($order->painter_images ?? null),
                'painter_sketch_images' => $this->splitLookupCsvImages($order->painter_sketch_images ?? null),
                'painter_images_status' => $this->cleanLookupScalar($order->painter_images_status ?? null),
                'painter_images_status_date' => $this->lookupDate($order->painter_images_status_date ?? null),
                'painter_sketch_images_status' => $this->cleanLookupScalar($order->painter_sketch_images_status ?? null),
                'painter_sketch_images_status_date' => $this->lookupDate($order->painter_sketch_images_status_date ?? null),
                'painter_comment' => $this->cleanLookupScalar($order->painter_comment ?? null),
                'is_show_painter_images' => isset($order->is_show_painter_images) ? (bool) $order->is_show_painter_images : null,
            ],
            'sa' => [
                'conversation_id' => (string) ($order->sa_conversation_id ?? ''),
                'client_phone' => (string) ($order->sa_client_phone ?? ''),
                'bot_mode' => (string) ($order->sa_bot_mode ?? ''),
            ],
        ];
    }

    private function isLookupCompanyOrder($order): bool
    {
        if ($this->cleanLookupScalar($order->ur_name ?? null) !== null) {
            return true;
        }

        foreach ([
            'ur_name_l',
            'ur_reg_num',
            'ur_legal_addr',
            'ur_pnr_nr',
            'ur_bank_name',
            'ur_bank_code',
            'ur_bank_acc_code',
            'billing_invoice_uuid',
        ] as $field) {
            if ($this->cleanLookupScalar($order->{$field} ?? null) !== null) {
                return true;
            }
        }

        return false;
    }

    private function extractLookupProducts(array $items): array
    {
        $products = [];

        foreach ($items as $key => $item) {
            if (!is_numeric($key) || !is_array($item)) {
                continue;
            }

            $name = (string) (
                data_get($item, 'title')
                ?: data_get($item, 'name')
                ?: data_get($item, 'product_name')
                ?: data_get($item, 'productName')
                ?: data_get($item, 'service_name')
                ?: data_get($item, 'type')
                ?: 'Product'
            );
            $size = (string) (
                data_get($item, 'size_name')
                ?: data_get($item, 'size')
                ?: data_get($item, 'show.size')
                ?: data_get($item, 'params.size')
                ?: ''
            );
            $comment = (string) (
                data_get($item, 'userComment')
                ?: data_get($item, 'user_comment')
                ?: data_get($item, 'comment')
                ?: data_get($item, 'comments')
                ?: ''
            );

            $products[] = [
                'name' => $name,
                'service_id' => $this->resolveLookupProductServiceId($item),
                'quantity' => (int) (data_get($item, 'count') ?: data_get($item, 'qty') ?: data_get($item, 'quantity') ?: 1),
                'size' => $size,
                'size_name' => (string) (data_get($item, 'size_name') ?: $size),
                'price' => $this->lookupNumber(data_get($item, 'sumPrice') ?: data_get($item, 'sum_price') ?: data_get($item, 'total_item_price') ?: data_get($item, 'price')),
                'unit_price' => $this->lookupNumber(data_get($item, 'price')),
                'original_item_price' => $this->lookupNumber(data_get($item, 'total_item_price')),
                'comment' => $comment,
                'image' => $this->resolveLookupProductImage($item),
                'original_images' => $this->normalizeLookupImageList(data_get($item, 'orig_images')),
                'options' => [
                    'form' => $this->cleanLookupScalar(data_get($item, 'form') ?: data_get($item, 'form_id') ?: data_get($item, 'formId')),
                    'canvas' => $this->cleanLookupScalar(data_get($item, 'canvas') ?: data_get($item, 'holst') ?: data_get($item, 'holst_id')),
                    'packaging' => $this->cleanLookupScalar(data_get($item, 'pack') ?: data_get($item, 'packaging') ?: data_get($item, 'compl_id')),
                    'production' => $this->cleanLookupScalar(data_get($item, 'terms') ?: data_get($item, 'production') ?: data_get($item, 'production_mode')),
                ],
            ];
        }

        return $products;
    }

    private function resolveLookupProductServiceId(array $item): string
    {
        $explicit = trim((string) (data_get($item, 'sa_service_id') ?: data_get($item, 'service_id') ?: ''));
        if ($explicit !== '') {
            return $explicit;
        }

        $pid = trim((string) data_get($item, 'pid', ''));
        $name = strtolower(trim((string) data_get($item, 'name', '')));
        if ($name !== '') {
            $nameMap = [
                'canvas' => 'HM-2',
                'collage' => 'HM-3',
                'caricature' => 'HM-27',
                'custom caricature' => 'HM-27',
                'simpsons' => 'HM-47',
            ];
            if (isset($nameMap[$name])) {
                return $nameMap[$name];
            }
        }

        if ($pid === '' || strtolower($pid) === 'undefined') {
            return '';
        }

        $legacyMap = [
            '1' => 'HM-2', // Canvas uses internal basket pid=1, header_menu id=2.
            '2' => 'HM-3', // Collage uses internal basket pid=2, header_menu id=3.
            '5' => 'GC-5',
        ];
        if (isset($legacyMap[$pid])) {
            return $legacyMap[$pid];
        }

        if (ctype_digit($pid)) {
            $serviceId = 'HM-' . $pid;
            if ($this->resolveHeaderMenuItemByServiceId($serviceId) !== null) {
                return $serviceId;
            }
        }

        return '';
    }

    private function resolveLookupProductImage(array $item)
    {
        $image = $this->cleanLookupScalar(
            data_get($item, 'activeImage')
            ?: data_get($item, 'image')
            ?: data_get($item, 'img')
            ?: data_get($item, 'photo')
            ?: data_get($item, 'preview')
            ?: data_get($item, 'savedImage')
        );
        if ($image !== null) {
            return $image;
        }

        $originalImages = $this->normalizeLookupImageList(data_get($item, 'orig_images'));

        return $originalImages[0] ?? null;
    }

    private function normalizeLookupImageList($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $images = [];
        foreach ($value as $image) {
            $image = $this->cleanLookupScalar($image);
            if ($image !== null) {
                $images[] = $image;
            }
        }

        return $images;
    }

    private function splitLookupCsvImages($value): array
    {
        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $images = [];
        foreach (explode(',', $value) as $image) {
            $image = $this->cleanLookupScalar($image);
            if ($image !== null) {
                $images[] = $image;
            }
        }

        return $images;
    }

    private function decodeJsonAssoc($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function lookupNumber($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return round((float) $value, 2);
        }
        if (is_string($value)) {
            $normalized = preg_replace('/[^\d,.\-]+/', '', $value);
            $normalized = str_replace(',', '.', is_string($normalized) ? $normalized : '');

            return is_numeric($normalized) ? round((float) $normalized, 2) : null;
        }

        return null;
    }

    private function cleanLookupScalar($value)
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '' || strtolower($value) === 'undefined' || strtolower($value) === 'null') {
            return null;
        }

        return $value;
    }

    private function lookupDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toIso8601String();
        } catch (\Exception $e) {
            return (string) $value;
        }
    }

    private function lookupOrderStatusTitle(string $status, string $lang = 'ru'): string
    {
        $lang = $this->normalizeLookupLang($lang);
        $labels = [
            'ru' => [
                'new' => 'Новый',
                'watching' => 'На рассмотрении',
                'pegging' => 'В процессе',
                'in_production' => 'В производстве',
                'sended' => 'Отправлен',
                'send_lubanas' => 'Отправлен на Лубанас 65',
                'completed' => 'Завершен',
            ],
            'en' => [
                'new' => 'New',
                'watching' => 'Under review',
                'pegging' => 'In progress',
                'in_production' => 'In production',
                'sended' => 'Shipped',
                'send_lubanas' => 'Sent to Lubanas 65',
                'completed' => 'Completed',
            ],
            'lv' => [
                'new' => 'Jauns',
                'watching' => 'Izskatīšanā',
                'pegging' => 'Procesā',
                'in_production' => 'Ražošanā',
                'sended' => 'Nosūtīts',
                'send_lubanas' => 'Nosūtīts uz Lubānas 65',
                'completed' => 'Pabeigts',
            ],
            'lt' => [
                'new' => 'Naujas',
                'watching' => 'Peržiūrima',
                'pegging' => 'Vykdoma',
                'in_production' => 'Gamyboje',
                'sended' => 'Išsiųsta',
                'send_lubanas' => 'Išsiųsta į Lubanas 65',
                'completed' => 'Užbaigta',
            ],
            'ee' => [
                'new' => 'Uus',
                'watching' => 'Ülevaatamisel',
                'pegging' => 'Töös',
                'in_production' => 'Tootmises',
                'sended' => 'Saadetud',
                'send_lubanas' => 'Saadetud Lubanas 65',
                'completed' => 'Lõpetatud',
            ],
            'de' => [
                'new' => 'Neu',
                'watching' => 'In Prüfung',
                'pegging' => 'In Bearbeitung',
                'in_production' => 'In Produktion',
                'sended' => 'Versendet',
                'send_lubanas' => 'An Lubanas 65 gesendet',
                'completed' => 'Abgeschlossen',
            ],
            'pl' => [
                'new' => 'Nowe',
                'watching' => 'W trakcie sprawdzania',
                'pegging' => 'W toku',
                'in_production' => 'W produkcji',
                'sended' => 'Wysłane',
                'send_lubanas' => 'Wysłane do Lubanas 65',
                'completed' => 'Zakończone',
            ],
        ];

        return $labels[$lang][$status] ?? $labels['ru'][$status] ?? $status;
    }

    private function lookupPaymentStatusTitle(string $status, string $lang = 'ru'): string
    {
        $lang = $this->normalizeLookupLang($lang);
        $labels = [
            'ru' => [
                'not_payed' => 'Не оплачено',
                'payed' => 'Оплачено',
                'prepayment' => 'Предоплата',
            ],
            'en' => [
                'not_payed' => 'Not paid',
                'payed' => 'Paid',
                'prepayment' => 'Prepayment',
            ],
            'lv' => [
                'not_payed' => 'Nav apmaksāts',
                'payed' => 'Apmaksāts',
                'prepayment' => 'Priekšapmaksa',
            ],
            'lt' => [
                'not_payed' => 'Neapmokėta',
                'payed' => 'Apmokėta',
                'prepayment' => 'Išankstinis apmokėjimas',
            ],
            'ee' => [
                'not_payed' => 'Maksmata',
                'payed' => 'Makstud',
                'prepayment' => 'Ettemaks',
            ],
            'de' => [
                'not_payed' => 'Nicht bezahlt',
                'payed' => 'Bezahlt',
                'prepayment' => 'Vorauszahlung',
            ],
            'pl' => [
                'not_payed' => 'Nieopłacone',
                'payed' => 'Opłacone',
                'prepayment' => 'Przedpłata',
            ],
        ];

        return $labels[$lang][$status] ?? $labels['ru'][$status] ?? $status;
    }

    private function normalizeLookupLang(string $lang): string
    {
        $lang = strtolower(trim($lang));
        if ($lang === 'uk') {
            return 'ru';
        }

        return in_array($lang, ['ru', 'en', 'lv', 'lt', 'ee', 'de', 'pl'], true) ? $lang : 'ru';
    }

    private function validationError($validator): JsonResponse
    {
        $details = [];
        foreach ($validator->errors()->toArray() as $field => $messages) {
            $details[] = [
                'field' => $field,
                'issue' => $messages[0] ?? 'invalid',
            ];
        }

        return response()->json([
            'status' => 'error',
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => $validator->errors()->first(),
                'details' => $details,
            ],
        ], 400);
    }

    private function validationErrorFromDetails(array $details, string $message = ''): JsonResponse
    {
        $normalized = [];
        foreach ($details as $detail) {
            $normalized[] = [
                'field' => (string) ($detail['field'] ?? 'field'),
                'issue' => (string) ($detail['issue'] ?? 'invalid'),
            ];
        }

        return response()->json([
            'status' => 'error',
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => $message !== '' ? $message : (string) ($normalized[0]['issue'] ?? 'VALIDATION_ERROR'),
                'details' => $normalized,
            ],
        ], 400);
    }

    /**
     * Normalizes phone values in selected payload paths:
     * keeps optional leading plus and strips all non-digit characters.
     */
    private function normalizePhoneFields(Request $request, array $paths): Request
    {
        $payload = $request->all();

        foreach ($paths as $path) {
            $raw = data_get($payload, $path);
            if (!is_string($raw)) {
                continue;
            }

            data_set($payload, $path, $this->sanitizePhoneValue($raw));
        }

        $request->replace($payload);

        return $request;
    }

    private function sanitizePhoneValue(string $phone): string
    {
        $trimmed = trim($phone);
        if ($trimmed === '') {
            return '';
        }

        $hasLeadingPlus = strpos($trimmed, '+') === 0;
        $digits = preg_replace('/\D+/', '', $trimmed);
        if (!is_string($digits) || $digits === '') {
            return '';
        }

        return $hasLeadingPlus ? ('+' . $digits) : $digits;
    }

    private function applyLeadPricingToOrderPayload(array $basket, array $checkoutParams, array $context, \App\Models\User $user): array
    {
        $pricing = (array) data_get($context, 'pricing', []);
        if (empty($pricing)) {
            return [
                'basket' => $basket,
                'checkout_params' => $checkoutParams,
            ];
        }

        $couponCode = trim((string) ($pricing['coupon_code'] ?? ''));
        $useBonus = filter_var($pricing['use_bonus'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if ($couponCode === '' && !$useBonus) {
            return [
                'basket' => $basket,
                'checkout_params' => $checkoutParams,
            ];
        }

        if ($couponCode !== '' && $useBonus) {
            throw new \InvalidArgumentException(json_encode([
                'message' => 'Нельзя одновременно передавать coupon_code и use_bonus=true',
                'details' => [
                    [
                        'field' => 'lead.pricing',
                        'issue' => 'Нельзя одновременно передавать coupon_code и use_bonus=true',
                    ],
                ],
            ], JSON_UNESCAPED_UNICODE));
        }

        $basket = $this->normalizeLeadBasketTotals($basket);
        $checkoutParams['deliv_price'] = round((float) ($checkoutParams['deliv_price'] ?? 0), 2);

        if ($useBonus) {
            $userBonuses = (int) ($user->bonuses ?? 0);
            if ($userBonuses <= 0) {
                throw new \InvalidArgumentException(json_encode([
                    'message' => 'У пользователя нет доступных бонусов для списания',
                    'details' => [
                        [
                            'field' => 'lead.pricing.use_bonus',
                            'issue' => 'У пользователя нет доступных бонусов для списания',
                        ],
                    ],
                ], JSON_UNESCAPED_UNICODE));
            }

            $basket['spend_bonus'] = 1;
            $basket['sale_price'] = $this->calculateBonusSalePrice((float) ($basket['totalPrice'] ?? 0), $userBonuses);

            return [
                'basket' => $basket,
                'checkout_params' => $checkoutParams,
            ];
        }

        $coupon = DB::table('coupons')->where('text', $couponCode)->first();
        if (!$coupon) {
            throw new \InvalidArgumentException(json_encode([
                'message' => 'Купон не найден',
                'details' => [
                    [
                        'field' => 'lead.pricing.coupon_code',
                        'issue' => 'Купон не найден',
                    ],
                ],
            ], JSON_UNESCAPED_UNICODE));
        }

        $couponType = $this->resolveCouponTypeForLead($coupon, $couponCode, $user);
        if ($couponType === null) {
            throw new \InvalidArgumentException(json_encode([
                'message' => 'Купон не подходит для текущего пользователя',
                'details' => [
                    [
                        'field' => 'lead.pricing.coupon_code',
                        'issue' => 'Купон не подходит для текущего пользователя',
                    ],
                ],
            ], JSON_UNESCAPED_UNICODE));
        }

        $basket['coupon_id'] = (int) $coupon->id;
        $basket['coupon_type'] = $couponType;
        $basket['coupon_val'] = (string) ($coupon->value ?? '');
        $basket['sale_price'] = $this->calculateCouponSalePrice($basket);

        if ($couponType === 'free_delivery') {
            $checkoutParams['deliv_price'] = 0.0;
        }

        return [
            'basket' => $basket,
            'checkout_params' => $checkoutParams,
        ];
    }

    private function normalizeLeadBasketTotals(array $basket): array
    {
        $total = 0.0;
        $withLabels = 0.0;
        $withoutLabels = 0.0;

        foreach ($basket as $key => $item) {
            if (!is_array($item) || isset($item['totalPrice'])) {
                continue;
            }

            $count = (int) ($item['count'] ?? 1);
            if ($count <= 0) {
                $count = 1;
            }

            $sumPrice = isset($item['sumPrice']) && is_numeric($item['sumPrice'])
                ? (float) $item['sumPrice']
                : round((float) ($item['price'] ?? 0) * $count, 2);

            $basket[$key]['sumPrice'] = $sumPrice;
            $total += $sumPrice;

            if (!empty($item['has_special_label'])) {
                $withLabels += $sumPrice;
            } else {
                $withoutLabels += $sumPrice;
            }
        }

        $basket['totalPrice'] = round($total, 2);
        $basket['totalPriceWithLabels'] = round($withLabels, 2);
        $basket['totalPriceWithoutLabels'] = round($withoutLabels, 2);
        $basket['formatedTotalPrice'] = rtrim(rtrim(number_format($basket['totalPrice'], 2, ',', ' '), '0'), ',') . ' €';

        return $basket;
    }

    private function calculateBonusSalePrice(float $totalPrice, int $bonuses): string
    {
        $discounted = $totalPrice - $bonuses;
        if ($discounted < 0) {
            $discounted = 0;
        }

        return number_format((float) $discounted, 2, '.', '');
    }

    private function resolveCouponTypeForLead(object $coupon, string $couponCode, \App\Models\User $user): ?string
    {
        $couponType = 'none';

        if (!empty($coupon->is_dates_sale)) {
            $couponType = 'date';
        }
        if (!empty($coupon->is_30_40_free)) {
            $couponType = '30_40';
        }
        if (!empty($coupon->is_universal)) {
            $couponType = 'universal';
        }
        if (!empty($coupon->is_facebook)) {
            $couponType = 'facebook';
        }
        if (!empty($coupon->is_1free)) {
            $couponType = '1free';
        }
        if (!empty($coupon->free_delivery)) {
            $couponType = 'free_delivery';
        }
        if (!empty($coupon->is_40_60)) {
            $couponType = '40_60';
        }
        if (!empty($coupon->is_abandoned_basket)) {
            $couponType = 'abandoned_basket';
        }
        if (!empty($coupon->is_giftcard)) {
            $couponType = 'giftcard';
        }

        if ($couponType === 'none' && (int) ($user->is_active_friend_inv ?? 0) === 0) {
            $friend = DB::table('users')->where('inv_sale_code', $couponCode)->first();
            if ($friend && (int) $friend->id === (int) ($coupon->user_id ?? 0) && (int) ($coupon->user_id ?? 0) !== (int) $user->id) {
                $couponType = 'friend';
            } else {
                return null;
            }
        }

        return $couponType === 'none' ? null : $couponType;
    }

    private function calculateCouponSalePrice(array $basket): string
    {
        $total = (float) ($basket['totalPrice'] ?? 0);
        $withoutLabels = (float) ($basket['totalPriceWithoutLabels'] ?? $total);
        $withLabels = (float) ($basket['totalPriceWithLabels'] ?? 0);
        $couponType = (string) ($basket['coupon_type'] ?? '');
        $couponValue = (string) ($basket['coupon_val'] ?? '');

        if ($couponType === 'date') {
            $discountedPrice = $withoutLabels;
            if ($discountedPrice < 21 && $discountedPrice > 0) {
                return number_format($discountedPrice - ($discountedPrice * (30 / 100)) + $withLabels, 2, '.', '');
            }
            if ($discountedPrice > 20 && $discountedPrice < 31) {
                return number_format($discountedPrice - ($discountedPrice * (20 / 100)) + $withLabels, 2, '.', '');
            }
            if ($discountedPrice > 30 && $discountedPrice < 100) {
                return number_format($discountedPrice - ($discountedPrice * (10 / 100)) + $withLabels, 2, '.', '');
            }
            if ($discountedPrice > 99) {
                return number_format($discountedPrice - ($discountedPrice * (5 / 100)) + $withLabels, 2, '.', '');
            }

            return number_format($total, 2, '.', '');
        }

        if ($couponType === 'friend') {
            return number_format(($withoutLabels - 5) + $withLabels, 2, '.', '');
        }

        if ($couponType === 'facebook') {
            return number_format(($withoutLabels - ($withoutLabels * (2 / 100))) + $withLabels, 2, '.', '');
        }

        if ($couponType === '30_40') {
            $sale = null;
            foreach ($basket as $basketItem) {
                if (!is_array($basketItem)) {
                    continue;
                }
                if (
                    isset($basketItem['sizeId'], $basketItem['name']) &&
                    (string) $basketItem['sizeId'] === '30x40' &&
                    (string) $basketItem['name'] === 'Canvas'
                ) {
                    $itemPrice = (float) ($basketItem['price'] ?? 0);
                    $sale = $sale === null ? $itemPrice : min($sale, $itemPrice);
                }
            }

            return number_format($sale !== null ? $total - $sale : $total, 2, '.', '');
        }

        if ($couponType === '40_60') {
            $haveSale = false;
            foreach ($basket as $basketItem) {
                if (!is_array($basketItem)) {
                    continue;
                }
                if (isset($basketItem['sizeId']) && in_array((string) $basketItem['sizeId'], ['80x120', '120x80'], true)) {
                    $haveSale = true;
                }
            }

            $sale = null;
            if ($haveSale) {
                foreach ($basket as $basketItem) {
                    if (!is_array($basketItem)) {
                        continue;
                    }
                    if (
                        isset($basketItem['sizeId'], $basketItem['name']) &&
                        in_array((string) $basketItem['sizeId'], ['40x60', '60x40'], true) &&
                        (string) $basketItem['name'] === 'Canvas'
                    ) {
                        $itemPrice = (float) ($basketItem['price'] ?? 0);
                        $sale = $sale === null ? $itemPrice : min($sale, $itemPrice);
                    }
                }
            }

            return number_format($sale !== null ? $total - $sale : $total, 2, '.', '');
        }

        if ($couponType === '1free') {
            $count = 0;
            foreach ($basket as $basketItem) {
                if (is_array($basketItem) && isset($basketItem['sumPrice'])) {
                    $count += (int) ($basketItem['count'] ?? 1);
                }
            }

            if ($count >= 4) {
                $sale = null;
                foreach ($basket as $basketItem) {
                    if (!is_array($basketItem) || !isset($basketItem['price'])) {
                        continue;
                    }
                    $itemPrice = (float) $basketItem['price'];
                    $sale = $sale === null ? $itemPrice : min($sale, $itemPrice);
                }

                return number_format($sale !== null ? $total - $sale : $total, 2, '.', '');
            }

            return number_format($total, 2, '.', '');
        }

        if (in_array($couponType, ['universal', 'abandoned_basket', 'giftcard'], true)) {
            $discountedPrice = $withoutLabels;
            if (strpos($couponValue, '%') !== false) {
                $numeric = str_replace('%', '', $couponValue);
                $numeric = str_replace(',', '.', $numeric);
                $discountedPrice = $discountedPrice * (100 - (float) $numeric) / 100;
            } else {
                $discountedPrice = $discountedPrice - (float) $couponValue;
            }
            if ($discountedPrice < 1) {
                $discountedPrice = 0;
            }

            return number_format($discountedPrice + $withLabels, 2, '.', '');
        }

        if ($couponType === 'free_delivery') {
            return number_format($total, 2, '.', '');
        }

        return number_format($total, 2, '.', '');
    }

    private function resolveCountryPricingContext(Request $request): array
    {
        $rawCountryCode = (string) ($request->input('country_code')
            ?? $request->input('country')
            ?? $request->header('X-Country-Code')
            ?? '');

        $countryCode = strtoupper(trim($rawCountryCode));
        $country = null;

        if ($countryCode !== '') {
            $country = DB::table('country_tels')
                ->whereRaw('UPPER(country_code) = ?', [$countryCode])
                ->first(['country_code', 'price_country_mltpr']);
        }

        if (!$country) {
            $clientPhone = (string) ($request->input('client_phone')
                ?: $request->input('phone')
                ?: data_get($request->all(), 'lead.client.phone')
                ?: data_get($request->all(), 'data.client.phone'));

            $country = $this->resolveCountryRowByPhone($clientPhone);
        }

        if (!$country) {
            $country = DB::table('country_tels')
                ->whereRaw('UPPER(country_code) = ?', ['LV'])
                ->first(['country_code', 'price_country_mltpr']);
        }

        if (!$country) {
            $country = DB::table('country_tels')
                ->orderBy('sort')
                ->first(['country_code', 'price_country_mltpr']);
        }

        $resolvedCountryCode = strtoupper((string) ($country->country_code ?? ($countryCode !== '' ? $countryCode : 'LV')));
        $multiplier = (float) ($country->price_country_mltpr ?? 1);
        if ($multiplier <= 0) {
            $multiplier = 1.0;
        }

        return [
            'country_code' => $resolvedCountryCode,
            'multiplier' => $multiplier,
        ];
    }

    private function resolveCountryRowByPhone(string $phone): ?object
    {
        $normalized = $this->sanitizePhoneValue($phone);
        if ($normalized === '' || !Schema::hasTable('country_tels')) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $normalized);
        if ($digits === '') {
            return null;
        }

        $rows = DB::table('country_tels')
            ->whereNotNull('phone_code')
            ->get(['country_code', 'price_country_mltpr', 'phone_code']);

        $best = null;
        $bestLength = -1;

        foreach ($rows as $row) {
            $phoneCodeDigits = preg_replace('/\D+/', '', (string) ($row->phone_code ?? ''));
            if ($phoneCodeDigits === '') {
                continue;
            }

            if (strpos($digits, $phoneCodeDigits) !== 0) {
                continue;
            }

            $length = strlen($phoneCodeDigits);
            if ($length > $bestLength) {
                $best = $row;
                $bestLength = $length;
            }
        }

        return $best;
    }

    private function buildCatalogFromRealSources(string $lang, string $countryCode, float $countryMultiplier): array
    {
        $items = HeaderMenu::withTranslation($lang, false)
            ->whereIn('menu_pos', [1, 2])
            ->orderBy('menu_pos')
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        if ($items->isEmpty()) {
            return $this->buildCatalog($lang);
        }

        $categories = collect([
            [
                'id' => 'CAT-PORTRAITS',
                'name' => $this->resolveMenuCategoryName(1, $lang),
                'order' => 10,
            ],
            [
                'id' => 'CAT-CANVAS',
                'name' => $this->resolveMenuCategoryName(2, $lang),
                'order' => 20,
            ],
        ]);

        $services = [];
        foreach ($items as $item) {
            $categoryId = ((int) $item->menu_pos) === 1 ? 'CAT-PORTRAITS' : 'CAT-CANVAS';
            $localizedTitle = $this->resolveHeaderMenuLocalizedValue((int) $item->id, 'title', $lang, (string) $item->title);
            $localizedLink = $this->resolveHeaderMenuLocalizedValue((int) $item->id, 'link', $lang, (string) $item->link);
            $path = $this->normalizeMenuPath($localizedLink);
            $servicePrice = $this->resolveServicePriceByPath($path, $countryMultiplier);
            $serviceName = trim(strip_tags($localizedTitle));

            $services[] = [
                'id' => 'HM-' . (int) $item->id,
                'category_id' => $categoryId,
                'name' => $serviceName !== '' ? $serviceName : ('Service #' . (int) $item->id),
                'short_description' => $path,
                'description' => 'Menu link: ' . $localizedLink,
                'price' => [
                    'type' => 'fixed',
                    'amount' => $servicePrice,
                ],
                'duration_minutes' => null,
                'availability' => [
                    'booking_required' => false,
                    'lead_time_hours' => 0,
                ],
                'constraints' => [
                    'requires_address' => false,
                    'requires_files' => true,
                ],
                'faq' => [],
                'tags' => $this->buildServiceTagsByPath($path),
                'is_active' => ((int) $item->is_show) === 1,
                'updated_at' => Carbon::parse($item->updated_at ?: now())->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z'),
            ];
        }

        $giftCardService = $this->buildGiftCardCatalogService($lang);
        if ($giftCardService !== null) {
            $categories->push([
                'id' => 'CAT-GIFTS',
                'name' => $this->resolveMenuCategoryName(3, $lang),
                'order' => 30,
            ]);
            $services[] = $giftCardService;
        }

        $familyConstructorService = $this->buildFamilyConstructorCatalogService($lang, $countryMultiplier);
        if ($familyConstructorService !== null) {
            $services[] = $familyConstructorService;
        }

        return [
            'categories' => $categories->values()->all(),
            'services' => $services,
            'bundles' => [],
        ];
    }

    private function resolveHeaderMenuLocalizedValue(int $menuId, string $column, string $lang, string $fallback): string
    {
        $fallback = trim($fallback);
        if ($menuId <= 0 || $column === '' || $lang === '') {
            return $fallback;
        }

        if (!Schema::hasTable('translations')) {
            return $fallback;
        }

        $translated = DB::table('translations')
            ->where('table_name', 'header_menu')
            ->where('column_name', $column)
            ->where('foreign_key', $menuId)
            ->where('locale', $lang)
            ->value('value');

        $translated = trim((string) $translated);
        if ($translated !== '') {
            return $translated;
        }

        // Contract rule: if `uk` translation is missing, use `ru` translation.
        if (strtolower($lang) === 'uk') {
            $ruTranslated = DB::table('translations')
                ->where('table_name', 'header_menu')
                ->where('column_name', $column)
                ->where('foreign_key', $menuId)
                ->where('locale', 'ru')
                ->value('value');

            $ruTranslated = trim((string) $ruTranslated);
            if ($ruTranslated !== '') {
                return $ruTranslated;
            }
        }

        return $fallback;
    }

    private function resolveServicePriceByPath(string $path, float $countryMultiplier): float
    {
        $basePrice = null;

        if ($path === '/new/gift-card') {
            $giftCardNominals = $this->resolveGiftCardNominalMap();
            $firstKey = (string) array_key_first($giftCardNominals);
            $basePrice = $firstKey !== '' ? (float) ($giftCardNominals[$firstKey]['amount'] ?? 0) : 0.0;

            return (float) round(max(0, (float) $basePrice), 2);
        }

        if ($path === '/family-constructor') {
            $basePrice = $this->extractMinConfiguredPrice($this->resolveFamilyConstructorSizesRaw(), null);
        } elseif ($path === '/new/canvas') {
            $row = DB::table('canvas_header')->orderBy('id')->first(['sizes_30x40', 'sizes_38x38', 'sizes_40x30', 'sizes_60x30']);
            $basePrice = $this->extractMinConfiguredPrice(
                implode(',', array_filter([
                    (string) ($row->sizes_30x40 ?? ''),
                    (string) ($row->sizes_38x38 ?? ''),
                    (string) ($row->sizes_40x30 ?? ''),
                    (string) ($row->sizes_60x30 ?? ''),
                ])),
                null
            );
        } elseif ($path === '/collage') {
            $row = DB::table('a_collage_head')->orderBy('id')->first(['sizes_30x40', 'sizes_38x38', 'sizes_40x30']);
            $basePrice = $this->extractMinConfiguredPrice(
                implode(',', array_filter([
                    (string) ($row->sizes_30x40 ?? ''),
                    (string) ($row->sizes_38x38 ?? ''),
                    (string) ($row->sizes_40x30 ?? ''),
                ])),
                null
            );
        } elseif ($path === '/modular-generator') {
            $basePrice = $this->resolveMinGalleryPriceByQuery(function ($query) {
                $query->where('id_type', 2);
            });
        } elseif ($path === '/new/caricature') {
            $basePrice = $this->resolveGalleryItemPriceBySlug('caricature');
            if ($basePrice === null) {
                $basePrice = $this->resolveMinGalleryPriceByQuery(function ($query) {
                    $query->where('is_sharj', 1);
                });
            }
        } elseif ($path === '/simpsons') {
            $basePrice = $this->resolveGalleryItemPriceBySlug('simpsons-portrait');
            if ($basePrice === null) {
                $basePrice = $this->resolveMinGalleryPriceByQuery(function ($query) {
                    $query->where('slug', 'simpsons');
                });
            }
        } elseif ($path === '/new/gallery') {
            $basePrice = $this->resolveMinGalleryPriceByQuery(function ($query) {
                $query->whereIn('id_type', [2, 3, 4]);
            });
        } elseif (strpos($path, '/new/graphic-portrait/') === 0) {
            $slug = trim((string) substr($path, strlen('/new/graphic-portrait/')));
            $basePrice = $this->resolveGalleryItemPriceBySlug($slug);
        }

        if ($basePrice === null || $basePrice <= 0) {
            $basePrice = $this->resolveMinGalleryPriceByQuery(function ($query) {
            }) ?: 0.0;
        }

        return (float) round($basePrice * max($countryMultiplier, 0.0001), 2);
    }

    private function resolveHeaderMenuItemByServiceId(string $serviceId): ?object
    {
        if ($this->isGiftCardServiceId($serviceId)) {
            return $this->resolveGiftCardServiceStub();
        }

        if ($this->isFamilyConstructorServiceId($serviceId)) {
            return $this->resolveFamilyConstructorServiceStub();
        }

        if (!preg_match('/^HM-(\d+)$/', $serviceId, $matches)) {
            return null;
        }

        $menuId = (int) $matches[1];
        if ($menuId <= 0) {
            return null;
        }

        return DB::table('header_menu')
            ->where('id', $menuId)
            ->first(['id', 'title', 'link', 'menu_pos', 'is_show', 'updated_at']);
    }

    /**
     * @return array<string, float>
     */
    private function resolveServiceSizePriceMapByPath(string $path): array
    {
        if ($path === '/new/gift-card') {
            $nominals = $this->resolveGiftCardNominalMap();
            $map = [];
            foreach ($nominals as $key => $payload) {
                $map[$key] = (float) ($payload['amount'] ?? 0);
            }

            return $map;
        }

        if ($path === '/family-constructor') {
            $rawSizes = $this->resolveFamilyConstructorSizesRaw();
            return $this->parseSizePriceMap($rawSizes);
        }

        if ($path === '/new/canvas') {
            $row = DB::table('canvas_header')->orderBy('id')->first(['sizes_30x40', 'sizes_38x38', 'sizes_40x30', 'sizes_60x30']);
            if (!$row) {
                return [];
            }
            return $this->parseSizePriceMap(implode(',', array_filter([
                (string) ($row->sizes_30x40 ?? ''),
                (string) ($row->sizes_38x38 ?? ''),
                (string) ($row->sizes_40x30 ?? ''),
                (string) ($row->sizes_60x30 ?? ''),
            ])));
        }

        if ($path === '/collage') {
            $row = DB::table('a_collage_head')->orderBy('id')->first(['sizes_30x40', 'sizes_38x38', 'sizes_40x30']);
            if (!$row) {
                return [];
            }
            return $this->parseSizePriceMap(implode(',', array_filter([
                (string) ($row->sizes_30x40 ?? ''),
                (string) ($row->sizes_38x38 ?? ''),
                (string) ($row->sizes_40x30 ?? ''),
            ])));
        }

        if ($path === '/new/caricature') {
            $map = $this->resolveSizeMapByGallerySlug('caricature');
            if (!empty($map)) {
                return $map;
            }
            return $this->resolveMergedSizeMapByGalleryQuery(function ($query) {
                $query->where('is_sharj', 1);
            });
        }

        if ($path === '/simpsons') {
            $map = $this->resolveSizeMapByGallerySlug('simpsons-portrait');
            if (!empty($map)) {
                return $map;
            }
            return $this->resolveMergedSizeMapByGalleryQuery(function ($query) {
                $query->where('slug', 'simpsons');
            });
        }

        if (strpos($path, '/new/graphic-portrait/') === 0) {
            $slug = trim((string) substr($path, strlen('/new/graphic-portrait/')));
            return $this->resolveSizeMapByGallerySlug($slug);
        }

        if ($path === '/modular-generator') {
            return $this->resolveMergedSizeMapByGalleryQuery(function ($query) {
                $query->where('id_type', 2);
            });
        }

        if ($path === '/new/gallery') {
            return $this->resolveMergedSizeMapByGalleryQuery(function ($query) {
                $query->whereIn('id_type', [2, 3, 4]);
            });
        }

        return [];
    }

    /**
     * @return array<string, float>
     */
    private function resolveSizeMapByGallerySlug(string $slug): array
    {
        if ($slug === '') {
            return [];
        }

        $row = DB::table('gallery_items')
            ->where('active', 1)
            ->where('slug', $slug)
            ->orderBy('id')
            ->first(['custom_size_prices']);

        if (!$row) {
            return [];
        }

        return $this->parseSizePriceMap((string) ($row->custom_size_prices ?? ''));
    }

    /**
     * @return array<string, float>
     */
    private function resolveMergedSizeMapByGalleryQuery(callable $applier): array
    {
        $query = DB::table('gallery_items')->where('active', 1);
        $applier($query);

        $rows = $query->get(['custom_size_prices']);
        if ($rows->isEmpty()) {
            return [];
        }

        $merged = [];
        foreach ($rows as $row) {
            $map = $this->parseSizePriceMap((string) ($row->custom_size_prices ?? ''));
            foreach ($map as $size => $price) {
                if (!isset($merged[$size]) || $price < $merged[$size]) {
                    $merged[$size] = $price;
                }
            }
        }

        ksort($merged);
        return $merged;
    }

    private function hasRequestedGalleryCatalogItem(array $source): bool
    {
        return !empty($this->resolveRequestedGalleryCatalogItemReference($source)['provided']);
    }

    private function resolveRequestedGalleryCatalogItem(array $source): ?object
    {
        $reference = $this->resolveRequestedGalleryCatalogItemReference($source);
        $id = (int) ($reference['id'] ?? 0);
        if ($id <= 0) {
            return null;
        }

        return $this->resolveGalleryCatalogItemById($id);
    }

    /**
     * @return array{field: string, id: int, provided: bool}
     */
    private function resolveRequestedGalleryCatalogItemReference(array $source, string $fieldPrefix = ''): array
    {
        $options = (array) ($source['options'] ?? []);
        $candidates = [
            ['field' => $fieldPrefix . 'gallery_item_id', 'value' => $source['gallery_item_id'] ?? null],
            ['field' => $fieldPrefix . 'item_id', 'value' => $source['item_id'] ?? null],
            ['field' => $fieldPrefix . 'product_id', 'value' => $source['product_id'] ?? null],
            ['field' => $fieldPrefix . 'options.gallery_item_id', 'value' => $options['gallery_item_id'] ?? null],
            ['field' => $fieldPrefix . 'options.item_id', 'value' => $options['item_id'] ?? null],
            ['field' => $fieldPrefix . 'options.product_id', 'value' => $options['product_id'] ?? null],
        ];

        foreach ($candidates as $candidate) {
            $value = $candidate['value'];
            if ($value === null || $value === '') {
                continue;
            }

            return [
                'field' => (string) $candidate['field'],
                'id' => max(0, (int) $value),
                'provided' => true,
            ];
        }

        return [
            'field' => $fieldPrefix . 'options.gallery_item_id',
            'id' => 0,
            'provided' => false,
        ];
    }

    private function resolveGalleryCatalogItemById(int $galleryItemId): ?object
    {
        if ($galleryItemId <= 0 || !Schema::hasTable('gallery_items')) {
            return null;
        }

        $columns = array_values(array_unique(array_merge(
            ['id', 'id_type', 'name', 'slug', 'price_from', 'updated_at'],
            $this->gallerySizePricingColumns()
        )));

        return DB::table('gallery_items')
            ->where('active', 1)
            ->whereIn('id_type', [2, 3, 4])
            ->where('id', $galleryItemId)
            ->first($columns);
    }

    /**
     * @return array<string, float>
     */
    private function resolveSizeMapByGalleryItemRow(object $galleryItem): array
    {
        $details = $this->resolveSizePriceDetailsByGalleryItemRow($galleryItem);
        $result = [];
        foreach ($details as $size => $payload) {
            $result[$size] = (float) ($payload['current'] ?? 0);
        }

        return $result;
    }

    /**
     * @return array<string, array{current: float, original: ?float, is_discounted: bool}>
     */
    private function resolveServiceSizePriceDetailsByPath(string $path): array
    {
        if ($path === '/new/gift-card') {
            $nominals = $this->resolveGiftCardNominalMap();
            $map = [];
            foreach ($nominals as $key => $payload) {
                $amount = (float) ($payload['amount'] ?? 0);
                if ($amount <= 0) {
                    continue;
                }

                $map[$key] = [
                    'current' => $amount,
                    'original' => null,
                    'is_discounted' => false,
                ];
            }

            return $map;
        }

        if ($path === '/family-constructor') {
            return $this->parseSizePriceDetailsMap($this->resolveFamilyConstructorSizesRaw());
        }

        if ($path === '/new/canvas') {
            $row = DB::table('canvas_header')->orderBy('id')->first(['sizes_30x40', 'sizes_38x38', 'sizes_40x30', 'sizes_60x30']);
            if (!$row) {
                return [];
            }

            return $this->parseSizePriceDetailsMap(implode(',', array_filter([
                (string) ($row->sizes_30x40 ?? ''),
                (string) ($row->sizes_38x38 ?? ''),
                (string) ($row->sizes_40x30 ?? ''),
                (string) ($row->sizes_60x30 ?? ''),
            ])));
        }

        if ($path === '/collage') {
            $row = DB::table('a_collage_head')->orderBy('id')->first(['sizes_30x40', 'sizes_38x38', 'sizes_40x30']);
            if (!$row) {
                return [];
            }

            return $this->parseSizePriceDetailsMap(implode(',', array_filter([
                (string) ($row->sizes_30x40 ?? ''),
                (string) ($row->sizes_38x38 ?? ''),
                (string) ($row->sizes_40x30 ?? ''),
            ])));
        }

        if ($path === '/new/caricature') {
            $map = $this->resolveSizePriceDetailsByGallerySlug('caricature');
            if (!empty($map)) {
                return $map;
            }
            return $this->resolveMergedSizeDetailsByGalleryQuery(function ($query) {
                $query->where('is_sharj', '1');
            });
        }

        if ($path === '/simpsons') {
            $map = $this->resolveSizePriceDetailsByGallerySlug('simpsons-portrait');
            if (!empty($map)) {
                return $map;
            }
            $legacy = $this->resolveSizePriceDetailsByGallerySlug('simpsons');
            if (!empty($legacy)) {
                return $legacy;
            }
        }

        if (strpos($path, '/new/graphic-portrait/') === 0) {
            $slug = trim((string) substr($path, strlen('/new/graphic-portrait/')));
            $map = $this->resolveSizePriceDetailsByGallerySlug($slug);
            if (!empty($map)) {
                return $map;
            }
        }

        if ($path === '/modular-generator') {
            return $this->resolveMergedSizeDetailsByGalleryQuery(function ($query) {
                $query->where('id_type', 2);
            });
        }

        if ($path === '/new/gallery') {
            return $this->resolveMergedSizeDetailsByGalleryQuery(function ($query) {
                $query->whereIn('id_type', [2, 3, 4]);
            });
        }

        return [];
    }

    /**
     * @return array<string, array{current: float, original: ?float, is_discounted: bool}>
     */
    private function resolveSizePriceDetailsByGallerySlug(string $slug): array
    {
        if ($slug === '') {
            return [];
        }

        $row = DB::table('gallery_items')
            ->where('active', 1)
            ->where('slug', $slug)
            ->orderBy('id')
            ->first($this->gallerySizePricingColumns());

        if (!$row) {
            return [];
        }

        return $this->resolveSizePriceDetailsByGalleryItemRow($row);
    }

    /**
     * @return array<string, array{current: float, original: ?float, is_discounted: bool}>
     */
    private function resolveMergedSizeDetailsByGalleryQuery(callable $applier): array
    {
        $query = DB::table('gallery_items')->where('active', 1);
        $applier($query);

        $rows = $query->get($this->gallerySizePricingColumns());
        if ($rows->isEmpty()) {
            return [];
        }

        $merged = [];
        foreach ($rows as $row) {
            $map = $this->resolveSizePriceDetailsByGalleryItemRow($row);
            foreach ($map as $size => $details) {
                $current = (float) ($details['current'] ?? 0);
                if ($current <= 0) {
                    continue;
                }

                if (!isset($merged[$size]) || $current < (float) $merged[$size]['current']) {
                    $merged[$size] = $details;
                }
            }
        }

        ksort($merged);
        return $merged;
    }

    /**
     * @return array<string, array{current: float, original: ?float, is_discounted: bool}>
     */
    private function resolveSizePriceDetailsByGalleryItemRow(object $galleryItem): array
    {
        $baseMap = $this->parseSizePriceDetailsMap((string) ($galleryItem->custom_size_prices ?? ''));
        $saleRaw = (string) ($galleryItem->custom_size_prices_sale ?? '');
        if ($saleRaw === '' || !$this->isGallerySaleActive($galleryItem)) {
            return $baseMap;
        }

        $saleMap = $this->parseSizePriceDetailsMap($saleRaw);
        if (empty($saleMap)) {
            return $baseMap;
        }

        $merged = $baseMap;
        foreach ($saleMap as $size => $saleDetails) {
            $saleCurrent = (float) ($saleDetails['current'] ?? 0);
            if ($saleCurrent <= 0) {
                continue;
            }

            $baseCurrent = isset($baseMap[$size]) ? (float) ($baseMap[$size]['current'] ?? 0) : 0.0;
            $original = $baseCurrent > 0 ? $baseCurrent : (isset($saleDetails['original']) ? (float) $saleDetails['original'] : null);
            $merged[$size] = [
                'current' => $saleCurrent,
                'original' => $original,
                'is_discounted' => $original !== null && abs($original - $saleCurrent) > 0.0001,
            ];
        }

        ksort($merged);
        return $merged;
    }

    /**
     * @return array<string, float>
     */
    private function parseSizePriceMap(string $raw): array
    {
        $details = $this->parseSizePriceDetailsMap($raw);
        $result = [];
        foreach ($details as $size => $payload) {
            $result[$size] = (float) ($payload['current'] ?? 0);
        }

        return $result;
    }

    /**
     * @return array<string, array{current: float, original: ?float, is_discounted: bool}>
     */
    private function parseSizePriceDetailsMap(string $raw): array
    {
        if ($raw === '') {
            return [];
        }

        $result = [];
        $parts = explode(',', $raw);
        foreach ($parts as $part) {
            $chunk = trim((string) $part);
            if ($chunk === '') {
                continue;
            }

            if (!preg_match('/^([0-9]+\s*x\s*[0-9]+)\s*\[(.*?)\]/i', $chunk, $matches)) {
                continue;
            }

            $size = strtolower(str_replace(' ', '', $matches[1]));
            if (!preg_match('/^\d+x\d+$/', $size)) {
                continue;
            }

            preg_match_all('/\d+(?:[.,]\d+)?/', (string) $matches[2], $numMatches);
            $numbers = $numMatches[0] ?? [];
            if (empty($numbers)) {
                continue;
            }

            $currentRaw = end($numbers);
            $current = (float) str_replace(',', '.', (string) $currentRaw);
            if ($current <= 0) {
                continue;
            }

            $original = null;
            if (count($numbers) > 1) {
                $firstRaw = reset($numbers);
                $first = (float) str_replace(',', '.', (string) $firstRaw);
                if ($first > 0 && abs($first - $current) > 0.0001) {
                    $original = $first;
                }
            }

            if (!isset($result[$size]) || $current < (float) ($result[$size]['current'] ?? 0)) {
                $result[$size] = [
                    'current' => $current,
                    'original' => $original,
                    'is_discounted' => $original !== null,
                ];
            }
        }

        return $result;
    }

    /**
     * @return array<int, string>
     */
    private function gallerySizePricingColumns(): array
    {
        $columns = ['id', 'custom_size_prices'];

        if (Schema::hasColumn('gallery_items', 'custom_size_prices_sale')) {
            $columns[] = 'custom_size_prices_sale';
        }
        if (Schema::hasColumn('gallery_items', 'sale_end')) {
            $columns[] = 'sale_end';
        }

        return $columns;
    }

    private function isGallerySaleActive(object $galleryItem): bool
    {
        $saleRaw = trim((string) ($galleryItem->custom_size_prices_sale ?? ''));
        if ($saleRaw === '') {
            return false;
        }

        $saleEnd = $galleryItem->sale_end ?? null;
        if ($saleEnd === null || $saleEnd === '') {
            return true;
        }

        try {
            return Carbon::parse($saleEnd)->greaterThanOrEqualTo(Carbon::now());
        } catch (\Throwable $e) {
            return true;
        }
    }

    private function enrichCatalogServicePriceFromSizes(array $service): array
    {
        $sizes = collect((array) ($service['sizes'] ?? []));
        if ($sizes->isEmpty()) {
            return $service;
        }

        $min = $sizes->sortBy(function (array $size) {
            return (float) data_get($size, 'price.amount', INF);
        })->first();

        if (!$min) {
            return $service;
        }

        $service['price'] = [
            'type' => 'fixed',
            'amount' => (float) data_get($min, 'price.amount', 0),
            'original_amount' => data_get($min, 'price.original_amount'),
            'is_discounted' => (bool) data_get($min, 'price.is_discounted', false),
        ];

        return $service;
    }

    private function resolveSizeFormat(int $width, int $height): string
    {
        if ($width === $height) {
            return 'square';
        }

        return $width > $height ? 'landscape' : 'portrait';
    }

    private function resolveGalleryItemPriceBySlug(string $slug): ?float
    {
        if ($slug === '') {
            return null;
        }

        $row = DB::table('gallery_items')
            ->where('active', 1)
            ->where('slug', $slug)
            ->orderBy('id')
            ->first(['price_from', 'custom_size_prices']);

        if (!$row) {
            return null;
        }

        return $this->extractMinConfiguredPrice((string) ($row->custom_size_prices ?? ''), (float) ($row->price_from ?? 0));
    }

    private function resolveMinGalleryPriceByQuery(callable $applier): ?float
    {
        $query = DB::table('gallery_items')
            ->where('active', 1);

        $applier($query);

        $rows = $query->get(['price_from', 'custom_size_prices']);
        if ($rows->isEmpty()) {
            return null;
        }

        $prices = [];
        foreach ($rows as $row) {
            $price = $this->extractMinConfiguredPrice((string) ($row->custom_size_prices ?? ''), (float) ($row->price_from ?? 0));
            if ($price !== null && $price > 0) {
                $prices[] = $price;
            }
        }

        if (empty($prices)) {
            return null;
        }

        return (float) min($prices);
    }

    private function extractMinConfiguredPrice(string $raw, ?float $fallback): ?float
    {
        $values = [];
        if ($raw !== '') {
            preg_match_all('/\[(.*?)\]/', $raw, $groups);
            foreach (($groups[1] ?? []) as $group) {
                preg_match_all('/\d+(?:[.,]\d+)?/', (string) $group, $numbers);
                foreach (($numbers[0] ?? []) as $number) {
                    $normalized = str_replace(',', '.', (string) $number);
                    $value = (float) $normalized;
                    if ($value > 0) {
                        $values[] = $value;
                    }
                }
            }
        }

        if (!empty($values)) {
            return (float) min($values);
        }

        if ($fallback !== null && $fallback > 0) {
            return (float) $fallback;
        }

        return null;
    }

    private function normalizeMenuPath(string $url): string
    {
        $path = trim((string) parse_url(trim($url), PHP_URL_PATH));
        $path = '/' . trim($path, '/');
        if ($path === '//') {
            $path = '/';
        }

        $segments = array_values(array_filter(explode('/', trim($path, '/')), function ($segment) {
            return $segment !== '';
        }));

        if (!empty($segments)) {
            $locales = $this->supportedCatalogLocales();
            if (in_array(strtolower($segments[0]), $locales, true)) {
                array_shift($segments);
            }
        }

        $normalized = '/' . implode('/', $segments);
        return rtrim($normalized, '/') !== '' ? rtrim($normalized, '/') : '/';
    }

    private function buildServiceTagsByPath(string $path): array
    {
        if ($path === '/new/gift-card') {
            return ['gift', 'certificate'];
        }

        if ($path === '/family-constructor') {
            return ['family', 'portrait', 'constructor'];
        }

        if ($path === '/new/caricature') {
            return ['caricature', 'portrait'];
        }

        if ($path === '/simpsons') {
            return ['simpsons', 'portrait'];
        }

        if ($path === '/new/canvas') {
            return ['canvas', 'photo'];
        }

        if ($path === '/collage') {
            return ['collage', 'canvas'];
        }

        if ($path === '/modular-generator') {
            return ['modular', 'canvas'];
        }

        if ($path === '/new/gallery') {
            return ['gallery', 'canvas'];
        }

        if (strpos($path, '/new/graphic-portrait/') === 0) {
            return ['portrait', 'graphic'];
        }

        return ['service'];
    }

    private function resolveMenuCategoryName(int $menuPos, string $lang): string
    {
        if (strtolower($lang) === 'uk') {
            $lang = 'ru';
        }

        $dictionary = [
            'ru' => [
                1 => 'Портреты и шаржи',
                2 => 'Холст и фотопродукты',
                3 => 'Подарочные карты',
            ],
            'uk' => [
                1 => 'Портрети та шаржі',
                2 => 'Полотно та фотопродукти',
                3 => 'Подарункові карти',
            ],
            'en' => [
                1 => 'Portraits and caricatures',
                2 => 'Canvas and photo products',
                3 => 'Gift cards',
            ],
        ];

        return $dictionary[$lang][$menuPos] ?? $dictionary['en'][$menuPos] ?? 'Services';
    }

    private function buildCatalog(string $lang): array
    {
        $dictionary = [
            'ru' => [
                'category_consulting' => 'Консультации',
                'service_primary_name' => 'Первичная консультация',
                'service_primary_short' => 'Созвон 30 минут, разбор запроса',
                'service_primary_desc' => 'Подробное описание услуги...',
                'service_extended_name' => 'Расширенная консультация',
                'service_extended_short' => 'Созвон 60 минут, углубленный разбор',
                'service_extended_desc' => 'Подробное описание расширенной услуги...',
                'bundle_start' => 'Стартовый пакет',
            ],
            'en' => [
                'category_consulting' => 'Consultations',
                'service_primary_name' => 'Initial consultation',
                'service_primary_short' => '30-minute call, request analysis',
                'service_primary_desc' => 'Detailed service description...',
                'service_extended_name' => 'Extended consultation',
                'service_extended_short' => '60-minute call, deep analysis',
                'service_extended_desc' => 'Detailed extended service description...',
                'bundle_start' => 'Starter bundle',
            ],
            'de' => [
                'category_consulting' => 'Beratungen',
                'service_primary_name' => 'Erstberatung',
                'service_primary_short' => '30-Minuten-Anruf, Analyse der Anfrage',
                'service_primary_desc' => 'Detaillierte Leistungsbeschreibung...',
                'service_extended_name' => 'Erweiterte Beratung',
                'service_extended_short' => '60-Minuten-Anruf, vertiefte Analyse',
                'service_extended_desc' => 'Detaillierte Beschreibung der erweiterten Leistung...',
                'bundle_start' => 'Startpaket',
            ],
            'lv' => [
                'category_consulting' => 'Konsultacijas',
                'service_primary_name' => 'Sakotneja konsultacija',
                'service_primary_short' => '30 min zvans, pieprasijuma izvertejums',
                'service_primary_desc' => 'Detalizets pakalpojuma apraksts...',
                'service_extended_name' => 'Paplasinata konsultacija',
                'service_extended_short' => '60 min zvans, padzilinata izvertejums',
                'service_extended_desc' => 'Detalizets paplasinata pakalpojuma apraksts...',
                'bundle_start' => 'Starta pakotne',
            ],
            'lt' => [
                'category_consulting' => 'Konsultacijos',
                'service_primary_name' => 'Pradine konsultacija',
                'service_primary_short' => '30 min skambutis, uzklausos analize',
                'service_primary_desc' => 'Isamus paslaugos aprasymas...',
                'service_extended_name' => 'Issamioji konsultacija',
                'service_extended_short' => '60 min skambutis, gili analize',
                'service_extended_desc' => 'Isamus issamios paslaugos aprasymas...',
                'bundle_start' => 'Pradinis paketas',
            ],
            'pl' => [
                'category_consulting' => 'Konsultacje',
                'service_primary_name' => 'Konsultacja wstepna',
                'service_primary_short' => 'Rozmowa 30 min, analiza zapytania',
                'service_primary_desc' => 'Szczegolowy opis uslugi...',
                'service_extended_name' => 'Konsultacja rozszerzona',
                'service_extended_short' => 'Rozmowa 60 min, poglebiona analiza',
                'service_extended_desc' => 'Szczegolowy opis uslugi rozszerzonej...',
                'bundle_start' => 'Pakiet startowy',
            ],
            'ee' => [
                'category_consulting' => 'Konsultatsioonid',
                'service_primary_name' => 'Esmane konsultatsioon',
                'service_primary_short' => '30-minutiline k6ne, vajaduse analuys',
                'service_primary_desc' => 'Teenuse detailne kirjeldus...',
                'service_extended_name' => 'Laiendatud konsultatsioon',
                'service_extended_short' => '60-minutiline k6ne, sygavam analuys',
                'service_extended_desc' => 'Laiendatud teenuse detailne kirjeldus...',
                'bundle_start' => 'Stardipakett',
            ],
        ];

        $dict = $dictionary[$lang] ?? $dictionary['ru'];

        return [
            'categories' => [
                [
                    'id' => 'CAT-1',
                    'name' => $dict['category_consulting'],
                    'order' => 10,
                ],
            ],
            'services' => [
                [
                    'id' => 'SRV-101',
                    'category_id' => 'CAT-1',
                    'name' => $dict['service_primary_name'],
                    'short_description' => $dict['service_primary_short'],
                    'description' => $dict['service_primary_desc'],
                    'price' => ['type' => 'fixed', 'amount' => 1500],
                    'duration_minutes' => 30,
                    'availability' => [
                        'booking_required' => true,
                        'lead_time_hours' => 2,
                    ],
                    'constraints' => [
                        'requires_address' => false,
                        'requires_files' => false,
                    ],
                    'faq' => [
                        ['q' => 'Как проходит консультация?', 'a' => 'По телефону/мессенджеру...'],
                    ],
                    'tags' => ['online', 'consulting'],
                    'is_active' => true,
                    'updated_at' => '2026-02-10T09:10:00Z',
                ],
                [
                    'id' => 'SRV-102',
                    'category_id' => 'CAT-1',
                    'name' => $dict['service_extended_name'],
                    'short_description' => $dict['service_extended_short'],
                    'description' => $dict['service_extended_desc'],
                    'price' => ['type' => 'fixed', 'amount' => 3000],
                    'duration_minutes' => 60,
                    'availability' => [
                        'booking_required' => true,
                        'lead_time_hours' => 4,
                    ],
                    'constraints' => [
                        'requires_address' => false,
                        'requires_files' => false,
                    ],
                    'faq' => [
                        ['q' => 'Как проходит консультация?', 'a' => 'По телефону/мессенджеру...'],
                    ],
                    'tags' => ['extended', 'consulting'],
                    'is_active' => false,
                    'updated_at' => '2026-02-01T10:00:00Z',
                ],
            ],
            'bundles' => [
                [
                    'id' => 'BND-1',
                    'name' => $dict['bundle_start'],
                    'items' => [['service_id' => 'SRV-101', 'qty' => 1]],
                    'price' => ['type' => 'fixed', 'amount' => 1200],
                    'is_active' => true,
                    'updated_at' => '2026-02-11T00:00:00Z',
                ],
                [
                    'id' => 'BND-2',
                    'name' => $dict['bundle_start'] . ' PRO',
                    'items' => [['service_id' => 'SRV-102', 'qty' => 1]],
                    'price' => ['type' => 'fixed', 'amount' => 2700],
                    'is_active' => false,
                    'updated_at' => '2026-01-15T00:00:00Z',
                ],
            ],
        ];
    }

    private function filterCategoriesByServices(Collection $categories, Collection $services): Collection
    {
        $categoryIds = $services->pluck('category_id')->unique()->values()->all();

        return $categories
            ->filter(function (array $category) use ($categoryIds) {
                return in_array($category['id'], $categoryIds, true);
            })
            ->values();
    }

    private function isGiftCardServiceId(string $serviceId): bool
    {
        return strtoupper(trim($serviceId)) === 'GC-5';
    }

    private function isFamilyConstructorServiceId(string $serviceId): bool
    {
        return strtoupper(trim($serviceId)) === 'FC-1';
    }

    private function validateGalleryCatalogLeadPayload(array $lead): array
    {
        $serviceRequest = (array) data_get($lead, 'service_request', []);
        $serviceId = strtoupper(trim((string) ($serviceRequest['service_id'] ?? '')));
        if ($serviceId !== 'HM-44') {
            return [];
        }

        $reference = $this->resolveRequestedGalleryCatalogItemReference($serviceRequest, 'lead.service_request.');
        if (!$reference['provided']) {
            return [];
        }

        $galleryItem = $this->resolveGalleryCatalogItemById((int) $reference['id']);
        if (!$galleryItem) {
            return [[
                'field' => (string) $reference['field'],
                'issue' => 'Конкретная картина каталога не найдена или неактивна',
            ]];
        }

        $options = (array) ($serviceRequest['options'] ?? []);
        $requestedSize = strtolower(str_replace(' ', '', (string) ($options['size'] ?? $serviceRequest['size'] ?? '')));
        if ($requestedSize !== '') {
            $sizeMap = $this->resolveSizeMapByGalleryItemRow($galleryItem);
            if (!isset($sizeMap[$requestedSize])) {
                return [[
                    'field' => 'lead.service_request.options.size',
                    'issue' => 'Размер недоступен для выбранной картины каталога',
                ]];
            }
        }

        return [];
    }

    private function validateGiftCardLeadPayload(array $lead): array
    {
        $serviceRequest = (array) data_get($lead, 'service_request', []);
        $serviceId = (string) ($serviceRequest['service_id'] ?? '');
        if (!$this->isGiftCardServiceId($serviceId)) {
            return [];
        }

        $nominalMap = $this->resolveGiftCardNominalMap();
        if (empty($nominalMap)) {
            return [[
                'field' => 'lead.service_request.service_id',
                'issue' => 'Подарочная карта не настроена: отсутствуют номиналы в gift_card_noms',
            ]];
        }

        $details = [];
        $resolvedNominal = $this->resolveRequestedGiftCardNominal($serviceRequest);
        if (!empty($resolvedNominal['raw_provided']) && empty($resolvedNominal['key'])) {
            $details[] = [
                'field' => (string) ($resolvedNominal['field'] ?? 'lead.service_request.options.amount'),
                'issue' => 'Недоступный номинал подарочной карты',
            ];
        }

        $pricing = (array) data_get($lead, 'pricing', []);
        if (trim((string) ($pricing['coupon_code'] ?? '')) !== '' || !empty($pricing['use_bonus'])) {
            $details[] = [
                'field' => 'lead.pricing',
                'issue' => 'Для покупки подарочной карты coupon_code и use_bonus не поддерживаются',
            ];
        }

        $cardType = strtolower(trim((string) data_get($serviceRequest, 'options.card_type', $serviceRequest['card_type'] ?? 'online')));
        if ($cardType === '') {
            $cardType = 'online';
        }

        $deliveryMethod = trim((string) data_get($lead, 'delivery.method', ''));
        if ($cardType === 'online' && $deliveryMethod !== '' && $deliveryMethod !== 'email') {
            $details[] = [
                'field' => 'lead.delivery.method',
                'issue' => 'Для online gift card допустим только delivery.method=email',
            ];
        }

        return $details;
    }

    /**
     * @return array<string, array{amount: float, label: string, updated_at: string|null}>
     */
    private function resolveGiftCardNominalMap(): array
    {
        if (!Schema::hasTable('gift_card_noms')) {
            return [];
        }

        $rows = DB::table('gift_card_noms')
            ->orderBy('id')
            ->get(['text', 'updated_at']);

        $map = [];
        foreach ($rows as $row) {
            $amount = $this->extractGiftCardNominalAmount($row->text ?? null);
            if ($amount === null || $amount <= 0) {
                continue;
            }

            $key = $this->normalizeGiftCardNominalKey($amount);
            if (!isset($map[$key])) {
                $map[$key] = [
                    'amount' => $amount,
                    'label' => trim((string) ($row->text ?? '')),
                    'updated_at' => isset($row->updated_at) ? (string) $row->updated_at : null,
                ];
            }
        }

        uksort($map, function (string $left, string $right) {
            return (float) $left <=> (float) $right;
        });

        return $map;
    }

    private function extractGiftCardNominalAmount($raw): ?float
    {
        if (is_int($raw) || is_float($raw)) {
            $amount = (float) $raw;
        } elseif (is_string($raw) && trim($raw) !== '') {
            if (!preg_match('/\d+(?:[.,]\d+)?/', $raw, $matches)) {
                return null;
            }

            $amount = (float) str_replace(',', '.', (string) $matches[0]);
        } else {
            return null;
        }

        if ($amount <= 0) {
            return null;
        }

        return (float) round($amount, 2);
    }

    private function normalizeGiftCardNominalKey(float $amount): string
    {
        return rtrim(rtrim(number_format($amount, 2, '.', ''), '0'), '.');
    }

    /**
     * @return array{field: string, key: string|null, amount: float|null, label: string|null, raw_provided: bool}
     */
    private function resolveRequestedGiftCardNominal(array $serviceRequest): array
    {
        $nominalMap = $this->resolveGiftCardNominalMap();
        $options = (array) ($serviceRequest['options'] ?? []);
        $candidates = [
            ['field' => 'lead.service_request.options.amount', 'value' => $options['amount'] ?? null],
            ['field' => 'lead.service_request.options.nominal', 'value' => $options['nominal'] ?? null],
            ['field' => 'lead.service_request.options.size', 'value' => $options['size'] ?? null],
            ['field' => 'lead.service_request.amount', 'value' => $serviceRequest['amount'] ?? null],
            ['field' => 'lead.service_request.nominal', 'value' => $serviceRequest['nominal'] ?? null],
            ['field' => 'lead.service_request.size', 'value' => $serviceRequest['size'] ?? null],
        ];

        foreach ($candidates as $candidate) {
            $value = $candidate['value'];
            if ($value === null || (is_string($value) && trim($value) === '')) {
                continue;
            }

            $amount = $this->extractGiftCardNominalAmount($value);
            if ($amount === null) {
                return [
                    'field' => $candidate['field'],
                    'key' => null,
                    'amount' => null,
                    'label' => null,
                    'raw_provided' => true,
                ];
            }

            $key = $this->normalizeGiftCardNominalKey($amount);
            if (!isset($nominalMap[$key])) {
                return [
                    'field' => $candidate['field'],
                    'key' => null,
                    'amount' => $amount,
                    'label' => null,
                    'raw_provided' => true,
                ];
            }

            return [
                'field' => $candidate['field'],
                'key' => $key,
                'amount' => (float) ($nominalMap[$key]['amount'] ?? $amount),
                'label' => (string) ($nominalMap[$key]['label'] ?? $key),
                'raw_provided' => true,
            ];
        }

        $firstKey = (string) array_key_first($nominalMap);
        if ($firstKey === '') {
            return [
                'field' => 'lead.service_request.options.amount',
                'key' => null,
                'amount' => null,
                'label' => null,
                'raw_provided' => false,
            ];
        }

        return [
            'field' => 'lead.service_request.options.amount',
            'key' => $firstKey,
            'amount' => (float) ($nominalMap[$firstKey]['amount'] ?? 0),
            'label' => (string) ($nominalMap[$firstKey]['label'] ?? $firstKey),
            'raw_provided' => false,
        ];
    }

    private function resolveGiftCardServiceStub(): ?object
    {
        $nominalMap = $this->resolveGiftCardNominalMap();
        if (empty($nominalMap)) {
            return null;
        }

        $giftCardRow = Schema::hasTable('gift_card')
            ? DB::table('gift_card')->orderBy('id')->first(['id', 'title', 'updated_at'])
            : null;

        return (object) [
            'id' => 'GC-5',
            'title' => (string) ($giftCardRow->title ?? 'Gift card'),
            'link' => '/new/gift-card',
            'menu_pos' => 3,
            'is_show' => 1,
            'updated_at' => $giftCardRow->updated_at ?? now(),
        ];
    }

    private function resolveFamilyConstructorServiceStub(): ?object
    {
        $row = $this->resolveFamilyConstructorRow();
        if ($row === null) {
            return null;
        }

        return (object) [
            'id' => 'FC-1',
            'title' => (string) ($row->title ?? 'Family constructor'),
            'link' => '/family-constructor',
            'menu_pos' => 1,
            'is_show' => 1,
            'updated_at' => $row->updated_at ?? now(),
        ];
    }

    private function buildGiftCardCatalogService(string $lang): ?array
    {
        $nominalMap = $this->resolveGiftCardNominalMap();
        if (empty($nominalMap)) {
            return null;
        }

        $effectiveLang = strtolower($lang) === 'uk' ? 'ru' : strtolower($lang);
        $giftCardRow = Schema::hasTable('gift_card')
            ? DB::table('gift_card')->orderBy('id')->first(['id', 'title', 'desc', 'updated_at'])
            : null;

        $title = trim((string) ($giftCardRow->title ?? trans('pages.gift_card', [], $effectiveLang)));
        $description = trim((string) ($giftCardRow->desc ?? strip_tags((string) trans('pages.gift_card_text', [], $effectiveLang))));
        if ($giftCardRow && !empty($giftCardRow->id)) {
            $title = $this->resolveGiftCardLocalizedValue((int) $giftCardRow->id, 'title', $effectiveLang, $title);
            $description = $this->resolveGiftCardLocalizedValue((int) $giftCardRow->id, 'desc', $effectiveLang, $description);
        } else {
            $description = trim(strip_tags($description));
        }

        $firstKey = (string) array_key_first($nominalMap);
        $minNominal = $firstKey !== '' ? (float) ($nominalMap[$firstKey]['amount'] ?? 0) : 0.0;
        $updatedAt = Carbon::parse($giftCardRow->updated_at ?? now())->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z');

        return [
            'id' => 'GC-5',
            'category_id' => 'CAT-GIFTS',
            'name' => $title !== '' ? $title : 'Gift card',
            'short_description' => '/new/gift-card',
            'description' => $description !== '' ? $description : 'Gift card purchase flow',
            'price' => [
                'type' => 'fixed',
                'amount' => $minNominal,
            ],
            'duration_minutes' => null,
            'availability' => [
                'booking_required' => false,
                'lead_time_hours' => 0,
            ],
            'constraints' => [
                'requires_address' => false,
                'requires_files' => false,
            ],
            'faq' => [],
            'tags' => $this->buildServiceTagsByPath('/new/gift-card'),
            'is_active' => true,
            'updated_at' => $updatedAt,
        ];
    }

    private function buildFamilyConstructorCatalogService(string $lang, float $countryMultiplier): ?array
    {
        $row = $this->resolveFamilyConstructorRow();
        $sizesRaw = $this->resolveFamilyConstructorSizesRaw();
        $minPrice = $this->extractMinConfiguredPrice($sizesRaw, null);
        if ($row === null || $minPrice === null) {
            return null;
        }

        $effectiveLang = strtolower($lang) === 'uk' ? 'ru' : strtolower($lang);
        $title = $this->resolveFamilyConstructorLocalizedValue('title', $effectiveLang, (string) ($row->title ?? 'Family constructor'));
        $description = $this->resolveFamilyConstructorLocalizedValue('meta_title', $effectiveLang, (string) ($row->meta_title ?? $title));
        $updatedAt = Carbon::parse($row->updated_at ?? now())->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z');

        return [
            'id' => 'FC-1',
            'category_id' => 'CAT-PORTRAITS',
            'name' => $title !== '' ? $title : 'Family constructor',
            'short_description' => '/family-constructor',
            'description' => $description !== '' ? $description : 'Family constructor purchase flow',
            'price' => [
                'type' => 'fixed',
                'amount' => (float) round($minPrice * max($countryMultiplier, 0.0001), 2),
            ],
            'duration_minutes' => null,
            'availability' => [
                'booking_required' => false,
                'lead_time_hours' => 0,
            ],
            'constraints' => [
                'requires_address' => false,
                'requires_files' => true,
            ],
            'faq' => [],
            'tags' => $this->buildServiceTagsByPath('/family-constructor'),
            'is_active' => true,
            'updated_at' => $updatedAt,
        ];
    }

    private function resolveFamilyConstructorRow(): ?object
    {
        if (!Schema::hasTable('family_constructor')) {
            return null;
        }

        return DB::table('family_constructor')
            ->orderBy('id')
            ->first(['id', 'title', 'meta_title', 'sizes', 'updated_at']);
    }

    private function resolveFamilyConstructorSizesRaw(): string
    {
        $row = $this->resolveFamilyConstructorRow();
        return trim((string) ($row->sizes ?? ''));
    }

    private function resolveFamilyConstructorLocalizedValue(string $column, string $lang, string $fallback): string
    {
        $fallback = trim(strip_tags($fallback));
        if (!Schema::hasTable('family_constructor') || !Schema::hasTable('translations')) {
            return $fallback;
        }

        $id = (int) DB::table('family_constructor')->orderBy('id')->value('id');
        if ($id <= 0) {
            return $fallback;
        }

        $translated = DB::table('translations')
            ->where('table_name', 'family_constructor')
            ->where('column_name', $column)
            ->where('foreign_key', $id)
            ->where('locale', $lang)
            ->value('value');

        if (is_string($translated) && trim($translated) !== '') {
            return trim(strip_tags($translated));
        }

        if ($lang !== 'ru') {
            $translated = DB::table('translations')
                ->where('table_name', 'family_constructor')
                ->where('column_name', $column)
                ->where('foreign_key', $id)
                ->where('locale', 'ru')
                ->value('value');

            if (is_string($translated) && trim($translated) !== '') {
                return trim(strip_tags($translated));
            }
        }

        return $fallback;
    }

    private function resolveGiftCardLocalizedValue(int $giftCardId, string $column, string $lang, string $fallback): string
    {
        $fallback = trim(strip_tags($fallback));
        if ($giftCardId <= 0 || $column === '' || $lang === '' || !Schema::hasTable('translations')) {
            return $fallback;
        }

        $translated = DB::table('translations')
            ->where('table_name', 'gift_card')
            ->where('column_name', $column)
            ->where('foreign_key', $giftCardId)
            ->where('locale', $lang)
            ->value('value');

        if (is_string($translated) && trim($translated) !== '') {
            return trim(strip_tags($translated));
        }

        if ($lang !== 'ru') {
            $translated = DB::table('translations')
                ->where('table_name', 'gift_card')
                ->where('column_name', $column)
                ->where('foreign_key', $giftCardId)
                ->where('locale', 'ru')
                ->value('value');

            if (is_string($translated) && trim($translated) !== '') {
                return trim(strip_tags($translated));
            }
        }

        return $fallback;
    }

    private function resolveGiftCardWhomLabel(string $cardType, string $locale): string
    {
        $translationKey = $cardType === 'offline' ? 'gift_card.gift_cart' : 'gift_card.cart_electron';
        $label = trim((string) trans($translationKey, [], $locale));
        if ($label !== '') {
            return $label;
        }

        return $cardType === 'offline' ? 'Gift card' : 'Electronic card';
    }

    /**
     * @return array<int, string>
     */
    private function supportedCatalogLocales(): array
    {
        $configuredLocales = array_keys((array) config('laravellocalization.supportedLocales', []));

        // SA catalog contract requires at least these locales.
        $requiredLocales = ['ru', 'uk', 'en'];

        $locales = array_unique(array_merge($configuredLocales, $requiredLocales));
        sort($locales);

        return $locales;
    }

    private function normalizeStageId(string $stageId): ?string
    {
        $value = trim($stageId);
        if ($value === '') {
            return null;
        }

        $map = [
            'WATCHING' => 'watching',
            'PEGGING' => 'pegging',
            'IN_PRODUCTION' => 'in_production',
            'SENDED' => 'sended',
            'SEND_LUBANAS' => 'send_lubanas',
            'COMPLETED' => 'completed',
        ];

        return $map[strtoupper($value)] ?? null;
    }

    private function supportedLeadStageStatuses(): array
    {
        return ['watching', 'pegging', 'in_production', 'sended', 'send_lubanas', 'completed'];
    }

    private function mapStageIdToOrderStatus(string $stageId): ?string
    {
        return $this->normalizeStageId($stageId);
    }

    private function mapOrderStatusToStageId(string $status): string
    {
        return $this->normalizeStageId($status) ?? 'watching';
    }

    private function resolveOrCreateLeadId(array $lead, string $idempotencyKey): ?string
    {
        if (!Schema::hasTable('users') || !Schema::hasTable('orders')) {
            return 'TMP-LEAD-' . substr(sha1($idempotencyKey), 0, 12);
        }

        // Resolve user: find by email -> find by phone -> create new user
        $email = (string) data_get($lead, 'client.email', data_get($lead, 'fields.email', data_get($lead, 'emails.0', '')));
        $phone = (string) data_get($lead, 'client.phone', '');
        $name  = (string) data_get($lead, 'client.name', '');

        $resolvedUser = null;

        // 1. Find by email
        if ($email) {
            $resolvedUser = \App\Models\User::where('email', $email)->first();
        }

        // 2. Find by phone if no user found yet
        if (!$resolvedUser && $phone) {
            $resolvedUser = \App\Models\User::where('phone', $phone)->first();
        }

        // 3. Create new user from lead data if still not found
        if (!$resolvedUser) {
            $resolvedUser = $this->createSaLeadUser($email, $phone, $name);
        }

        if (!$resolvedUser) {
            Log::warning('SA Integration: failed to resolve user for lead', [
                'email' => $email ?: 'none',
                'phone' => $phone ?: 'none',
            ]);
            return null;
        }

        // Try to create a real order through existing business flow.
        $serviceRequest = (array) data_get($lead, 'service_request', []);
        if (!empty($serviceRequest['service_id'])) {
            $basket = $this->resolveServiceToBasket($serviceRequest);
            $checkoutParams = !empty($basket) ? $this->buildCheckoutParamsFromLead($lead) : [];

            if (!empty($basket) && !empty($checkoutParams)) {
                try {
                    $pricingResult = $this->applyLeadPricingToOrderPayload($basket, $checkoutParams, $lead, $resolvedUser);
                    $basket = $pricingResult['basket'];
                    $checkoutParams = $pricingResult['checkout_params'];
                    $newOrderId = $this->saveOrderAsUser($resolvedUser, $basket, $checkoutParams);
                    if (!empty($newOrderId)) {
                        return (string) $newOrderId;
                    }
                } catch (\InvalidArgumentException $e) {
                    throw $e;
                } catch (\Throwable $e) {
                    Log::error('SA Integration: saveOrder failed in resolveOrCreateLeadId', [
                        'error' => $e->getMessage(),
                        'service_id' => (string) $serviceRequest['service_id'],
                    ]);
                }
            }
        }

        // Fallback: create minimal order if service mapping/create failed.
        $countryCode = strtoupper((string) data_get($lead, 'service_request.country_code', data_get($lead, 'fields.country_code', 'LV')));
        $country = $this->resolveCountryRowByCode($countryCode);
        $resolvedCountryCode = strtoupper((string) ($country->country_code ?? 'LV'));
        $deliveryPrice = $country ? (float) (($country->delivery_venipak ?? null) ?? ($country->deliv_price ?? 0)) : 0.0;
        if ($deliveryPrice < 0) {
            $deliveryPrice = 0.0;
        }

        $order = new \App\Models\Orders();
        $order->user_id = $resolvedUser->id;
        $order->status = 'watching';
        $order->payment_status = 'not_payed';
        $order->items = json_encode([]);
        $order->price = '0';
        $order->country = $resolvedCountryCode;
        $order->delivery = json_encode([
            'source' => 'sa_integration',
            'first_name' => (string) data_get($lead, 'client.name', 'Integration'),
            'phone' => (string) data_get($lead, 'client.phone', ''),
            'payer_phone' => (string) data_get($lead, 'client.phone', ''),
            'email' => $email ?: 'integration@example.com',
            'sposob' => 'to_the_door',
            'country' => $resolvedCountryCode,
            'city' => (string) data_get($lead, 'fields.city', ''),
            'address' => (string) data_get($lead, 'fields.address', ''),
            'postal_index' => (string) data_get($lead, 'fields.postal_index', ''),
            'deliv_price' => $deliveryPrice,
            'payment' => 'transfer',
            'comment' => (string) data_get($lead, 'service_request.notes', ''),
        ]);
        $order->payment = 'transfer';
        if (Schema::hasColumn('orders', 'sa_client_phone')) {
            $order->sa_client_phone = (string) data_get($lead, 'client.phone', '');
        }
        $order->save();

        return (string) $order->id;
    }

    private function buildEscalationTaskId(string $leadId, string $conversationId, string $idempotencyKey): string
    {
        $seed = $leadId . '|' . $conversationId . '|' . $idempotencyKey;

        return 'CRM-TASK-' . (10000 + (abs(crc32($seed)) % 90000));
    }
}
