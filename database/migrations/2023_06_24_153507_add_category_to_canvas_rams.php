<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryToCanvasRams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('canvas_rams', function (Blueprint $table) {
            $table->text('type')->default('baguette');
        });


        $json   =  <<<SSS
{
    "default": "baguette",
    "options": {
        "default" : "Стандартная",
        "baguette": "Багетная",
        "paper": "Бумажная",
        "paper_premium": "Бумажная премиум"
    }
}
SSS;

        $bread_config = [
            'data_type_id'=>42,
            'field'=>'type',
            'type'=>'select_dropdown',
            'display_name'=>'Тип',
            'details'=>$json,
            'order'=>13
        ];
        DB::table('data_rows')->insert($bread_config);


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('canvas_rams', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        DB::table('data_rows')->where('field','type')
            ->where('data_type_id',42)->delete();
    }
}
