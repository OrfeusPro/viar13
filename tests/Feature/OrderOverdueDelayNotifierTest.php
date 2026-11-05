<?php

namespace Tests\Feature;

use App\Mail\OrderOverdueDelayMail;
use App\Models\Orders;
use App\Services\OrderOverdueDelayNotifier;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderOverdueDelayNotifierTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
    }

    /** @test */
    public function test_it_sends_delay_email_for_overdue_open_order()
    {
        Mail::fake();

        $this->createOrder(801, [
            'email' => 'client@example.test',
            'when_send' => '2026-05-28',
        ]);

        $result = app(OrderOverdueDelayNotifier::class)
            ->sendDueNotifications(Carbon::parse('2026-05-28 18:00:00'));

        $this->assertSame(1, $result['sent']);

        Mail::assertSent(OrderOverdueDelayMail::class, function (OrderOverdueDelayMail $mail) {
            return (int) $mail->order->id === 801
                && ($mail->delivery['email'] ?? null) === 'client@example.test';
        });

        $this->assertNotNull(Orders::query()->find(801)->overdue_delay_email_sent_at);
    }

    /** @test */
    public function test_it_uses_client_language_from_order_delivery()
    {
        Mail::fake();

        $this->createOrder(804, [
            'email' => 'client@example.test',
            'when_send' => '2026-05-28',
            'lang' => 'en',
        ]);

        app(OrderOverdueDelayNotifier::class)
            ->sendDueNotifications(Carbon::parse('2026-05-28 18:00:00'));

        Mail::assertSent(OrderOverdueDelayMail::class, function (OrderOverdueDelayMail $mail) {
            return (int) $mail->order->id === 804
                && $mail->locale === 'en'
                && $mail->build()->subject === 'A small update about your order #804';
        });
    }

    /** @test */
    public function test_it_uses_user_locale_when_delivery_language_is_empty()
    {
        Mail::fake();

        \DB::table('users')->insert([
            'id' => 55,
            'email' => 'client@example.test',
            'locale' => 'lv',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->createOrder(805, [
            'email' => 'client@example.test',
            'when_send' => '2026-05-28',
        ], [
            'user_id' => 55,
        ]);

        app(OrderOverdueDelayNotifier::class)
            ->sendDueNotifications(Carbon::parse('2026-05-28 18:00:00'));

        Mail::assertSent(OrderOverdueDelayMail::class, function (OrderOverdueDelayMail $mail) {
            return (int) $mail->order->id === 805
                && $mail->locale === 'lv'
                && $mail->build()->subject === 'Neliels jaunums par jūsu pasūtījumu #805';
        });
    }

    /** @test */
    public function test_it_falls_back_to_russian_when_client_language_is_unknown()
    {
        Mail::fake();

        $this->createOrder(806, [
            'email' => 'client@example.test',
            'when_send' => '2026-05-28',
            'lang' => 'unknown',
        ]);

        app(OrderOverdueDelayNotifier::class)
            ->sendDueNotifications(Carbon::parse('2026-05-28 18:00:00'));

        Mail::assertSent(OrderOverdueDelayMail::class, function (OrderOverdueDelayMail $mail) {
            return (int) $mail->order->id === 806
                && $mail->locale === 'ru'
                && $mail->build()->subject === 'Небольшое обновление по вашему заказу #806';
        });
    }

    /** @test */
    public function test_it_does_not_send_before_planned_shipping_date()
    {
        Mail::fake();

        $this->createOrder(802, [
            'email' => 'client@example.test',
            'when_send' => '2026-05-31',
        ]);

        $result = app(OrderOverdueDelayNotifier::class)
            ->sendDueNotifications(Carbon::parse('2026-05-31 17:59:59'));

        $this->assertSame(0, $result['sent']);
        Mail::assertNotSent(OrderOverdueDelayMail::class);
        $this->assertNull(Orders::query()->find(802)->overdue_delay_email_sent_at);
    }

    /** @test */
    public function test_it_does_not_send_duplicate_email_after_first_success()
    {
        Mail::fake();

        $this->createOrder(803, [
            'email' => 'client@example.test',
            'when_send' => '2026-05-28',
        ]);

        $service = app(OrderOverdueDelayNotifier::class);

        $firstResult = $service->sendDueNotifications(Carbon::parse('2026-05-28 18:00:00'));
        $secondResult = $service->sendDueNotifications(Carbon::parse('2026-05-28 18:00:00'));

        $this->assertSame(1, $firstResult['sent']);
        $this->assertSame(0, $secondResult['sent']);
        Mail::assertSent(OrderOverdueDelayMail::class, 1);
    }

    /** @test */
    public function test_it_uses_desired_delivery_date_before_sla_rules()
    {
        Mail::fake();

        $this->createOrder(807, [
            'email' => 'client@example.test',
            'when_send' => '2026-06-10',
        ], [
            'created_at' => '2026-06-02 20:11:00',
            'updated_at' => '2026-06-02 20:11:00',
        ], [
            ['basketType' => '1', 'name' => 'Canvas'],
        ]);

        $beforeResult = app(OrderOverdueDelayNotifier::class)
            ->sendDueNotifications(Carbon::parse('2026-06-10 17:59:59'));

        $this->assertSame(0, $beforeResult['sent']);

        $dueResult = app(OrderOverdueDelayNotifier::class)
            ->sendDueNotifications(Carbon::parse('2026-06-10 18:00:00'));

        $this->assertSame(1, $dueResult['sent']);
        Mail::assertSent(OrderOverdueDelayMail::class, 1);
    }

    /** @test */
    public function test_it_sends_canvas_print_order_after_three_business_days_when_desired_date_is_empty()
    {
        Mail::fake();

        $this->createOrder(808, [
            'email' => 'client@example.test',
            'when_send' => '',
        ], [
            'created_at' => '2026-06-02 20:11:00',
            'updated_at' => '2026-06-02 20:11:00',
        ], [
            ['basketType' => '1', 'name' => 'Canvas'],
        ]);

        $beforeResult = app(OrderOverdueDelayNotifier::class)
            ->sendDueNotifications(Carbon::parse('2026-06-05 17:59:59'));

        $this->assertSame(0, $beforeResult['sent']);

        $dueResult = app(OrderOverdueDelayNotifier::class)
            ->sendDueNotifications(Carbon::parse('2026-06-05 18:00:00'));

        $this->assertSame(1, $dueResult['sent']);
    }

    /** @test */
    public function test_it_sends_other_portrait_orders_after_eight_business_days_when_desired_date_is_empty()
    {
        Mail::fake();

        $this->createOrder(809, [
            'email' => 'client@example.test',
            'when_send' => '',
        ], [
            'created_at' => '2026-06-05 17:16:00',
            'updated_at' => '2026-06-05 17:16:00',
        ], [
            ['basketType' => '1', 'is_port_product' => 1, 'service_id' => 'HM-27', 'name' => 'Custom portrait'],
        ]);

        $beforeResult = app(OrderOverdueDelayNotifier::class)
            ->sendDueNotifications(Carbon::parse('2026-06-17 17:59:59'));

        $this->assertSame(0, $beforeResult['sent']);

        $dueResult = app(OrderOverdueDelayNotifier::class)
            ->sendDueNotifications(Carbon::parse('2026-06-17 18:00:00'));

        $this->assertSame(1, $dueResult['sent']);
    }

    private function createSchema(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->nullable();
            $table->string('locale')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->text('items')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('country')->nullable();
            $table->text('delivery')->nullable();
            $table->string('payment')->nullable();
            $table->string('payment_status')->default('not_payed');
            $table->string('status')->default('watching');
            $table->timestamp('send_date')->nullable();
            $table->timestamp('overdue_delay_email_sent_at')->nullable();
            $table->timestamps();
        });
    }

    private function createOrder(int $id, array $deliveryOverrides = [], array $overrides = [], array $items = []): void
    {
        \DB::table('orders')->insert(array_merge([
            'id' => $id,
            'user_id' => 1,
            'items' => json_encode($items),
            'price' => 50,
            'country' => 'LV',
            'delivery' => json_encode(array_merge([
                'country' => 'LV',
                'email' => 'client@example.test',
                'first_name' => 'Test',
                'last_name' => 'User',
                'when_send' => '2026-05-28',
            ], $deliveryOverrides)),
            'payment' => 'transfer',
            'payment_status' => 'payed',
            'status' => 'watching',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }
}
