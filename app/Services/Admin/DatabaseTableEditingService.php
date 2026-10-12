<?php

namespace App\Services\Admin;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Query\Expression;

class DatabaseTableEditingService
{
    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['name' => $message]);
    }

    public function snapshot(string $table): array
    {
        $structure = app(DatabaseInspectionService::class)->describe($table);
        $incoming = [];
        $prefix = DB::connection()->getTablePrefix();
        foreach ($this->referencingTables($table) as $physical) {
            if ($prefix !== '' && ! str_starts_with($physical, $prefix)) { continue; }
            $name = $prefix === '' ? $physical : substr($physical, strlen($prefix));
            foreach (Schema::getForeignKeys($name) as $key) {
                if (in_array($key['foreign_table'], [$table, $prefix.$table], true)) { $incoming[] = ['table' => $name, 'key' => $key]; }
            }
        }
        return ['structure' => $structure, 'incoming' => $incoming];
    }

    protected function referencingTables(string $table): array
    {
        $connection = DB::connection();
        if (in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) {
            // Avoid an information_schema query for every table on each page load.
            // Hidden tables must still participate in dependency protection.
            $rows = $connection->select(
                'select distinct TABLE_NAME as table_name from information_schema.KEY_COLUMN_USAGE '
                .'where TABLE_SCHEMA = ? and REFERENCED_TABLE_SCHEMA = ? and REFERENCED_TABLE_NAME = ? order by TABLE_NAME',
                [$connection->getDatabaseName(), $connection->getDatabaseName(), $connection->getTablePrefix().$table]
            );
            return array_map(fn ($row) => $row->table_name, $rows);
        }
        return Schema::getTableListing(Schema::getCurrentSchemaName(), false);
    }

    public function fingerprint(array $snapshot): string { return hash('sha256', serialize($snapshot)); }

    private function defaultExpression(array $column): ?string
    {
        $default = $column['default'];
        if (! is_string($default) || preg_match("/^'.*'$/s", $default)) { return null; }
        if (str_contains(strtolower($column['extra'] ?? ''), 'default_generated')) { return $default; }
        if (($column['mariadb_quoted_defaults'] ?? false) && $default !== '' && strtoupper($default) !== 'NULL' && ! is_numeric($default)) { return $default; }
        if (in_array(strtolower($column['type_name']), ['timestamp', 'datetime'], true)
            && preg_match('/^current_timestamp(?:\([0-6]?\))?$/iD', $default)) { return $default; }
        return null;
    }

    private function onUpdate(array $column): ?string
    {
        if (preg_match('/\bon update (current_timestamp(?:\([0-6]?\))?)/i', $column['extra'] ?? '', $matches)) { return $matches[1]; }
        return null;
    }

    public function definition(string $table, array $snapshot): array
    {
        $columns = [];
        foreach ($snapshot['structure']['columns'] as $column) {
            $type = strtolower($column['type_name']);
            $type = match ($type) { 'int' => 'integer', 'numeric' => 'decimal', 'double precision' => 'double', default => $type };
            $length = '';
            if (preg_match('/\((\d+(?:,\s*\d+)?)\)/', $column['type'], $matches)
                && in_array($type, ['varchar', 'string', 'char', 'binary', 'varbinary', 'bit', 'decimal'], true)) { $length = str_replace(' ', '', $matches[1]); }
            if ($type === 'decimal' && $length === '') { $length = '10,0'; }
            $default = $column['default'];
            if (is_string($default) && strtoupper($default) === 'NULL') { $default = null; }
            if (is_string($default) && preg_match("/^'(.*)'$/s", $default, $matches)) { $default = str_replace("''", "'", $matches[1]); }
            $indexes = array_values(array_filter($snapshot['structure']['indexes'], fn ($index) => $index['columns'] === [$column['name']]));
            usort($indexes, fn ($a, $b) => ($b['primary'] <=> $a['primary']) ?: ($b['unique'] <=> $a['unique']));
            $index = $indexes[0] ?? null;
            $columns[] = ['name' => $column['name'], 'type' => $type, 'length' => $length,
                'nullable' => (bool) $column['nullable'], 'unsigned' => str_contains($column['type'], 'unsigned'),
                'auto_increment' => (bool) $column['auto_increment'], 'index' => $index ? ($index['primary'] ? 'PRIMARY' : ($index['unique'] ? 'UNIQUE' : 'INDEX')) : '',
                'default' => $default === null ? null : (string) $default, 'old_name' => $column['name']];
        }
        return ['name' => $table, 'columns' => $columns];
    }

    public function plan(string $table, array $input, string $fingerprint): array
    {
        $snapshot = $this->snapshot($table);
        if (! hash_equals($fingerprint, $this->fingerprint($snapshot))) { $this->fail('Структура изменилась. Откройте форму заново и проверьте изменения.'); }
        $baseline = $this->definition($table, $snapshot);
        $data = Validator::make($input, [
            'name' => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/'],
            'columns' => ['required', 'array', 'min:1', 'max:100'],
            'columns.*.name' => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/', 'distinct:ignore_case'],
        ])->validate();
        // Keep full definitions, while rejecting forged/unknown column properties.
        $data['columns'] = array_values($input['columns']);
        if (strlen(DB::connection()->getTablePrefix().$data['name']) > 64) { $this->fail('Имя таблицы с префиксом длиннее 64 символов.'); }
        if ($data['name'] !== $table && Schema::hasTable($data['name'])) { $this->fail('Таблица с новым именем уже существует.'); }
        $originals = array_column($baseline['columns'], null, 'old_name');
        $raw = array_column($snapshot['structure']['columns'], null, 'name');
        $used = []; $changed = []; $added = []; $renames = [];
        foreach ($data['columns'] as &$column) {
            $old = $column['old_name'] ?? null;
            if ($old !== null && (! is_string($old) || ! isset($originals[$old]) || isset($used[$old]))) { $this->fail('Исходная колонка не найдена или указана повторно.'); }
            if ($old !== null) { $used[$old] = true; }
            $column['old_name'] = $old;
            $column['length'] = $column['length'] ?? '';
            $column['index'] = $column['index'] ?? '';
            $column['default'] = $column['default'] ?? null;
            if ($old !== null && $column === $originals[$old]) { continue; }
            if ($old !== null && ($raw[$old]['generation'] ?? null)) { $this->fail('Изменение вычисляемой колонки пока не поддерживается.'); }
            $attributesBefore = $old === null ? [] : array_diff_key($originals[$old], ['name' => true, 'old_name' => true, 'index' => true]);
            $attributesAfter = array_diff_key($column, ['name' => true, 'old_name' => true, 'index' => true]);
            ksort($attributesBefore); ksort($attributesAfter);
            // A rename/index edit preserves existing type/default expressions verbatim.
            // Only new or actually modified definitions require creation validation.
            $attributesUnchanged = $old !== null && $attributesBefore === $attributesAfter;
            $candidate = $column; unset($candidate['old_name']);
            if ($attributesUnchanged) {
                if (! in_array($column['index'], ['', 'PRIMARY', 'UNIQUE', 'INDEX'], true)) { $this->fail('Недопустимый вид индекса.'); }
                if ($column['index'] !== $originals[$old]['index'] && $column['index'] !== ''
                    && in_array($column['type'], ['tinytext', 'text', 'mediumtext', 'longtext', 'tinyblob', 'blob', 'mediumblob', 'longblob', 'json'], true)) {
                    $this->fail('Прямой индекс этого типа не поддерживается.');
                }
                if ($column['index'] === 'PRIMARY' && $column['nullable']) { $this->fail('PRIMARY не может допускать NULL.'); }
                if ($column['auto_increment'] && $column['index'] !== 'PRIMARY') { $this->fail('Сначала отключите AUTO INCREMENT перед удалением PRIMARY.'); }
                $normalized = $candidate;
            } else {
                $expression = $old === null ? null : $this->defaultExpression($raw[$old]);
                $preserveExpression = $expression !== null && $column['default'] === $originals[$old]['default'];
                if ($preserveExpression) {
                    if ($column['type'] !== $originals[$old]['type']) { $this->fail('Изменение типа с существующим выражением default требует явного нового значения default.'); }
                    $candidate['default'] = null;
                }
                if ($old !== null) {
                    $extra = strtolower($raw[$old]['extra'] ?? '');
                    $remaining = trim(preg_replace('/\b(?:auto_increment|default_generated)\b|\bon update current_timestamp(?:\([0-6]?\))?/i', '', $extra));
                    if ($remaining !== '') { $this->fail('Колонка содержит дополнительные свойства, которые редактор не может безопасно сохранить.'); }
                    if ($this->onUpdate($raw[$old]) !== null && ! in_array($column['type'], ['timestamp', 'datetime'], true)) {
                        $this->fail('ON UPDATE CURRENT_TIMESTAMP несовместим с выбранным типом.');
                    }
                    if (str_contains(strtolower($raw[$old]['type']), 'zerofill')
                        && ($column['type'] !== $originals[$old]['type'] || $column['unsigned'] !== $originals[$old]['unsigned'] || $column['length'] !== $originals[$old]['length'])) {
                        $this->fail('Изменение типа, длины или unsigned колонки ZEROFILL этим редактором не поддерживается.');
                    }
                }
                $normalized = app(DatabaseTableCreationService::class)->validate(['name' => $table, 'columns' => [$candidate]], $table)['columns'][0];
                if ($preserveExpression) { $normalized['default'] = $originals[$old]['default']; }
            }
            $column = [...$normalized, 'old_name' => $old];
            if ($old === null) { $added[] = $column; }
            else {
                if ($column['name'] !== $old) {
                    if (isset($originals[$column['name']])) { $this->fail('Переименование в имя существующей колонки не поддерживается.'); }
                    $renames[$old] = $column['name'];
                }
                $before = array_diff_key($originals[$old], ['name' => true, 'old_name' => true, 'index' => true]);
                $after = array_diff_key($column, ['name' => true, 'old_name' => true, 'index' => true]);
                ksort($before); ksort($after);
                if ($before !== $after) { $changed[] = $column; }
            }
        }
        unset($column);
        $dropped = array_values(array_diff(array_keys($originals), array_keys($used)));
        $affected = array_unique([...$dropped, ...array_keys($renames), ...array_column($changed, 'old_name')]);
        foreach ($snapshot['structure']['foreign_keys'] as $key) {
            if (array_intersect($affected, $key['columns']) !== []) { $this->fail('Колонка участвует во внешнем ключе. Сначала отдельно согласуйте изменение связи.'); }
        }
        foreach ($snapshot['incoming'] as $reference) {
            if (array_intersect($affected, $reference['key']['foreign_columns']) !== []) { $this->fail('На колонку ссылается другая таблица. Изменение заблокировано.'); }
        }
        $dropIndexes = []; $addIndexes = [];
        foreach ($snapshot['structure']['indexes'] as $index) {
            if (count($index['columns']) !== 1) {
                if (array_intersect([...$dropped, ...array_keys($renames)], $index['columns']) !== []) { $this->fail('Составной индекс сохраняется: изменение его колонок требует отдельного редактора.'); }
                continue;
            }
            $old = $index['columns'][0];
            $replacement = collect($data['columns'])->firstWhere('old_name', $old);
            if ($replacement !== null && $replacement['index'] === $originals[$old]['index']) { continue; }
            $kind = $index['primary'] ? 'PRIMARY' : ($index['unique'] ? 'UNIQUE' : 'INDEX');
            if ($replacement === null || $replacement['index'] !== $kind) { $dropIndexes[] = $index; }
        }
        foreach ($data['columns'] as $column) {
            $old = $column['old_name'];
            if ($column['index'] !== '' && ($old === null || $column['index'] !== $originals[$old]['index'])) { $addIndexes[] = $column; }
        }
        foreach ($snapshot['structure']['indexes'] as $index) {
            if ($index['primary'] && count($index['columns']) > 1 && collect($addIndexes)->contains(fn ($column) => $column['index'] === 'PRIMARY')) {
                $this->fail('Составной PRIMARY уже существует. Добавление второго PRIMARY запрещено.');
            }
        }
        $indexColumns = array_merge([], ...array_column($dropIndexes, 'columns'));
        foreach ($snapshot['structure']['foreign_keys'] as $key) {
            if (array_intersect($indexColumns, $key['columns']) !== []) { $this->fail('Индекс участвует во внешнем ключе. Изменение заблокировано.'); }
        }
        foreach ($snapshot['incoming'] as $reference) {
            if (array_intersect($indexColumns, $reference['key']['foreign_columns']) !== []) { $this->fail('Индекс используется входящей связью. Изменение заблокировано.'); }
        }
        if (count(array_filter($data['columns'], fn ($c) => $c['index'] === 'PRIMARY')) > 1 || count(array_filter($data['columns'], fn ($c) => $c['auto_increment'])) > 1) { $this->fail('Допускается один PRIMARY и один AUTO INCREMENT.'); }
        $hasChanges = $added !== [] || $changed !== [] || $dropped !== [] || $renames !== [] || $dropIndexes !== [] || $addIndexes !== [] || $data['name'] !== $table;
        if (DB::getDriverName() === 'sqlite' && collect($dropIndexes)->contains(fn ($index) => $index['primary'])) { $this->fail('Изменение PRIMARY в SQLite этим редактором не поддерживается.'); }
        if ($hasChanges && DB::getDriverName() === 'sqlite' && ($snapshot['incoming'] !== [] || $snapshot['structure']['foreign_keys'] !== [])) { $this->fail('SQLite: изменение таблицы со связями заблокировано, чтобы не затронуть связанные данные.'); }
        return compact('data', 'snapshot', 'changed', 'added', 'renames', 'dropped', 'dropIndexes', 'addIndexes', 'hasChanges');
    }

    public function preview(string $table, array $input, string $fingerprint): array
    {
        $plan = $this->plan($table, $input, $fingerprint);
        $plan['sql'] = $this->compile($table, $plan);
        return $plan;
    }

    public function apply(string $table, array $input, string $fingerprint): void
    {
        $plan = $this->preview($table, $input, $fingerprint);
        $connection = DB::connection();
        $execute = function () use ($plan, $connection): void {
            foreach ($plan['sql'] as $statement) { $connection->statement($statement); }
        };
        if ($connection->getDriverName() !== 'sqlite') { $execute(); return; }
        $foreignKeys = (bool) $connection->scalar('pragma foreign_keys');
        try { $connection->transaction($execute); }
        finally { $connection->statement('pragma foreign_keys = '.($foreignKeys ? '1' : '0')); }
    }

    private function compile(string $table, array $plan): array
    {
        if (! $plan['hasChanges']) { return []; }
        $connection = DB::connection(); $grammar = $connection->getSchemaGrammar();
        if (in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) { $connection->setSchemaGrammar(new DatabaseMySqlGrammar($connection)); }
        try {
            $blueprint = new Blueprint($connection, $table, function (Blueprint $blueprint) use ($plan): void {
                foreach ($plan['dropIndexes'] as $index) {
                    if ($index['primary']) { $blueprint->dropPrimary($index['name']); }
                    elseif ($index['unique']) { $blueprint->dropUnique($index['name']); }
                    else { $blueprint->dropIndex($index['name']); }
                }
                if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
                    foreach ($plan['renames'] as $from => $to) { $blueprint->renameColumn($from, $to); }
                }
                foreach ($plan['dropped'] as $name) { $blueprint->dropColumn($name); }
                $raw = array_column($plan['snapshot']['structure']['columns'], null, 'name');
                foreach ($plan['changed'] as $column) {
                    $field = app(DatabaseTableCreationService::class)->addColumn($blueprint, $column);
                    $original = $raw[$column['old_name']];
                    $expression = $this->defaultExpression($original);
                    if ($expression !== null && $column['default'] === $original['default']) {
                        $sqlExpression = $expression;
                        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)
                            && ! preg_match('/^current_timestamp(?:\([0-6]?\))?$/iD', $expression)) {
                            $sqlExpression = '('.$expression.')';
                        }
                        $field->default(new Expression($sqlExpression));
                    }
                    if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
                        if ($update = $this->onUpdate($original)) { $field->onUpdate(new Expression($update)); }
                        if (str_contains(strtolower($original['type']), 'zerofill')) { $field->databaseNumericType(strtolower($original['type'])); }
                    }
                    if (in_array(strtolower($original['type_name']), ['timestamp', 'datetime', 'time'], true)
                        && in_array($column['type'], ['timestamp', 'datetime', 'time'], true)
                        && preg_match('/\((\d+)\)/', $original['type'], $precision)) {
                        $field->precision((int) $precision[1]);
                    }
                    if ($original['collation'] && in_array($column['type'], ['string', 'varchar', 'char', 'tinytext', 'text', 'mediumtext', 'longtext'], true)) {
                        $field->collation($original['collation']);
                    }
                    if ($original['comment']) { $field->comment($original['comment']); }
                    $field->change();
                }
                foreach ($plan['added'] as $column) { app(DatabaseTableCreationService::class)->addColumn($blueprint, $column); }
                foreach ($plan['addIndexes'] as $column) {
                    $name = $column['name'];
                    if ($column['index'] === 'PRIMARY' && (! $column['auto_increment'] || in_array(DB::getDriverName(), ['mysql', 'mariadb'], true))) { $blueprint->primary($name); }
                    elseif ($column['index'] === 'UNIQUE') { $blueprint->unique($name, 'db_'.substr(hash('sha256', $plan['data']['name'].'.'.$name), 0, 24).'_unique'); }
                    elseif ($column['index'] === 'INDEX') { $blueprint->index($name, 'db_'.substr(hash('sha256', $plan['data']['name'].'.'.$name), 0, 24).'_index'); }
                }
            });
            $sql = $blueprint->toSql();
            if (in_array($connection->getDriverName(), ['mysql', 'mariadb'], true) && $sql !== []) {
                // PRIMARY and AUTO_INCREMENT must change in the same statement:
                // dropping the old key first otherwise leaves an invalid intermediate table.
                $prefix = 'alter table '.$connection->getSchemaGrammar()->wrapTable($table).' ';
                foreach ($sql as $statement) {
                    if (! str_starts_with($statement, $prefix)) { $this->fail('Этот вариант SQL изменения не поддерживается.'); }
                }
                $sql = [$prefix.implode(', ', array_map(fn ($statement) => substr($statement, strlen($prefix)), $sql))];
            }
            $renames = [];
            foreach (in_array($connection->getDriverName(), ['mysql', 'mariadb'], true) ? $plan['renames'] : [] as $from => $to) {
                $rename = new Blueprint($connection, $table); $rename->renameColumn($from, $to);
                $renames = [...$renames, ...$rename->toSql()];
            }
            $sql = [...$renames, ...$sql];
            if ($plan['data']['name'] !== $table) { $rename = new Blueprint($connection, $table); $rename->rename($plan['data']['name']); $sql = [...$sql, ...$rename->toSql()]; }
            return $sql;
        } finally { $connection->setSchemaGrammar($grammar); }
    }

    public function dropPreview(string $table, string $fingerprint): array
    {
        $snapshot = $this->snapshot($table);
        if (! hash_equals($fingerprint, $this->fingerprint($snapshot))) { $this->fail('Структура изменилась. Откройте форму заново.'); }
        if (Schema::hasTable('data_types') && DB::table('data_types')->where('name', $table)->exists()) {
            $this->fail('Сначала удалите BREAD этой таблицы, как в оригинальном Voyager.');
        }
        if ($snapshot['incoming'] !== []) { $this->fail('Удаление заблокировано: на таблицу ссылаются внешние ключи.'); }
        $blueprint = new Blueprint(DB::connection(), $table); $blueprint->drop();
        return $blueprint->toSql();
    }

    public function drop(string $table, string $fingerprint): void
    {
        foreach ($this->dropPreview($table, $fingerprint) as $statement) { DB::connection()->statement($statement); }
    }
}
