<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscriptionCanvasNewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('canvas_new', function (Blueprint $table) {
            $table->text('description')->nullable();
            $table->text('seo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('canvas_new', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('seo');
        });
    }
}
