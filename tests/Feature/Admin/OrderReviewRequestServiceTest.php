<?php

namespace Tests\Feature\Admin;

use App\Models\Orders;
use App\Services\Admin\OrderReviewRequestService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderReviewRequestServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('email');
            $table->json('settings')->nullable();
            $table->string('country')->nullable();
            $table->string('last_ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    public function test_review_request_is_suppressed_by_default(): void
    {
        Mail::fake();
        config()->set('admin_migration.review_request_enabled', false);

        $result = app(OrderReviewRequestService::class)->send($this->createOrder());

        $this->assertTrue($result['suppressed']);
        Mail::assertNothingSent();
    }

    public function test_order_without_valid_user_is_rejected(): void
    {
        $order = (new Orders)->forceFill(['id' => 9, 'user_id' => null]);

        $this->expectException(ValidationException::class);

        app(OrderReviewRequestService::class)->send($order);
    }

    private function createOrder(): Orders
    {
        $userId = DB::table('users')->insertGetId([
            'email' => 'review-request@example.invalid',
            'settings' => json_encode(['locale' => 'ru']),
            'country' => 'LV',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Orders::query()->findOrFail($orderId);
    }

    public function test_review_can_be_sent_to_user_without_orders_using_fake_mail(): void
    {
        Mail::fake();
        config(['admin_migration.review_request_enabled' => true]);
        Schema::create('user_messages', function (Blueprint $table): void { $table->id(); $table->string('rev_subject'); $table->timestamps(); });
        Schema::create('translations', function (Blueprint $table): void {
            $table->id(); $table->string('table_name'); $table->string('column_name');
            $table->integer('foreign_key'); $table->string('locale'); $table->text('value');
        });
        DB::table('user_messages')->insert(['rev_subject' => 'Review']);
        $userId = DB::table('users')->insertGetId(['email' => 'no-orders@example.invalid', 'settings' => json_encode(['locale' => 'en']), 'country' => 'LV']);
        $user = \App\Models\User::findOrFail($userId);
        $result = app(OrderReviewRequestService::class)->sendToUser($user);
        $this->assertTrue($result['sent']);
        $this->assertFalse($result['suppressed']);
        Mail::assertSent(\App\Mail\SendUserReview::class, fn ($mail): bool => $mail->hasTo($user->email));
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_user_without_orders_is_suppressed_and_invalid_email_is_rejected(): void
    {
        Mail::fake();
        config(['admin_migration.review_request_enabled' => false]);
        $user = new \App\Models\User(['email' => 'no-orders@example.invalid']);
        $this->assertTrue(app(OrderReviewRequestService::class)->sendToUser($user)['suppressed']);
        Mail::assertNothingSent();
        $user->email = 'invalid';
        $this->expectException(ValidationException::class);
        app(OrderReviewRequestService::class)->sendToUser($user);
    }
}
