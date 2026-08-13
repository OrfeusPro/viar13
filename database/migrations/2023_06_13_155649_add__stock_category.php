<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStockCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('a_order_from')->insert([
            'id' => 10,
            'title' => 'Stock',
            'sortorder' => 10,
            'created_at' => '2023-06-13 00:00:00',
           'updated_at' =>  '2023-06-13 00:00:00'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('a_order_from')->where('id', 10)->delete();
    }
}
