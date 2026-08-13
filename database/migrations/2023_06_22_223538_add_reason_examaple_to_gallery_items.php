<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReasonExamapleToGalleryItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->text('reason_example')->nullable();
        });

        $json = <<<SSS
{
    "tab_title": "Образы",
    "extra_fields": {
        "category": {
            "type": "dropdown",
            "title": "Категория",
            "options": [
                "s_reason_cat_family",
                "s_reason_cat_female",
                "s_reason_cat_male",
                "s_reason_cat_pet",
                "s_reason_cat_pair"
            ]
        }
    }
}
SSS;
        //{"tab_title":"\u0420\u0430\u0431\u043e\u0442\u044b \u043a\u043b\u0438\u0435\u043d\u0442\u043e\u0432","extra_fields":{"category":{"type":"dropdown","title":"\u041a\u0430\u0442\u0435\u0433\u043e\u0440\u0438\u044f","options":["male_portrait","femaile_portrait","family_portrait"]}}}
        $bread_config = [
            'data_type_id'=>16,
            'field'=>'reason_example',
            'type'=>'adv_media_files',
            'display_name'=>'Галерея образов',
            'details'=>$json,
            'order'=>62
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
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->dropColumn('reason_example');
        });
        DB::table('data_rows')->where('field','reason_example')
            ->where('data_type_id',16)->delete();
    }
}
