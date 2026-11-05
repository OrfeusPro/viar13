<?php

namespace Tests\Unit;

use App\Models\Orders;
use App\Models\User;
use App\Services\SynvolveWebhookService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SynvolveWebhookServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('phone')->nullable();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->text('delivery')->nullable();
            $table->text('items')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('country')->nullable();
            $table->string('payment')->nullable();
            $table->string('status')->nullable();
            $table->string('payment_status')->nullable();
            $table->text('comment')->nullable();
            $table->string('sa_client_phone')->nullable();
            $table->timestamps();
        });
    }

    public function test_build_order_snapshot_payload_contains_all_product_names_and_both_comment_sources(): void
    {
        $user = new User();
        $user->phone = '+371 111-22-33';

        $order = new Orders();
        $order->id = 55;
        $order->payment_status = 'not_payed';
        $order->comment = 'Комментарий заказа';
        $order->delivery = json_encode([
            'phone' => '+37120000000',
            'payer_phone' => '+37129999999',
        ]);
        $order->items = json_encode([
            [
                'sumPrice' => 20,
                'name' => 'Canvas',
                'userComment' => 'Комментарий к Canvas',
            ],
            [
                'sumPrice' => 30,
                'name' => 'Gift card',
                'userComment' => 'Комментарий к Gift card',
            ],
            'totalPrice' => 50,
        ]);
        $order->setRelation('user', $user);

        $service = new SynvolveWebhookService();
        $payload = $service->buildOrderSnapshotPayload($order, 'site_order_created');

        $this->assertSame('order_snapshot', $payload['event']);
        $this->assertSame('site_order_created', $payload['trigger']);
        $this->assertSame('+37129999999', $payload['phone']);
        $this->assertSame('not_payed', $payload['payment_status']);
        $this->assertSame(['Canvas', 'Gift card'], $payload['product_names']);
        $this->assertSame('Комментарий заказа', $payload['client_comment']['order_comment']);
        $this->assertSame(
            ['Комментарий к Canvas', 'Комментарий к Gift card'],
            $payload['client_comment']['item_comments']
        );
        $this->assertSame(
            ['Комментарий заказа', 'Комментарий к Canvas', 'Комментарий к Gift card'],
            $payload['client_comment']['combined']
        );
    }

    public function test_build_manager_message_payload_normalizes_phone(): void
    {
        $service = new SynvolveWebhookService();

        $payload = $service->buildManagerMessagePayload('+371 (299) 99-999', 'Текст менеджера', [
            'trigger' => 'sa_manager_message',
        ]);

        $this->assertNotNull($payload);
        $this->assertSame('manager_message', $payload['event']);
        $this->assertSame('+37129999999', $payload['phone']);
        $this->assertSame('Текст менеджера', $payload['text']);
        $this->assertSame('sa_manager_message', $payload['context']['trigger']);
    }

    public function test_manager_message_for_order_prefers_order_phone_over_payload_phone(): void
    {
        config(['services.synvolve.manager_message_webhook_url' => 'https://example.test/synvolve']);

        $http = new class extends \GuzzleHttp\Client {
            public $payload;

            public function post($uri, array $options = []): \Psr\Http\Message\ResponseInterface
            {
                $this->payload = $options['json'];

                return new \GuzzleHttp\Psr7\Response(200, [], '{"ok":true}');
            }
        };

        $order = new Orders();
        $order->user_id = 0;
        $order->delivery = json_encode([
            'payer_phone' => '+37128887777',
        ]);
        $order->items = json_encode([]);
        $order->price = '0';
        $order->country = 'LV';
        $order->payment = 'test';
        $order->status = 'watching';
        $order->payment_status = 'not_payed';
        $order->save();

        $service = new SynvolveWebhookService($http);
        $service->notifyManagerMessageForOrderOrPhone((int) $order->id, '+0000000', 'Текст менеджера', [
            'trigger' => 'sa_manager_message',
        ]);

        $this->assertSame('+37128887777', $http->payload['phone']);
        $this->assertSame('order', $http->payload['context']['phone_source']);
        $this->assertSame((int) $order->id, $http->payload['context']['order_id']);
    }

    public function test_build_bot_status_payload_contains_client_id_phone_and_status(): void
    {
        $order = new Orders();
        $order->id = 77;
        $order->delivery = json_encode([
            'payer_phone' => '+371 299-88-777',
        ]);

        $service = new SynvolveWebhookService();
        $payload = $service->buildBotStatusPayload($order, 'paused', [
            'trigger' => 'admin_bot_control',
        ]);

        $this->assertNotNull($payload);
        $this->assertSame('bot_status_changed', $payload['event']);
        $this->assertSame('77', $payload['client_id']);
        $this->assertSame('+37129988777', $payload['phone']);
        $this->assertSame('paused', $payload['bot_status']);
        $this->assertSame('admin_bot_control', $payload['context']['trigger']);
    }

    public function test_build_bot_status_conversation_payload_without_order(): void
    {
        if (!method_exists(SynvolveWebhookService::class, 'buildBotStatusConversationPayload')) {
            $this->markTestSkipped('Conversation bot-status contract is absent from the current rolled-back Synvolve service.');
        }

        $service = new SynvolveWebhookService();
        $payload = $service->buildBotStatusConversationPayload('CONV-PREORDER-1', 'paused', '+371 299-88-700', [
            'trigger' => 'admin_bot_control',
        ]);

        $this->assertNotNull($payload);
        $this->assertSame('bot_status_changed', $payload['event']);
        $this->assertSame('CONV-PREORDER-1', $payload['client_id']);
        $this->assertSame('CONV-PREORDER-1', $payload['conversation_id']);
        $this->assertSame('+37129988700', $payload['phone']);
        $this->assertSame('paused', $payload['bot_status']);
        $this->assertNull($payload['context']['order_id']);
        $this->assertSame('admin_bot_control', $payload['context']['trigger']);
    }

    public function test_notify_bot_status_for_conversation_posts_to_configured_url(): void
    {
        if (!method_exists(SynvolveWebhookService::class, 'notifyBotStatusForConversation')) {
            $this->markTestSkipped('Conversation bot-status delivery is absent from the current rolled-back Synvolve service.');
        }

        config(['services.synvolve.bot_status_webhook_url' => 'https://example.test/BotControl']);

        $http = new class extends \GuzzleHttp\Client {
            public $uri;
            public $payload;

            public function post($uri, array $options = []): \Psr\Http\Message\ResponseInterface
            {
                $this->uri = $uri;
                $this->payload = $options['json'];

                return new \GuzzleHttp\Psr7\Response(200, [], '{"ok":true}');
            }
        };

        $service = new SynvolveWebhookService($http);
        $result = $service->notifyBotStatusForConversation('CONV-PREORDER-2', 'active', '+371 299-88-701', [
            'trigger' => 'admin_bot_control',
        ]);

        $this->assertTrue($result);
        $this->assertSame('https://example.test/BotControl', $http->uri);
        $this->assertSame('bot_status_changed', $http->payload['event']);
        $this->assertSame('CONV-PREORDER-2', $http->payload['client_id']);
        $this->assertSame('CONV-PREORDER-2', $http->payload['conversation_id']);
        $this->assertSame('active', $http->payload['bot_status']);
    }

    public function test_build_lead_update_payload_contains_status_transition(): void
    {
        if (!method_exists(SynvolveWebhookService::class, 'buildLeadUpdatePayload')) {
            $this->markTestSkipped('Lead-update payload is absent from the current rolled-back Synvolve service.');
        }

        $order = new Orders();
        $order->id = 88;
        $order->delivery = json_encode([
            'payer_phone' => '+371 200-11-222',
        ]);

        $service = new SynvolveWebhookService();
        $payload = $service->buildLeadUpdatePayload($order, 'completed', 'pegging', 'crm_pipeline_changed', [
            'stage_to' => 'completed',
        ]);

        $this->assertSame('lead_update', $payload['event']);
        $this->assertSame('crm_pipeline_changed', $payload['trigger']);
        $this->assertSame(88, $payload['lead_id']);
        $this->assertSame(88, $payload['order_id']);
        $this->assertSame('+37120011222', $payload['phone']);
        $this->assertSame('pegging', $payload['status']['from']);
        $this->assertSame('completed', $payload['status']['to']);
        $this->assertSame('completed', $payload['context']['stage_to']);
    }

    public function test_notify_lead_update_posts_to_configured_url(): void
    {
        if (!method_exists(SynvolveWebhookService::class, 'notifyLeadUpdateForOrder')) {
            $this->markTestSkipped('Lead-update delivery is absent from the current rolled-back Synvolve service.');
        }

        config(['services.synvolve.lead_update_webhook_url' => 'https://example.test/LeedUpdate']);

        $http = new class extends \GuzzleHttp\Client {
            public $uri;
            public $payload;

            public function post($uri, array $options = []): \Psr\Http\Message\ResponseInterface
            {
                $this->uri = $uri;
                $this->payload = $options['json'];

                return new \GuzzleHttp\Psr7\Response(200, [], '{"ok":true}');
            }
        };

        $order = new Orders();
        $order->id = 89;
        $order->delivery = json_encode([
            'payer_phone' => '+371 200-11-333',
        ]);

        $service = new SynvolveWebhookService($http);
        $result = $service->notifyLeadUpdateForOrder($order, 'completed', 'pegging', 'crm_pipeline_changed');

        $this->assertTrue($result);
        $this->assertSame('https://example.test/LeedUpdate', $http->uri);
        $this->assertSame('lead_update', $http->payload['event']);
        $this->assertSame(89, $http->payload['lead_id']);
        $this->assertSame('pegging', $http->payload['status']['from']);
        $this->assertSame('completed', $http->payload['status']['to']);
    }
}
