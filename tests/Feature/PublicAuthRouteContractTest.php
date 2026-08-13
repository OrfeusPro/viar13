<?php

namespace Tests\Feature;

use App\Mail\SendUserRegister;
use App\Models\User;
use App\Notifications\BrandedResetPassword;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->unique();
            $table->string('password');
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
}
