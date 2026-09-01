<?php

namespace Tests\Feature\Api;

use App\Models\Orders;
use App\Models\SaConversation;
use App\Models\SaEvent;
use App\Services\SaMessageIngressService;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\CreatesSaChatSchema;
use Tests\TestCase;

class SaMessageIngressAtomicityTest extends TestCase
{
    use CreatesSaChatSchema;

    private Orders $order;

    private SaConversation $conversation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSaChatSchema();
        $this->order = Orders::query()->forceCreate(['status' => 'watching']);
        $this->conversation = SaConversation::query()->create([
            'conversation_id' => 'ATOMIC-TEST', 'orders_id' => $this->order->id,
            'unread_for_manager' => false, 'bot_mode' => 'paused',
        ]);
        $this->order->refresh();
        $this->conversation->refresh();
    }

    #[DataProvider('failureTables')]
    public function test_failure_rolls_back_all_writes_and_same_event_can_retry(string $table): void
    {
        $payload = $this->payload();
        $orderBefore = $this->order->getAttributes();
        $conversationBefore = $this->conversation->getAttributes();
        $fail = true;
        DB::listen(function (QueryExecuted $query) use ($table, &$fail): void {
            if ($fail && str_starts_with($query->sql, 'insert into "'.$table.'"')) {
                $fail = false;
                $this->assertGreaterThan(0, DB::transactionLevel());
                throw new \RuntimeException('Injected failure after SQL write');
            }
        });

        $this->postJson('/api/sa/webhooks/messages', $payload, ['X-Api-Key' => 'test-key'])
            ->assertStatus(503)->assertJsonPath('error.code', 'PERSISTENCE_ERROR');
        $this->assertFalse($fail, 'The requested failure point was reached.');
        foreach (['sa_events', 'sa_messages', 'order_user_comments'] as $name) {
            $this->assertDatabaseCount($name, 0);
        }
        $this->assertSame($orderBefore, $this->order->fresh()->getAttributes());
        $this->assertSame($conversationBefore, $this->conversation->fresh()->getAttributes());
        $this->assertNull(Cache::get('sa:webhooks:messages:event:'.sha1($payload['event_id'])));
        $this->postJson('/api/sa/webhooks/messages', $payload, ['X-Api-Key' => 'test-key'])
            ->assertOk()->assertJsonPath('status', 'ok');
        $receipt = SaEvent::query()->sole();
        $this->assertSame('processed', $receipt->status);
        $this->assertNotNull($receipt->processed_at);
        $this->assertTrue($this->conversation->fresh()->unread_for_manager);
        $this->assertDatabaseCount('sa_messages', 1);
        $this->assertDatabaseCount('order_user_comments', 1);
        $this->postJson('/api/sa/webhooks/messages', $payload, ['X-Api-Key' => 'test-key'])
            ->assertOk()->assertJsonPath('status', 'duplicate');
        $this->assertDatabaseCount('sa_events', 1);
        Http::assertNothingSent();
        Mail::assertNothingSent();
    }

    public static function failureTables(): array
    {
        return [['sa_events'], ['sa_messages'], ['order_user_comments']];
    }

    public function test_same_idempotency_key_with_different_event_id_is_duplicate(): void
    {
        $payload = $this->payload();
        $this->postJson('/api/sa/webhooks/messages', $payload, ['X-Api-Key' => 'test-key'])->assertOk();
        $payload['event_id'] = 'b4d633ec-8965-4c31-b3c6-1b4ae7ac49c1';
        $payload['data']['message']['message_id'] = 'SHOULD-NOT-EXIST';
        $this->postJson('/api/sa/webhooks/messages', $payload, ['X-Api-Key' => 'test-key'])
            ->assertOk()->assertJsonPath('status', 'duplicate');
        $this->assertDatabaseCount('sa_events', 1);
        $this->assertDatabaseCount('sa_messages', 1);
    }

    public function test_same_event_id_with_different_key_is_duplicate(): void
    {
        $payload = $this->payload();
        $this->postJson('/api/sa/webhooks/messages', $payload, ['X-Api-Key' => 'test-key'])->assertOk();
        $payload['idempotency_key'] = 'different-key';
        $this->postJson('/api/sa/webhooks/messages', $payload, ['X-Api-Key' => 'test-key'])
            ->assertOk()->assertJsonPath('status', 'duplicate');
        $this->assertDatabaseCount('sa_events', 1);
        $this->assertDatabaseCount('order_user_comments', 1);
    }

    public function test_legacy_receipt_is_respected_without_replaying_or_rewriting_it(): void
    {
        $payload = $this->payload();
        $receipt = SaEvent::query()->create([
            'dedupe_key' => 'sa:webhooks:messages:event:'.sha1($payload['event_id']),
            'event_id' => $payload['event_id'], 'idempotency_key' => $payload['idempotency_key'],
            'event_type' => 'message.created', 'source' => 'SA', 'status' => 'received', 'processed_at' => now(),
        ])->fresh();
        $this->postJson('/api/sa/webhooks/messages', $payload, ['X-Api-Key' => 'test-key'])
            ->assertOk()->assertJsonPath('status', 'duplicate');
        $this->assertSame($receipt->getAttributes(), $receipt->fresh()->getAttributes());
        $this->assertDatabaseCount('sa_messages', 0);
    }

    public function test_service_does_not_treat_unrelated_unique_violation_as_duplicate(): void
    {
        $this->expectException(UniqueConstraintViolationException::class);
        try {
            app(SaMessageIngressService::class)->process($this->payload(), function (): void {
                SaConversation::query()->create(['conversation_id' => $this->conversation->conversation_id]);
            });
        } finally {
            $this->assertDatabaseCount('sa_events', 0);
        }
    }

    public function test_delivery_update_rolls_back_and_retry_preserves_manager_read_state(): void
    {
        $this->postJson('/api/sa/webhooks/messages', $this->payload(), ['X-Api-Key' => 'test-key'])->assertOk();
        $this->conversation->refresh()->update(['unread_for_manager' => false]);
        $before = $this->conversation->fresh()->getAttributes();
        $payload = [
            'event_id' => '3a7ea4c8-7c88-4e80-970f-12c2b93f47a2', 'event_type' => 'message.status',
            'idempotency_key' => 'atomic-delivery-1', 'occurred_at' => now()->toIso8601String(), 'source' => 'SA',
            'data' => ['lead_id' => $this->order->id, 'conversation_id' => 'ATOMIC-TEST', 'message_id' => 'ATOMIC-1',
                'status' => 'delivered', 'status_at' => now()->toIso8601String()],
        ];
        $fail = true;
        DB::listen(function (QueryExecuted $query) use (&$fail): void {
            if ($fail && str_starts_with($query->sql, 'update "sa_messages"')) {
                $fail = false;
                throw new \RuntimeException('Injected status failure');
            }
        });
        $this->postJson('/api/sa/webhooks/messages', $payload, ['X-Api-Key' => 'test-key'])->assertStatus(503);
        $this->assertFalse($fail);
        $this->assertDatabaseCount('sa_events', 1);
        $this->assertDatabaseHas('sa_messages', ['message_id' => 'ATOMIC-1', 'status' => 'received']);
        $this->assertSame($before, $this->conversation->fresh()->getAttributes());
        $this->postJson('/api/sa/webhooks/messages', $payload, ['X-Api-Key' => 'test-key'])->assertOk()->assertJsonPath('status', 'ok');
        $this->assertFalse($this->conversation->fresh()->unread_for_manager);
        $this->assertDatabaseHas('sa_messages', ['message_id' => 'ATOMIC-1', 'status' => 'delivered']);
        $this->assertDatabaseCount('order_user_comments', 1);
        $this->assertDatabaseCount('sa_events', 2);
    }

    public function test_attachments_are_prepared_before_transaction_and_duplicate_skips_preparation(): void
    {
        $payload = $this->payload();
        $payload['data']['message']['attachments'] = [['url' => 'data://text/plain;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jXioAAAAASUVORK5CYII=', 'name' => 'fixture.png']];
        $paths = [];
        $deleted = [];
        $disk = \Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('put')->twice()->andReturnUsing(function ($path, $binary) use (&$paths): bool {
            $this->assertSame(0, DB::transactionLevel());
            $this->assertStringStartsWith('sa/attachments/', $path);
            $this->assertNotEmpty($binary);
            $paths[] = $path;

            return true;
        });
        $disk->shouldReceive('delete')->once()->andReturnUsing(function ($path) use (&$deleted): bool {
            $deleted[] = $path;

            return true;
        });
        Storage::shouldReceive('disk')->with('public')->times(3)->andReturn($disk);
        $fail = true;
        DB::listen(function (QueryExecuted $query) use (&$fail): void {
            if ($fail && str_starts_with($query->sql, 'insert into "sa_messages"')) {
                $fail = false;
                throw new \RuntimeException('Retry with another prepared file');
            }
        });
        $this->postJson('/api/sa/webhooks/messages', $payload, ['X-Api-Key' => 'test-key'])->assertStatus(503);
        $this->postJson('/api/sa/webhooks/messages', $payload, ['X-Api-Key' => 'test-key'])->assertOk()->assertJsonPath('status', 'ok');
        $this->postJson('/api/sa/webhooks/messages', $payload, ['X-Api-Key' => 'test-key'])->assertOk()->assertJsonPath('status', 'duplicate');
        $this->assertCount(2, $paths);
        $this->assertSame([$paths[0]], $deleted);
        $this->assertNotSame($paths[0], $paths[1]);
        $this->assertStringContainsString($paths[1], str_replace('\\/', '/', DB::table('sa_messages')->value('attachments_json')));
        Http::assertNothingSent();
        Mail::assertNothingSent();
    }

    private function payload(): array
    {
        return [
            'event_id' => '1d827c91-b775-4a19-b185-cd002e64ae49', 'event_type' => 'message.created',
            'idempotency_key' => 'atomic-inbound-1', 'occurred_at' => now()->toIso8601String(), 'source' => 'SA',
            'data' => ['lead_id' => $this->order->id, 'conversation_id' => $this->conversation->conversation_id,
                'channel' => 'whatsapp', 'message' => ['message_id' => 'ATOMIC-1', 'direction' => 'inbound',
                    'from' => ['type' => 'client'], 'to' => ['type' => 'agent'], 'text' => 'Atomic test',
                    'attachments' => [], 'sent_at' => now()->toIso8601String(), 'status' => 'received']],
        ];
    }
}
