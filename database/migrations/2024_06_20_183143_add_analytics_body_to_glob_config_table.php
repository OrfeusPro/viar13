<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAnalyticsBodyToGlobConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('glob_config', function (Blueprint $table) {
            $table->text('analytics_body')->nullable(); // Add the new column
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('glob_config', function (Blueprint $table) {
            $table->dropColumn('analytics_body'); // Remove the column if rolling back
        });
    }
}
