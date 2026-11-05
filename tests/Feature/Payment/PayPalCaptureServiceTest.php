<?php

namespace Tests\Feature\Payment;

use App\Models\OrderPaymentRequest;
use App\Models\Orders;
use App\Services\Payment\PayPalCaptureService;
use App\Services\SynvolveWebhookService;
use DomainException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PayPalCaptureServiceTest extends TestCase
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
            $table->decimal('sale_eur', 10, 2)->nullable();
            $table->decimal('sale_percent', 10, 2)->nullable();
            $table->longText('items')->nullable();
            $table->longText('delivery')->nullable();
            $table->string('payment');
            $table->string('payment_status');
            $table->timestamps();
        });

        Schema::create('order_payment_requests', function (Blueprint $table): void {
            $table->id();
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

    public function test_completed_matching_capture_marks_checkout_order_as_paid(): void
    {
        $order = $this->createOrder();
        $webhook = \Mockery::mock(SynvolveWebhookService::class);
        $webhook->shouldReceive('notifyOrderSnapshotById')
            ->once()
            ->with((int) $order->id, 'payment_status_changed_paypal');
        $this->app->instance(SynvolveWebhookService::class, $webhook);

        app(PayPalCaptureService::class)->confirmOrder(
            $order,
            $this->capture((string) $order->id, '63.00', 'EUR')
        );

        $this->assertSame('payed', $order->fresh()->payment_status);
    }

    public function test_checkout_capture_rejects_incomplete_wrong_reference_amount_and_currency(): void
    {
        $cases = [
            ['status' => 'APPROVED'],
            ['reference' => '999999'],
            ['amount' => '62.00'],
            ['currency' => 'USD'],
            ['capture_status' => 'PENDING'],
        ];

        foreach ($cases as $case) {
            $order = $this->createOrder();
            $capture = $this->capture(
                $case['reference'] ?? (string) $order->id,
                $case['amount'] ?? '63.00',
                $case['currency'] ?? 'EUR',
                $case['status'] ?? 'COMPLETED',
                $case['capture_status'] ?? 'COMPLETED'
            );

            try {
                app(PayPalCaptureService::class)->confirmOrder($order, $capture);
                $this->fail('Invalid PayPal capture was accepted.');
            } catch (DomainException) {
                $this->assertSame('not_payed', $order->fresh()->payment_status);
            }
        }
    }

    public function test_checkout_capture_rejects_non_paypal_order_and_forbidden_transition(): void
    {
        foreach ([
            ['payment' => 'transfer'],
            ['payment_status' => 'prepayment'],
        ] as $override) {
            $order = $this->createOrder($override);

            try {
                app(PayPalCaptureService::class)->confirmOrder(
                    $order,
                    $this->capture((string) $order->id, '63.00', 'EUR')
                );
                $this->fail('Invalid PayPal order transition was accepted.');
            } catch (DomainException) {
                $this->assertSame($override['payment_status'] ?? 'not_payed', $order->fresh()->payment_status);
            }
        }
    }

    public function test_already_paid_checkout_order_is_idempotent_without_repeated_webhook(): void
    {
        $order = $this->createOrder(['payment_status' => 'payed']);
        $webhook = \Mockery::mock(SynvolveWebhookService::class);
        $webhook->shouldNotReceive('notifyOrderSnapshotById');
        $this->app->instance(SynvolveWebhookService::class, $webhook);

        app(PayPalCaptureService::class)->confirmOrder(
            $order,
            $this->capture((string) $order->id, '63.00', 'EUR')
        );

        $this->assertSame('payed', $order->fresh()->payment_status);
    }

    public function test_capture_uses_same_saved_discount_total_as_repayment_start(): void
    {
        $order = $this->createOrder([
            'price' => '100.00',
            'sale_price' => '90.00',
            'sale_eur' => '10.00',
            'sale_percent' => '10.00',
        ]);
        $webhook = \Mockery::mock(SynvolveWebhookService::class);
        $webhook->shouldReceive('notifyOrderSnapshotById')->once();
        $this->app->instance(SynvolveWebhookService::class, $webhook);

        app(PayPalCaptureService::class)->confirmOrder(
            $order,
            $this->capture((string) $order->id, '85.00', 'EUR')
        );

        $this->assertSame('payed', $order->fresh()->payment_status);
    }

    public function test_payment_request_capture_validates_reference_amount_currency_and_transition(): void
    {
        $paymentRequest = $this->createPaymentRequest('OPR-000001');

        app(PayPalCaptureService::class)->confirmPaymentRequest(
            $paymentRequest,
            $this->capture('OPR-000001', '15.00', 'EUR')
        );

        $this->assertSame(OrderPaymentRequest::STATUS_PAID, $paymentRequest->fresh()->status);
        $this->assertNotNull($paymentRequest->fresh()->paid_at);

        $invalid = $this->createPaymentRequest('OPR-000002');

        try {
            app(PayPalCaptureService::class)->confirmPaymentRequest(
                $invalid,
                $this->capture('OPR-000002', '14.00', 'EUR')
            );
            $this->fail('Invalid PayPal payment-request amount was accepted.');
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
            'payment' => 'paypalOnetimePayment',
            'payment_status' => 'not_payed',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));

        return Orders::query()->findOrFail($id);
    }

    private function createPaymentRequest(string $publicNumber): OrderPaymentRequest
    {
        return OrderPaymentRequest::query()->create([
            'public_number' => $publicNumber,
            'amount' => '15.00',
            'currency' => 'EUR',
            'status' => OrderPaymentRequest::STATUS_PENDING,
            'selected_payment_method' => 'paypalOnetimePayment',
        ]);
    }

    private function capture(
        string $reference,
        string $amount,
        string $currency,
        string $status = 'COMPLETED',
        string $captureStatus = 'COMPLETED'
    ): object {
        return json_decode(json_encode([
            'status' => $status,
            'purchase_units' => [[
                'reference_id' => $reference,
                'payments' => [
                    'captures' => [[
                        'status' => $captureStatus,
                        'amount' => [
                            'value' => $amount,
                            'currency_code' => $currency,
                        ],
                    ]],
                ],
            ]],
        ]));
    }
}
