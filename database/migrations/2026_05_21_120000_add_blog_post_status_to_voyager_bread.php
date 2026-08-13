<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddBlogPostStatusToVoyagerBread extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('data_types') || !Schema::hasTable('data_rows')) {
            return;
        }

        $dataType = DB::table('data_types')->where('name', 'blog_posts')->first();
        if (!$dataType) {
            return;
        }

        $details = json_encode([
            'default' => 'published',
            'options' => [
                'published' => 'published',
                'draft' => 'draft',
            ],
        ], JSON_UNESCAPED_UNICODE);

        $existing = DB::table('data_rows')
            ->where('data_type_id', $dataType->id)
            ->where('field', 'status')
            ->first();

        $payload = [
            'type' => 'select_dropdown',
            'display_name' => 'Статус',
            'required' => 1,
            'browse' => 1,
            'read' => 1,
            'edit' => 1,
            'add' => 1,
            'delete' => 1,
            'details' => $details,
            'order' => 11,
        ];

        if ($existing) {
            DB::table('data_rows')->where('id', $existing->id)->update($payload);
            return;
        }

        DB::table('data_rows')->insert(array_merge($payload, [
            'data_type_id' => $dataType->id,
            'field' => 'status',
        ]));
    }

    public function down()
    {
        if (!Schema::hasTable('data_types') || !Schema::hasTable('data_rows')) {
            return;
        }

        $dataType = DB::table('data_types')->where('name', 'blog_posts')->first();
        if (!$dataType) {
            return;
        }

        DB::table('data_rows')
            ->where('data_type_id', $dataType->id)
            ->where('field', 'status')
            ->delete();
    }
}
