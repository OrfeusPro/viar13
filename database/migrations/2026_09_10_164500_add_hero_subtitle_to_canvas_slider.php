<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('canvas_slider') && ! Schema::hasColumn('canvas_slider', 'hero_subtitle')) {
            Schema::table('canvas_slider', function (Blueprint $table): void {
                $table->text('hero_subtitle')->nullable()->after('sub_title');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('canvas_slider') && Schema::hasColumn('canvas_slider', 'hero_subtitle')) {
            Schema::table('canvas_slider', function (Blueprint $table): void {
                $table->dropColumn('hero_subtitle');
            });
        }
    }
};
