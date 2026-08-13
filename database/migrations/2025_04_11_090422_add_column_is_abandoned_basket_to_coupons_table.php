<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnIsAbandonedBasketToCouponsTable extends Migration
{

    public function up()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->boolean('is_abandoned_basket')->default(false);
        });
    }

    public function down()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('is_abandoned_basket');
        });
    }
}
