<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumsToOrderPainterImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_painter_images', function (Blueprint $table) {
            $table->string('small_image')->nullable();
            $table->integer('img_error')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_painter_images', function (Blueprint $table) {
            $table->dropColumn('small_image');
            $table->dropColumn('img_error');
        });
    }
}
