<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsCouponSentToAbandonedCarts extends Migration
{
    public function up()
    {
        Schema::table('abandoned_carts', function (Blueprint $table) {
            $table->boolean('is_coupon_sent')->default(false)->after('is_send_email_twelve_hours');
        });
    }

    public function down()
    {
        Schema::table('abandoned_carts', function (Blueprint $table) {
            $table->dropColumn('is_coupon_sent');
        });
    }
}
