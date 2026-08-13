<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBackgroundToGaleryItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->text('background')->nullable();
        });
    }

    public function down()
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->dropColumn('background');
        });
    }
}
