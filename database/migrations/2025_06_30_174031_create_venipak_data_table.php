<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVenipakDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('venipak_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user')->default('viarstudia');
            $table->string('pass')->default('viarstudia');
            $table->string('login_id')->default('08354');
            $table->string('import_url')->default('https://go.venipak.lt/import/send.php');
            $table->string('print_url')->default('https://go.venipak.lt/ws/print_label');
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
        Schema::dropIfExists('venipak_data');
    }
}
