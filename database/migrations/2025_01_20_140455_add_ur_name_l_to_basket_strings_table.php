<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUrNameLToBasketStringsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('basket_strings', function (Blueprint $table) {
            $table->string('ur_name_l', 255)
                  ->nullable()
                  ->charset('utf8mb4')
                  ->collation('utf8mb4_general_ci')
                  ->after('ur_lico');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('ur_name_l', 255)
                  ->nullable()
                  ->charset('utf8mb4')
                  ->collation('utf8mb4_general_ci')
                  ->after('ur_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('basket_strings', function (Blueprint $table) {
            $table->dropColumn('ur_name_l');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('ur_name_l');
        });
    }
}
