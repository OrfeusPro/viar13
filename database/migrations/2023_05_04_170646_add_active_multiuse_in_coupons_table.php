<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActiveMultiuseInCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
     Schema::table('coupons', function (Blueprint $table) {
    $table->integer('is_active')->default(1);
    $table->integer('is_multiuse')->default(0);
    $table->integer('user_id')->default(0);
     $table->date('sale_date')->nullable();
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::table('coupons', function (Blueprint $table) {
    $table->dropColumn('is_active');
    $table->dropColumn('is_multiuse');
    $table->dropColumn('user_id');
    $table->dropColumn('sale_date');
});
    }
}
