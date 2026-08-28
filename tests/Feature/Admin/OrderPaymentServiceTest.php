<?php

namespace Tests\Feature\Admin;

use App\Models\Orders;
use App\Services\Admin\OrderPaymentService;
use App\Services\BestEffortMailService;
use App\Services\SynvolveWebhookService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class OrderPaymentServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('email');
            $table->string('country')->nullable();
            $table->string('locale')->nullable();
            $table->decimal('bonuses', 10, 2)->default(0);
            $table->string('inv_sale_code')->nullable();
            $table->string('last_ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id');
            $table->text('items')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('payment_status')->default('not_payed');
            $table->decimal('prepayment_price', 10, 2)->default(0);
            $table->unsignedTinyInteger('use_bonus')->default(0);
            $table->string('used_coupon')->nullable();
            $table->timestamps();
        });
    }

    public function test_paid_status_preserves_legacy_customer_and_inviter_bonuses(): void
    {
        config()->set('admin_migration.payment_notifications_enabled', false);
        $inviterId = $this->createUser('inviter@example.test', ['bonuses' => 2, 'inv_sale_code' => 'INVITER']);
        $customerId = $this->createUser('customer@example.test', ['country' => 'lv', 'bonuses' => 4]);
        $order = $this->createOrder($customerId, [
            'price' => 100,
            'used_coupon' => 'INVITER',
        ]);

        $mail = Mockery::mock(BestEffortMailService::class);
        $mail->shouldReceive('attempt')->twice()->andReturnTrue();
        $mail->shouldNotReceive('send');
        $this->app->instance(BestEffortMailService::class, $mail);

        $webhook = Mockery::mock(SynvolveWebhookService::class);
        $webhook->shouldNotReceive('notifyOrderSnapshotById');
        $this->app->instance(SynvolveWebhookService::class, $webhook);

        $result = app(OrderPaymentService::class)->updateStatus($order, 'payed');

        $this->assertTrue($result['notifications_suppressed']);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'payed',
            'use_bonus' => 2,
        ]);
        $this->assertSame(8.0, (float) DB::table('users')->where('id', $customerId)->value('bonuses'));
        $this->assertSame(7.0, (float) DB::table('users')->where('id', $inviterId)->value('bonuses'));
    }

    public function test_prepayment_status_logs_customer_mail_without_external_webhook(): void
    {
        config()->set('admin_migration.payment_notifications_enabled', false);
        $order = $this->createOrder($this->createUser('customer@example.test', ['country' => 'lt']));

        $mail = Mockery::mock(BestEffortMailService::class);
        $mail->shouldReceive('attempt')->once()->andReturnTrue();
        $mail->shouldNotReceive('send');
        $this->app->instance(BestEffortMailService::class, $mail);

        $webhook = Mockery::mock(SynvolveWebhookService::class);
        $webhook->shouldNotReceive('notifyOrderSnapshotById');
        $this->app->instance(SynvolveWebhookService::class, $webhook);

        app(OrderPaymentService::class)->updateStatus($order, 'prepayment');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'prepayment',
            'use_bonus' => 0,
        ]);
    }

    public function test_prepayment_amount_is_validated_and_updated_atomically(): void
    {
        $order = $this->createOrder($this->createUser('customer@example.test'));
        $service = app(OrderPaymentService::class);

        $service->updatePrepayment($order, 25.50);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'prepayment_price' => 25.50]);

        try {
            $service->updatePrepayment($order, -1);
            $this->fail('ValidationException was not thrown.');
        } catch (ValidationException) {
            $this->assertDatabaseHas('orders', ['id' => $order->id, 'prepayment_price' => 25.50]);
        }
    }

    public function test_unknown_payment_status_is_rejected_without_changes(): void
    {
        $order = $this->createOrder($this->createUser('customer@example.test'));

        $this->expectException(ValidationException::class);

        try {
            app(OrderPaymentService::class)->updateStatus($order, 'unknown');
        } finally {
            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'payment_status' => 'not_payed',
            ]);
        }
    }

    private function createUser(string $email, array $attributes = []): int
    {
        return DB::table('users')->insertGetId(array_merge([
            'email' => $email,
            'bonuses' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));
    }

    private function createOrder(int $userId, array $attributes = []): Orders
    {
        $id = DB::table('orders')->insertGetId(array_merge([
            'user_id' => $userId,
            'items' => '[]',
            'price' => 100,
            'payment_status' => 'not_payed',
            'prepayment_price' => 0,
            'use_bonus' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));

        return Orders::findOrFail($id);
    }
}
