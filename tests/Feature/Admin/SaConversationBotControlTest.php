<?php

namespace Tests\Feature\Admin;

use App\Models\SaConversation;
use App\Models\User;
use App\Services\Admin\OrderSaCommandService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\CreatesSaChatSchema;
use Tests\TestCase;

class SaConversationBotControlTest extends TestCase
{
    use CreatesSaChatSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSaChatSchema();
        config(['services.synvolve.bot_status_webhook_url' => 'https://sa.test.invalid/bot']);
    }

    #[DataProvider('actions')]
    public function test_bot_control_without_order_uses_conversation_and_retries_once(string $action, string $mode): void
    {
        config(['admin_migration.sa_commands_enabled' => true]);
        Http::fake(['sa.test.invalid/bot' => Http::response([], 200)]);
        $conversation = $this->conversation();
        $author = $this->author();
        $service = app(OrderSaCommandService::class);
        $input = ['action' => $action, 'token' => $service->inboxToken($conversation, $author)];
        $result = $service->executeInbox($conversation, $author, $input);
        $this->assertSame('accepted', $result['status']);
        $this->assertSame($mode, $conversation->fresh()->bot_mode);
        $this->assertNull($conversation->fresh()->orders_id);
        Http::assertSent(fn ($request) => $request->url() === 'https://sa.test.invalid/bot'
            && $request['event'] === 'bot_status_changed' && $request['client_id'] === 'CONV-NO-ORDER'
            && $request['phone'] === '+12025550123' && $request['bot_status'] === $mode
            && empty($request['context']['order_id']));
        $service->executeInbox($conversation->fresh(), $author, $input);
        Http::assertSentCount(1);
        $this->assertDatabaseCount('sa_bot_controls', 1);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_user_comments', 0);
        Mail::assertNothingSent();
    }

    public function test_disabled_transport_records_suppression_without_changing_mode(): void
    {
        config(['admin_migration.sa_commands_enabled' => false]);
        $conversation = $this->conversation();
        $author = $this->author();
        $service = app(OrderSaCommandService::class);
        $result = $service->executeInbox($conversation, $author, ['action' => 'resume_bot', 'token' => $service->inboxToken($conversation, $author)]);
        $this->assertSame('uat_suppressed', $result['status']);
        $this->assertSame('active', $conversation->fresh()->bot_mode);
        $this->assertDatabaseCount('orders', 0);
        Http::assertNothingSent(); Mail::assertNothingSent();
    }

    public static function actions(): array
    {
        return [['pause_bot', 'paused'], ['resume_bot', 'active'], ['handoff_to_manager', 'handoff_to_manager']];
    }

    private function conversation(): SaConversation
    {
        return SaConversation::create(['conversation_id' => 'CONV-NO-ORDER', 'orders_id' => null,
            'client_phone' => '+12025550123', 'client_name' => 'No Order Client', 'channel' => 'whatsapp', 'bot_mode' => 'active']);
    }

    private function author(): User
    {
        DB::table('users')->insert(['id' => 123, 'email' => 'manager@example.invalid']);
        $author = \Mockery::mock(User::class)->makePartial();
        $author->id = 123;
        $author->shouldReceive('hasPermission')->andReturn(true);
        return $author;
    }
}