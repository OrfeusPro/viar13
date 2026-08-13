<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUseridToAdminComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_admin_comments', function (Blueprint $table) {
            $table->integer('user_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('order_admin_comments', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
}
