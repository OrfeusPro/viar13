<?php

namespace App\Services;

use App\Models\Orders;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class SynvolveWebhookService
{
    /** @var \GuzzleHttp\Client */
    private $http;
    /** @var bool */
    private $verifySsl;

    public function __construct(?Client $http = null)
    {
        $verifySsl = (bool) config('services.synvolve.verify_ssl', true);
        $this->verifySsl = $verifySsl;
        $clientConfig = [
            'timeout' => (float) config('services.synvolve.timeout', 5),
            'connect_timeout' => (float) config('services.synvolve.connect_timeout', 3),
            'http_errors' => false,
            'verify' => $verifySsl,
        ];

        if (!$verifySsl) {
            $clientConfig['curl'] = [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ];
        }

        $this->http = $http ?: new Client($clientConfig);
    }

    public function notifyOrderSnapshotById(?int $orderId, string $trigger, array $context = []): bool
    {
        if (!$orderId) {
            return false;
        }

        $order = Orders::query()->find($orderId);
        if (!$order) {
            return false;
        }

        return $this->notifyOrderSnapshot($order, $trigger, $context);
    }

    public function notifyOrderSnapshot(Orders $order, string $trigger, array $context = []): bool
    {
        $url = (string) config('services.synvolve.order_webhook_url', '');
        if ($url === '') {
            return false;
        }

        $payload = $this->buildOrderSnapshotPayload($order, $trigger, $context);

        return $this->postJson($url, $payload, 'order_snapshot');
    }

    public function notifyManagerMessage(string $phone, string $text, array $context = []): bool
    {
        $url = (string) config('services.synvolve.manager_message_webhook_url', '');
        if ($url === '') {
            return false;
        }

        $payload = $this->buildManagerMessagePayload($phone, $text, $context);
        if ($payload === null) {
            return false;
        }

        return $this->postJson($url, $payload, 'manager_message');
    }

    public function notifyManagerMessageForOrder(?int $orderId, string $text, array $context = []): bool
    {
        if (!$orderId) {
            return false;
        }

        $order = Orders::query()->find($orderId);
        if (!$order) {
            return false;
        }

        $phone = $this->resolvePrimaryOrderPhone($order);

        return $this->notifyManagerMessage($phone, $text, array_merge([
            'order_id' => (int) $order->id,
        ], $context));
    }

    public function notifyManagerMessageForOrderOrPhone(?int $orderId, string $fallbackPhone, string $text, array $context = []): bool
    {
        $phone = $fallbackPhone;

        if ($orderId) {
            $order = Orders::query()->find($orderId);
            if ($order) {
                $orderPhone = $this->resolvePrimaryOrderPhone($order);
                if ($orderPhone !== '') {
                    $phone = $orderPhone;
                }

                $context = array_merge([
                    'order_id' => (int) $order->id,
                    'phone_source' => $orderPhone !== '' ? 'order' : 'fallback_payload',
                ], $context);
            }
        }

        return $this->notifyManagerMessage($phone, $text, $context);
    }

    public function notifyBotStatusForOrder(Orders $order, string $botStatus, array $context = []): bool
    {
        $url = (string) config('services.synvolve.bot_status_webhook_url', '');
        if ($url === '') {
            return false;
        }

        $payload = $this->buildBotStatusPayload($order, $botStatus, $context);
        if ($payload === null) {
            return false;
        }

        return $this->postJson($url, $payload, 'bot_status');
    }

    public function buildOrderSnapshotPayload(Orders $order, string $trigger, array $context = []): array
    {
        $items = $this->decodeJsonArray($order->items);
        $productNames = [];
        $itemComments = [];

        foreach ($items as $item) {
            if (!is_array($item) || !isset($item['sumPrice'])) {
                continue;
            }

            $itemName = trim((string) ($item['name'] ?? ''));
            if ($itemName !== '') {
                $productNames[] = $itemName;
            }

            $itemComment = trim((string) ($item['userComment'] ?? ($item['user_comment'] ?? ($item['comment'] ?? ''))));
            if ($itemComment !== '') {
                $itemComments[] = $itemComment;
            }
        }

        $orderComment = trim((string) $order->comment);
        $allComments = [];
        if ($orderComment !== '') {
            $allComments[] = $orderComment;
        }
        foreach ($itemComments as $itemComment) {
            if (!in_array($itemComment, $allComments, true)) {
                $allComments[] = $itemComment;
            }
        }

        return [
            'event' => 'order_snapshot',
            'trigger' => $trigger,
            'order_id' => (int) $order->id,
            'phone' => $this->resolvePrimaryOrderPhone($order),
            'payment_status' => (string) ($order->payment_status ?? ''),
            'product_names' => array_values($productNames),
            'client_comment' => [
                'order_comment' => $orderComment,
                'item_comments' => array_values($itemComments),
                'combined' => array_values($allComments),
            ],
            'context' => $context,
            'sent_at' => now()->toIso8601String(),
        ];
    }

    public function buildManagerMessagePayload(string $phone, string $text, array $context = []): ?array
    {
        $normalizedPhone = $this->normalizePhone($phone);
        $normalizedText = trim($text);

        if ($normalizedPhone === '' || $normalizedText === '') {
            return null;
        }

        return [
            'event' => 'manager_message',
            'phone' => $normalizedPhone,
            'text' => $normalizedText,
            'context' => $context,
            'sent_at' => now()->toIso8601String(),
        ];
    }

    public function buildBotStatusPayload(Orders $order, string $botStatus, array $context = []): ?array
    {
        $phone = $this->resolvePrimaryOrderPhone($order);
        if ($phone === '') {
            return null;
        }

        return [
            'event' => 'bot_status_changed',
            'client_id' => (string) $order->id,
            'phone' => $phone,
            'bot_status' => $botStatus,
            'context' => array_merge([
                'order_id' => (int) $order->id,
            ], $context),
            'sent_at' => now()->toIso8601String(),
        ];
    }

    private function postJson(string $url, array $payload, string $channel): bool
    {
        try {
            $requestOptions = [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json; charset=utf-8',
                ],
                'json' => $payload,
                'verify' => $this->verifySsl,
            ];

            if (!$this->verifySsl) {
                $requestOptions['curl'] = [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                ];
            }

            $response = $this->http->post($url, $requestOptions);

            $status = (int) $response->getStatusCode();
            if ($status >= 200 && $status < 300) {
                Log::info('Synvolve webhook sent successfully.', [
                    'channel' => $channel,
                    'url' => $url,
                    'status' => $status,
                    'payload' => $payload,
                    'body' => (string) $response->getBody(),
                ]);
                return true;
            }

            Log::warning('Synvolve webhook returned non-2xx response.', [
                'channel' => $channel,
                'url' => $url,
                'status' => $status,
                'payload' => $payload,
                'body' => (string) $response->getBody(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Synvolve webhook request failed.', [
                'channel' => $channel,
                'url' => $url,
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    private function resolvePrimaryOrderPhone(Orders $order): string
    {
        $delivery = $this->decodeJsonArray($order->delivery);

        $candidates = [
            $delivery['payer_phone'] ?? '',
            optional($order->user)->phone ?? '',
            $delivery['phone'] ?? '',
            $order->sa_client_phone ?? '',
        ];

        foreach ($candidates as $candidate) {
            $normalized = $this->normalizePhone((string) $candidate);
            if ($normalized !== '') {
                return $normalized;
            }
        }

        return '';
    }

    private function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        if ($phone === '') {
            return '';
        }

        $hasPlus = strpos($phone, '+') === 0;
        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === '') {
            return '';
        }

        return $hasPlus ? '+' . $digits : $digits;
    }

    private function decodeJsonArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }
}
