<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaIntegrationTables extends Migration
{
    public function up()
    {
        Schema::create('sa_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('dedupe_key', 128)->unique();
            $table->string('event_id', 64)->nullable();
            $table->string('idempotency_key', 255)->nullable();
            $table->string('event_type', 128)->nullable();
            $table->string('source', 32)->nullable();
            $table->longText('payload')->nullable();
            $table->string('status', 32)->default('received');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique('event_id');
            $table->index('idempotency_key');
            $table->index('event_type');
            $table->index('processed_at');
        });

        Schema::create('sa_conversations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('conversation_id', 255)->unique();
            $table->unsignedBigInteger('orders_id')->nullable();
            $table->string('client_phone', 50)->nullable();
            $table->string('client_name', 255)->nullable();
            $table->string('channel', 50)->nullable();
            $table->string('bot_mode', 50)->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index('orders_id');
            $table->index('channel');
            $table->index('last_message_at');
        });

        Schema::create('sa_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('message_id', 255)->unique();
            $table->string('event_id', 64)->nullable();
            $table->unsignedBigInteger('orders_id')->nullable();
            $table->string('conversation_id', 255)->nullable();
            $table->string('direction', 32)->nullable();
            $table->string('status', 32)->nullable();
            $table->longText('from_json')->nullable();
            $table->longText('to_json')->nullable();
            $table->longText('text')->nullable();
            $table->longText('attachments_json')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->longText('provider_meta_json')->nullable();
            $table->timestamps();

            $table->index('orders_id');
            $table->index('conversation_id');
            $table->index('status');
            $table->index('sent_at');
        });

        Schema::create('sa_escalations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('orders_id')->nullable();
            $table->string('conversation_id', 255)->nullable();
            $table->string('priority', 32)->nullable();
            $table->string('reason_code', 255);
            $table->text('reason_text')->nullable();
            $table->decimal('confidence', 5, 4)->nullable();
            $table->longText('suggested_next_json')->nullable();
            $table->longText('dialog_json')->nullable();
            $table->string('task_ref', 64)->nullable();
            $table->timestamps();

            $table->index('orders_id');
            $table->index('conversation_id');
            $table->index('priority');
            $table->index('task_ref');
        });

        Schema::create('sa_bot_controls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('orders_id')->nullable();
            $table->string('conversation_id', 255)->nullable();
            $table->string('action', 64);
            $table->string('mode_after', 64)->nullable();
            $table->longText('changed_by_json')->nullable();
            $table->longText('payload')->nullable();
            $table->timestamps();

            $table->index('orders_id');
            $table->index('conversation_id');
            $table->index('action');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sa_bot_controls');
        Schema::dropIfExists('sa_escalations');
        Schema::dropIfExists('sa_messages');
        Schema::dropIfExists('sa_conversations');
        Schema::dropIfExists('sa_events');
    }
}

