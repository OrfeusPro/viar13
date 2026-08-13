<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumsNewhomeTopSliderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('newhome_top_slider', function (Blueprint $table) {
            $table->integer('order')->default(0);
            $table->integer('is_show')->default(1);
            $table->integer('is_white')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('newhome_top_slider', function (Blueprint $table) {
            $table->dropColumn('order');
            $table->dropColumn('is_show');
            $table->dropColumn('is_white');
        });
    }
}
