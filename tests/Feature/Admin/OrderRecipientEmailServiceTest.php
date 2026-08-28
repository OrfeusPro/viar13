<?php

namespace Tests\Feature\Admin;

use App\Models\Orders;
use App\Notifications\AdminMailNotification;
use App\Services\Admin\OrderRecipientEmailService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderRecipientEmailServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('email');
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

    public function test_email_is_suppressed_by_default_without_notification(): void
    {
        Notification::fake();
        config()->set('admin_migration.recipient_email_enabled', false);

        $result = app(OrderRecipientEmailService::class)->send($this->createOrder(), $this->payload());

        $this->assertTrue($result['suppressed']);
        Notification::assertNothingSent();
    }

    public function test_enabled_email_targets_only_the_order_user(): void
    {
        Notification::fake();
        config()->set('admin_migration.recipient_email_enabled', true);
        $order = $this->createOrder();

        $result = app(OrderRecipientEmailService::class)->send($order, $this->payload());

        $this->assertTrue($result['sent']);
        Notification::assertSentTo($order->user, AdminMailNotification::class, fn ($notification): bool => $notification->subject === 'Тестовая тема');
    }

    public function test_missing_user_or_invalid_message_is_rejected(): void
    {
        $order = (new Orders())->forceFill(['id' => 9, 'user_id' => null]);

        $this->expectException(ValidationException::class);

        app(OrderRecipientEmailService::class)->send($order, $this->payload());
    }

    private function createOrder(): Orders
    {
        $userId = DB::table('users')->insertGetId([
            'email' => 'recipient@example.invalid',
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

    private function payload(): array
    {
        return [
            'subject' => 'Тестовая тема',
            'greetings' => 'Здравствуйте',
            'line' => 'Тестовое сообщение',
            'salutation' => 'Спасибо',
        ];
    }
}
