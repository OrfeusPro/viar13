<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdminOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_admin_comments', function (Blueprint $table) {
            $table->bigIncrements('id');  // Auto-incremental primary key
            $table->unsignedBigInteger('orders_id');  // Reference to the orders table
            $table->text('comment');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_admin_comments');
    }
}
