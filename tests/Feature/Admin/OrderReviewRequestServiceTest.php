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
            $table->string('locale')->nullable();
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
            'locale' => 'ru',
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
}
