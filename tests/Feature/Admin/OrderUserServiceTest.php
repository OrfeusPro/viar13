<?php

namespace Tests\Feature\Admin;

use App\Models\Orders;
use App\Services\Admin\OrderUserService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderUserServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('user_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('email');
            $table->string('pdf_locale')->nullable();
            $table->unsignedBigInteger('client_status')->nullable();
            $table->string('last_ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

        DB::table('user_types')->insert([
            'id' => 2,
            'name' => 'Бывалый',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_it_updates_invoice_locale_and_client_status_on_the_order_user(): void
    {
        $order = $this->createOrder();
        $service = app(OrderUserService::class);

        $service->updatePdfLocale($order, 'lv');
        $service->updateClientStatus($order, 2);

        $this->assertDatabaseHas('users', [
            'id' => $order->user_id,
            'pdf_locale' => 'lv',
            'client_status' => 2,
        ]);
    }

    public function test_it_rejects_unknown_locale_and_status_without_changes(): void
    {
        $order = $this->createOrder();
        $service = app(OrderUserService::class);

        foreach ([
            fn () => $service->updatePdfLocale($order, 'unknown'),
            fn () => $service->updateClientStatus($order, 999),
        ] as $operation) {
            try {
                $operation();
                $this->fail('ValidationException was not thrown.');
            } catch (ValidationException) {
                $this->assertDatabaseHas('users', [
                    'id' => $order->user_id,
                    'pdf_locale' => null,
                    'client_status' => null,
                ]);
            }
        }
    }

    public function test_it_rejects_order_without_existing_user(): void
    {
        $order = (new Orders())->forceFill(['id' => 77, 'user_id' => null]);

        $this->expectException(ValidationException::class);

        app(OrderUserService::class)->updatePdfLocale($order, 'ru');
    }

    private function createOrder(): Orders
    {
        $userId = DB::table('users')->insertGetId([
            'email' => 'client@example.test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Orders::query()->findOrFail($orderId);
    }
}
