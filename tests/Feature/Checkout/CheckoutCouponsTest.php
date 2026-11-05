<?php

namespace Tests\Feature\Checkout;

use App\Http\Controllers\BasketController;
use App\Mail\AbandonedCartMailTwelve;
use App\Mail\SendUserYourOrderGiven;
use App\Models\Orders;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\CheckoutCouponService;
use App\Services\SynvolveWebhookService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CheckoutCouponsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('inv_sale_code')->nullable();
            $table->integer('is_active_friend_inv')->default(0);
            $table->rememberToken();
        });
        Schema::create('coupons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('text')->unique();
            $table->string('value')->default('10%');
            $table->integer('user_id')->default(0);
            $table->timestamps();
            foreach (['is_active', 'is_multiuse', 'is_dates_sale', 'is_30_40_free',
                'is_universal', 'is_facebook', 'is_1free', 'free_delivery',
                'is_40_60', 'is_abandoned_basket', 'is_giftcard'] as $flag) {
                $table->integer($flag)->default($flag === 'is_active' ? 1 : 0);
            }
        });
        $migration = require_once database_path('migrations/2026_10_05_120000_add_guest_coupon_recovery_identity.php');
        (new \AddGuestCouponRecoveryIdentity)->up();
    }

    private function coupon(array $extra = []): int
    {
        return DB::table('coupons')->insertGetId(array_merge([
            'text' => 'TEST-CODE', 'is_universal' => 1, 'is_multiuse' => 0,
        ], $extra));
    }

    private function basket(int $id): array
    {
        return ['totalPrice' => 100, 'coupon_id' => $id, 0 => ['sumPrice' => 100, 'count' => 1, 'pid' => 1]];
    }

    public function test_order_and_single_use_coupon_commit_together_and_failure_rolls_back()
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['first_name', 'last_name', 'address', 'postal_index', 'phone', 'settings',
                'active_coupon', 'is_coupon_dates', 'used_universal_coupon', 'last_ip'] as $field) {
                $table->text($field)->nullable();
            }
            $table->integer('bonuses')->default(0);
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2);
            $table->integer('use_bonus');
            foreach (['items', 'country', 'delivery', 'payment', 'payment_status', 'comment',
                'status', 'order_image', 'photo', 'ur_name', 'ur_name_l', 'ur_reg_num',
                'ur_legal_addr', 'ur_pnr_nr', 'ur_bank_name', 'ur_bank_code', 'ur_bank_acc_code'] as $field) {
                $table->text($field)->nullable();
            }
            $table->timestamps();
        });
        Schema::create('order_action', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user');
            $table->text('activity');
            $table->timestamp('created_at');
        });
        Schema::create('order_painter_images', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id');
            $table->text('image')->nullable();
            $table->text('small_image')->nullable();
            $table->integer('is_img_sketch')->default(0);
            $table->integer('is_img_painter')->default(0);
        });
        Schema::create('order_user_images', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id');
            $table->text('image')->nullable();
        });
        $userId = DB::table('users')->insertGetId(['email' => 'buyer@example.test']);
        $this->actingAs(User::find($userId));
        \Mail::fake();
        $webhook = \Mockery::mock(SynvolveWebhookService::class);
        $webhook->shouldReceive('notifyOrderSnapshotById')->once();
        $this->app->instance(SynvolveWebhookService::class, $webhook);
        $id = $this->coupon();
        $basket = $this->basket($id);
        $basket[0]['terms_price'] = 0;
        $params = array_fill_keys(['name_rec', 'last_name_rec', 'phone_rec', 'address_rec',
            'postal_index_rec', 'name', 'last_name', 'address', 'postal_index', 'city', 'comment', 'when_send'], '');
        $params = array_merge($params, ['email' => 'buyer@example.test', 'phone' => '+37129816036',
            'country' => 'LV', 'delivery' => 'pickup', 'payment' => 'bank', 'deliv_price' => 5]);
        // A failure after the order insert must restore both the order and coupon.
        $real = app(CheckoutCouponService::class);
        $failing = \Mockery::mock(CheckoutCouponService::class)->makePartial();
        $failing->shouldReceive('confirm')->andReturnUsing(function ($basket, $user) use ($real) {
            return $real->confirm($basket, $user);
        });
        $failing->shouldReceive('consume')->andReturnUsing(function ($basket) use ($real) {
            $real->consume($basket);
            throw new \RuntimeException('simulated persistence failure');
        });
        $this->app->instance(CheckoutCouponService::class, $failing);
        try {
            (new Orders)->saveOrder($basket, $params);
            $this->fail('Expected rollback');
        } catch (\RuntimeException $exception) {
            $this->assertSame('simulated persistence failure', $exception->getMessage());
        }
        $this->assertSame(0, DB::table('orders')->count());
        $this->assertEquals(1, DB::table('coupons')->where('id', $id)->value('is_active'));
        \Mail::assertNothingSent();
        $this->app->instance(CheckoutCouponService::class, $real);
        $orderId = (new Orders)->saveOrder($basket, $params);
        $this->assertGreaterThan(0, $orderId);
        $this->assertEquals(90, DB::table('orders')->where('id', $orderId)->value('sale_price'));
        $this->assertEquals(0, DB::table('coupons')->where('id', $id)->value('is_active'));
        $this->assertSame(1, DB::table('order_action')->count());
        \Mail::assertSent(SendUserYourOrderGiven::class);
        try {
            (new Orders)->saveOrder($basket, $params);
            $this->fail('Consumed code accepted');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('coupon', $exception->errors());
        }
        $this->assertSame(1, DB::table('orders')->count());
    }

    public function test_guest_applies_code_without_authentication_and_replay_is_duplicate()
    {
        $this->coupon();
        $this->withSession(['email' => 'guest@example.test'])
            ->post(route('coupon_use'), ['couponData' => 'TEST-CODE'])
            ->assertOk()->assertJson(['finded' => '1', 'status' => 'applied', 'provisional' => false]);
        $this->post(route('coupon_use'), ['couponData' => 'TEST-CODE'])
            ->assertOk()->assertJson(['status' => 'duplicate']);
        $this->assertGuest();
        $this->assertSame(0, DB::table('users')->count());
    }

    public function test_recovery_command_issues_a_guest_coupon_once_and_queues_one_email()
    {
        Schema::create('abandoned_carts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->string('locale');
            $table->text('cart_data');
            $table->string('recovery_token')->nullable();
            $table->boolean('is_send_email_twelve_hours')->default(false);
            $table->boolean('is_coupon_sent')->default(false);
            $table->timestamps();
        });
        DB::table('abandoned_carts')->insert(['email' => 'guest@example.test', 'locale' => 'ru',
            'cart_data' => '[{"pid":1}]', 'recovery_token' => 'test-recovery-cycle',
            'is_send_email_twelve_hours' => 1, 'created_at' => now()->subHours(25), 'updated_at' => now()->subHours(25)]);
        \Mail::fake();
        $this->artisan('cart:send-recovery-emails-coupons')->assertExitCode(0);
        $this->artisan('cart:send-recovery-emails-coupons')->assertExitCode(0);
        \Mail::assertQueued(AbandonedCartMailTwelve::class, 1);
        $this->assertSame(1, DB::table('coupons')->count());
        $this->assertSame(0, DB::table('users')->count());
        $this->assertEquals(0, DB::table('coupons')->value('user_id'));
        $this->assertSame('guest@example.test', DB::table('coupons')->value('recipient_email'));
        $this->assertSame('5%', DB::table('coupons')->value('value'));
    }

    public function test_endpoint_rejects_invalid_email_and_inactive_or_missing_codes()
    {
        $this->coupon(['is_active' => 0]);
        $this->post(route('coupon_use'), ['couponData' => 'TEST-CODE', 'email' => 'wrong'])
            ->assertStatus(422)->assertJson(['finded' => 'error']);
        $this->post(route('coupon_use'), ['couponData' => 'TEST-CODE'])
            ->assertStatus(422)->assertJson(['finded' => 'email']);
        $this->withSession(['email' => 'guest@example.test'])
            ->post(route('coupon_use'), ['couponData' => 'TEST-CODE'])
            ->assertStatus(422)->assertJson(['code' => 'inactive']);
        $this->post(route('coupon_use'), ['couponData' => 'missing'])
            ->assertStatus(422)->assertJson(['code' => 'not_found']);
    }

    public function test_personal_code_is_provisional_and_changed_identity_removes_discount()
    {
        $id = $this->coupon(['is_universal' => 0, 'is_abandoned_basket' => 1,
            'recipient_email' => 'guest@example.test']);
        $this->withSession(['email' => 'guest@example.test'])
            ->post(route('coupon_use'), ['couponData' => 'TEST-CODE'])
            ->assertOk()->assertJson(['provisional' => true]);
        $service = app(CheckoutCouponService::class);
        $basket = $service->refresh($this->basket($id));
        $this->assertSame('90.00', $basket['sale_price']);
        session()->put('email', 'other@example.test');
        $basket = $service->refresh($basket);
        $this->assertSame(0, $basket['coupon_id']);
        $this->assertArrayNotHasKey('sale_price', $basket);
        $this->assertNotEmpty(session('coupon_error'));
        BasketController::clearcart();
        $this->assertNull(session('coupon_error'));
    }

    public function test_final_confirmation_checks_user_even_if_session_claims_another_email()
    {
        $id = $this->coupon(['is_universal' => 0, 'is_dates_sale' => 1,
            'recipient_email' => 'owner@example.test']);
        $user = new User(['email' => 'other@example.test']);
        $user->id = 1;
        $this->expectException(ValidationException::class);
        DB::transaction(function () use ($id, $user) {
            app(CheckoutCouponService::class)->confirm($this->basket($id), $user);
        });
    }

    public function test_single_use_is_consumed_once_and_transaction_failure_restores_coupon()
    {
        $id = $this->coupon();
        $user = new User(['email' => 'guest@example.test']);
        $user->id = 1;
        $service = app(CheckoutCouponService::class);
        try {
            DB::transaction(function () use ($id, $user, $service) {
                $basket = $service->confirm($this->basket($id), $user);
                $service->consume($basket);
                throw new \RuntimeException('order failed');
            });
        } catch (\RuntimeException $e) {
        }
        $this->assertSame(1, (int) DB::table('coupons')->where('id', $id)->value('is_active'));
        DB::transaction(function () use ($id, $user, $service) {
            $service->consume($service->confirm($this->basket($id), $user));
        });
        $this->assertSame(0, (int) DB::table('coupons')->where('id', $id)->value('is_active'));
        $this->expectException(ValidationException::class);
        DB::transaction(function () use ($id, $user, $service) {
            $service->confirm($this->basket($id), $user);
        });
    }

    public function test_multiuse_survives_but_giftcard_is_always_single_use()
    {
        $id = $this->coupon(['is_multiuse' => 1]);
        $service = app(CheckoutCouponService::class);
        DB::transaction(function () use ($id, $service) {
            $service->consume(['coupon_id' => $id, 'coupon_type' => 'universal']);
        });
        $this->assertSame(1, (int) DB::table('coupons')->where('id', $id)->value('is_active'));
        DB::transaction(function () use ($id, $service) {
            $service->consume(['coupon_id' => $id, 'coupon_type' => 'giftcard']);
        });
        $this->assertSame(0, (int) DB::table('coupons')->where('id', $id)->value('is_active'));
    }

    public function test_guest_recovery_coupon_is_idempotent_and_does_not_create_an_account()
    {
        $repo = app(UserRepository::class);
        $key = hash('sha256', 'cart:token');
        $code = $repo->generateCouponUser('Guest@example.test', $key);
        $this->assertSame($code, $repo->generateCouponUser('guest@example.test', $key));
        $this->assertSame(1, DB::table('coupons')->count());
        $this->assertSame(0, DB::table('users')->count());
        $coupon = DB::table('coupons')->first();
        $this->assertSame('guest@example.test', $coupon->recipient_email);
        $this->assertSame('5%', $coupon->value);
        $this->assertTrue(app(CheckoutCouponService::class)->inspect($coupon, 'guest@example.test')['provisional']);
    }

    public function test_capture_email_validates_and_never_logs_in_existing_user()
    {
        DB::table('users')->insert(['email' => 'owner@example.test']);
        $this->post(route('cart.set-email'), ['email' => 'wrong'])->assertSessionHasErrors('email');
        $this->post(route('cart.set-email'), ['email' => 'owner@example.test'])
            ->assertRedirect(route('cart.index'))->assertSessionHas('email', 'owner@example.test');
        $this->assertGuest();
    }
}
