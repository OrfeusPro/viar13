<?php

namespace App\Services\Admin;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DatabaseTableCreationService
{
    public const TYPES = ['integer', 'bigint', 'mediumint', 'smallint', 'tinyint', 'string', 'varchar', 'char', 'tinytext', 'text', 'mediumtext', 'longtext', 'decimal', 'float', 'double', 'boolean', 'date', 'datetime', 'timestamp', 'time', 'year', 'json', 'binary', 'varbinary', 'bit', 'tinyblob', 'blob', 'mediumblob', 'longblob', 'geometry', 'point', 'linestring', 'polygon', 'multipoint', 'multilinestring', 'multipolygon', 'geometrycollection'];
    private const INTEGERS = ['integer', 'bigint', 'mediumint', 'smallint', 'tinyint'];
    public const MYSQL_ONLY = ['tinytext', 'bit', 'tinyblob', 'blob', 'mediumblob', 'longblob', 'geometry', 'point', 'linestring', 'polygon', 'multipoint', 'multilinestring', 'multipolygon', 'geometrycollection'];

    public static function typeOptions(): array
    {
        return [
            'Числа' => array_combine($numbers = ['tinyint', 'smallint', 'mediumint', 'integer', 'bigint', 'float', 'double', 'decimal', 'boolean'], array_map('strtoupper', $numbers)),
            'Строки' => array_combine($strings = ['tinytext', 'mediumtext', 'longtext', 'text', 'varchar', 'char', 'string'], array_map('strtoupper', $strings)),
            'Дата и время' => array_combine($dates = ['date', 'datetime', 'timestamp', 'time', 'year'], array_map('strtoupper', $dates)),
            'Двоичные' => array_combine($binary = ['longblob', 'blob', 'mediumblob', 'tinyblob', 'binary', 'varbinary', 'bit'], array_map('strtoupper', $binary)),
            'Списки' => ['set' => 'SET — не поддерживается Voyager', 'json' => 'JSON', 'enum' => 'ENUM — не поддерживается Voyager'],
            'Геометрия' => array_combine($geometry = ['geometrycollection', 'geometry', 'linestring', 'multilinestring', 'multipoint', 'multipolygon', 'point', 'polygon'], array_map('strtoupper', $geometry)),
        ];
    }

