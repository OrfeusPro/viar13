<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSizeColumnsToSliderTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('portrait_slider', function (Blueprint $table) {
            $table->text('size_text')->nullable();
            $table->string('size_img')->nullable();
            $table->string('size_title')->nullable();
        });

        Schema::table('canvas_slider', function (Blueprint $table) {
            $table->text('size_text')->nullable();
            $table->string('size_img')->nullable();
            $table->string('size_title')->nullable();
        });


        Schema::table('a_collage_slider', function (Blueprint $table) {
            $table->text('size_text')->nullable();
            $table->string('size_img')->nullable();
            $table->string('size_title')->nullable();
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
            $table->dropColumn('size_text');
            $table->dropColumn('size_img');
            $table->dropColumn('size_title');
        });

        Schema::table('a_collage_slider', function (Blueprint $table) {
            $table->dropColumn('size_text');
            $table->dropColumn('size_img');
            $table->dropColumn('size_title');
        });

        Schema::table('portrait_slider', function (Blueprint $table) {
            $table->dropColumn('size_text');
            $table->dropColumn('size_img');
            $table->dropColumn('size_title');
        });


    }
}
