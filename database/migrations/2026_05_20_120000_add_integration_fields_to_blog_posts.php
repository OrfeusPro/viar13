<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddIntegrationFieldsToBlogPosts extends Migration
{
    public function up()
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('blog_posts', 'status')) {
                $table->string('status', 32)->default('published')->after('right_banner');
            }

            if (!Schema::hasColumn('blog_posts', 'excerpt')) {
                $table->text('excerpt')->nullable()->after('text');
            }

            if (!Schema::hasColumn('blog_posts', 'tags')) {
                $table->text('tags')->nullable()->after('excerpt');
            }

            if (!Schema::hasColumn('blog_posts', 'idempotency_key')) {
                $table->string('idempotency_key')->nullable()->after('tags');
            }

            if (!Schema::hasColumn('blog_posts', 'external_id')) {
                $table->string('external_id')->nullable()->after('idempotency_key');
            }

            if (!Schema::hasColumn('blog_posts', 'api_payload')) {
                $table->longText('api_payload')->nullable()->after('external_id');
            }
        });

        $this->upsertVoyagerStatusRow();
    }

    public function down()
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            foreach (['api_payload', 'external_id', 'idempotency_key', 'tags', 'excerpt', 'status'] as $column) {
                if (Schema::hasColumn('blog_posts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function upsertVoyagerStatusRow(): void
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
}
