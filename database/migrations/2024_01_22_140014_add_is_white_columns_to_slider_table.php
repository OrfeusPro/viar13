<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsWhiteColumnsToSliderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('portrait_slider', function (Blueprint $table) {
            $table->integer('is_white')->default(0);
        });

        Schema::table('canvas_slider', function (Blueprint $table) {
            $table->integer('is_white')->default(0);
        });

        Schema::table('a_collage_slider', function (Blueprint $table) {
            $table->integer('is_white')->default(0);
            $table->integer('is_show')->default(1);
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('portrait_slider', function (Blueprint $table) {
            $table->dropColumn('is_white');
        });

        Schema::table('a_collage_slider', function (Blueprint $table) {
            $table->dropColumn('is_white');
            $table->dropColumn('is_show');
        });

        Schema::table('portrait_slider', function (Blueprint $table) {
            $table->dropColumn('is_white');
        });


    }
}
