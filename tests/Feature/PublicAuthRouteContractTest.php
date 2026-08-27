<?php

namespace Tests\Feature;

use App\Mail\SendUserRegister;
use App\Models\User;
use App\Notifications\BrandedResetPassword;
use App\Repositories\BasketRepository;
use App\Services\UpdatePainterImageService;
use App\Services\UpdatePainterSketchImageService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicAuthRouteContractTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('role_id')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->string('ad')->nullable();
            $table->string('client_data')->nullable();
            $table->decimal('bonuses', 10, 2)->nullable();
            $table->string('inv_sale_code')->nullable();
            $table->string('invited')->nullable();
            $table->string('news')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->string('registration_page')->nullable();
            $table->string('referrer_url')->nullable();
            $table->json('utm_parameters')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('last_ip')->nullable();
            $table->timestamps();
        });
        Schema::dropIfExists('roles');
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('display_name')->nullable();
            $table->timestamps();
        });
        Schema::dropIfExists('a_mail_top_sale');
        Schema::create('a_mail_top_sale', function (Blueprint $table): void {
            $table->id();
            $table->string('image')->nullable();
            $table->string('size')->nullable();
            $table->decimal('sale_price', 10, 2)->default(0);
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedBigInteger('cat_id')->nullable();
        });
        Schema::dropIfExists('translations');
        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('table_name');
            $table->string('column_name');
            $table->unsignedBigInteger('foreign_key');
            $table->string('locale');
            $table->text('value')->nullable();
        });
        Schema::dropIfExists('header_menu');
        Schema::create('header_menu', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->nullable();
            $table->string('link')->nullable();
            $table->string('images')->nullable();
            $table->unsignedInteger('menu_pos')->default(1);
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_show')->default(true);
            $table->timestamps();
        });
        Schema::dropIfExists('footer_menu');
        Schema::create('footer_menu', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->nullable();
            $table->string('link')->nullable();
            $table->unsignedInteger('menu_pos')->default(1);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
        Schema::dropIfExists('locales');
        Schema::create('locales', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('lang')->nullable();
        });
        Schema::dropIfExists('country_tels');
        Schema::create('country_tels', function (Blueprint $table): void {
            $table->id();
            $table->string('country_code')->nullable();
            $table->string('country_name')->nullable();
            $table->decimal('price_country_mltpr', 10, 2)->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
        Schema::dropIfExists('orders');
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('status')->nullable();
            $table->boolean('painter_payed')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->text('items')->nullable();
            $table->text('delivery')->nullable();
            $table->string('payment')->nullable();
            $table->string('payment_status')->nullable();
            $table->text('labels')->nullable();
            $table->text('comment')->nullable();
            $table->text('admin_comment')->nullable();
            $table->text('client_comment')->nullable();
            $table->text('client_images')->nullable();
            $table->text('painter_images')->nullable();
            $table->text('painter_sketch_images')->nullable();
            $table->unsignedBigInteger('painter_images_status')->nullable();
            $table->unsignedBigInteger('painter_sketch_images_status')->nullable();
            $table->timestamp('painter_images_status_date')->nullable();
            $table->timestamp('painter_sketch_images_status_date')->nullable();
            $table->timestamp('painter_endtime')->nullable();
            $table->boolean('is_show_painter_images')->default(false);
            $table->boolean('has_pdf')->default(false);
            $table->timestamps();
        });
        Schema::dropIfExists('painter_orders');
        Schema::create('painter_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('order_id');
            $table->timestamp('complete_until')->nullable();
            $table->timestamps();
        });
        Schema::dropIfExists('order_painter_images');
        Schema::create('order_painter_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id');
            $table->text('image')->nullable();
            $table->text('small_image')->nullable();
            $table->unsignedBigInteger('status')->nullable();
            $table->boolean('is_img_painter')->default(false);
            $table->boolean('is_img_sketch')->default(false);
            $table->timestamps();
        });
        Schema::dropIfExists('orders_chats');
        Schema::create('orders_chats', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('orders_id');
            $table->text('comment')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_read')->default(false);
            $table->boolean('is_img_painter')->default(false);
            $table->boolean('is_img_sketch')->default(false);
            $table->unsignedBigInteger('order_painter_image_id')->nullable();
            $table->timestamps();
        });
        Schema::dropIfExists('order_user_comments');
        Schema::create('order_user_comments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id');
            $table->foreignId('user_id')->nullable();
            $table->text('comment')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_read')->default(false);
            $table->boolean('admin_is_read')->default(false);
            $table->boolean('is_img_painter')->default(false);
            $table->boolean('is_img_sketch')->default(false);
            $table->unsignedBigInteger('order_painter_image_id')->nullable();
            $table->timestamps();
        });
        Schema::dropIfExists('order_painter_comments');
        Schema::create('order_painter_comments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
        Schema::dropIfExists('user_messages');
        Schema::create('user_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('admin_user_chat_title')->nullable();
            $table->string('user_painter_mail_subject')->nullable();
            $table->timestamps();
        });
        DB::table('user_messages')->insert([
            'admin_user_chat_title' => 'Order {order_id}',
            'user_painter_mail_subject' => 'Order message',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Schema::dropIfExists('stocks');
        Schema::create('stocks', function (Blueprint $table): void {
            $table->id();
            $table->decimal('date_1_sale', 10, 2)->nullable();
            $table->decimal('date_2_sale', 10, 2)->nullable();
            $table->decimal('custom_coupon_sale', 10, 2)->nullable();
            $table->string('lk_title')->nullable();
            $table->timestamps();
        });
        DB::table('stocks')->insert([
            'date_1_sale' => 0,
            'date_2_sale' => 0,
            'custom_coupon_sale' => 0,
            'lk_title' => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Schema::dropIfExists('coupons');
        Schema::create('coupons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->boolean('is_active')->default(true);
        });
        Schema::dropIfExists('a_painter_images_status');
        Schema::create('a_painter_images_status', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->nullable();
            $table->timestamps();
        });
        Schema::dropIfExists('gallery_items');
        Schema::create('gallery_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('id_type')->nullable();
            $table->text('custom_size_prices_sale')->nullable();
            $table->boolean('is_big_sale')->default(false);
            $table->timestamp('sale_end')->nullable();
            $table->timestamps();
        });
        Schema::dropIfExists('gallery_page');
        Schema::create('gallery_page', function (Blueprint $table): void {
            $table->id();
            $table->string('gal__desc')->nullable();
            $table->timestamps();
        });
        DB::table('gallery_page')->insert([
            'gal__desc' => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Schema::dropIfExists('password_resets');
        Schema::create('password_resets', function (Blueprint $table): void {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function test_guest_auth_forms_are_available(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
        $this->get('/password/reset')->assertOk();
    }

    public function test_empty_auth_submissions_are_rejected_before_database_access(): void
    {
        $this->from('/login')
            ->post('/login')
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email', 'password']);

        $this->from('/register')
            ->post('/register')
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['email', 'password']);

        $this->from('/password/reset')
            ->post('/password/email')
            ->assertRedirect('/password/reset')
            ->assertSessionHasErrors(['email']);
    }

    public function test_guest_ajax_auth_validation_returns_json_errors(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);
        $this->postJson('/custom_login_ajax', [
            'email' => 'not-an-email',
            'password' => '',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);

        $this->postJson('/custom_register_ajax', [
            'name' => '',
            'email' => 'not-an-email',
            'phone' => '123',
            'password' => 'secret',
            'password_confirmation' => 'different',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'phone', 'password']);
    }

    public function test_guest_ajax_registration_normalizes_contacts_and_does_not_require_broken_captcha(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);
        Mail::fake();
        $this->mock(BasketRepository::class)
            ->shouldReceive('saveBasketToAbandonedCartModel')
            ->once()
            ->with([], true);

        $this->postJson('/custom_register_ajax', [
            'name' => 'Guest',
            'surname' => 'Customer',
            'email' => '  GUEST@EXAMPLE.TEST ',
            'phone' => '+371 (20) 123-456',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertOk()->assertContent('true');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'guest@example.test',
            'phone' => '+37120123456',
        ]);
        Mail::assertSent(SendUserRegister::class);
    }

    public function test_guest_ajax_login_keeps_the_legacy_json_contract(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);
        $user = User::query()->create([
            'email' => 'ajax@example.test',
            'password' => Hash::make('secret123'),
        ]);
        $this->mock(BasketRepository::class)
            ->shouldReceive('saveBasketToAbandonedCartModel')
            ->once()
            ->with(null);

        $this->postJson('/custom_login_ajax', [
            'email' => ' AJAX@EXAMPLE.TEST ',
            'password' => 'secret123',
        ])->assertOk()->assertExactJson(['status' => true]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_guest_ajax_auth_routes_are_rate_limited(): void
    {
        $this->assertContains(
            'throttle:10,1',
            Route::getRoutes()->getByName('custom_login_ajax')->gatherMiddleware()
        );
        $this->assertContains(
            'throttle:5,1',
            Route::getRoutes()->getByName('custom_register_ajax')->gatherMiddleware()
        );
    }

    public function test_guest_account_routes_keep_the_legacy_home_redirect(): void
    {
        $this->get('/account')->assertRedirect('/');
        $this->get('/new/account')->assertRedirect('/');
        $this->get('/password/confirm')->assertRedirect('/');
    }

    public function test_email_verification_routes_are_not_published(): void
    {
        $this->assertFalse(Route::has('verification.notice'));
        $this->assertFalse(Route::has('verification.verify'));
        $this->assertFalse(Route::has('verification.resend'));
    }

    public function test_user_can_log_in_and_log_out(): void
    {
        DB::table('users')->insert([
            'email' => 'frontend@example.test',
            'password' => Hash::make('secret-pass'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->post('/login', [
            'email' => 'frontend@example.test',
            'password' => 'secret-pass',
        ])->assertRedirect('/');

        $this->assertAuthenticated();

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_user_can_register_without_sending_real_mail(): void
    {
        Mail::fake();

        $this->post('/register', [
            'email' => 'registered@example.test',
            'password' => 'secret-pass',
            'password_confirmation' => 'secret-pass',
        ])->assertRedirect('/');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'registered@example.test']);
        Mail::assertSent(SendUserRegister::class);
    }

    public function test_user_can_request_and_complete_password_reset(): void
    {
        Notification::fake();
        $user = $this->createUser('reset@example.test', 'old-secret');

        $this->from('/password/reset')
            ->post('/password/email', ['email' => $user->email])
            ->assertRedirect('/password/reset')
            ->assertSessionHas('status');

        $token = null;
        Notification::assertSentTo(
            $user,
            BrandedResetPassword::class,
            function (BrandedResetPassword $notification) use (&$token): bool {
                $token = $notification->token;

                return true;
            }
        );

        $this->post('/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-secret-pass',
            'password_confirmation' => 'new-secret-pass',
        ])->assertRedirect('/login');

        $this->assertTrue(Hash::check('new-secret-pass', $user->fresh()->password));
    }

    public function test_authenticated_user_can_confirm_password(): void
    {
        $user = $this->createUser('confirm@example.test', 'secret-pass');
        $this->actingAs($user);

        $this->get('/password/confirm')->assertOk();

        $this->from('/password/confirm')
            ->post('/password/confirm', ['password' => 'wrong-pass'])
            ->assertRedirect('/password/confirm')
            ->assertSessionHasErrors('password');

        $this->post('/password/confirm', ['password' => 'secret-pass'])
            ->assertRedirect('/')
            ->assertSessionHas('auth.password_confirmed_at');
    }

    public function test_legacy_account_route_redirects_to_new_account(): void
    {
        $user = $this->createAccountUser();

        $this->actingAs($user)
            ->withHeader('Accept-Language', 'ru')
            ->get('/account')
            ->assertRedirect(route('new_account.index'));
    }

    public function test_authenticated_customer_can_open_new_account_home(): void
    {
        $user = $this->createAccountUser();

        $this->actingAs($user)
            ->withHeader('Accept-Language', 'ru')
            ->get('/new/account')
            ->assertOk();
    }

    public function test_authenticated_customer_can_open_settings_and_empty_orders(): void
    {
        $user = $this->createAccountUser();
        $client = $this->actingAs($user)->withHeader('Accept-Language', 'ru');

        $client->get('/new/settings')->assertOk();
        $client->get('/new/orders')->assertOk();
    }

    public function test_authenticated_painter_can_open_empty_orders(): void
    {
        $roleId = DB::table('roles')->insertGetId([
            'name' => 'painter',
            'display_name' => 'Painter',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'role_id' => $roleId,
            'email' => 'painter@example.test',
            'password' => Hash::make('secret-pass'),
            'inv_sale_code' => 'PAINTER1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $painter = User::query()->where('email', 'painter@example.test')->firstOrFail();

        $this->actingAs($painter)
            ->withHeader('Accept-Language', 'ru')
            ->get('/new/orders')
            ->assertOk();
    }

    public function test_authenticated_painter_can_open_assigned_order(): void
    {
        [$painter, $orderId] = $this->createAssignedPainterOrder();

        $this->actingAs($painter)
            ->withHeader('Accept-Language', 'ru')
            ->get('/new/orders')
            ->assertOk()
            ->assertSee('# ' . $orderId, false)
            ->assertSee('js_painter_form_images_upd', false)
            ->assertSee('js_painter_form_images_upd_sketch', false)
            ->assertSee('js_painter_admin_chat', false);
    }

    public function test_assigned_painter_upload_endpoints_keep_the_legacy_json_contract(): void
    {
        Mail::fake();
        [$painter, $orderId] = $this->createAssignedPainterOrder();

        $this->mock(UpdatePainterImageService::class)
            ->shouldReceive('store')
            ->once()
            ->andReturn('https://viar.test/orders/picture.jpg');

        $this->actingAs($painter)
            ->postJson('/new/update_painter_order_images', ['order_id' => $orderId])
            ->assertOk()
            ->assertJsonPath('status', 1)
            ->assertJsonPath('order_id', $orderId);

        $this->mock(UpdatePainterSketchImageService::class)
            ->shouldReceive('store')
            ->once()
            ->andReturn('https://viar.test/orders/sketch.jpg');

        $this->actingAs($painter)
            ->postJson('/new/update_painter_sketch_order_images', ['order_id' => $orderId])
            ->assertOk()
            ->assertJsonPath('status', 1)
            ->assertJsonPath('order_id', $orderId);

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'painter_images_status' => 2,
            'painter_sketch_images_status' => 2,
        ]);
    }

    public function test_painter_can_send_admin_chat_message_for_assigned_order(): void
    {
        Mail::fake();
        [$painter, $orderId] = $this->createAssignedPainterOrder();

        $this->actingAs($painter)
            ->postJson('/new/update_order_chat', [
                'order_id' => $orderId,
                'msg' => 'Synthetic painter message',
            ])
            ->assertOk()
            ->assertJson([
                'status' => true,
                'comment' => 'Synthetic painter message',
            ]);

        $this->assertDatabaseHas('orders_chats', [
            'orders_id' => $orderId,
            'comment' => 'Synthetic painter message',
            'is_admin' => 0,
        ]);
    }

    public function test_unassigned_painter_cannot_mutate_another_painters_order(): void
    {
        Mail::fake();
        [, $orderId] = $this->createAssignedPainterOrder();
        $otherPainter = $this->createPainter('other-painter@example.test');

        $this->mock(UpdatePainterImageService::class)
            ->shouldNotReceive('store');
        $this->mock(UpdatePainterSketchImageService::class)
            ->shouldNotReceive('store');

        $client = $this->actingAs($otherPainter);
        $client->postJson('/new/update_painter_order_images', ['order_id' => $orderId])
            ->assertForbidden();
        $client->postJson('/new/update_painter_sketch_order_images', ['order_id' => $orderId])
            ->assertForbidden();
        $client->postJson('/new/update_order_chat', [
            'order_id' => $orderId,
            'msg' => 'Must not be stored',
        ])->assertForbidden();
        $client->postJson('/new/new_send_client_painter_comments', [
            'order_id' => $orderId,
            'client_comment' => 'Must not reach client chat',
        ])->assertForbidden();
        $client->postJson('/new/new_send_admin_to_client_painter_comments', [
            'order_id' => $orderId,
            'client_comment' => 'Must not reach image chat',
        ])->assertForbidden();

        $this->assertDatabaseMissing('orders_chats', [
            'orders_id' => $orderId,
            'comment' => 'Must not be stored',
        ]);
        Mail::assertNothingSent();
    }

    public function test_customer_can_only_send_comment_to_own_order(): void
    {
        Mail::fake();
        [, $orderId, $owner] = $this->createAssignedPainterOrder();
        $otherCustomer = $this->createAccountUser();

        $this->actingAs($otherCustomer)
            ->postJson('/new/new_send_client_painter_comments', [
                'order_id' => $orderId,
                'client_comment' => 'Must not be stored',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('order_user_comments', [
            'order_id' => $orderId,
            'comment' => 'Must not be stored',
        ]);
        Mail::assertNothingSent();
        $this->assertNotSame($owner->id, $otherCustomer->id);

        $this->actingAs($owner)
            ->postJson('/new/new_send_client_painter_comments', [
                'order_id' => $orderId,
                'client_comment' => 'Owner message',
            ])
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('order_id', $orderId);

        $this->assertDatabaseHas('order_user_comments', [
            'order_id' => $orderId,
            'user_id' => $owner->id,
            'comment' => 'Owner message',
        ]);
    }

    public function test_customer_cannot_call_painter_mutation_endpoint(): void
    {
        Mail::fake();
        [, $orderId] = $this->createAssignedPainterOrder();
        $customer = $this->createAccountUser();

        $this->mock(UpdatePainterImageService::class)
            ->shouldNotReceive('store');

        $this->actingAs($customer)
            ->postJson('/new/update_painter_order_images', ['order_id' => $orderId])
            ->assertForbidden();

        Mail::assertNothingSent();
    }

    public function test_invalid_painter_uploads_return_json_validation_errors_without_side_effects(): void
    {
        Mail::fake();
        [$painter, $orderId] = $this->createAssignedPainterOrder();

        $this->actingAs($painter)
            ->postJson('/new/update_painter_order_images', [
                'order_id' => $orderId,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['painter_images']);

        $this->actingAs($painter)
            ->postJson('/new/update_painter_order_images', [
                'order_id' => $orderId,
                'painter_images' => [
                    UploadedFile::fake()->create('not-an-image.exe', 10, 'application/x-msdownload'),
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['painter_images.0']);

        $this->actingAs($painter)
            ->postJson('/new/update_painter_sketch_order_images', [
                'order_id' => $orderId,
                'painter_sketch_images' => [
                    UploadedFile::fake()->create('not-a-sketch.exe', 10, 'application/x-msdownload'),
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['painter_sketch_images.0']);

        $this->assertDatabaseCount('order_painter_images', 0);
        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'painter_images_status' => null,
            'painter_sketch_images_status' => null,
        ]);
        Mail::assertNothingSent();
    }

    public function test_account_users_can_only_mark_messages_from_their_orders_as_read(): void
    {
        [$painter, $orderId, $owner] = $this->createAssignedPainterOrder();
        $otherCustomer = $this->createAccountUser();
        $foreignOrderId = $this->createOrderForCustomer($otherCustomer);

        $ownUserChatId = DB::table('order_user_comments')->insertGetId([
            'order_id' => $orderId,
            'user_id' => $painter->id,
            'comment' => 'Own order user chat',
            'is_read' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $foreignUserChatId = DB::table('order_user_comments')->insertGetId([
            'order_id' => $foreignOrderId,
            'user_id' => $otherCustomer->id,
            'comment' => 'Foreign order user chat',
            'is_read' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $ownPainterChatId = DB::table('orders_chats')->insertGetId([
            'orders_id' => $orderId,
            'comment' => 'Own painter chat',
            'is_read' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $foreignPainterChatId = DB::table('orders_chats')->insertGetId([
            'orders_id' => $foreignOrderId,
            'comment' => 'Foreign painter chat',
            'is_read' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($owner)
            ->postJson('/new/message_read', ['chatId' => $ownUserChatId])
            ->assertOk()
            ->assertJsonPath('status', 1);
        $this->actingAs($owner)
            ->postJson('/new/message_read', ['chatId' => $foreignUserChatId])
            ->assertForbidden();

        $this->actingAs($painter)
            ->postJson('/new/message_read', ['chatId' => $ownPainterChatId])
            ->assertOk()
            ->assertJsonPath('status', 1);
        $this->actingAs($painter)
            ->postJson('/new/message_read', ['chatId' => $foreignPainterChatId])
            ->assertForbidden();

        $this->assertDatabaseHas('order_user_comments', ['id' => $ownUserChatId, 'is_read' => 1]);
        $this->assertDatabaseHas('order_user_comments', ['id' => $foreignUserChatId, 'is_read' => 0]);
        $this->assertDatabaseHas('orders_chats', ['id' => $ownPainterChatId, 'is_read' => 1]);
        $this->assertDatabaseHas('orders_chats', ['id' => $foreignPainterChatId, 'is_read' => 0]);
    }

    public function test_customer_can_only_change_status_of_image_from_own_order(): void
    {
        [, $orderId, $owner] = $this->createAssignedPainterOrder();
        $otherCustomer = $this->createAccountUser();
        $imageId = DB::table('order_painter_images')->insertGetId([
            'order_id' => $orderId,
            'image' => 'orders/status-test.jpg',
            'status' => 1,
            'is_img_painter' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($otherCustomer)
            ->postJson('/new/changeOrderPainterImageStatus', [
                'order_id' => $orderId,
                'order_painter_image_id' => $imageId,
                'status_name' => 4,
            ])
            ->assertForbidden();

        $this->actingAs($owner)
            ->postJson('/new/changeOrderPainterImageStatus', [
                'order_id' => $orderId,
                'order_painter_image_id' => $imageId,
                'status_name' => 99,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status_name']);

        $this->actingAs($owner)
            ->postJson('/new/changeOrderPainterImageStatus', [
                'order_id' => $orderId,
                'order_painter_image_id' => $imageId,
                'status_name' => 4,
            ])
            ->assertOk()
            ->assertJsonPath('info', true);

        $this->assertDatabaseHas('order_painter_images', [
            'id' => $imageId,
            'order_id' => $orderId,
            'status' => 4,
        ]);

        DB::table('order_user_comments')->insert([
            'order_id' => $orderId,
            'user_id' => $owner->id,
            'comment' => 'Please revise',
            'order_painter_image_id' => $imageId,
            'is_img_painter' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->actingAs($owner)
            ->postJson('/new/changeOrderPainterImageStatus', [
                'order_id' => (string) $orderId,
                'order_painter_image_id' => (string) $imageId,
                'status_name' => '5',
                'check_comment' => 'true',
            ])
            ->assertOk()
            ->assertJsonPath('info', true)
            ->assertJsonPath('check_comment', true);

        $this->assertDatabaseHas('order_painter_images', [
            'id' => $imageId,
            'status' => 5,
        ]);
    }

    public function test_image_chat_rejects_image_id_from_another_order(): void
    {
        Mail::fake();
        [$painter, $orderId, $owner] = $this->createAssignedPainterOrder();
        $otherCustomer = $this->createAccountUser();
        $foreignOrderId = $this->createOrderForCustomer($otherCustomer);
        $foreignImageId = DB::table('order_painter_images')->insertGetId([
            'order_id' => $foreignOrderId,
            'image' => 'orders/foreign.jpg',
            'status' => 1,
            'is_img_painter' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = [
            'order_id' => $orderId,
            'order_painter_image_id' => $foreignImageId,
            'is_img_painter' => 1,
            'msg' => 'Must not be linked',
        ];

        $this->actingAs($owner)
            ->postJson('/new/new_send_client_painter_comments', $payload)
            ->assertForbidden();
        $this->actingAs($painter)
            ->postJson('/new/update_order_chat', $payload)
            ->assertForbidden();

        $this->assertDatabaseMissing('order_user_comments', [
            'order_id' => $orderId,
            'order_painter_image_id' => $foreignImageId,
        ]);
        $this->assertDatabaseMissing('orders_chats', [
            'orders_id' => $orderId,
            'order_painter_image_id' => $foreignImageId,
        ]);
        Mail::assertNothingSent();
    }

    public function test_public_account_roles_cannot_call_legacy_admin_read_endpoints(): void
    {
        [$painter, $orderId, $owner] = $this->createAssignedPainterOrder();
        $userChatId = DB::table('order_user_comments')->insertGetId([
            'order_id' => $orderId,
            'user_id' => $owner->id,
            'comment' => 'Admin unread',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $painterChatId = DB::table('orders_chats')->insertGetId([
            'orders_id' => $orderId,
            'comment' => 'Admin unread painter chat',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($owner)
            ->postJson('/new/admin_to_client_message_read', ['chatId' => $userChatId])
            ->assertForbidden();
        $this->actingAs($painter)
            ->postJson('/new/admin_message_read', ['chatId' => $painterChatId])
            ->assertForbidden();
    }

    public function test_painter_image_service_accepts_large_print_file_without_application_size_limit(): void
    {
        Storage::fake('uploads');
        [, $orderId] = $this->createAssignedPainterOrder();
        $file = UploadedFile::fake()->image('print-source.jpg')->size(102400);
        $request = new Request(
            ['order_id' => $orderId],
            [],
            [],
            [],
            ['painter_images' => [$file]]
        );

        $result = app(UpdatePainterImageService::class)->store($request);

        $this->assertIsString($result);
        $this->assertDatabaseHas('order_painter_images', [
            'order_id' => $orderId,
            'is_img_painter' => 1,
        ]);
        $this->assertCount(1, Storage::disk('uploads')->allFiles('orders'));
    }

    public function test_authenticated_customer_can_open_bonus_page_without_active_sales(): void
    {
        $user = $this->createAccountUser();

        $this->actingAs($user)
            ->withHeader('Accept-Language', 'ru')
            ->get('/new/mystocks')
            ->assertOk();
    }

    private function createUser(string $email, string $password): User
    {
        DB::table('users')->insert([
            'email' => $email,
            'password' => Hash::make($password),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::query()->where('email', $email)->firstOrFail();
    }

    private function createAccountUser(): User
    {
        $roleId = DB::table('roles')->insertGetId([
            'name' => 'user',
            'display_name' => 'Customer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'role_id' => $roleId,
            'email' => 'account@example.test',
            'password' => Hash::make('secret-pass'),
            'first_name' => 'Frontend',
            'last_name' => 'Customer',
            'inv_sale_code' => 'ACCOUNT1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::query()->where('email', 'account@example.test')->firstOrFail();
    }

    private function createPainter(string $email): User
    {
        $roleId = DB::table('roles')->insertGetId([
            'name' => 'painter',
            'display_name' => 'Painter',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'role_id' => $roleId,
            'email' => $email,
            'password' => Hash::make('secret-pass'),
            'inv_sale_code' => 'PAINTER2',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::query()->where('email', $email)->firstOrFail();
    }

    private function createAssignedPainterOrder(): array
    {
        $painter = $this->createPainter('assigned-painter@example.test');
        $customer = $this->createUser('assigned-customer@example.test', 'secret-pass');
        $customerRoleId = DB::table('roles')->insertGetId([
            'name' => 'user',
            'display_name' => 'Customer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('users')->where('id', $customer->id)->update([
            'role_id' => $customerRoleId,
        ]);
        $customer->refresh();
        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $customer->id,
            'status' => 'watching',
            'price' => 55,
            'items' => '[]',
            'delivery' => '{}',
            'payment_status' => 'not_payed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('painter_orders')->insert([
            'user_id' => $painter->id,
            'order_id' => $orderId,
            'complete_until' => now()->addDays(3),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$painter, $orderId, $customer];
    }

    private function createOrderForCustomer(User $customer): int
    {
        return DB::table('orders')->insertGetId([
            'user_id' => $customer->id,
            'status' => 'watching',
            'price' => 10,
            'items' => '[]',
            'delivery' => '{}',
            'payment_status' => 'not_payed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
