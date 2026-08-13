<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumsOrdersUserCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_user_comments', function (Blueprint $table) {
            $table->integer('admin_is_read')->default(0);
        });
        
        DB::table('order_user_comments')->update(['admin_is_read' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_user_comments', function (Blueprint $table) {
            $table->dropColumn('admin_is_read');
        });
    }
}
