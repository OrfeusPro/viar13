<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAllowClientPhotoUploadToGalleryItemsTable extends Migration
{
    public function up()
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            if (!Schema::hasColumn('gallery_items', 'allow_client_photo_upload')) {
                $table->boolean('allow_client_photo_upload')
                    ->default(false)
                    ->after('active');
            }
        });
    }

    public function down()
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            if (Schema::hasColumn('gallery_items', 'allow_client_photo_upload')) {
                $table->dropColumn('allow_client_photo_upload');
            }
        });
    }
}
