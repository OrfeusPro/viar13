<?php

namespace Tests\Feature\Api;

use App\Models\SaMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CrmWebhooksSendMessageTest extends TestCase
{
    use \Tests\Support\CreatesSaChatSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSaChatSchema();
    }

    private const ENDPOINT = '/api/crm/webhooks/send-message';
    private const API_KEY = 'test-key';

    #[\PHPUnit\Framework\Attributes\Test]
    public function t02_001_valid_send_request_returns_accepted()
    {
        $this->skipIfRouteMissing('POST', self::ENDPOINT);

        $response = $this->postJson(self::ENDPOINT, $this->sendMessagePayload(), $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('result.accepted', true);

        $messageId = (string) $response->json('result.message_id');
        $this->assertNotSame('', $messageId);
        $this->assertStringStartsWith('OUT-', $messageId);
        $this->assertSame($messageId, (string) $response->json('result.sa_message_id'));
        $this->assertNull($response->json('result.provider_message_id'));
        $this->assertNotNull(SaMessage::query()->where('message_id', $messageId)->first());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function t02_002_duplicate_idempotency_key_does_not_resend()
    {
        $this->skipIfRouteMissing('POST', self::ENDPOINT);

        $this->mock(\App\Services\SynvolveWebhookService::class, fn ($mock) => $mock
            ->shouldReceive('notifyManagerMessageForOrderOrPhone')->once()->andReturn(true));

        $payload = $this->sendMessagePayload();

        $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders())->assertStatus(200);

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'duplicate');
        $this->assertDatabaseCount('sa_messages', 1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function t02_003_mode_after_send_handoff_to_manager_returns_bot_mode()
    {
        $this->skipIfRouteMissing('POST', self::ENDPOINT);

        $response = $this->postJson(self::ENDPOINT, $this->sendMessagePayload(), $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('result.bot_mode', 'handoff_to_manager');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function t02_004_missing_client_phone_returns_validation_error()
    {
        $this->skipIfRouteMissing('POST', self::ENDPOINT);

        $payload = $this->sendMessagePayload();
        unset($payload['data']['client']['phone']);

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function t02_005_missing_message_text_returns_validation_error()
    {
        $this->skipIfRouteMissing('POST', self::ENDPOINT);

        $payload = $this->sendMessagePayload();
        unset($payload['data']['message']['text']);
        $payload['idempotency_key'] = 'crm.message.send:CRM-LEAD-100045:1701';
        $payload['event_id'] = '6ea6270f-0df2-4f67-9eb8-f55de16d4fa1';

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function t02_006_missing_api_key_returns_unauthorized()
    {
        $this->skipIfRouteMissing('POST', self::ENDPOINT);

        $response = $this->postJson(self::ENDPOINT, $this->sendMessagePayload());

        $this->assertContains($response->getStatusCode(), [401, 403]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function t02_007_missing_lead_id_is_allowed_for_preorder_conversation()
    {
        $this->skipIfRouteMissing('POST', self::ENDPOINT);

        $payload = $this->sendMessagePayload();
        unset($payload['data']['lead_id']);
        $payload['idempotency_key'] = 'crm.message.send:no-lead:' . microtime(true);
        $payload['event_id'] = $this->uuidV4();

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('result.accepted', true);

        if (!Schema::hasTable('sa_messages')) {
            $this->markTestSkipped('sa_messages is not available in current test DB connection');
        }

        $messageId = (string) $response->json('result.message_id');
        $message = SaMessage::query()->where('message_id', $messageId)->first();

        $this->assertNotNull($message);
        $this->assertNull($message->orders_id);
        $this->assertSame((string) data_get($payload, 'data.conversation_id'), (string) $message->conversation_id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function t02_008_outbound_send_marks_conversation_as_read_for_manager()
    {
        $this->skipIfRouteMissing('POST', self::ENDPOINT);

        if (!Schema::hasTable('sa_conversations') || !Schema::hasColumn('sa_conversations', 'unread_for_manager')) {
            $this->markTestSkipped('unread_for_manager column is not available in current test DB connection');
        }

        $payload = $this->sendMessagePayload();
        DB::table('sa_conversations')->updateOrInsert(
            ['conversation_id' => (string) data_get($payload, 'data.conversation_id')],
            [
                'client_phone' => (string) data_get($payload, 'data.client.phone'),
                'client_name' => 'CRM Test',
                'channel' => 'whatsapp',
                'unread_for_manager' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders())
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok');

        $unread = (int) DB::table('sa_conversations')
            ->where('conversation_id', (string) data_get($payload, 'data.conversation_id'))
            ->value('unread_for_manager');

        $this->assertSame(0, $unread);
    }

    private function apiHeaders(): array
    {
        return [
            'X-Api-Key' => self::API_KEY,
            'Content-Type' => 'application/json',
        ];
    }

    private function sendMessagePayload(): array
    {
        $uniq = str_replace('.', '', (string) microtime(true)) . mt_rand(100, 999);

        return [
            'event_id' => $this->uuidV4(),
            'event_type' => 'crm.message.send',
            'idempotency_key' => 'crm.message.send:CRM-LEAD-100045:' . $uniq,
            'occurred_at' => '2026-02-12T16:40:00Z',
            'source' => 'CRM',
            'data' => [
                'lead_id' => 'CRM-LEAD-100045',
                'conversation_id' => 'CONV-778899',
                'channel' => 'whatsapp',
                'client' => [
                    'phone' => '+380686914307',
                ],
                'manager' => [
                    'id' => 'CRM-U-12',
                    'name' => 'Ольга',
                ],
                'message' => [
                    'client_visible_sender' => 'manager',
                    'text' => 'Здравствуйте! Подскажите, пожалуйста, удобное время для звонка?',
                ],
                'bot_control' => [
                    'mode_after_send' => 'handoff_to_manager',
                ],
            ],
        ];
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
