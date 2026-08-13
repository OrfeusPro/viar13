<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SynvolveTestWebhookReceiverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.synvolve.test_receiver_enabled' => true]);

        Storage::disk('local')->delete([
            'synvolve-test/last-order.json',
            'synvolve-test/last-message.json',
        ]);
    }

    public function test_order_capture_endpoint_persists_payload(): void
    {
        $payload = [
            'phone' => '+37129999999',
            'payment_status' => 'payed',
            'product_names' => ['Canvas', 'Gift card'],
            'client_comment' => [
                'order_comment' => 'Комментарий заказа',
            ],
        ];

        $this->postJson('/api/test/synvolve/order-capture', $payload)
            ->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'captured' => true,
                'type' => 'order',
            ]);

        $this->assertTrue(Storage::disk('local')->exists('synvolve-test/last-order.json'));

        $stored = json_decode((string) Storage::disk('local')->get('synvolve-test/last-order.json'), true);
        $this->assertIsArray($stored);
        $this->assertSame($payload['phone'], data_get($stored, 'payload.phone'));
        $this->assertSame($payload['product_names'], data_get($stored, 'payload.product_names'));
    }

    public function test_message_capture_endpoint_persists_payload(): void
    {
        $payload = [
            'phone' => '+37128888888',
            'text' => 'Тестовое сообщение менеджера',
        ];

        $this->postJson('/api/test/synvolve/message-capture', $payload)
            ->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'captured' => true,
                'type' => 'message',
            ]);

        $this->assertTrue(Storage::disk('local')->exists('synvolve-test/last-message.json'));

        $stored = json_decode((string) Storage::disk('local')->get('synvolve-test/last-message.json'), true);
        $this->assertIsArray($stored);
        $this->assertSame($payload['phone'], data_get($stored, 'payload.phone'));
        $this->assertSame($payload['text'], data_get($stored, 'payload.text'));
    }

    public function test_latest_endpoint_returns_captured_payloads(): void
    {
        Storage::disk('local')->put('synvolve-test/last-order.json', json_encode([
            'payload' => ['phone' => '+37120000001'],
        ]));
        Storage::disk('local')->put('synvolve-test/last-message.json', json_encode([
            'payload' => ['text' => 'hello'],
        ]));

        $this->getJson('/api/test/synvolve/latest/all')
            ->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'data' => [
                    'order' => [
                        'payload' => ['phone' => '+37120000001'],
                    ],
                    'message' => [
                        'payload' => ['text' => 'hello'],
                    ],
                ],
            ]);
    }

    public function test_receiver_is_not_available_when_disabled(): void
    {
        config(['services.synvolve.test_receiver_enabled' => false]);

        $this->postJson('/api/test/synvolve/order-capture', [
            'phone' => '+37129999999',
        ])->assertStatus(404);
    }
}
