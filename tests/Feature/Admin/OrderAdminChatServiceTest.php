<?php

namespace Tests\Feature\Admin;

use App\Models\Orders;
use App\Models\User;
use App\Services\Admin\OrderAdminChatService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderAdminChatServiceTest extends TestCase
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
            $table->timestamps();
        });
        Schema::create('order_admin_comments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('orders_id');
            $table->unsignedBigInteger('user_id');
            $table->text('comment');
            $table->timestamps();
        });
    }

    public function test_admin_message_is_saved_with_order_and_author(): void
    {
        $order = Orders::query()->create();
        $author = $this->createUser();

        $message = app(OrderAdminChatService::class)->add($order, $author, '  Внутренняя заметка  ');

        $this->assertSame($order->id, $message->orders_id);
        $this->assertSame($author->id, $message->user_id);
        $this->assertSame('Внутренняя заметка', $message->comment);
        $this->assertDatabaseHas('order_admin_comments', ['id' => $message->id]);
    }

    public function test_empty_message_is_rejected(): void
    {
        $this->expectException(ValidationException::class);

        app(OrderAdminChatService::class)->add(Orders::query()->create(), $this->createUser(), '');
    }

    public function test_popup_preserves_author_email_date_and_escaped_text(): void
    {
        $order = Orders::query()->create();
        $author = $this->createUser();
        $message = app(OrderAdminChatService::class)->add($order, $author, '<script>unsafe</script>');
        $author->forceFill(['first_name' => 'Test', 'last_name' => 'Manager']);
        $message->setRelation('user', $author);
        $order->setRelation('adminChats', new Collection([$message]));

        $html = view('filament.tables.modals.order-chat-history', ['record' => $order, 'stream' => 'admin'])->render();
        $this->assertStringContainsString('Test Manager (admin-chat@example.invalid)', $html);
        $this->assertStringContainsString($message->created_at->format('Y/m/d'), $html);
        $this->assertStringContainsString('&lt;script&gt;unsafe&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>unsafe</script>', $html);

        $message->setRelation('user', null);
        $html = view('filament.tables.modals.order-chat-history', ['record' => $order, 'stream' => 'admin'])->render();
        $this->assertStringContainsString('Администратор', $html);
        $this->assertStringNotContainsString('admin-chat@example.invalid', $html);
    }

    private function createUser(): User
    {
        $id = DB::table('users')->insertGetId([
            'email' => 'admin-chat@example.invalid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::query()->findOrFail($id);
    }
}
