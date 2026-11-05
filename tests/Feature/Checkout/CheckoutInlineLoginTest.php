<?php

namespace Tests\Feature\Checkout;

use RuntimeException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CheckoutInlineLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('role_id')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('address')->nullable();
            $table->string('postal_index')->nullable();
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->string('client_data')->nullable();
            $table->string('news')->nullable();
            $table->string('avatar')->nullable();
            $table->string('active_coupon')->nullable();
            $table->text('settings')->nullable();
            $table->string('last_ip')->nullable();
            $table->string('registration_page')->nullable();
            $table->string('referrer_url')->nullable();
            $table->json('utm_parameters')->nullable();
            $table->text('user_agent')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function test_existing_email_requires_inline_authorization_instead_of_creating_another_user()
    {
        \DB::table('users')->insert([
            'email' => 'customer@example.test',
            'password' => Hash::make('secret-password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post(route('setuser'), [
            'name' => 'Zita',
            'surname' => 'Celherta',
            'email' => 'customer@example.test',
            'phone' => '+37129816036',
            'phone_rec' => '',
            'country_code' => 'LV',
            'ur_name' => 'false',
        ]);

        $response->assertOk();
        $this->assertSame([
            'success' => 0,
            'reason' => 'existing_user',
        ], json_decode($response->getContent(), true));
        $this->assertGuest();
        $this->assertSame(1, \DB::table('users')->count());
    }

    public function test_checkout_step_two_contains_inline_password_and_google_login_controls()
    {
        $template = file_get_contents(resource_path('views/theme/viar/cart/step2_data.blade.php'));

        $this->assertStringContainsString('js-cart-inline-login-form', $template);
        $this->assertStringContainsString('autocomplete="current-password"', $template);
        $this->assertStringContainsString("route('google_redirect', ['return' => url()->full()])", $template);
        $this->assertStringContainsString('cart-inline-login__google-icon', $template);
        $this->assertStringContainsString('fill="#4285F4"', $template);
        $this->assertStringContainsString('js-cart-inline-reset-password', $template);
        $this->assertStringContainsString("route('forget_email_reset')", $template);
        $this->assertStringContainsString('window.location.reload()', $template);
        $this->assertStringContainsString('.cart-inline-login__error:empty { display: none; }', $template);
        $this->assertStringContainsString('gap: 18px 12px', $template);

        $checkoutScript = file_get_contents(public_path('theme/viar/js/custom.js'));
        $this->assertStringContainsString('.slideDown(200, function()', $checkoutScript);
        $this->assertStringContainsString('window.matchMedia("(max-width: 767px)").matches', $checkoutScript);
        $this->assertStringContainsString('if (!isMobile)', $checkoutScript);
    }

    public function test_new_checkout_user_is_authenticated_when_registration_email_transport_fails()
    {
        Mail::shouldReceive('to')
            ->once()
            ->with('checkout@example.test')
            ->andThrow(new RuntimeException('Simulated SMTP failure'));
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'Transactional email could not be sent.'
                    && $context['purpose'] === 'checkout_step_user_registration'
                    && $context['exception'] instanceof RuntimeException;
            });

        $response = $this->post(route('setuser'), [
            'name' => 'Checkout',
            'surname' => 'Customer',
            'email' => 'checkout@example.test',
            'phone' => '+37129816036',
            'phone_rec' => '',
            'country_code' => 'LV',
            'ur_name' => 'false',
        ]);

        $response->assertOk();
        $this->assertSame(['success' => 1], json_decode($response->getContent(), true));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'checkout@example.test',
            'phone' => '+37129816036',
        ]);
    }
}
