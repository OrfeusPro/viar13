<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnreadForManagerToSaConversations extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('sa_conversations')) {
            return;
        }

        Schema::table('sa_conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('sa_conversations', 'unread_for_manager')) {
                $table->boolean('unread_for_manager')->default(0)->after('bot_mode');
                $table->index('unread_for_manager');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('sa_conversations')) {
            return;
        }

        Schema::table('sa_conversations', function (Blueprint $table) {
            if (Schema::hasColumn('sa_conversations', 'unread_for_manager')) {
                $table->dropColumn('unread_for_manager');
            }
        });
    }
}
