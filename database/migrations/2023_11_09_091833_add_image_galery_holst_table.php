<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImageGaleryHolstTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::table('gallery_holsts', function (Blueprint $table) {
            $table->string('image')->nullable();
            $table->boolean('default')->default(false);
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */

    public function down()
    {
        Schema::table('gallery_holsts', function (Blueprint $table) {
            $table->dropColumn('image');
            $table->dropColumn('default');
        });
    }
}
