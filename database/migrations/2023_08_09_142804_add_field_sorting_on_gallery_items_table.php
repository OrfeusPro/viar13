<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddFieldSortingOnGalleryItemsTable extends Migration
{
    public function up()
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->integer('sorting')->nullable()->default(null);
        });

        DB::table('gallery_items')->where(['id' => 13])->update(['sorting' => 1]);
        DB::table('gallery_items')->where(['id' => 15])->update(['sorting' => 2]);
        DB::table('gallery_items')->where(['id' => 16])->update(['sorting' => 3]);
        DB::table('gallery_items')->where(['id' => 17])->update(['sorting' => 4]);
        DB::table('gallery_items')->where(['id' => 18])->update(['sorting' => 5]);
        DB::table('gallery_items')->where(['id' => 1053])->update(['sorting' => 6]);
        DB::table('gallery_items')->where(['id' => 1054])->update(['sorting' => 7]);
        DB::table('gallery_items')->where(['id' => 1055])->update(['sorting' => 8]);
        DB::table('gallery_items')->where(['id' => 1056])->update(['sorting' => 9]);
    }

    public function down()
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->dropColumn('sorting');
        });
    }
}
