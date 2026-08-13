<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnIsGiftCardToCouponsTable extends Migration
{

    public function up()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->boolean('is_giftcard')->default(false);
            $table->boolean('is_offline')->default(false);
            $table->integer('order_id')->default(0);
            $table->string('pdf', 255)->nullable();
        });
    }

    public function down()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('is_giftcard');
            $table->dropColumn('is_offline');
            $table->dropColumn('order_id');
            $table->dropColumn('pdf');
        });
    }
}
