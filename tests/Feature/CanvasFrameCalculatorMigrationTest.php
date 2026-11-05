<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CanvasFrameCalculatorMigrationTest extends TestCase
{
    private function migration(): \Illuminate\Database\Migrations\Migration
    {
        return require database_path('migrations/2026_11_05_100000_add_calculator_fields_to_canvas_rams.php');
    }

    public function test_legacy_fixed_prices_are_preserved_without_voyager_tables(): void
    {
        Schema::create('canvas_rams', function (Blueprint $table): void {
            $table->increments('id');
            $table->decimal('price', 10, 2);
        });
        DB::table('canvas_rams')->insert(['id' => 16, 'price' => 25]);

        $migration = $this->migration();
        $migration->up();
        $migration->up();

        $this->assertTrue(Schema::hasColumns('canvas_rams', [
            'price_per_meter', 'source_article', 'source_data', 'calculator_enabled',
            'profile_width_mm', 'profile_depth_mm',
        ]));
        $frame = DB::table('canvas_rams')->find(16);
        $this->assertEquals(25, $frame->price);
        $this->assertNull($frame->price_per_meter);
        $this->assertNull($frame->profile_width_mm);
        $this->assertFalse((bool) $frame->calculator_enabled);
        $this->assertFalse(Schema::hasTable('data_rows'));
        $this->assertSame(1, DB::table('canvas_rams')->count());
    }

    public function test_partial_schema_and_imported_values_survive_migration_and_rollback_attempt(): void
    {
        Schema::create('canvas_rams', function (Blueprint $table): void {
            $table->increments('id');
            $table->decimal('price_per_meter', 10, 2)->nullable();
            $table->string('source_article')->nullable();
            $table->decimal('profile_width_mm', 7, 2)->nullable();
        });
        DB::table('canvas_rams')->insert([
            'id' => 15, 'price_per_meter' => 12.75, 'source_article' => 'FRAME-A', 'profile_width_mm' => 33,
        ]);
        $before = (array) DB::table('canvas_rams')->find(15);
        $migration = $this->migration();
        $migration->up();
        foreach ($before as $key => $value) {
            $this->assertEquals($value, DB::table('canvas_rams')->find(15)->$key);
        }
        DB::table('canvas_rams')->where('id', 15)->update(['calculator_enabled' => true, 'profile_depth_mm' => 22]);
        $migration->up();
        $this->assertTrue((bool) DB::table('canvas_rams')->find(15)->calculator_enabled);
        try {
            $migration->down();
            $this->fail('An implicit destructive rollback must be refused.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('preserved', $e->getMessage());
        }
        $this->assertEquals(22, DB::table('canvas_rams')->find(15)->profile_depth_mm);
    }

    public function test_missing_base_catalogue_is_reported_instead_of_recording_a_noop_migration(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('existing canvas_rams catalogue');
        $this->migration()->up();
    }
}
