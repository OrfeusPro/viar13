<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSortColumnsToSliderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('portrait_slider', function (Blueprint $table) {
            $table->integer('sort')->default(0);
        });

        Schema::table('canvas_slider', function (Blueprint $table) {
            $table->integer('sort')->default(0);
        });

        Schema::table('a_collage_slider', function (Blueprint $table) {
            $table->integer('sort')->default(0);
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
            $table->dropColumn('sort');
        });

        Schema::table('a_collage_slider', function (Blueprint $table) {
            $table->dropColumn('sort');
        });

        Schema::table('portrait_slider', function (Blueprint $table) {
            $table->dropColumn('sort');
        });
    }
}
