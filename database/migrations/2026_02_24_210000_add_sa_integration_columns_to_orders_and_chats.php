<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSaIntegrationColumnsToOrdersAndChats extends Migration
{
    public function up()
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'sa_conversation_id')) {
                    $table->string('sa_conversation_id', 255)->nullable()->after('status');
                    $table->index('sa_conversation_id');
                }
                if (!Schema::hasColumn('orders', 'sa_client_phone')) {
                    $table->string('sa_client_phone', 50)->nullable()->after('sa_conversation_id');
                    $table->index('sa_client_phone');
                }
                if (!Schema::hasColumn('orders', 'sa_bot_mode')) {
                    $table->string('sa_bot_mode', 64)->nullable()->after('sa_client_phone');
                    $table->index('sa_bot_mode');
                }
            });
        }

        if (Schema::hasTable('orders_chats')) {
            Schema::table('orders_chats', function (Blueprint $table) {
                if (!Schema::hasColumn('orders_chats', 'sa_message_id')) {
                    $table->string('sa_message_id', 255)->nullable()->after('comment');
                    $table->index('sa_message_id');
                }
                if (!Schema::hasColumn('orders_chats', 'sa_direction')) {
                    $table->string('sa_direction', 32)->nullable()->after('sa_message_id');
                    $table->index('sa_direction');
                }
                if (!Schema::hasColumn('orders_chats', 'sa_payload_json')) {
                    $table->longText('sa_payload_json')->nullable()->after('sa_direction');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('orders_chats')) {
            Schema::table('orders_chats', function (Blueprint $table) {
                if (Schema::hasColumn('orders_chats', 'sa_payload_json')) {
                    $table->dropColumn('sa_payload_json');
                }
                if (Schema::hasColumn('orders_chats', 'sa_direction')) {
                    $table->dropColumn('sa_direction');
                }
                if (Schema::hasColumn('orders_chats', 'sa_message_id')) {
                    $table->dropColumn('sa_message_id');
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'sa_bot_mode')) {
                    $table->dropColumn('sa_bot_mode');
                }
                if (Schema::hasColumn('orders', 'sa_client_phone')) {
                    $table->dropColumn('sa_client_phone');
                }
                if (Schema::hasColumn('orders', 'sa_conversation_id')) {
                    $table->dropColumn('sa_conversation_id');
                }
            });
        }
    }
}

