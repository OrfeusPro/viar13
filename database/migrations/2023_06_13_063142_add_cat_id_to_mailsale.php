<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCatIdToMailsale extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('a_mail_top_sale', function (Blueprint $table) {
            $table->integer('cat_id')->default(1);
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
            $table->dropColumn('cat_id');
        });
    }
}
