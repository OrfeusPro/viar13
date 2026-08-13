<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbandonedCartsTable extends Migration
{

    public function up()
    {
        Schema::create('abandoned_carts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email');
            $table->json('cart_data');
            $table->string('bonus')->nullable();
            $table->string('recovery_token')->nullable()->unique();
            $table->timestamp('token_expires_at')->nullable();
            $table->boolean('is_send_email_twelve_hours')->default(false);
            $table->string('locale')->default('ru');
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('abandoned_carts');
    }
}
