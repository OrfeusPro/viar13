<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumsOrdersChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders_chats', function (Blueprint $table) {
            $table->integer('is_img_sketch')->default(0);
            $table->integer('is_img_painter')->default(0);
            $table->integer('is_read')->default(0);
        });

        DB::table('orders_chats')->update(['is_read' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders_chats', function (Blueprint $table) {
            $table->dropColumn('is_img_sketch');
            $table->dropColumn('is_img_painter');
            $table->dropColumn('is_read');
        });
    }
}
