<?php

namespace Tests\Feature\Payment;

use App\Models\OrderPaymentRequest;
use App\Models\User;
use App\Services\Payment\OrderPaymentRequestService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderPaymentRequestTest extends TestCase
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

    public function test_admin_can_create_payment_request_for_order()
    {
        if (! Route::has('admin.order_payment_requests.store')) {
            $this->markTestSkipped('Admin routes are intentionally disabled during the frontend migration stage.');
        }

        $admin = $this->createUser(1, 1, 'admin@example.test');
        $this->createUser(10, 2, 'client@example.test');
        $this->createOrder(701, 10);

        $response = $this->actingAs($admin)->post(route('admin.order_payment_requests.store', ['order' => 701]), [
            'amount' => '15.00',
            'purpose' => 'Доплата за доставку',
        ]);

        $response->assertRedirect(route('edit_admin_order', ['id' => 701]));

        $paymentRequest = OrderPaymentRequest::query()->where('order_id', 701)->first();

        $this->assertNotNull($paymentRequest);
        $this->assertSame('pending', $paymentRequest->status);
        $this->assertSame('15.00', $paymentRequest->amount);
        $this->assertSame('Доплата за доставку', $paymentRequest->purpose);
        $this->assertNotEmpty($paymentRequest->public_number);
        $this->assertNotEmpty($paymentRequest->token);
    }

    public function test_payment_request_can_be_marked_as_paid_idempotently()
    {
        $this->createUser(11, 2, 'viewer@example.test');
        $this->createOrder(702, 11);

        $paymentRequest = OrderPaymentRequest::query()->create([
            'order_id' => 702,
            'user_id' => 11,
            'created_by' => 1,
            'public_number' => 'OPR-000702',
            'token' => 'token-702',
            'amount' => '12.50',
            'currency' => 'EUR',
            'purpose' => 'Доплата',
            'status' => 'pending',
            'customer_email' => 'viewer@example.test',
            'customer_first_name' => 'Test',
            'customer_last_name' => 'User',
            'customer_country' => 'LV',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = app(OrderPaymentRequestService::class);

        $markedRequest = $service->markAsPaidByPublicNumber('OPR-000702');
        $this->assertNotNull($markedRequest);
        $this->assertSame('paid', $markedRequest->status);
        $this->assertNotNull($markedRequest->paid_at);

        $firstPaidAt = $markedRequest->paid_at->format('Y-m-d H:i:s');

        $markedAgain = $service->markAsPaidByPublicNumber('OPR-000702');
        $this->assertSame('paid', $markedAgain->status);
        $this->assertSame($firstPaidAt, $markedAgain->paid_at->format('Y-m-d H:i:s'));
    }

    public function test_payment_request_start_uses_selected_gateway()
    {
        $this->createUser(12, 2, 'payer@example.test');
        $this->createOrder(703, 12);

        $paymentRequest = OrderPaymentRequest::query()->create([
            'order_id' => 703,
            'user_id' => 12,
            'public_number' => 'OPR-000703',
            'token' => 'token-703',
            'amount' => '25.00',
            'currency' => 'EUR',
            'status' => 'pending',
            'customer_email' => 'payer@example.test',
            'customer_first_name' => 'Pay',
            'customer_last_name' => 'Er',
            'customer_country' => 'LV',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $paymentService = \Mockery::mock(OrderPaymentRequestService::class);
        $paymentService->shouldReceive('start')
            ->once()
            ->withArgs(function ($resolvedPaymentRequest, $paymentMethod) use ($paymentRequest) {
                return (int) $resolvedPaymentRequest->id === (int) $paymentRequest->id
                    && $paymentMethod === 'paypalOnetimePayment';
            })
            ->andReturn(redirect('/fake-payment-request-gateway'));

        $this->app->instance(OrderPaymentRequestService::class, $paymentService);

        $response = $this->post(route('payment_request.start', ['token' => $paymentRequest->token]), [
            'payment' => 'paypalOnetimePayment',
        ]);

        $response->assertRedirect('/fake-payment-request-gateway');
    }

    public function test_payment_request_creation_splits_full_name_from_delivery_snapshot()
    {
        $admin = $this->createUser(20, 1, 'admin2@example.test');
        $this->createUser(21, 2, 'full.name@example.test');
        $this->createOrder(704, 21, [
            'country' => 'LV',
            'email' => 'full.name@example.test',
            'first_name' => null,
            'last_name' => null,
            'name' => 'Ada Lovelace',
            'deliv_price' => 0,
        ]);

        $paymentRequest = app(OrderPaymentRequestService::class)->createForOrder(
            \App\Models\Orders::query()->findOrFail(704),
            ['amount' => '18.00', 'purpose' => 'Доплата'],
            $admin
        );

        $this->assertSame('Ada', $paymentRequest->customer_first_name);
        $this->assertSame('Lovelace', $paymentRequest->customer_last_name);
        $this->assertSame('full.name@example.test', $paymentRequest->customer_email);
        $this->assertSame('LV', $paymentRequest->customer_country);
    }

    public function test_gateway_payload_restores_client_identity_from_order_delivery_when_request_snapshot_is_empty()
    {
        $this->createUser(22, 2, 'legacy.request@example.test', [
            'first_name' => null,
            'last_name' => null,
            'name' => 'Ada Lovelace',
        ]);
        $this->createOrder(705, 22, [
            'country' => 'LV',
            'email' => 'legacy.request@example.test',
            'first_name' => null,
            'last_name' => null,
            'name' => 'Ada Lovelace',
            'deliv_price' => 0,
        ]);

        $paymentRequest = OrderPaymentRequest::query()->create([
            'order_id' => 705,
            'user_id' => 22,
            'public_number' => 'OPR-000705',
            'token' => 'token-705',
            'amount' => '9.50',
            'currency' => 'EUR',
            'status' => 'pending',
            'customer_email' => null,
            'customer_first_name' => null,
            'customer_last_name' => null,
            'customer_country' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = new class extends OrderPaymentRequestService {
            public function exposeGatewayPayload(OrderPaymentRequest $paymentRequest, string $paymentMethod): array
            {
                return $this->buildGatewayPayload($paymentRequest, $paymentMethod);
            }
        };

        $payload = $service->exposeGatewayPayload(
            OrderPaymentRequest::with(['order', 'customer'])->findOrFail($paymentRequest->id),
            'online_paysera'
        );

        $this->assertSame('Ada', $payload['name']);
        $this->assertSame('Lovelace', $payload['last_name']);
        $this->assertSame('legacy.request@example.test', $payload['email']);
        $this->assertSame('LV', $payload['country']);
    }

    private function createSchema(): void
    {
        Schema::dropIfExists('order_payment_requests');
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
            $table->string('name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('country')->nullable();
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

        Schema::create('order_payment_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->string('public_number')->nullable()->unique();
            $table->string('token')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('purpose')->nullable();
            $table->string('status')->default('pending');
            $table->string('selected_payment_method')->nullable();
            $table->string('billing_invoice_uuid')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_first_name')->nullable();
            $table->string('customer_last_name')->nullable();
            $table->string('customer_country', 10)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        \DB::table('roles')->insert([
            [
                'id' => 1,
                'name' => 'admin',
                'display_name' => 'Admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'user',
                'display_name' => 'User',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'manager',
                'display_name' => 'Manager',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    private function createUser(int $id, int $roleId, string $email, array $overrides = []): User
    {
        \DB::table('users')->insert(array_merge([
            'id' => $id,
            'role_id' => $roleId,
            'email' => $email,
            'password' => Hash::make('secret'),
            'name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
            'country' => 'LV',
            'phone' => '+37120000000',
            'avatar' => 'users/default.png',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));

        return User::query()->findOrFail($id);
    }

    private function createOrder(int $id, int $userId, array $deliveryOverrides = []): void
    {
        \DB::table('orders')->insert([
            'id' => $id,
            'user_id' => $userId,
            'items' => json_encode([
                ['sumPrice' => 1, 'count' => 1, 'terms_price' => 0],
                'total_terms_price' => 0,
            ]),
            'price' => 50,
            'country' => 'LV',
            'delivery' => json_encode(array_merge([
                'country' => 'LV',
                'email' => 'client@example.test',
                'first_name' => 'Test',
                'last_name' => 'User',
                'deliv_price' => 0,
            ], $deliveryOverrides)),
            'payment' => 'transfer',
            'payment_status' => 'not_payed',
            'sale_price' => 50,
            'status' => 'watching',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
