<?php

namespace Tests\Feature\Admin;

use App\Models\Orders;
use App\Services\Admin\UpdateOrderService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateOrderServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('manager_id')->nullable();
            $table->string('status');
            $table->string('payment_status');
            $table->string('payment')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->decimal('prepayment_price', 10, 2)->nullable();
            $table->text('admin_comment')->nullable();
            $table->timestamp('status_date')->nullable();
            $table->timestamp('watching_date')->nullable();
            $table->timestamp('pegging_date')->nullable();
            $table->timestamp('in_production_date')->nullable();
            $table->timestamp('send_date')->nullable();
            $table->timestamp('send_lubanas_date')->nullable();
            $table->timestamp('completed_date')->nullable();
            $table->timestamps();
        });
    }

    public function test_it_updates_allowed_admin_fields_and_status_dates_atomically(): void
    {
        DB::table('users')->insert(['id' => 7]);
        $orderId = DB::table('orders')->insertGetId([
            'status' => 'watching',
            'payment_status' => 'not_payed',
            'price' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $order = app(UpdateOrderService::class)->update(Orders::findOrFail($orderId), [
            'manager_id' => 7,
            'status' => 'in_production',
            'payment_status' => 'prepayment',
            'payment' => 'transfer',
            'price' => 120,
            'sale_price' => 110,
            'prepayment_price' => 50,
            'admin_comment' => 'Проверено',
        ]);

        $this->assertSame('in_production', $order->status);
        $this->assertNotNull($order->in_production_date);
        $this->assertNotNull($order->status_date);
        $this->assertSame(7, (int) $order->manager_id);
    }

    public function test_it_rejects_unknown_order_status_without_changing_order(): void
    {
        $orderId = DB::table('orders')->insertGetId([
            'status' => 'watching',
            'payment_status' => 'not_payed',
            'price' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            app(UpdateOrderService::class)->update(Orders::findOrFail($orderId), [
                'status' => 'invalid',
                'payment_status' => 'not_payed',
                'price' => 100,
            ]);
            $this->fail('ValidationException was not thrown.');
        } catch (ValidationException) {
            $this->assertSame('watching', DB::table('orders')->where('id', $orderId)->value('status'));
        }
    }
}
