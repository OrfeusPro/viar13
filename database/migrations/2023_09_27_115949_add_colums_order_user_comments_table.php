<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumsOrderUserCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_user_comments', function (Blueprint $table) {
            $table->integer('order_painter_image_id')->default(0);
            $table->integer('order_user_image_id')->default(0);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_user_comments', function (Blueprint $table) {
            $table->dropColumn('order_painter_image_id');
            $table->dropColumn('order_user_image_id');
        });
    }
}
