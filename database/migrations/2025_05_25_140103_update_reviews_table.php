<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateReviewsTable extends Migration
{
    public function up()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['photo_order', 'photo', 'audio']);

            $table->text('img')->nullable()->after('id');
            $table->text('avatar')->nullable()->after('img');
            
            $table->string('name')->nullable()->change();
            $table->text('text')->nullable()->change();
            $table->string('email')->nullable()->change();

            $table->integer('active')->nullable()->default(1)->after('text');
            $table->text('a_player')->nullable()->after('updated_at');
            $table->string('orig_locale')->nullable()->after('a_player');
            $table->integer('user_id')->default(0);
            $table->integer('order_id')->default(0);
            $table->integer('pid')->default(0);
            $table->integer('city')->nullable();
        });
    }

    public function down()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn([
                'img',
                'avatar',
                'active',
                'a_player',
                'orig_locale',
                'city',
            ]);

            $table->string('photo_order')->nullable()->after('text');
            $table->string('photo')->nullable()->after('photo_order');
            $table->string('audio')->nullable()->after('photo');

            $table->string('name')->nullable(false)->change();
            $table->longText('text')->change();
            $table->string('email')->nullable(false)->change();
        });
    }
}
