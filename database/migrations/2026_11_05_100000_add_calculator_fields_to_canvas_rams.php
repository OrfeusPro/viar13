<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('canvas_rams')) {
            throw new RuntimeException('The existing canvas_rams catalogue must be migrated before its calculator fields.');
        }

        // Existing fixed prices and imported catalogue values must remain unchanged.
        $columns = [
            'price_per_meter' => fn (Blueprint $table) => $table->decimal('price_per_meter', 10, 2)->nullable(),
            'source_article' => fn (Blueprint $table) => $table->string('source_article')->nullable()->unique(),
            'source_data' => fn (Blueprint $table) => $table->text('source_data')->nullable(),
            'calculator_enabled' => fn (Blueprint $table) => $table->boolean('calculator_enabled')->default(false),
            'profile_width_mm' => fn (Blueprint $table) => $table->decimal('profile_width_mm', 7, 2)->nullable(),
            'profile_depth_mm' => fn (Blueprint $table) => $table->decimal('profile_depth_mm', 7, 2)->nullable(),
        ];

        foreach ($columns as $name => $addColumn) {
            if (! Schema::hasColumn('canvas_rams', $name)) {
                Schema::table('canvas_rams', $addColumn);
            }
        }
    }

    public function down(): void
    {
        // These fields may predate this migration on restored TEST databases.
        // A rollback must not silently drop imported catalogue data.
        throw new RuntimeException('Frame catalogue fields are preserved. Use an explicit data-safe removal migration to revert them.');
    }
};
