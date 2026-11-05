<?php

namespace Tests\Feature\Account;

use App\Models\User;
use App\Services\Payment\ClientOrderPaymentService;
use App\Services\Payment\PayPal\OneTimePayPalService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ClientOrderPaymentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        \Mockery::close();

        parent::tearDown();
    }

    public function test_owned_unpaid_order_can_start_online_payment()
    {
        $user = $this->createUser(10, 2, 'client@example.test');
        $this->createOrder(501, 10, 'not_payed');

        $paymentService = \Mockery::mock(ClientOrderPaymentService::class);
        $paymentService->shouldReceive('start')
            ->once()
            ->withArgs(function ($order, $paymentMethod) {
                return (int) $order->id === 501 && $paymentMethod === 'paypalOnetimePayment';
            })
            ->andReturn(redirect('/fake-payment-provider'));

        $this->app->instance(ClientOrderPaymentService::class, $paymentService);

        $response = $this->actingAs($user)->post(route('new_account.order_payment.start', ['order' => 501]), [
            'payment' => 'paypalOnetimePayment',
        ]);

        $response->assertRedirect('/fake-payment-provider');
    }

    public function test_payment_method_is_validated()
    {
        $user = $this->createUser(11, 2, 'validator@example.test');
        $this->createOrder(502, 11, 'not_payed');

        $response = $this->from(route('new_account.order_payment', ['order' => 502]))
            ->actingAs($user)
            ->post(route('new_account.order_payment.start', ['order' => 502]), [
                'payment' => 'invalid_gateway',
            ]);

        $response->assertRedirect(route('new_account.order_payment', ['order' => 502]));
        $response->assertSessionHasErrors('payment');
    }

    public function test_foreign_order_payment_is_not_accessible()
    {
        $user = $this->createUser(12, 2, 'owner@example.test');
        $this->createUser(13, 2, 'other@example.test');
        $this->createOrder(503, 13, 'not_payed');

        $this->withoutExceptionHandling();
        $this->expectException(ModelNotFoundException::class);

        $this->actingAs($user)->post(route('new_account.order_payment.start', ['order' => 503]), [
            'payment' => 'paypalOnetimePayment',
        ]);
    }

    public function test_repayment_uses_the_same_saved_discount_total_as_capture_validation()
    {
        $user = $this->createUser(14, 2, 'discount@example.test');
        $this->createOrder(504, 14, 'not_payed');
        $order = \App\Models\Orders::query()->findOrFail(504);
        $order->price = '100.00';
        $order->sale_price = '90.00';
        $order->sale_eur = '10.00';
        $order->sale_percent = '10.00';
        $order->save();

        $paypal = \Mockery::mock(OneTimePayPalService::class);
        $paypal->shouldReceive('getRequisites')
            ->once()
            ->withArgs(function (array $payload): bool {
                return $payload['order_id'] === 504
                    && $payload['paypalTotalPrice'] === '87.00';
            })
            ->andReturn(redirect('/fake-paypal'));
        $this->app->instance(OneTimePayPalService::class, $paypal);

        $this->actingAs($user);
        $response = app(ClientOrderPaymentService::class)->start($order, 'paypalOnetimePayment');

        $this->assertTrue($response->isRedirect());
        $this->assertStringEndsWith('/fake-paypal', $response->getTargetUrl());
    }

    private function createSchema(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');

        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('display_name')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('role_id')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->text('settings')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->text('items')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('country')->nullable();
            $table->text('delivery')->nullable();
            $table->string('payment')->nullable();
            $table->string('payment_status')->default('not_payed');
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->decimal('sale_eur', 10, 2)->nullable();
            $table->decimal('sale_percent', 10, 2)->nullable();
            $table->string('status')->default('watching');
            $table->string('billing_invoice_uuid')->nullable();
            $table->timestamps();
        });

        \DB::table('roles')->insert([
            'id' => 1,
            'name' => 'admin',
            'display_name' => 'Admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('roles')->insert([
            'id' => 2,
            'name' => 'user',
            'display_name' => 'User',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createUser(int $id, int $roleId, string $email): User
    {
        \DB::table('users')->insert([
            'id' => $id,
            'role_id' => $roleId,
            'email' => $email,
            'password' => Hash::make('secret'),
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '+37120000000',
            'avatar' => 'users/default.png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::query()->findOrFail($id);
    }

    private function createOrder(int $id, int $userId, string $paymentStatus): void
    {
        \DB::table('orders')->insert([
            'id' => $id,
            'user_id' => $userId,
            'items' => json_encode([
                ['sumPrice' => 1, 'count' => 1, 'terms_price' => 5],
                'total_terms_price' => 5,
            ]),
            'price' => 50,
            'country' => 'LV',
            'delivery' => json_encode([
                'country' => 'LV',
                'email' => 'client@example.test',
                'first_name' => 'Test',
                'last_name' => 'User',
                'deliv_price' => 10,
            ]),
            'payment' => 'transfer',
            'payment_status' => $paymentStatus,
            'sale_price' => 45,
            'status' => 'watching',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
