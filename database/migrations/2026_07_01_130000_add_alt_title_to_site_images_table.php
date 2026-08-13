<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddAltTitleToSiteImagesTable extends Migration
{
    private const TABLE = 'site_images';
    private const INDEX = 'site_images_page_position_name_index';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE)) {
            return;
        }

        $this->addColumnIfMissing('img_alt', 'img');
        $this->addColumnIfMissing('img_title', 'img_alt');
        $this->addColumnIfMissing('svg_alt', 'svg');
        $this->addColumnIfMissing('svg_title', 'svg_alt');

        if (
            Schema::hasColumn(self::TABLE, 'page')
            && Schema::hasColumn(self::TABLE, 'position_name')
            && !$this->indexExists(self::INDEX)
        ) {
            Schema::table(self::TABLE, function (Blueprint $table): void {
                $table->index(['page', 'position_name'], self::INDEX);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable(self::TABLE)) {
            return;
        }

        if ($this->indexExists(self::INDEX)) {
            Schema::table(self::TABLE, function (Blueprint $table): void {
                $table->dropIndex(self::INDEX);
            });
        }

        foreach (['svg_title', 'svg_alt', 'img_title', 'img_alt'] as $column) {
            if (Schema::hasColumn(self::TABLE, $column)) {
                Schema::table(self::TABLE, function (Blueprint $table) use ($column): void {
                    $table->dropColumn($column);
                });
            }
        }
    }

    /**
     * @param string $column
     * @param string $after
     * @return void
     */
    private function addColumnIfMissing(string $column, string $after): void
    {
        if (Schema::hasColumn(self::TABLE, $column)) {
            return;
        }

        Schema::table(self::TABLE, function (Blueprint $table) use ($column, $after): void {
            $definition = $table->string($column, 255)->nullable();

            if (Schema::hasColumn(self::TABLE, $after)) {
                $definition->after($after);
            }
        });
    }

    /**
     * @param string $index
     * @return bool
     */
    private function indexExists(string $index): bool
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            $rows = DB::select('SHOW INDEX FROM `' . self::TABLE . '` WHERE Key_name = ?', [$index]);

            return count($rows) > 0;
        }

        if ($driver === 'sqlite') {
            $rows = DB::select('PRAGMA index_list(`' . self::TABLE . '`)');

            foreach ($rows as $row) {
                if (isset($row->name) && $row->name === $index) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }
}