    public function validate(array $input, ?string $existingTable = null): array
    {
        $data = Validator::make($input, [
            'name' => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/'],
            'columns' => ['required', 'array', 'min:1', 'max:100'],
            'columns.*' => ['array:name,type,length,nullable,unsigned,auto_increment,index,default'],
            'columns.*.name' => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/', 'distinct:ignore_case'],
            'columns.*.type' => ['required', Rule::in(self::TYPES)],
            'columns.*.length' => ['nullable', 'string', 'max:20'],
            'columns.*.nullable' => ['required', 'boolean'],
            'columns.*.unsigned' => ['required', 'boolean'],
            'columns.*.auto_increment' => ['required', 'boolean'],
            'columns.*.index' => ['nullable', Rule::in(['PRIMARY', 'UNIQUE', 'INDEX'])],
            'columns.*.default' => ['nullable', 'string', 'max:1000'],
        ])->validate();
        $fail = fn (string $key, string $message) => throw ValidationException::withMessages([$key => $message]);
        if (strlen(DB::connection()->getTablePrefix().$data['name']) > 64) { $fail('name', 'Имя таблицы вместе с префиксом длиннее 64 символов.'); }
        if ($data['name'] !== $existingTable && Schema::hasTable($data['name'])) { $fail('name', 'Таблица уже существует.'); }
        $primaries = 0; $auto = 0;
        foreach ($data['columns'] as $i => &$column) {
            $path = 'columns.'.$i;
            $type = $column['type'];
            $length = trim($column['length'] ?? '');
            $column['length'] = $length;
            $default = trim($column['default'] ?? '');
            $column['default'] = $default === '' ? null : $default;
            $column['index'] = $column['index'] ?? '';
            if (in_array($type, self::MYSQL_ONLY, true) && ! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) { $fail($path.'.type', 'Этот тип доступен только для MySQL/MariaDB.'); }
            if (in_array($type, ['string', 'varchar', 'char', 'binary', 'varbinary', 'bit'], true)) {
                $max = match ($type) { 'char', 'binary' => 255, 'bit' => 64, default => 65535 };
                if ($length !== '' && (! ctype_digit($length) || (int) $length < 1 || (int) $length > $max)) { $fail($path.'.length', 'Укажите допустимую длину: 1–'.$max.'.'); }
            } elseif ($type === 'decimal') {
                if ($length === '') { $column['length'] = '10,0'; }
                elseif (! preg_match('/^(\d{1,2}),(\d{1,2})$/', $length, $parts) || (int) $parts[1] < 1 || (int) $parts[1] > 65 || (int) $parts[2] > 30 || (int) $parts[2] > (int) $parts[1]) { $fail($path.'.length', 'DECIMAL: precision,scale, например 8,2.'); }
            } elseif (in_array($type, [...self::INTEGERS, 'float', 'double'], true)) {
                // The original Doctrine declarations ignore the UI length for these types.
                if ($length !== '' && ! preg_match('/^\d+(,\d+)?$/', $length)) { $fail($path.'.length', 'Укажите числовую длину.'); }
                $column['length'] = '';
            } elseif ($length !== '') { $fail($path.'.length', 'Для этого типа длина не поддерживается.'); }
            if ($column['unsigned'] && ! in_array($type, [...self::INTEGERS, 'decimal', 'double'], true)) { $fail($path.'.unsigned', 'Unsigned поддерживается для целых чисел, decimal и double.'); }
            if ($column['index'] === 'PRIMARY') {
                $primaries++;
                if ($column['nullable']) { $fail($path.'.nullable', 'PRIMARY не может допускать NULL.'); }
            }
            if ($column['auto_increment']) {
                $auto++;
                if (! in_array($type, self::INTEGERS, true) || $column['index'] !== 'PRIMARY' || $column['nullable'] || $column['default'] !== null) { $fail($path.'.auto_increment', 'AUTO INCREMENT требует целый PRIMARY без NULL и default.'); }
                if (DB::getDriverName() === 'sqlite' && ! in_array($type, ['integer', 'bigint'], true)) { $fail($path.'.type', 'SQLite AUTO INCREMENT поддерживает integer/bigint.'); }
            }
            if (in_array($type, ['tinytext', 'text', 'mediumtext', 'longtext', 'tinyblob', 'blob', 'mediumblob', 'longblob'], true) && ($column['index'] !== '' || $column['default'] !== null)) { $fail($path.'.index', 'Этот тип создаётся без индекса и default, как в Voyager.'); }
            if ($type === 'json' && $column['default'] !== null) { $fail($path.'.default', 'Default JSON отключён в Voyager.'); }
            if ($type === 'json' && $column['index'] !== '') { $fail($path.'.index', 'MySQL не поддерживает прямой индекс JSON.'); }
            if ($type === 'year' && $column['default'] !== null && (! ctype_digit($column['default']) || ! ((int) $column['default'] === 0 || ((int) $column['default'] >= 1901 && (int) $column['default'] <= 2155)))) { $fail($path.'.default', 'YEAR: 0 или 1901–2155.'); }
            if ($column['default'] !== null && (in_array($type, self::INTEGERS, true) || $type === 'boolean') && ! preg_match('/^-?\d+$/', $column['default'])) { $fail($path.'.default', 'Default должен быть целым числом.'); }
            if ($column['default'] !== null && in_array($type, ['decimal', 'float', 'double'], true) && ! is_numeric($column['default'])) { $fail($path.'.default', 'Default должен быть числом.'); }
            if ($column['default'] !== null && $column['unsigned'] && (float) $column['default'] < 0) { $fail($path.'.default', 'Unsigned default не может быть отрицательным.'); }
            if ($type === 'boolean' && $column['default'] !== null && ! in_array($column['default'], ['0', '1'], true)) { $fail($path.'.default', 'Boolean default: 0 или 1.'); }
            $dateFormat = match ($type) { 'date' => 'Y-m-d', 'datetime', 'timestamp' => 'Y-m-d H:i:s', 'time' => 'H:i:s', default => null };
            if ($column['default'] !== null && $dateFormat !== null
                && Validator::make(['value' => $column['default']], ['value' => 'date_format:'.$dateFormat])->fails()) { $fail($path.'.default', 'Default должен соответствовать формату '.$dateFormat.'.'); }
            if ($column['default'] !== null && in_array($type, ['string', 'varchar', 'char', 'binary', 'varbinary'], true) && (in_array($type, ['binary', 'varbinary'], true) ? strlen($column['default']) : mb_strlen($column['default'])) > (int) ($length ?: 255)) { $fail($path.'.default', 'Default длиннее колонки.'); }
        }
        unset($column);
        if ($primaries > 1 || $auto > 1) { $fail('columns', 'Поддерживается один PRIMARY и один AUTO INCREMENT.'); }
        return $data;
    }

