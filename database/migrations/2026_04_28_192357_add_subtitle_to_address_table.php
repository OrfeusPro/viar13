<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSubtitleToAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('address') && !Schema::hasColumn('address', 'subtitle')) {
            Schema::table('address', function (Blueprint $table) {
                $table->text('subtitle')->nullable()->after('working_hours');
            });
        }

        if (!Schema::hasTable('data_types') || !Schema::hasTable('data_rows')) {
            return;
        }

        $dataType = DB::table('data_types')
            ->where('name', 'address')
            ->orWhere('slug', 'address')
            ->first();

        if (!$dataType) {
            return;
        }

        $exists = DB::table('data_rows')
            ->where('data_type_id', $dataType->id)
            ->where('field', 'subtitle')
            ->exists();

        if ($exists) {
            return;
        }

        $order = ((int) DB::table('data_rows')
            ->where('data_type_id', $dataType->id)
            ->max('order')) + 1;

        DB::table('data_rows')->insert([
            'data_type_id' => $dataType->id,
            'field' => 'subtitle',
            'type' => 'rich_text_box',
            'display_name' => 'Подтекст',
            'required' => 0,
            'browse' => 0,
            'read' => 1,
            'edit' => 1,
            'add' => 1,
            'delete' => 1,
            'details' => json_encode([
                'display' => [
                    'width' => '12',
                ],
            ]),
            'order' => $order,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('data_types') && Schema::hasTable('data_rows')) {
            $dataType = DB::table('data_types')
                ->where('name', 'address')
                ->orWhere('slug', 'address')
                ->first();

            if ($dataType) {
                DB::table('data_rows')
                    ->where('data_type_id', $dataType->id)
                    ->where('field', 'subtitle')
                    ->delete();
            }
        }

        if (Schema::hasTable('address') && Schema::hasColumn('address', 'subtitle')) {
            Schema::table('address', function (Blueprint $table) {
                $table->dropColumn('subtitle');
            });
        }
    }
}
