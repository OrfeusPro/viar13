<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSvgToSiteImagesTable extends Migration
{
    public function up()
    {
        Schema::table('site_images', function (Blueprint $table) {
            $table->string('svg')->nullable()->after('img');
        });
    }

    public function down()
    {
        Schema::table('site_images', function (Blueprint $table) {
            $table->dropColumn('svg');
        });
    }
}
