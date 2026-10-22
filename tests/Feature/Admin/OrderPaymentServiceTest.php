<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\GiftcardController;
use App\Mail\GiftCard;
use App\Mail\Payment_successful;
use App\Models\Coupon;
use App\Models\Orders;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\Admin\OrderPaymentService;
use App\Services\BestEffortMailService;
use App\Services\Invoice\InvoicePdfWriter;
use App\Services\SynvolveWebhookService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Stevebauman\Location\Facades\Location;
use Tests\TestCase;

class OrderPaymentServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Http::preventStrayRequests();
        Mail::fake();

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

    public function test_manual_repeats_and_unpaid_flag_match_original_bonus_behavior(): void
    {
        config(['admin_migration.payment_notifications_enabled' => false]);
        $inviter = $this->createUser('inviter@example.test', ['bonuses' => 2, 'inv_sale_code' => 'INVITER']);
        $customer = $this->createUser('customer@example.test', ['bonuses' => 4]);
        $order = $this->createOrder($customer, ['price' => 100, 'used_coupon' => 'INVITER']);
        $mail = Mockery::mock(BestEffortMailService::class);
        $mail->shouldReceive('attempt')->times(6)->andReturnTrue();
        $mail->shouldNotReceive('send');
        $this->app->instance(BestEffortMailService::class, $mail);
        $webhook = Mockery::mock(SynvolveWebhookService::class);
        $webhook->shouldNotReceive('notifyOrderSnapshotById');
        $this->app->instance(SynvolveWebhookService::class, $webhook);
        $service = app(OrderPaymentService::class);
        $service->updateStatus($order, 'payed');
        $this->assertSame(2, (int) $order->use_bonus);
        $this->assertEquals(8, User::findOrFail($customer)->bonuses);
        $service->updateStatus($order, 'payed');
        $this->assertSame(1, (int) $order->use_bonus);
        $this->assertEquals(8, User::findOrFail($customer)->bonuses);
        $service->updateStatus($order, 'not_payed');
        $this->assertSame(1, (int) $order->use_bonus);
        $this->assertSame('not_payed', $order->payment_status);
        $service->updateStatus($order, 'payed');
        $this->assertEquals(8, User::findOrFail($customer)->bonuses);
        $this->assertEquals(17, User::findOrFail($inviter)->bonuses);
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_unpaid_from_zero_sets_original_bonus_flag_without_notifying(): void
    {
        $order = $this->createOrder($this->createUser('customer@example.test'));
        $mail = Mockery::mock(BestEffortMailService::class);
        $mail->shouldNotReceive('attempt');
        $mail->shouldNotReceive('send');
        $this->app->instance(BestEffortMailService::class, $mail);
        config(['admin_migration.payment_notifications_enabled' => false]);
        app(OrderPaymentService::class)->updateStatus($order, 'not_payed');
        $this->assertSame(1, (int) $order->use_bonus);
    }

    public static function failedPaymentSaves(): array
    {
        return [
            ['payed', 'inviter'], ['payed', 'customer'], ['payed', 'order'],
            ['prepayment', 'order'], ['amount', 'order'],
        ];
    }

    #[DataProvider('failedPaymentSaves')]
    public function test_save_failure_rolls_back_payment_and_bonuses_before_notifications(string $operation, string $target): void
    {
        $inviter = $this->createUser('inviter@example.test', ['bonuses' => 2, 'inv_sale_code' => 'INVITER']);
        $customer = $this->createUser('customer@example.test', ['bonuses' => 4]);
        $order = $this->createOrder($customer, ['price' => 100, 'used_coupon' => 'INVITER']);
        $beforeOrder = $order->getRawOriginal();
        $beforeUsers = DB::table('users')->orderBy('id')->get()->toJson();
        $event = 'eloquent.saving: '.($target === 'order' ? Orders::class : User::class);
        $id = match ($target) {
            'order' => $order->id, 'customer' => $customer, default => $inviter,
        };
        app('events')->listen($event, fn ($model) => $model->id === $id ? false : null);
        $mail = Mockery::mock(BestEffortMailService::class);
        $mail->shouldNotReceive('attempt');
        $mail->shouldNotReceive('send');
        $this->app->instance(BestEffortMailService::class, $mail);
        $webhook = Mockery::mock(SynvolveWebhookService::class);
        $webhook->shouldNotReceive('notifyOrderSnapshotById');
        $this->app->instance(SynvolveWebhookService::class, $webhook);
        config(['admin_migration.payment_notifications_enabled' => true]);
        try {
            $service = app(OrderPaymentService::class);
            $operation === 'amount' ? $service->updatePrepayment($order, 25) : $service->updateStatus($order, $operation);
            $this->fail('Failed save must reject payment update.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($operation === 'amount' ? 'prepayment_price' : 'payment_status', $exception->errors());
        } finally {
            app('events')->forget($event);
        }
        $this->assertSame($beforeOrder, $order->fresh()->getRawOriginal());
        $this->assertSame($beforeUsers, DB::table('users')->orderBy('id')->get()->toJson());
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    private function giftOrder(): Orders
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->json('settings')->nullable();
            $table->string('first_name')->nullable();
        });
        Schema::create('coupons', function (Blueprint $table): void {
            $table->id();
            $table->string('text');
            $table->integer('user_id');
            $table->integer('order_id');
            $table->integer('value');
            $table->boolean('is_giftcard');
            $table->boolean('is_offline');
            $table->string('pdf')->nullable();
            $table->timestamps();
        });
        Schema::create('a_mail_top_sale', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->integer('foreign_key');
            $table->string('table_name');
            $table->string('column_name');
            $table->string('locale');
        });
        DB::connection()->getPdo()->sqliteCreateFunction('RAND', fn () => 0.5);
        Location::shouldReceive('get')->andReturnFalse();
        Storage::fake('gift-card-test');

        return $this->createOrder($this->createUser('gift@example.test', [
            'country' => 'de', 'settings' => '{"locale":"lv"}', 'first_name' => 'Fixture', 'bonuses' => 4,
        ]), ['price' => 125, 'items' => json_encode([
            ['pid' => 5, 'count' => 2, 'card_type' => 'offline', 'price' => 50],
            ['pid' => 5, 'count' => 1, 'card_type' => 'online', 'price' => 25],
            ['pid' => 2, 'count' => 10, 'price' => 999],
        ])]);
    }

    public function test_gift_payment_creates_one_coupon_per_card_with_original_type_locale_and_one_mail(): void
    {
        $order = $this->giftOrder();
        $pdf = Mockery::mock(GiftcardController::class);
        $pdf->shouldReceive('generateGiftCardPDF')->times(3)->andReturnUsing(function ($user, $orderId, $couponId, $price, $locale, $code) use ($order): string {
            $coupon = Coupon::findOrFail($couponId);
            $this->assertEquals($order->user_id, $user);
            $this->assertSame($order->id, $orderId);
            $this->assertSame('lv', $locale);
            $this->assertSame($code, $coupon->text);
            $this->assertEquals($coupon->value, $price);
            $path = 'cards/'.$couponId.'.pdf';
            app(InvoicePdfWriter::class)->write(Storage::disk('gift-card-test')->path($path), '%PDF fixture');

            return 'https://example.test/'.$path;
        });
        $this->app->instance(GiftcardController::class, $pdf);
        $mail = Mockery::mock(BestEffortMailService::class);
        $mail->shouldReceive('send')->once()->with('gift@example.test', Mockery::type(Payment_successful::class), 'admin_order_payment_status', Mockery::type('array'))->andReturnTrue();
        $mail->shouldReceive('send')->once()->withArgs(function ($email, $mailable, $purpose): bool {
            $this->assertSame('gift@example.test', $email);
            $this->assertInstanceOf(GiftCard::class, $mailable);
            $this->assertSame('lv', $mailable->locale);
            $this->assertSame('Fixture', $mailable->data['first_name']);

            return $purpose === 'admin_order_gift_cards';
        })->andReturnTrue();
        $this->app->instance(BestEffortMailService::class, $mail);
        $webhook = Mockery::mock(SynvolveWebhookService::class);
        $webhook->shouldReceive('notifyOrderSnapshotById')->once()->with($order->id, 'payment_status_changed_manual', ['payment_status' => 'payed'])->andReturnTrue();
        $this->app->instance(SynvolveWebhookService::class, $webhook);
        config(['admin_migration.payment_notifications_enabled' => true]);
        $result = app(OrderPaymentService::class)->updateStatus($order, 'payed');
        $this->assertTrue($result['gift_card_mail_processed']);
        $this->assertSame(3, Coupon::count());
        $this->assertSame(2, Coupon::where('is_offline', 1)->where('value', 50)->count());
        $this->assertSame(1, Coupon::where('is_offline', 0)->where('value', 25)->count());
        $this->assertSame(0, Coupon::whereNull('pdf')->count());
        $this->assertCount(3, Storage::disk('gift-card-test')->allFiles());
        $this->assertSame('payed', $order->payment_status);
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public static function giftFailures(): array
    {
        return [['second PDF'], ['coupon save'], ['missing coupon']];
    }

    public static function nonPaidGiftStatuses(): array
    {
        return [['prepayment'], ['not_payed']];
    }

    #[DataProvider('nonPaidGiftStatuses')]
    public function test_gift_cards_are_not_issued_for_unpaid_or_prepayment_status(string $status): void
    {
        $order = $this->giftOrder();
        $this->mock(GiftcardController::class)->shouldNotReceive('generateGiftCardPDF');
        $this->mock(UserRepository::class)->shouldNotReceive('generateCouponUserGiftCard');
        $mail = Mockery::mock(BestEffortMailService::class);
        $mail->shouldNotReceive('send');
        if ($status === 'prepayment') {
            $mail->shouldReceive('attempt')->once()->andReturnTrue();
        } else {
            $mail->shouldNotReceive('attempt');
        }
        $this->app->instance(BestEffortMailService::class, $mail);
        $this->mock(SynvolveWebhookService::class)->shouldNotReceive('notifyOrderSnapshotById');
        config(['admin_migration.payment_notifications_enabled' => false]);
        $result = app(OrderPaymentService::class)->updateStatus($order, $status);
        $this->assertFalse($result['gift_card_mail_processed']);
        $this->assertSame(0, Coupon::count());
        $this->assertSame([], Storage::disk('gift-card-test')->allFiles());
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    #[DataProvider('giftFailures')]
    public function test_failed_gift_issue_rolls_back_payment_coupons_and_new_pdfs_before_mail(string $failure): void
    {
        $order = $this->giftOrder();
        $before = $order->getRawOriginal();
        Storage::disk('gift-card-test')->put('existing.pdf', 'keep');
        if ($failure === 'missing coupon') {
            $this->mock(UserRepository::class)->shouldReceive('generateCouponUserGiftCard')->once()->andReturnNull();
        }
        if ($failure === 'coupon save') {
            Coupon::saving(fn () => false);
        }
        $calls = 0;
        $pdf = Mockery::mock(GiftcardController::class);
        if ($failure === 'missing coupon') {
            $pdf->shouldNotReceive('generateGiftCardPDF');
        } else {
            $pdf->shouldReceive('generateGiftCardPDF')->times($failure === 'second PDF' ? 2 : 1)
                ->andReturnUsing(function ($userId, $orderId, $couponId) use (&$calls): string {
                    if (++$calls === 2) {
                        throw new RuntimeException('Gift PDF failed');
                    }
                    app(InvoicePdfWriter::class)->write(Storage::disk('gift-card-test')->path('cards/'.$couponId.'.pdf'), '%PDF fixture');

                    return 'https://example.test/cards/'.$couponId.'.pdf';
                });
        }
        $this->app->instance(GiftcardController::class, $pdf);
        $mail = Mockery::mock(BestEffortMailService::class);
        $mail->shouldNotReceive('send');
        $mail->shouldNotReceive('attempt');
        $this->app->instance(BestEffortMailService::class, $mail);
        $this->mock(SynvolveWebhookService::class)->shouldNotReceive('notifyOrderSnapshotById');
        config(['admin_migration.payment_notifications_enabled' => true]);
        try {
            app(OrderPaymentService::class)->updateStatus($order, 'payed');
            $this->fail('Gift issue failure must reject payment update.');
        } catch (\Throwable $exception) {
            $this->assertInstanceOf($failure === 'second PDF' ? RuntimeException::class : ValidationException::class, $exception);
        } finally {
            app('events')->forget('eloquent.saving: '.Coupon::class);
        }
        $this->assertSame($before, $order->fresh()->getRawOriginal());
        $this->assertEquals(4, User::findOrFail($order->user_id)->bonuses);
        $this->assertSame(0, Coupon::count());
        $this->assertSame(['existing.pdf'], Storage::disk('gift-card-test')->allFiles());
        $this->assertSame('keep', Storage::disk('gift-card-test')->get('existing.pdf'));
        Mail::assertNothingSent();
        Http::assertNothingSent();
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
