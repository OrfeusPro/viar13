<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SynvolveTestWebhookController extends Controller
{
    public function captureOrder(Request $request): JsonResponse
    {
        $this->ensureEnabled();

        return $this->capture('order', $request);
    }

    public function captureManagerMessage(Request $request): JsonResponse
    {
        $this->ensureEnabled();

        return $this->capture('message', $request);
    }

    public function latest(string $type = 'all'): JsonResponse
    {
        $this->ensureEnabled();

        $allowed = ['order', 'message', 'all'];
        if (!in_array($type, $allowed, true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unknown type',
            ], 404);
        }

        if ($type === 'all') {
            return response()->json([
                'status' => 'ok',
                'data' => [
                    'order' => $this->readPayload('order'),
                    'message' => $this->readPayload('message'),
                ],
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'data' => $this->readPayload($type),
        ]);
    }

    private function ensureEnabled(): void
    {
        abort_unless((bool) config('services.synvolve.test_receiver_enabled', false), 404);
    }

    private function capture(string $type, Request $request): JsonResponse
    {
        $payload = [
            'received_at' => now()->toIso8601String(),
            'type' => $type,
            'method' => $request->method(),
            'path' => $request->path(),
            'headers' => [
                'content_type' => (string) $request->header('Content-Type', ''),
                'user_agent' => (string) $request->header('User-Agent', ''),
            ],
            'payload' => $request->all(),
        ];

        Storage::disk('local')->put(
            'synvolve-test/last-' . $type . '.json',
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        file_put_contents(
            storage_path('logs/synvolve_test.log'),
            '[' . now()->toDateTimeString() . '] ' . strtoupper($type) . ' ' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
            FILE_APPEND
        );

        return response()->json([
            'status' => 'ok',
            'captured' => true,
            'type' => $type,
        ]);
    }

    private function readPayload(string $type): array
    {
        $path = 'synvolve-test/last-' . $type . '.json';
        if (!Storage::disk('local')->exists($path)) {
            return [];
        }

        $decoded = json_decode((string) Storage::disk('local')->get($path), true);

        return is_array($decoded) ? $decoded : [];
    }
}
