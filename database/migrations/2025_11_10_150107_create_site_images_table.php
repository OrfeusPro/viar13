<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteImagesTable extends Migration
{
    public function up()
    {
        Schema::create('site_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('page');
            $table->string('position_name');
            $table->string('img')->nullable();
            $table->boolean('is_show')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('site_images');
    }
}
