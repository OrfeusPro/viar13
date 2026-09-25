<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('media') && ! Schema::hasColumn('media', 'generated_conversions')) {
            Schema::table('media', function (Blueprint $table): void {
                $table->json('generated_conversions')->nullable()->after('custom_properties');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('media') && Schema::hasColumn('media', 'generated_conversions')) {
            Schema::table('media', function (Blueprint $table): void {
                $table->dropColumn('generated_conversions');
            });
        }
    }
};
