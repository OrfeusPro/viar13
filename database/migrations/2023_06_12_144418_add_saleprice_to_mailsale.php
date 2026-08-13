<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalepriceToMailsale extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('a_mail_top_sale', function (Blueprint $table) {
            $table->float('sale_price')->default(0);
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('a_mail_top_sale', function (Blueprint $table) {
            $table->dropColumn('sale_price');
        });
    }
}
