<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCountryCodeToDeliveryPickupAtViarWorkshopTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_pickup_at_viar_workshop', function (Blueprint $table) {
            $table->string('country_code', 4)->default('ALL')->after('title');
        });

        DB::table('delivery_pickup_at_viar_workshop')->whereIn('id', [1, 2])->update(['country_code' => 'LV']);
        DB::table('delivery_pickup_at_viar_workshop')->whereIn('id', [3, 9, 10])->update(['country_code' => 'LT']);
        DB::table('delivery_pickup_at_viar_workshop')->whereIn('id', [5, 7, 8])->update(['country_code' => 'EE']);
        DB::table('delivery_pickup_at_viar_workshop')->where('id', 6)->update(['country_code' => 'ALL']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_pickup_at_viar_workshop', function (Blueprint $table) {
            $table->dropColumn('country_code');
        });
    }
}
