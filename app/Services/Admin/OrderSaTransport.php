<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderSaTransport
{
    public function dispatch(string $kind, string $phone, string $value, array $context): bool
    {
        // A second boundary guard prevents accidental transport calls during UAT.
        if (! config('admin_migration.sa_commands_enabled', false)) {
            return false;
        }
        if (str_starts_with((string) ($context['conversation_id'] ?? ''), 'ADM-FIL-UAT-')) {
            return false;
        }
        $url = (string) config('services.synvolve.'.($kind === 'message' ? 'manager_message_webhook_url' : 'bot_status_webhook_url'));
        if (! filter_var($url, FILTER_VALIDATE_URL) || ! str_starts_with($url, 'https://')) {
            Log::warning('SA command transport is not configured with HTTPS.', ['event_id' => $context['event_id']]);

            return false;
        }
        // Preserve the existing Synvolve contracts, addressing the selected conversation's phone.
        $payload = $kind === 'message'
            ? ['event' => 'manager_message', 'phone' => $phone, 'text' => $value]
            : ['event' => 'bot_status_changed', 'client_id' => (string) $context['order_id'], 'phone' => $phone, 'bot_status' => $value];
        $payload['context'] = $context;
        $payload['sent_at'] = now()->utc()->toIso8601String();
        try {
            $response = Http::acceptJson()->timeout((float) config('services.synvolve.timeout', 5))
                ->connectTimeout((float) config('services.synvolve.connect_timeout', 3))
                ->withHeaders(['Idempotency-Key' => $context['idempotency_key']])
                ->post($url, $payload);
            Log::info('SA command transport completed.', ['event_id' => $context['event_id'], 'kind' => $kind, 'status' => $response->status()]);

            return $response->successful();
        } catch (Throwable $exception) {
            Log::warning('SA command transport outcome is uncertain.', ['event_id' => $context['event_id'], 'kind' => $kind, 'exception_type' => $exception::class]);

            return false;
        }
    }
}
