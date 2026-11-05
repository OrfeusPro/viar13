<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFrontendImagesTable extends Migration
{
    public function up(): void
    {
        Schema::create('frontend_images', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('placement', 191)->index();
            $table->string('image_path', 1024);
            $table->string('page_url', 1024)->nullable();
            $table->json('context')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('frontend_images');
    }
}
