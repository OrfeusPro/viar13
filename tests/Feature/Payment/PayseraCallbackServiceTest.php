<?php

namespace Tests\Feature\Payment;

use App\Models\OrderPaymentRequest;
use App\Models\Orders;
use App\Services\Payment\PayseraCallbackService;
use App\Services\SynvolveWebhookService;
use DomainException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PayseraCallbackServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('order_payment_requests');
        Schema::dropIfExists('orders');

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->longText('items')->nullable();
            $table->longText('delivery')->nullable();
            $table->string('payment');
            $table->string('payment_status');
            $table->timestamps();
        });

        Schema::create('order_payment_requests', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('public_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->string('status');
            $table->string('selected_payment_method')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('order_payment_requests');
        Schema::dropIfExists('orders');

        parent::tearDown();
    }

    public function test_checkout_callback_marks_only_matching_paysera_order_as_paid(): void
    {
        $order = $this->createOrder();
        $webhook = \Mockery::mock(SynvolveWebhookService::class);
        $webhook->shouldReceive('notifyOrderSnapshotById')
            ->once()
            ->with((int) $order->id, 'payment_status_changed_paysera');
        $this->app->instance(SynvolveWebhookService::class, $webhook);

        app(PayseraCallbackService::class)->confirmOrder($this->response([
            'orderid' => (string) $order->id,
            'amount' => '6300',
            'currency' => 'EUR',
        ]));

        $this->assertSame('payed', $order->fresh()->payment_status);
    }

    public function test_checkout_callback_rejects_wrong_amount_currency_and_status_transition(): void
    {
        foreach ([
            ['amount' => '6200', 'currency' => 'EUR'],
            ['amount' => '6300', 'currency' => 'USD'],
        ] as $invalidPayment) {
            $order = $this->createOrder();

            try {
                app(PayseraCallbackService::class)->confirmOrder($this->response([
                    'orderid' => (string) $order->id,
                    ...$invalidPayment,
                ]));
                $this->fail('Invalid Paysera payment data was accepted.');
            } catch (DomainException) {
                $this->assertSame('not_payed', $order->fresh()->payment_status);
            }
        }

        $order = $this->createOrder(['payment_status' => 'prepayment']);

        $this->expectException(DomainException::class);
        app(PayseraCallbackService::class)->confirmOrder($this->response([
            'orderid' => (string) $order->id,
            'amount' => '6300',
            'currency' => 'EUR',
        ]));
    }

    public function test_checkout_callback_is_idempotent_for_already_paid_order(): void
    {
        $order = $this->createOrder(['payment_status' => 'payed']);
        $webhook = \Mockery::mock(SynvolveWebhookService::class);
        $webhook->shouldNotReceive('notifyOrderSnapshotById');
        $this->app->instance(SynvolveWebhookService::class, $webhook);

        app(PayseraCallbackService::class)->confirmOrder($this->response([
            'orderid' => (string) $order->id,
            'payamount' => '6300',
            'paycurrency' => 'EUR',
        ]));

        $this->assertSame('payed', $order->fresh()->payment_status);
    }

    public function test_payment_request_callback_validates_amount_currency_and_transition(): void
    {
        $paymentRequest = OrderPaymentRequest::query()->create([
            'public_number' => 'OPR-000001',
            'amount' => '15.00',
            'currency' => 'EUR',
            'status' => OrderPaymentRequest::STATUS_PENDING,
            'selected_payment_method' => 'creditcart',
        ]);

        app(PayseraCallbackService::class)->confirmPaymentRequest($this->response([
            'orderid' => $paymentRequest->public_number,
            'amount' => '1500',
            'currency' => 'EUR',
        ]));

        $this->assertSame(OrderPaymentRequest::STATUS_PAID, $paymentRequest->fresh()->status);
        $this->assertNotNull($paymentRequest->fresh()->paid_at);

        $invalid = OrderPaymentRequest::query()->create([
            'public_number' => 'OPR-000002',
            'amount' => '15.00',
            'currency' => 'EUR',
            'status' => OrderPaymentRequest::STATUS_PENDING,
            'selected_payment_method' => 'creditcart',
        ]);

        try {
            app(PayseraCallbackService::class)->confirmPaymentRequest($this->response([
                'orderid' => $invalid->public_number,
                'amount' => '1400',
                'currency' => 'EUR',
            ]));
            $this->fail('Invalid payment request amount was accepted.');
        } catch (DomainException) {
            $this->assertSame(OrderPaymentRequest::STATUS_PENDING, $invalid->fresh()->status);
        }
    }

    private function createOrder(array $overrides = []): Orders
    {
        $id = DB::table('orders')->insertGetId(array_merge([
            'price' => '55.00',
            'sale_price' => '50.00',
            'items' => json_encode(['total_terms_price' => '8.00']),
            'delivery' => json_encode(['deliv_price' => '5.00']),
            'payment' => 'creditcart',
            'payment_status' => 'not_payed',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));

        return Orders::query()->findOrFail($id);
    }

    private function response(array $overrides): array
    {
        return array_merge(['status' => '1'], $overrides);
    }
}
