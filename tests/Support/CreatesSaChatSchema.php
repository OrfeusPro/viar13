<?php

namespace Tests\Support;

use App\Services\SynvolveWebhookService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

trait CreatesSaChatSchema
{
    protected function createSaChatSchema(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        config(['services.sa_integration.api_key' => 'test-key', 'cache.default' => 'array', 'cache.limiter' => 'array']);
        Mail::fake();
        Http::preventStrayRequests();
        $this->mock(SynvolveWebhookService::class, function ($mock): void {
            $mock->shouldReceive('notifyManagerMessageForOrderOrPhone')->andReturn(true);
        });
        require_once database_path('migrations/2026_02_24_200000_create_sa_integration_tables.php');
        require_once database_path('migrations/2026_04_02_220000_add_unread_for_manager_to_sa_conversations.php');
        (new \CreateSaIntegrationTables)->up();
        (new \AddUnreadForManagerToSaConversations)->up();
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->integer('role_id')->nullable();
            foreach (['email', 'password', 'first_name', 'last_name', 'phone', 'avatar', 'news', 'ad', 'client_data', 'country', 'settings', 'last_ip', 'registration_page', 'referrer_url', 'utm_parameters', 'user_agent'] as $field) {
                $table->text($field)->nullable();
            }
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            foreach (['status', 'payment_status', 'items', 'price', 'country', 'delivery', 'payment', 'comment', 'admin_comment', 'sa_client_phone', 'sa_conversation_id', 'sa_bot_mode'] as $field) {
                $table->text($field)->nullable();
            }
            $table->timestamps();
        });
        Schema::create('order_user_comments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('comment');
            $table->string('sa_message_id')->nullable();
            $table->string('sa_direction')->nullable();
            foreach (['is_admin', 'is_read', 'admin_is_read', 'is_img_sketch', 'is_img_painter', 'order_painter_image_id', 'order_user_image_id'] as $field) {
                $table->integer($field)->default(0);
            }
            $table->timestamps();
        });
    }
}
