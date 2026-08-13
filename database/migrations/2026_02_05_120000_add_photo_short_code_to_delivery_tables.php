<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhotoShortCodeToDeliveryTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('delivery_pickup_at_viar_workshop') && !Schema::hasColumn('delivery_pickup_at_viar_workshop', 'photo_short_code')) {
            Schema::table('delivery_pickup_at_viar_workshop', function (Blueprint $table) {
                $table->string('photo_short_code', 32)->nullable()->after('country_code');
            });
        }

        if (Schema::hasTable('a_delivery_towns') && !Schema::hasColumn('a_delivery_towns', 'photo_short_code')) {
            Schema::table('a_delivery_towns', function (Blueprint $table) {
                $table->string('photo_short_code', 32)->nullable()->after('country');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('delivery_pickup_at_viar_workshop') && Schema::hasColumn('delivery_pickup_at_viar_workshop', 'photo_short_code')) {
            Schema::table('delivery_pickup_at_viar_workshop', function (Blueprint $table) {
                $table->dropColumn('photo_short_code');
            });
        }

        if (Schema::hasTable('a_delivery_towns') && Schema::hasColumn('a_delivery_towns', 'photo_short_code')) {
            Schema::table('a_delivery_towns', function (Blueprint $table) {
                $table->dropColumn('photo_short_code');
            });
        }
    }
}

