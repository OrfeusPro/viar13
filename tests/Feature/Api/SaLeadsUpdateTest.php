<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class SaLeadsUpdateTest extends TestCase
{
    private const CREATE_ENDPOINT = '/api/sa/leads';
    private const API_KEY = 'test-key';

    /** @test */
    public function t06_001_update_fields_valid_returns_ok()
    {
        $leadId = $this->createLeadForUpdate();
        $response = $this->patchJson('/api/sa/leads/' . $leadId, $this->payloadFields(), $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.updated', true)
            ->assertJsonPath('data.fields_updated.0', 'email');
    }

    /** @test */
    public function t06_002_update_stage_valid_returns_ok()
    {
        $leadId = $this->createLeadForUpdate();
        $response = $this->patchJson('/api/sa/leads/' . $leadId, $this->payloadStageValid(), $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.stage.id', 'pegging');
    }

    /** @test */
    public function t06_003_non_sequential_stage_transition_returns_ok()
    {
        $leadId = $this->createLeadForUpdate();
        $response = $this->patchJson('/api/sa/leads/' . $leadId, $this->payloadStageForbidden(), $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.stage.id', 'pegging');
    }

    /** @test */
    public function t06_003b_direct_jump_to_completed_returns_ok()
    {
        $leadId = $this->createLeadForUpdate();
        $payload = [
            'idempotency_key' => 'lead_update:stage_completed_direct:' . $this->uniqueSuffix(),
            'source' => 'SA',
            'update' => [
                'stage' => [
                    'from' => ['id' => 'watching'],
                    'id' => 'completed',
                ],
            ],
        ];

        $response = $this->patchJson('/api/sa/leads/' . $leadId, $payload, $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.stage.id', 'completed');
    }

    /** @test */
    public function t06_003a_legacy_stg_stage_returns_validation_error()
    {
        $leadId = $this->createLeadForUpdate();
        $payload = [
            'idempotency_key' => 'lead_update:legacy_stage:' . $this->uniqueSuffix(),
            'source' => 'SA',
            'update' => [
                'stage' => [
                    'from' => ['id' => 'watching'],
                    'id' => 'STG-20',
                ],
            ],
        ];

        $response = $this->patchJson('/api/sa/leads/' . $leadId, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function t06_003c_unknown_stage_returns_validation_error()
    {
        $leadId = $this->createLeadForUpdate();
        $payload = [
            'idempotency_key' => 'lead_update:unknown_stage:' . $this->uniqueSuffix(),
            'source' => 'SA',
            'update' => [
                'stage' => [
                    'id' => 'archived',
                ],
            ],
        ];

        $response = $this->patchJson('/api/sa/leads/' . $leadId, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function t06_004_tags_add_remove_returns_ok()
    {
        $leadId = $this->createLeadForUpdate();
        $response = $this->patchJson('/api/sa/leads/' . $leadId, $this->payloadTags(), $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.tags.added.0', 'vip')
            ->assertJsonPath('data.tags.removed.0', 'cold');
    }

    /** @test */
    public function t06_005_duplicate_idempotency_key_returns_duplicate()
    {
        $leadId = $this->createLeadForUpdate();
        $payload = $this->payloadFields();

        $this->patchJson('/api/sa/leads/' . $leadId, $payload, $this->apiHeaders())->assertStatus(200);
        $response = $this->patchJson('/api/sa/leads/' . $leadId, $payload, $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'duplicate');
    }

    /** @test */
    public function t06_006_unknown_lead_id_returns_not_found()
    {
        $endpoint = '/api/sa/leads/UNKNOWN-LEAD-999';
        $response = $this->patchJson($endpoint, $this->payloadFieldsWithKey('lead_update:unknown:' . $this->uniqueSuffix()), $this->apiHeaders());

        $response->assertStatus(404)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'LEAD_NOT_FOUND');
    }

    /** @test */
    public function t06_007_missing_api_key_returns_unauthorized()
    {
        $leadId = $this->createLeadForUpdate();
        $response = $this->patchJson('/api/sa/leads/' . $leadId, $this->payloadFields());

        $this->assertContains($response->getStatusCode(), [401, 403]);
    }

    private function apiHeaders(): array
    {
        return [
            'X-Api-Key' => self::API_KEY,
            'Content-Type' => 'application/json',
        ];
    }

    private function payloadFields(): array
    {
        return $this->payloadFieldsWithKey('lead_update:fields:' . $this->uniqueSuffix());
    }

    private function payloadFieldsWithKey(string $key): array
    {
        return [
            'idempotency_key' => $key,
            'source' => 'SA',
            'update' => [
                'fields' => [
                    'email' => 'ivan@gmail.com',
                    'city' => 'Kyiv',
                    'budget' => 1500,
                ],
            ],
        ];
    }

    private function payloadStageValid(): array
    {
        return [
            'idempotency_key' => 'lead_update:stage_valid:' . $this->uniqueSuffix(),
            'source' => 'SA',
            'update' => [
                'stage' => [
                    'from' => ['id' => 'watching'],
                    'id' => 'pegging',
                ],
            ],
        ];
    }

    private function payloadStageForbidden(): array
    {
        return [
            'idempotency_key' => 'lead_update:stage_forbidden:' . $this->uniqueSuffix(),
            'source' => 'SA',
            'update' => [
                'stage' => [
                    'from' => ['id' => 'in_production'],
                    'id' => 'pegging',
                ],
            ],
        ];
    }

    private function payloadTags(): array
    {
        return [
            'idempotency_key' => 'lead_update:tags:' . $this->uniqueSuffix(),
            'source' => 'SA',
            'update' => [
                'tags_add' => ['vip'],
                'tags_remove' => ['cold'],
                'notes_append' => [
                    [
                        'text' => 'Client confirmed email',
                        'created_at' => '2026-02-12T17:10:00Z',
                    ],
                ],
            ],
        ];
    }

    private function createLeadForUpdate(): string
    {
        $uniq = $this->uniqueSuffix();
        $phone = '+37128' . substr($uniq, -6);

        $payload = [
            'idempotency_key' => 'create_for_update:' . $uniq,
            'source' => 'SA',
            'lead' => [
                'client' => [
                    'phone' => $phone,
                    'name' => 'Update Test',
                ],
                'channel' => 'whatsapp',
            ],
        ];

        $response = $this->postJson(self::CREATE_ENDPOINT, $payload, $this->apiHeaders());
        $response->assertStatus(200)->assertJsonPath('status', 'ok');

        return (string) $response->json('data.lead_id');
    }

    private function uniqueSuffix(): string
    {
        return str_replace('.', '', (string) microtime(true)) . mt_rand(100, 999);
    }
}
