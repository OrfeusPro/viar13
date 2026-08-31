<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SaWebhooksMessagesTest extends TestCase
{
    use \Tests\Support\CreatesSaChatSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSaChatSchema();
    }

    private const ENDPOINT = '/api/sa/webhooks/messages';
    private const CREATE_LEAD_ENDPOINT = '/api/sa/leads';
    private const API_KEY = 'test-key';

    #[\PHPUnit\Framework\Attributes\Test]
    public function t01_001_message_created_valid_payload_returns_ok()
    {
        $this->skipIfRouteMissing('POST', self::ENDPOINT);
        $leadId = $this->createLeadForTests();

        $response = $this->postJson(self::ENDPOINT, $this->messageCreatedPayload($leadId), $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function t01_002_duplicate_event_id_returns_duplicate()
    {
        $this->skipIfRouteMissing('POST', self::ENDPOINT);
        $leadId = $this->createLeadForTests();

        $payload = $this->messageCreatedPayload($leadId);

        $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders())->assertStatus(200);

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'duplicate');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function t01_003_message_status_for_known_message_returns_ok()
    {
        $this->skipIfRouteMissing('POST', self::ENDPOINT);
        $leadId = $this->createLeadForTests();
        $messageId = 'MSG_' . $this->uniqueSuffix();

        $this->postJson(self::ENDPOINT, $this->messageCreatedPayload($leadId, $messageId), $this->apiHeaders())
            ->assertStatus(200);

        $response = $this->postJson(self::ENDPOINT, $this->messageStatusPayload($leadId, $messageId), $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('result.status_updated', true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function t01_004_message_created_without_lead_id_is_stored_without_order()
    {
        $this->skipIfRouteMissing('POST', self::ENDPOINT);
        $payload = $this->messageCreatedPayload(null);
        unset($payload['data']['lead_id']);

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok');

        if (!Schema::hasTable('sa_messages') || !Schema::hasTable('sa_conversations')) {
            $this->markTestSkipped('sa_messages/sa_conversations are not available in current test DB connection');
        }

        $message = DB::table('sa_messages')
            ->where('message_id', (string) data_get($payload, 'data.message.message_id'))
            ->first();

        $conversation = DB::table('sa_conversations')
            ->where('conversation_id', (string) data_get($payload, 'data.conversation_id'))
            ->first();

        $this->assertNotNull($message);
        $this->assertNull($message->orders_id);
        $this->assertNotNull($conversation);
        $this->assertNull($conversation->orders_id);
        if (property_exists($conversation, 'unread_for_manager') || Schema::hasColumn('sa_conversations', 'unread_for_manager')) {
            $this->assertSame(1, (int) $conversation->unread_for_manager);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function t01_005_invalid_datetime_returns_validation_error()
    {
        $this->skipIfRouteMissing('POST', self::ENDPOINT);
        $leadId = $this->createLeadForTests();

        $payload = $this->messageCreatedPayload($leadId);
        $payload['occurred_at'] = 'not-a-date';

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function t01_006_missing_api_key_returns_unauthorized()
    {
        $this->skipIfRouteMissing('POST', self::ENDPOINT);
        $leadId = $this->createLeadForTests();

        $response = $this->postJson(self::ENDPOINT, $this->messageCreatedPayload($leadId));

        $this->assertContains($response->getStatusCode(), [401, 403]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function t01_007_message_created_phone_is_sanitized_before_persist()
    {
        $this->skipIfRouteMissing('POST', self::ENDPOINT);
        $leadId = $this->createLeadForTests();
        $payload = $this->messageCreatedPayload($leadId);
        $payload['data']['message']['from']['phone'] = '+371 (29) 123-45-67 ext.89';

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(200)->assertJsonPath('status', 'ok');

        if (!Schema::hasTable('sa_conversations')) {
            $this->markTestSkipped('sa_conversations is not available in current test DB connection');
        }

        $storedPhone = (string) DB::table('sa_conversations')
            ->where('conversation_id', (string) data_get($payload, 'data.conversation_id'))
            ->value('client_phone');

        $this->assertSame('+37129123456789', $storedPhone);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function t01_008_message_created_overlong_phone_returns_validation_error()
    {
        $this->skipIfRouteMissing('POST', self::ENDPOINT);
        $leadId = $this->createLeadForTests();
        $payload = $this->messageCreatedPayload($leadId);
        $payload['data']['message']['from']['phone'] = '+38017756437000000000000';

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function t01_009_create_lead_binds_preorder_messages_by_conversation()
    {
        $this->skipIfRouteMissing('POST', self::ENDPOINT);
        $this->skipIfRouteMissing('POST', self::CREATE_LEAD_ENDPOINT);

        $payload = $this->messageCreatedPayload(null);
        unset($payload['data']['lead_id']);

        $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders())
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok');

        $conversationId = (string) data_get($payload, 'data.conversation_id');
        $messageId = (string) data_get($payload, 'data.message.message_id');
        $clientPhone = (string) data_get($payload, 'data.message.from.phone');

        $leadResponse = $this->postJson(self::CREATE_LEAD_ENDPOINT, [
            'idempotency_key' => 'bind_preorder_messages:' . $this->uniqueSuffix(),
            'source' => 'SA',
            'lead' => [
                'client' => [
                    'phone' => $clientPhone,
                    'name' => 'Bind Test',
                ],
                'channel' => 'whatsapp',
                'external_ids' => [
                    'conversation_id' => $conversationId,
                ],
            ],
        ], $this->apiHeaders());

        $leadResponse->assertStatus(200)->assertJsonPath('status', 'ok');
        $leadId = (int) $leadResponse->json('data.lead_id');

        if (!Schema::hasTable('sa_messages') || !Schema::hasTable('sa_conversations')) {
            $this->markTestSkipped('sa_messages/sa_conversations are not available in current test DB connection');
        }

        $storedOrderId = DB::table('sa_messages')
            ->where('message_id', $messageId)
            ->value('orders_id');
        $conversationOrderId = DB::table('sa_conversations')
            ->where('conversation_id', $conversationId)
            ->value('orders_id');

        $this->assertSame($leadId, (int) $storedOrderId);
        $this->assertSame($leadId, (int) $conversationOrderId);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function t01_010_inbound_message_marks_conversation_as_unread_for_manager()
    {
        $this->skipIfRouteMissing('POST', self::ENDPOINT);
        $leadId = $this->createLeadForTests();

        if (!Schema::hasTable('sa_conversations') || !Schema::hasColumn('sa_conversations', 'unread_for_manager')) {
            $this->markTestSkipped('unread_for_manager column is not available in current test DB connection');
        }

        $payload = $this->messageCreatedPayload($leadId);

        $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders())
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok');

        $unread = (int) DB::table('sa_conversations')
            ->where('conversation_id', (string) data_get($payload, 'data.conversation_id'))
            ->value('unread_for_manager');

        $this->assertSame(1, $unread);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function t01_011_bot_outbound_message_marks_conversation_as_unread_for_manager()
    {
        $this->skipIfRouteMissing('POST', self::ENDPOINT);
        $leadId = $this->createLeadForTests();

        if (!Schema::hasTable('sa_conversations') || !Schema::hasColumn('sa_conversations', 'unread_for_manager')) {
            $this->markTestSkipped('unread_for_manager column is not available in current test DB connection');
        }

        $payload = $this->messageCreatedPayload($leadId);
        $payload['data']['message']['message_id'] = 'MSG_BOT_' . $this->uniqueSuffix();
        $payload['data']['message']['direction'] = 'outbound';
        $payload['data']['message']['from'] = [
            'type' => 'bot',
            'id' => 'SA-BOT-1',
            'name' => 'SA Bot',
        ];
        $payload['data']['message']['to'] = [
            'type' => 'client',
            'phone' => '+380686914307',
        ];
        $payload['data']['message']['text'] = 'Ответ бота клиенту.';
        $payload['idempotency_key'] = 'message.created:bot:' . $payload['data']['message']['message_id'];

        $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders())
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok');

        $conversation = DB::table('sa_conversations')
            ->where('conversation_id', (string) data_get($payload, 'data.conversation_id'))
            ->first();
        $message = DB::table('sa_messages')
            ->where('message_id', (string) data_get($payload, 'data.message.message_id'))
            ->first();

        $this->assertSame(1, (int) $conversation->unread_for_manager);
        $this->assertSame('bot', (string) data_get(json_decode($message->from_json, true), 'type'));
    }

    private function apiHeaders(): array
    {
        return [
            'X-Api-Key' => self::API_KEY,
            'Content-Type' => 'application/json',
        ];
    }

    private function messageCreatedPayload(?string $leadId, ?string $messageId = null): array
    {
        $uniq = $this->uniqueSuffix();
        $messageId = $messageId ?: 'MSG_' . $uniq;
        $conversationId = 'CONV-' . $uniq;

        $payload = [
            'event_id' => $this->uuidV4(),
            'event_type' => 'message.created',
            'idempotency_key' => 'message.created:whatsapp:' . $messageId . ':' . $uniq,
            'occurred_at' => now()->toIso8601String(),
            'source' => 'SA',
            'data' => [
                'conversation_id' => $conversationId,
                'channel' => 'whatsapp',
                'message' => [
                    'message_id' => $messageId,
                    'direction' => 'inbound',
                    'from' => [
                        'type' => 'client',
                        'phone' => '+380686914307',
                        'name' => 'Иван',
                    ],
                    'to' => [
                        'type' => 'agent',
                        'id' => 'SA-BOT-1',
                    ],
                    'text' => 'Добрый день! Хочу заказать услугу.',
                    'attachments' => [],
                    'sent_at' => now()->toIso8601String(),
                    'status' => 'received',
                    'provider_meta' => [
                        'provider' => 'WASender',
                        'wa_message_id' => 'wamid.HBgM...',
                    ],
                ],
            ],
        ];

        if ($leadId !== null && $leadId !== '') {
            $payload['data']['lead_id'] = $leadId;
        }

        return $payload;
    }

    private function messageStatusPayload(string $leadId, string $messageId): array
    {
        $uniq = $this->uniqueSuffix();

        return [
            'event_id' => $this->uuidV4(),
            'event_type' => 'message.status',
            'idempotency_key' => 'message.status:whatsapp:' . $messageId . ':delivered:' . $uniq,
            'occurred_at' => now()->toIso8601String(),
            'source' => 'SA',
            'data' => [
                'lead_id' => $leadId,
                'conversation_id' => 'CONV-' . $uniq,
                'channel' => 'whatsapp',
                'message_id' => $messageId,
                'status' => 'delivered',
                'status_at' => now()->toIso8601String(),
            ],
        ];
    }

    private function createLeadForTests(): string
    {
        $uniq = $this->uniqueSuffix();
        $phone = '+37125' . substr($uniq, -6);

        $payload = [
            'idempotency_key' => 'create_for_messages:' . $uniq,
            'source' => 'SA',
            'lead' => [
                'client' => [
                    'phone' => $phone,
                    'name' => 'Messages Test',
                ],
                'channel' => 'whatsapp',
            ],
        ];

        $response = $this->postJson(self::CREATE_LEAD_ENDPOINT, $payload, $this->apiHeaders());
        $response->assertStatus(200)->assertJsonPath('status', 'ok');

        return (string) $response->json('data.lead_id');
    }

    private function uniqueSuffix(): string
    {
        return str_replace('.', '', (string) microtime(true)) . mt_rand(100, 999);
    }

    private function uuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    private function skipIfRouteMissing(string $method, string $uri): void
    {
        $routes = app('router')->getRoutes();
        $normalized = ltrim($uri, '/');

        foreach ($routes as $route) {
            if (!in_array(strtoupper($method), $route->methods(), true)) {
                continue;
            }

            if ($route->uri() === $normalized) {
                return;
            }
        }

        $this->markTestSkipped(sprintf('%s %s is not registered yet.', strtoupper($method), $uri));
    }
}
