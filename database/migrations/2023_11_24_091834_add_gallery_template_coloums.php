<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGalleryTemplateColoums extends Migration
{
/**
* Run the migrations.
*
* @return void
*/
public function up()
{
Schema::table('gallery_items', function (Blueprint $table) {
$table->text('template_sizes1')->nullable();
$table->text('template_sizes2')->nullable();
$table->text('template_sizes3')->nullable();
});
}

/**
* Reverse the migrations.
*
* @return void
*/
public function down()
{
Schema::table('gallery_items', function (Blueprint $table) {
$table->dropColumn('template_sizes1');
$table->dropColumn('template_sizes2');
$table->dropColumn('template_sizes3');
});
}
}
