<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class CrmWebhooksPipelineChangedTest extends TestCase
{
    private const ENDPOINT = '/api/crm/webhooks/pipeline-changed';
    private const CREATE_LEAD_ENDPOINT = '/api/sa/leads';
    private const API_KEY = 'test-key';

    /** @test */
    public function t04_001_valid_stage_change_returns_ok()
    {
        $leadId = $this->createLeadForTests();
        $response = $this->postJson(self::ENDPOINT, $this->payload($leadId), $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.stage.to.id', 'in_production');
    }

    /** @test */
    public function t04_002_duplicate_event_returns_duplicate()
    {
        $leadId = $this->createLeadForTests();
        $payload = $this->payload($leadId);

        $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders())->assertStatus(200);

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'duplicate');
    }

    /** @test */
    public function t04_003_non_sequential_stage_transition_returns_ok()
    {
        $leadId = $this->createLeadForTests();
        $payload = $this->payload($leadId);
        $payload['event_id'] = $this->uuidV4();
        $payload['idempotency_key'] = 'crm.lead.stage_changed:invalid:' . $this->uniqueSuffix();
        $payload['data']['stage']['from']['id'] = 'in_production';
        $payload['data']['stage']['to']['id'] = 'pegging';

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.stage.to.id', 'pegging')
            ->assertJsonPath('data.order_status', 'pegging');
    }

    /** @test */
    public function t04_003b_direct_jump_to_completed_returns_ok()
    {
        $leadId = $this->createLeadForTests();
        $payload = $this->payload($leadId);
        $payload['event_id'] = $this->uuidV4();
        $payload['idempotency_key'] = 'crm.lead.stage_changed:direct_completed:' . $this->uniqueSuffix();
        $payload['data']['stage']['from']['id'] = 'watching';
        $payload['data']['stage']['to']['id'] = 'completed';

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.stage.to.id', 'completed')
            ->assertJsonPath('data.order_status', 'completed');
    }

    /** @test */
    public function t04_004_missing_stage_to_id_returns_validation_error()
    {
        $leadId = $this->createLeadForTests();
        $payload = $this->payload($leadId);
        $payload['event_id'] = $this->uuidV4();
        $payload['idempotency_key'] = 'crm.lead.stage_changed:missing_to:' . $this->uniqueSuffix();
        unset($payload['data']['stage']['to']['id']);

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function t04_004a_legacy_stg_stage_returns_validation_error()
    {
        $leadId = $this->createLeadForTests();
        $payload = $this->payload($leadId);
        $payload['event_id'] = $this->uuidV4();
        $payload['idempotency_key'] = 'crm.lead.stage_changed:legacy_to:' . $this->uniqueSuffix();
        $payload['data']['stage']['to']['id'] = 'STG-30';

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function t04_004b_unknown_stage_returns_validation_error()
    {
        $leadId = $this->createLeadForTests();
        $payload = $this->payload($leadId);
        $payload['event_id'] = $this->uuidV4();
        $payload['idempotency_key'] = 'crm.lead.stage_changed:unknown_to:' . $this->uniqueSuffix();
        $payload['data']['stage']['to']['id'] = 'archived';

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function t04_005_bot_control_paused_is_reflected_in_response()
    {
        $leadId = $this->createLeadForTests();
        $payload = $this->payload($leadId);
        $payload['event_id'] = $this->uuidV4();
        $payload['idempotency_key'] = 'crm.lead.stage_changed:paused:' . $this->uniqueSuffix();
        $payload['data']['bot_control']['mode'] = 'paused';

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.bot_mode', 'paused');
    }

    /** @test */
    public function t04_006_missing_api_key_returns_unauthorized()
    {
        $leadId = $this->createLeadForTests();
        $response = $this->postJson(self::ENDPOINT, $this->payload($leadId));

        $this->assertContains($response->getStatusCode(), [401, 403]);
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
        $uniq = $this->uniqueSuffix();

        return [
            'event_id' => $this->uuidV4(),
            'event_type' => 'crm.lead.stage_changed',
            'idempotency_key' => 'crm.lead.stage_changed:' . $leadId . ':' . $uniq,
            'occurred_at' => now()->toIso8601String(),
            'source' => 'CRM',
            'data' => [
                'lead_id' => $leadId,
                'pipeline' => [
                    'id' => 'PIPE-1',
                    'name' => 'Sales',
                ],
                'stage' => [
                    'from' => ['id' => 'pegging', 'name' => 'В процессе'],
                    'to' => ['id' => 'in_production', 'name' => 'В производстве'],
                ],
                'changed_by' => [
                    'type' => 'manager',
                    'id' => 'CRM-U-12',
                    'name' => 'Olga',
                ],
                'reason' => 'Client replied',
                'bot_control' => [
                    'mode' => 'paused',
                ],
            ],
        ];
    }

    private function createLeadForTests(): string
    {
        $uniq = $this->uniqueSuffix();
        $phone = '+37126' . substr($uniq, -6);

        $payload = [
            'idempotency_key' => 'create_for_pipeline:' . $uniq,
            'source' => 'SA',
            'lead' => [
                'client' => [
                    'phone' => $phone,
                    'name' => 'Pipeline Test',
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

    /** @test */
    public function t04_007_send_lubanas_stage_change_returns_ok()
    {
        $leadId = $this->createLeadForTests();

        $responseToProduction = $this->postJson(self::ENDPOINT, $this->payloadWithStages($leadId, 'watching', 'pegging'), $this->apiHeaders());
        $responseToProduction->assertStatus(200)->assertJsonPath('data.stage.to.id', 'pegging');

        $responseToShipped = $this->postJson(self::ENDPOINT, $this->payloadWithStages($leadId, 'pegging', 'in_production'), $this->apiHeaders());
        $responseToShipped->assertStatus(200)->assertJsonPath('data.stage.to.id', 'in_production');

        $responseToSent = $this->postJson(self::ENDPOINT, $this->payloadWithStages($leadId, 'in_production', 'sended'), $this->apiHeaders());
        $responseToSent->assertStatus(200)->assertJsonPath('data.stage.to.id', 'sended');

        $response = $this->postJson(self::ENDPOINT, $this->payloadWithStages($leadId, 'sended', 'send_lubanas'), $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.stage.to.id', 'send_lubanas')
            ->assertJsonPath('data.order_status', 'send_lubanas');
    }

    private function payloadWithStages(string $leadId, string $fromStage, string $toStage): array
    {
        $payload = $this->payload($leadId);
        $payload['event_id'] = $this->uuidV4();
        $payload['idempotency_key'] = 'crm.lead.stage_changed:' . $leadId . ':' . $fromStage . ':' . $toStage . ':' . $this->uniqueSuffix();
        $payload['data']['stage']['from']['id'] = $fromStage;
        $payload['data']['stage']['to']['id'] = $toStage;

        return $payload;
    }
}
