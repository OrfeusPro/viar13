<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseInspectionService
{
    public function tables(string $search = ''): array
    {
        $names = Schema::getTableListing(Schema::getCurrentSchemaName(), false);
        $prefix = DB::connection()->getTablePrefix();
        if ($prefix !== '') {
            $names = array_map(fn (string $name): string => substr($name, strlen($prefix)),
                array_values(array_filter($names, fn (string $name): bool => str_starts_with($name, $prefix))));
        }
        $hidden = config('voyager.database.tables.hidden', []);
        $names = array_values(array_diff($names, $hidden));
        sort($names, SORT_STRING);
        $types = Schema::hasTable('data_types') ? DB::table('data_types')->get()->keyBy('name') : collect();

        return array_values(array_filter(array_map(function (string $name) use ($types): array {
            $type = $types->get($name);
            return ['name' => $name, 'bread_id' => $type?->id, 'title' => $type?->display_name_plural, 'slug' => $type?->slug];
        }, $names), fn (array $row): bool => $search === '' || str_contains(mb_strtolower($row['name'].' '.$row['title']), mb_strtolower($search))));
    }

    public function describe(string $table): array
    {
        // Resolve only known visible table identifiers before querying schema metadata.
        abort_unless(in_array($table, array_column($this->tables(), 'name'), true), 404);

        $columns = Schema::getColumns($table);
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $extras = $this->columnExtras($table);
            $connection = DB::connection();
            $quotedDefaults = $connection instanceof \Illuminate\Database\MySqlConnection
                && $connection->isMaria() && version_compare($connection->getServerVersion(), '10.2.7', '>=');
            foreach ($columns as &$column) {
                abort_unless(array_key_exists($column['name'], $extras), 409, 'Не удалось прочитать все свойства колонок. Откройте структуру заново.');
                $column['extra'] = $extras[$column['name']];
                $column['mariadb_quoted_defaults'] = $quotedDefaults;
                $column['auto_increment'] = str_contains(strtolower($column['extra']), 'auto_increment');
            }
            unset($column);
        }
        return ['columns' => $columns, 'indexes' => Schema::getIndexes($table),
            'foreign_keys' => Schema::getForeignKeys($table)];
    }

    protected function columnExtras(string $table): array
    {
        $connection = DB::connection();
        $rows = $connection->select(
            'select COLUMN_NAME as name, EXTRA as extra from information_schema.COLUMNS where TABLE_SCHEMA = ? and TABLE_NAME = ?',
            [$connection->getDatabaseName(), $connection->getTablePrefix().$table]
        );
        return array_column($rows, 'extra', 'name');
    }
}
