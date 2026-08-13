<?php

namespace Tests\Feature\Api;

use App\Models\SaEscalation;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SaEscalationsCreateTest extends TestCase
{
    private const ENDPOINT = '/api/sa/escalations';
    private const CREATE_LEAD_ENDPOINT = '/api/sa/leads';
    private const API_KEY = 'test-key';

    /** @test */
    public function t07_001_valid_escalation_returns_ok()
    {
        $leadId = $this->createLeadForTests();
        $response = $this->postJson(self::ENDPOINT, $this->payload($leadId), $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonStructure([
                'data' => ['task_id', 'lead_id', 'links' => ['task_url', 'lead_url']],
            ]);
    }

    /** @test */
    public function t07_002_duplicate_request_returns_duplicate()
    {
        $leadId = $this->createLeadForTests();
        $payload = $this->payload($leadId);

        $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders())->assertStatus(200);
        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'duplicate');
    }

    /** @test */
    public function t07_003_missing_reason_code_returns_validation_error()
    {
        $leadId = $this->createLeadForTests();
        $payload = $this->payloadWithKey($leadId, 'escalation:missing_reason:' . $this->uniqueSuffix());
        unset($payload['escalation']['reason_code']);

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function t07_004_missing_dialog_messages_returns_validation_error()
    {
        $leadId = $this->createLeadForTests();
        $payload = $this->payloadWithKey($leadId, 'escalation:missing_messages:' . $this->uniqueSuffix());
        unset($payload['escalation']['dialog']['messages']);

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function t07_005_set_handoff_to_manager_is_reflected()
    {
        $leadId = $this->createLeadForTests();
        $payload = $this->payloadWithKey($leadId, 'escalation:handoff:' . $this->uniqueSuffix());
        $payload['escalation']['bot_control']['set_mode'] = 'handoff_to_manager';

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.bot_mode', 'handoff_to_manager');
    }

    /** @test */
    public function t07_006_missing_api_key_returns_unauthorized()
    {
        $leadId = $this->createLeadForTests();
        $response = $this->postJson(self::ENDPOINT, $this->payload($leadId));

        $this->assertContains($response->getStatusCode(), [401, 403]);
    }

    /** @test */
    public function t07_007_client_phone_is_sanitized_before_persist()
    {
        $leadId = $this->createLeadForTests();
        $payload = $this->payloadWithKey($leadId, 'escalation:sanitize_phone:' . $this->uniqueSuffix());
        $payload['escalation']['dialog']['client']['phone'] = '+371 (29) 123-45-67 ext.89';

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(200)->assertJsonPath('status', 'ok');

        if (!Schema::hasTable('sa_escalations')) {
            $this->markTestSkipped('sa_escalations is not available in current test DB connection');
        }

        $taskId = (string) $response->json('data.task_id');
        $dialogJson = (string) optional(SaEscalation::query()->where('task_ref', $taskId)->latest('id')->first())->dialog_json;
        $dialog = json_decode($dialogJson, true);

        $this->assertSame('+37129123456789', data_get($dialog, 'client.phone'));
    }

    /** @test */
    public function t07_008_overlong_client_phone_returns_validation_error()
    {
        $leadId = $this->createLeadForTests();
        $payload = $this->payloadWithKey($leadId, 'escalation:long_phone:' . $this->uniqueSuffix());
        $payload['escalation']['dialog']['client']['phone'] = '+38017756437000000000000';

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    private function apiHeaders(): array
    {
        return [
            'X-Api-Key' => self::API_KEY,
            'Content-Type' => 'application/json',
        ];
    }

    private function payload(string $leadId): array
    {
        return $this->payloadWithKey($leadId, 'escalation:' . $this->uniqueSuffix());
    }

    private function payloadWithKey(string $leadId, string $idempotencyKey): array
    {
        $uniq = $this->uniqueSuffix();

        return [
            'idempotency_key' => $idempotencyKey,
            'source' => 'SA',
            'escalation' => [
                'lead_id' => $leadId,
                'conversation_id' => 'CONV-' . $uniq,
                'priority' => 'urgent',
                'reason_code' => 'NO_KB_ANSWER',
                'reason_text' => 'Need manager handoff',
                'confidence' => 0.42,
                'suggested_next' => [
                    'manager_goal' => 'Clarify conditions',
                    'draft_reply' => 'Manager will contact you shortly',
                ],
                'dialog' => [
                    'channel' => 'whatsapp',
                    'client' => [
                        'phone' => '+380686914307',
                        'name' => 'Ivan',
                    ],
                    'messages' => [
                        [
                            'message_id' => 'MSG_' . $uniq,
                            'direction' => 'inbound',
                            'text' => 'Need help',
                            'sent_at' => now()->toIso8601String(),
                        ],
                    ],
                ],
                'bot_control' => [
                    'set_mode' => 'handoff_to_manager',
                    'allow_manager_takeover' => true,
                ],
            ],
        ];
    }

    private function createLeadForTests(): string
    {
        $uniq = $this->uniqueSuffix();
        $phone = '+37127' . substr($uniq, -6);

        $payload = [
            'idempotency_key' => 'create_for_escalation:' . $uniq,
            'source' => 'SA',
            'lead' => [
                'client' => [
                    'phone' => $phone,
                    'name' => 'Escalation Test',
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
}
