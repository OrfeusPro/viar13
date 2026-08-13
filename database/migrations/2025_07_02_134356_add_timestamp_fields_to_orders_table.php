<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimestampFieldsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('send_lubanas_date')->nullable();
            $table->timestamp('watching_date')->nullable();
            $table->timestamp('pegging_date')->nullable();
            $table->timestamp('in_production_date')->nullable();
            $table->timestamp('completed_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('send_lubanas_date');
            $table->dropColumn('watching_date');
            $table->dropColumn('pegging_date');
            $table->dropColumn('in_production_date');
            $table->dropColumn('completed_date');
        });
    }
}
