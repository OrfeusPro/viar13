<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\AdminSaIntegrationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SaConversationBotControlTest extends TestCase
{
    public function test_admin_bot_control_accepts_conversation_id_without_order_id(): void
    {
        if (!Schema::hasTable('sa_conversations')) {
            $this->markTestSkipped('sa_conversations is not available in current test DB connection');
        }

        $conversationId = 'CONV-ADMIN-NO-ORDER-' . substr(str_replace('-', '', $this->uuidV4()), 0, 12);

        DB::table('sa_conversations')->insert([
            'conversation_id' => $conversationId,
            'orders_id' => null,
            'client_phone' => '+37129999999',
            'client_name' => 'No Order Client',
            'channel' => 'whatsapp',
            'bot_mode' => 'handoff_to_manager',
            'last_message_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $controller = new FakeAdminSaIntegrationController();
            $synvolve = new FakeSynvolveWebhookService();
            app()->instance(\App\Services\SynvolveWebhookService::class, $synvolve);

            $request = Request::create('/admin/sa/bot-control', 'POST', [
                'conversation_id' => $conversationId,
                'action' => 'pause_bot',
            ]);

            $response = $controller->botControl($request);
            $payload = $controller->lastPayload;

            $this->assertSame(200, $response->getStatusCode());
            $this->assertSame('ok', $response->getData(true)['status']);
            $this->assertSame('/api/crm/webhooks/bot-control', $controller->lastEndpoint);
            $this->assertSame('POST', $controller->lastMethod);
            $this->assertSame('crm.bot_control', $payload['event_type']);
            $this->assertSame('pause_bot', $payload['data']['action']);
            $this->assertSame($conversationId, $payload['data']['conversation_id']);
            $this->assertArrayNotHasKey('lead_id', $payload['data']);
            $this->assertSame('conversation', $synvolve->lastType);
            $this->assertSame($conversationId, $synvolve->lastConversationId);
            $this->assertSame('paused', $synvolve->lastBotStatus);
            $this->assertSame('+37129999999', $synvolve->lastPhone);
        } finally {
            app()->forgetInstance(\App\Services\SynvolveWebhookService::class);
            DB::table('sa_conversations')->where('conversation_id', $conversationId)->delete();
        }
    }

    private function uuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

class FakeAdminSaIntegrationController extends AdminSaIntegrationController
{
    public $lastEndpoint;
    public $lastPayload = [];
    public $lastMethod;

    public function __construct()
    {
        parent::__construct(new \Illuminate\Support\Facades\Request());
    }

    protected function dispatchInternalWebhook(string $endpoint, array $payloadArray = [], string $method = 'POST'): array
    {
        $this->lastEndpoint = $endpoint;
        $this->lastPayload = $payloadArray;
        $this->lastMethod = $method;
        $action = (string) ($payloadArray['data']['action'] ?? '');
        $botMode = [
            'resume_bot' => 'active',
            'pause_bot' => 'paused',
            'handoff_to_manager' => 'handoff_to_manager',
        ][$action] ?? 'active';

        return [
            'http_code' => 200,
            'api_response' => [
                'status' => 'ok',
                'data' => [
                    'lead_id' => null,
                    'resolved_lead_id' => null,
                    'conversation_id' => $payloadArray['data']['conversation_id'] ?? null,
                    'bot_mode' => $botMode,
                ],
            ],
        ];
    }
}

class FakeSynvolveWebhookService extends \App\Services\SynvolveWebhookService
{
    public $lastType;
    public $lastConversationId;
    public $lastBotStatus;
    public $lastPhone;

    public function notifyBotStatusForConversation(string $conversationId, string $botStatus, ?string $phone = null, array $context = []): bool
    {
        $this->lastType = 'conversation';
        $this->lastConversationId = $conversationId;
        $this->lastBotStatus = $botStatus;
        $this->lastPhone = $phone;

        return true;
    }
}
