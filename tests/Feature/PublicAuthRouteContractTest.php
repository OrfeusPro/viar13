<?php

namespace Tests\Feature;

use App\Mail\SendUserRegister;
use App\Models\User;
use App\Notifications\BrandedResetPassword;
use App\Repositories\BasketRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
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
            $table->timestamps();
        });
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
}
