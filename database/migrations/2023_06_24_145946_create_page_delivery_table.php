<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePageDeliveryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('page_delivery', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('meta_title')->nullable();
            $table->string('meta_desc')->nullable();
            $table->string('title')->nullable();
            $table->text('first_block_text')->nullable();
            $table->text('first_block_box1')->nullable();
            $table->text('first_block_box2')->nullable();
            $table->text('first_block_box3')->nullable();
            $table->text('first_block_box4')->nullable();
            $table->text('second_block_text')->nullable();
            $table->text('second_block_text2')->nullable();
            $table->text('second_block_box1')->nullable();
            $table->text('second_block_box2')->nullable();
            $table->text('second_block_box3')->nullable();
            $table->text('third_block_text_top')->nullable();
            $table->text('third_block_box1')->nullable();
            $table->text('third_block_box2')->nullable();
            $table->text('third_block_box3')->nullable();
            $table->text('third_block_text')->nullable();
            $table->text('third_block_text2')->nullable();
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
        Schema::dropIfExists('page_delivery');
    }
}
