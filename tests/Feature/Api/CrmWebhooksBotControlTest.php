<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CrmWebhooksBotControlTest extends TestCase
{
    private const ENDPOINT = '/api/crm/webhooks/bot-control';
    private const API_KEY = 'test-key';

    /** @test */
    public function t08_001_resume_bot_returns_active_mode()
    {
        $payload = $this->payloadWithAction('resume_bot', $this->uuidV4());
        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.bot_mode', 'active');
    }

    /** @test */
    public function t08_002_pause_bot_returns_paused_mode()
    {
        $payload = $this->payloadWithAction('pause_bot', $this->uuidV4());
        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.bot_mode', 'paused');
    }

    /** @test */
    public function t08_003_handoff_to_manager_returns_handoff_mode()
    {
        $payload = $this->payloadWithAction('handoff_to_manager', $this->uuidV4());
        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.bot_mode', 'handoff_to_manager');
    }

    /** @test */
    public function t08_004_duplicate_event_returns_duplicate()
    {
        $payload = $this->payloadWithAction('resume_bot', $this->uuidV4());

        $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders())->assertStatus(200);
        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'duplicate');
    }

    /** @test */
    public function t08_005_conversation_id_without_lead_id_updates_conversation_bot_mode()
    {
        if (!Schema::hasTable('sa_conversations')) {
            $this->markTestSkipped('sa_conversations is not available in current test DB connection');
        }

        $conversationId = 'CONV-BOT-NO-LEAD-' . substr(str_replace('-', '', $this->uuidV4()), 0, 12);
        DB::table('sa_conversations')->insert([
            'conversation_id' => $conversationId,
            'orders_id' => null,
            'client_phone' => '+37129999999',
            'client_name' => 'No Lead Client',
            'channel' => 'whatsapp',
            'bot_mode' => 'active',
            'last_message_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = $this->payloadWithAction('pause_bot', $this->uuidV4());
        unset($payload['data']['lead_id']);
        $payload['data']['conversation_id'] = $conversationId;
        $payload['idempotency_key'] = 'crm.bot_control:' . $conversationId . ':pause';

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.lead_id', null)
            ->assertJsonPath('data.resolved_lead_id', null)
            ->assertJsonPath('data.bot_mode', 'paused');

        $this->assertSame(
            'paused',
            (string) DB::table('sa_conversations')->where('conversation_id', $conversationId)->value('bot_mode')
        );
    }

    /** @test */
    public function t08_006_lead_id_without_conversation_id_updates_order_bot_mode()
    {
        if (!Schema::hasTable('orders') || !Schema::hasColumn('orders', 'sa_bot_mode')) {
            $this->markTestSkipped('orders.sa_bot_mode is not available in current test DB connection');
        }

        $orderId = DB::table('orders')->orderByDesc('id')->value('id');
        if ($orderId === null) {
            $this->markTestSkipped('No real order is available for lead-only bot-control test');
        }

        $oldBotMode = DB::table('orders')->where('id', $orderId)->value('sa_bot_mode');

        try {
            $payload = $this->payloadWithAction('pause_bot', $this->uuidV4());
            $payload['data']['lead_id'] = (string) $orderId;
            unset($payload['data']['conversation_id']);
            $payload['idempotency_key'] = 'crm.bot_control:lead:' . $orderId . ':pause:' . substr(str_replace('-', '', $this->uuidV4()), 0, 8);

            $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

            $response->assertStatus(200)
                ->assertJsonPath('status', 'ok')
                ->assertJsonPath('data.lead_id', (string) $orderId)
                ->assertJsonPath('data.resolved_lead_id', (int) $orderId)
                ->assertJsonPath('data.bot_mode', 'paused');

            $this->assertSame(
                'paused',
                (string) DB::table('orders')->where('id', $orderId)->value('sa_bot_mode')
            );
        } finally {
            DB::table('orders')->where('id', $orderId)->update(['sa_bot_mode' => $oldBotMode]);
        }
    }

    /** @test */
    public function t08_007_missing_lead_id_and_conversation_id_returns_validation_error()
    {
        $payload = $this->payloadWithAction('resume_bot', $this->uuidV4());
        unset($payload['data']['lead_id'], $payload['data']['conversation_id']);

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function t08_008_missing_changed_by_id_returns_validation_error()
    {
        $payload = $this->payloadWithAction('resume_bot', $this->uuidV4());
        unset($payload['data']['changed_by']['id']);

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function t08_009_missing_api_key_returns_unauthorized()
    {
        $response = $this->postJson(self::ENDPOINT, $this->payloadWithAction('resume_bot', $this->uuidV4()));

        $this->assertContains($response->getStatusCode(), [401, 403]);
    }

    private function apiHeaders(): array
    {
        return [
            'X-Api-Key' => self::API_KEY,
            'Content-Type' => 'application/json',
        ];
    }

    private function payloadWithAction(string $action, string $eventId): array
    {
        return [
            'event_id' => $eventId,
            'event_type' => 'crm.bot_control',
            'idempotency_key' => 'crm.bot_control:CONV-778899:' . $action,
            'occurred_at' => '2026-02-12T18:00:00Z',
            'source' => 'CRM',
            'data' => [
                'lead_id' => 'CRM-LEAD-100045',
                'conversation_id' => 'CONV-778899',
                'action' => $action,
                'changed_by' => [
                    'type' => 'manager',
                    'id' => 'CRM-U-12',
                    'name' => 'Olga',
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
}
