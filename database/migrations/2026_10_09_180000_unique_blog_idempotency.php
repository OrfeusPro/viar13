<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasIndex('blog_posts', 'blog_posts_idempotency_unique')) {
            return;
        }

        if (DB::table('blog_posts')->whereNotNull('idempotency_key')
            ->groupBy('idempotency_key')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Duplicate blog idempotency keys exist; reconcile before retrying. No posts were changed.');
        }

        Schema::table('blog_posts', function (Blueprint $table): void {
            $table->unique('idempotency_key', 'blog_posts_idempotency_unique');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table): void {
            $table->dropUnique('blog_posts_idempotency_unique');
        });
    }
};
