<?php

namespace Tests\Feature\Api;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\CreatesSaChatSchema;
use Tests\TestCase;

class SaLeadUpdatePersistenceTest extends TestCase
{
    use CreatesSaChatSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSaChatSchema();
        DB::table('users')->insert(['id' => 1, 'email' => 'profile@example.invalid']);
        DB::table('orders')->insert(['id' => 1, 'user_id' => 1, 'status' => 'watching', 'price' => '20',
            'sa_conversation_id' => 'UPDATE-TEST', 'admin_comment' => 'Existing internal note',
            'delivery' => json_encode(['email' => 'old@example.invalid', 'phone' => '+12025550100', 'deliv_price' => 4,
                'sa_lead' => ['tags' => ['cold', 'keep'], 'fields' => ['question' => 'Existing question']]])]);
        DB::table('sa_conversations')->insert(['conversation_id' => 'UPDATE-TEST', 'orders_id' => 1, 'client_phone' => '+12025550100']);
    }

    public function test_fields_tags_notes_stage_are_saved_and_duplicate_does_not_append_again(): void
    {
        $payload = $this->payload();
        $this->send($payload)->assertOk()->assertJsonPath('data.order_updated', true)->assertJsonPath('data.notes_appended', 1);
        $order = DB::table('orders')->find(1);
        $delivery = json_decode($order->delivery, true);
        $this->assertSame('new@example.invalid', $delivery['email']);
        $this->assertSame('Riga', $delivery['city']);
        $this->assertSame(4, $delivery['deliv_price']);
        $this->assertSame('+12025550123', $delivery['payer_phone']);
        $this->assertSame('+12025550100', $delivery['phone'], 'Recipient is separate from client.');
        $this->assertSame(1500, $delivery['sa_lead']['fields']['budget']);
        $this->assertSame('Existing question', $delivery['sa_lead']['fields']['question']);
        $this->assertSame(['keep', 'vip'], $delivery['sa_lead']['tags']);
        $this->assertSame('2026-10-08T09:00:00+00:00', $delivery['sa_lead']['notes'][0]['created_at']);
        $this->assertStringContainsString('Existing internal note', $order->admin_comment);
        $this->assertStringContainsString('Confirmed address', $order->admin_comment);
        $this->assertSame('pegging', $order->status);
        $this->assertDatabaseHas('sa_conversations', ['conversation_id' => 'UPDATE-TEST', 'client_phone' => '+12025550123']);
        $this->assertDatabaseHas('users', ['id' => 1, 'email' => 'profile@example.invalid']);
        $this->assertDatabaseHas('sa_events', ['status' => 'processed']);
        $before = $this->snapshot();
        $this->send($payload)->assertOk()->assertJsonPath('status', 'duplicate');
        $this->assertSame($before, $this->snapshot());
        $this->getJson('/api/sa/orders/lookup?order_id=1', ['X-Api-Key' => 'test-key'])->assertOk()
            ->assertJsonPath('data.orders.0.client.email', 'new@example.invalid')
            ->assertJsonPath('data.orders.0.delivery.raw.sa_lead.fields.budget', 1500);
    }

    public function test_followup_merges_custom_fields_removes_tags_and_preserves_null(): void
    {
        $this->send($this->payload())->assertOk();
        $this->send(['source' => 'SA', 'idempotency_key' => 'followup', 'update' => ['fields' => ['budget' => 0, 'email' => null],
            'tags_add' => ['new', 'keep'], 'tags_remove' => ['vip', 'new']]])->assertOk();
        $delivery = json_decode(DB::table('orders')->find(1)->delivery, true);
        $this->assertNull($delivery['email']);
        $this->assertSame(0, $delivery['sa_lead']['fields']['budget']);
        $this->assertSame(['keep'], $delivery['sa_lead']['tags']);
        $this->assertCount(1, $delivery['sa_lead']['notes']);
        $this->getJson('/api/sa/orders/lookup?order_id=1', ['X-Api-Key' => 'test-key'])->assertOk()
            ->assertJsonPath('data.orders.0.client.email', '');
    }

    #[DataProvider('invalidUpdates')]
    public function test_invalid_input_does_not_write_or_consume_key(array $update): void
    {
        $before = $this->snapshot();
        $this->send(['source' => 'SA', 'idempotency_key' => 'retry-key', 'update' => $update])->assertStatus(400)->assertJsonPath('error.code', 'VALIDATION_ERROR');
        $this->assertSame($before, $this->snapshot());
        $payload = $this->payload(); $payload['idempotency_key'] = 'retry-key';
        $this->send($payload)->assertOk()->assertJsonPath('status', 'ok');
    }

    public static function invalidUpdates(): array
    {
        return [[['fields' => ['email' => 'invalid']]], [['fields' => ['phone' => '123']]],
            [['fields' => ['city' => ['nested']]]], [['fields' => ['bad.key' => 'value']]],
            [['notes_append' => [['created_at' => '2026-10-08']]]], [['stage' => ['id' => 'archived']]],
            [['tags_add' => ['']]], [['tags_remove' => ['']]],
            [['notes_append' => array_fill(0, 7, ['text' => str_repeat('x', 10000)])]]];
    }

    #[DataProvider('failurePoints')]
    public function test_partial_failure_rolls_back_receipt_order_and_conversation_then_retries(string $sql): void
    {
        $before = $this->snapshot(); $fail = true;
        DB::listen(function (QueryExecuted $query) use ($sql, &$fail): void {
            if ($fail && str_starts_with($query->sql, $sql)) { $fail = false; throw new \RuntimeException('Injected local failure'); }
        });
        $payload = $this->payload();
        $this->send($payload)->assertStatus(503)->assertJsonPath('error.code', 'PERSISTENCE_ERROR');
        $this->assertFalse($fail);
        $this->assertSame($before, $this->snapshot());
        $this->send($payload)->assertOk()->assertJsonPath('status', 'ok');
        $this->assertDatabaseCount('sa_events', 1);
    }

    public static function failurePoints(): array
    {
        return [['insert into "sa_events"'], ['update "orders"'], ['update "sa_conversations"'], ['update "sa_events"']];
    }

    public function test_unknown_order_and_missing_key_do_not_consume_receipt(): void
    {
        $this->patchJson('/api/sa/leads/9999', $this->payload(), ['X-Api-Key' => 'test-key'])->assertStatus(404);
        $this->patchJson('/api/sa/leads/1', $this->payload())->assertStatus(401);
        $this->assertDatabaseCount('sa_events', 0);
        $this->send($this->payload())->assertOk();
    }

    public function test_existing_legacy_receipt_remains_duplicate(): void
    {
        DB::table('sa_events')->insert(['dedupe_key' => 'sa:leads:update:key:'.sha1('update-persistence'),
            'idempotency_key' => 'update-persistence', 'event_type' => 'sa.lead.update', 'source' => 'SA', 'status' => 'received']);
        $before = $this->snapshot();
        $this->send($this->payload())->assertOk()->assertJsonPath('status', 'duplicate');
        $this->assertSame($before, $this->snapshot());
    }

    public function test_corrupt_delivery_is_preserved_and_key_is_retryable(): void
    {
        DB::table('orders')->where('id', 1)->update(['delivery' => 'invalid json']);
        $this->send($this->payload())->assertStatus(503);
        $this->assertDatabaseHas('orders', ['id' => 1, 'delivery' => 'invalid json']);
        $this->assertDatabaseCount('sa_events', 0);
        DB::table('orders')->where('id', 1)->update(['delivery' => '{}']);
        $this->send($this->payload())->assertOk();
    }

    private function send(array $payload)
    {
        return $this->patchJson('/api/sa/leads/1', $payload, ['X-Api-Key' => 'test-key']);
    }

    private function payload(): array
    {
        return ['source' => 'SA', 'idempotency_key' => 'update-persistence', 'update' => [
            'fields' => ['email' => 'new@example.invalid', 'city' => 'Riga', 'budget' => 1500, 'phone' => '+12025550123'],
            'stage' => ['id' => 'pegging'], 'tags_add' => ['vip', 'keep', 'vip'], 'tags_remove' => ['cold'],
            'notes_append' => [['text' => 'Confirmed address', 'created_at' => '2026-10-08T12:00:00+03:00']]]];
    }

    private function snapshot(): array
    {
        return collect(['orders', 'users', 'sa_events', 'sa_conversations'])
            ->mapWithKeys(fn ($table) => [$table => DB::table($table)->orderBy('id')->get()->toJson()])->all();
    }
}