    public function preview(array $input): array
    {
        $data = $this->validate($input);
        $queries = DB::connection()->pretend(fn () => $this->build($data));
        return ['definition' => $data, 'sql' => array_column($queries, 'query')];
    }

    public function create(array $input): void { $this->build($this->validate($input)); }

    private function build(array $data): void
    {
        $connection = DB::connection();
        $originalGrammar = $connection->getSchemaGrammar();
        if (in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) { $connection->setSchemaGrammar(new DatabaseMySqlGrammar($connection)); }
        try { $connection->getSchemaBuilder()->create($data['name'], function (Blueprint $table) use ($data): void {
            foreach ($data['columns'] as $column) {
                $this->addColumn($table, $column);
                $name = $column['name'];
                if ($column['index'] === 'PRIMARY' && ! $column['auto_increment']) { $table->primary($name); }
                elseif ($column['index'] === 'UNIQUE') { $table->unique($name, 'db_'.substr(hash('sha256', $data['name'].'.'.$name), 0, 24).'_unique'); }
                elseif ($column['index'] === 'INDEX') { $table->index($name, 'db_'.substr(hash('sha256', $data['name'].'.'.$name), 0, 24).'_index'); }
            }
        }); } finally { $connection->setSchemaGrammar($originalGrammar); }
    }

    public function addColumn(Blueprint $table, array $column): \Illuminate\Database\Schema\ColumnDefinition
    {
        $name = $column['name'];
        $field = match ($column['type']) {
                    'integer' => $table->integer($name, $column['auto_increment'], $column['unsigned']),
                    'bigint' => $table->bigInteger($name, $column['auto_increment'], $column['unsigned']),
                    'mediumint' => $table->mediumInteger($name, $column['auto_increment'], $column['unsigned']),
                    'smallint' => $table->smallInteger($name, $column['auto_increment'], $column['unsigned']),
                    'tinyint' => $table->tinyInteger($name, $column['auto_increment'], $column['unsigned']),
                    'string', 'varchar' => $table->string($name, (int) ($column['length'] ?: 255)),
                    'char' => $table->char($name, (int) ($column['length'] ?: 255)),
                    'decimal' => $table->decimal($name, ...array_map('intval', explode(',', $column['length']))),
                    'mediumtext' => $table->mediumText($name),
                    'longtext' => $table->longText($name),
                    'float' => $table->float($name, 24),
                    'binary', 'varbinary' => $table->binary($name, (int) ($column['length'] ?: 255), $column['type'] === 'binary'),
                    'tinytext', 'bit', 'tinyblob', 'blob', 'mediumblob', 'longblob' => $table->addColumn('voyagerNative', $name, ['voyagerType' => $column['type'], 'length' => (int) ($column['length'] ?: 1)]),
                    'geometry', 'point', 'linestring', 'polygon', 'multipoint', 'multilinestring', 'multipolygon', 'geometrycollection' => $table->geometry($name, $column['type'] === 'geometry' ? null : $column['type']),
                    default => $table->{$column['type']}($name),
                };
                if ($column['unsigned']) { $field->unsigned(); }
                $field->nullable((bool) $column['nullable']);
                if ($column['default'] !== null) { $field->default($column['default']); }
        return $field;
    }
}
