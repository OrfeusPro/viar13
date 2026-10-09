<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BreadCreationService
{
    public function tables(): array
    {
        $configured = DB::table('data_types')->pluck('name')->all();
        return array_values(array_filter(Schema::getTableListing(Schema::getCurrentSchemaName(), false), fn ($name) =>
            preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name) && ! in_array($name, $configured, true)));
    }

    public function models(string $table): array
    {
        $result = [];
        foreach (glob(app_path('Models/*.php')) as $file) {
            $class = 'App\\Models\\'.basename($file, '.php');
            if (! class_exists($class) || ! is_subclass_of($class, Model::class)) { continue; }
            $reflection = new \ReflectionClass($class);
            if ($reflection->isAbstract() || ($reflection->getConstructor()?->getNumberOfRequiredParameters() ?? 0) > 0) { continue; }
            $model = new $class;
            if (strcasecmp($model->getTable(), $table) === 0 && $model->getKeyName() === 'id') { $result[$class] = $class; }
        }
        return $result;
    }

    public function snapshot(string $table): string
    {
        return hash('sha256', json_encode([Schema::getColumns($table), Schema::getIndexes($table)]));
    }

    public function defaults(string $table): array
    {
        $rows = [];
        foreach (Schema::getColumns($table) as $index => $column) {
            $field = $column['name'];
            $secret = in_array($field, ['password', 'remember_token'], true);
            $system = in_array($field, ['id', 'created_at', 'updated_at'], true);
            $type = match (true) {
                $secret => $field === 'password' && $table === 'users' ? 'password' : 'hidden',
                in_array($column['type_name'], ['boolean', 'bool'], true) => 'checkbox',
                in_array($column['type_name'], ['tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint', 'float', 'double', 'decimal', 'numeric'], true) => 'number',
                $column['type_name'] === 'date' => 'date',
                in_array($column['type_name'], ['timestamp', 'datetime'], true) => 'timestamp',
                in_array($column['type_name'], ['text', 'mediumtext', 'longtext', 'json', 'jsonb'], true) => 'text_area',
                default => 'text',
            };
            $rows[] = ['field' => $field, 'sql_type' => $column['type'], 'type' => $type,
                'display_name' => Str::headline($field), 'order' => $index + 1, 'details' => '{}',
                'required' => ! $column['nullable'] && $column['default'] === null,
                'browse' => ! $secret && $field !== 'updated_at', 'read' => ! $secret && $field !== 'id' && $field !== 'updated_at',
                'add' => ! $system && ! $secret, 'edit' => ! $system && ! $secret, 'delete' => ! $system && ! $secret];
        }
        return $rows;
    }

    public function create(User $actor, array $input, string $snapshot): int
    {
        app(BreadMetadataService::class)->authorize($actor);
        $values = validator($input, [
            'name' => ['required', Rule::in($this->tables())],
            'model_name' => 'required|string', 'display_name_singular' => 'required|string|max:191',
            'display_name_plural' => 'required|string|max:191', 'slug' => ['required', 'max:191', 'regex:/^[a-z0-9]+(?:[-_][a-z0-9]+)*$/', Rule::unique('data_types', 'slug')],
            'icon' => 'nullable|string|max:191', 'description' => 'nullable|string|max:5000',
            'generate_permissions' => 'required|boolean', 'add_menu' => 'required|boolean',
            'rows' => 'required|array',
            'rows.*' => 'required|array', 'rows.*.field' => 'required|string',
        ])->validate();
        // The field service validates and filters each complete row below.
        $values['rows'] = $input['rows'];
        if (! isset($this->models($values['name'])[$values['model_name']])) {
            throw ValidationException::withMessages(['model_name' => 'Выберите существующую модель этой таблицы с ключом id.']);
        }
        $primary = collect(Schema::getIndexes($values['name']))->firstWhere('primary', true);
        if (($primary['columns'] ?? []) !== ['id']) {
            throw ValidationException::withMessages(['name' => 'Для общего BREAD нужен единственный первичный ключ id.']);
        }
        $defaults = $this->defaults($values['name']);
        if (array_column($values['rows'], 'field') !== array_column($defaults, 'field')) {
            throw ValidationException::withMessages(['rows' => 'Состав колонок изменён. Откройте создание заново.']);
        }
        return DB::transaction(function () use ($actor, $values, $snapshot): int {
            app(BreadMetadataService::class)->authorize($actor);
            if (DB::table('data_types')->where('name', $values['name'])->lockForUpdate()->exists()
                || DB::table('data_types')->where('slug', $values['slug'])->lockForUpdate()->exists()) {
                throw ValidationException::withMessages(['name' => 'BREAD уже создан. Обновите список таблиц.']);
            }
            if ($this->snapshot($values['name']) !== $snapshot) {
                throw ValidationException::withMessages(['name' => 'Структура таблицы изменена. Откройте создание заново.']);
            }
            $menu = null;
            if ($values['add_menu']) {
                $menu = Schema::hasTable('menus') && Schema::hasTable('menu_items')
                    ? DB::table('menus')->where('name', 'admin')->lockForUpdate()->first() : null;
                if (! $menu) { throw ValidationException::withMessages(['add_menu' => 'Меню admin недоступно. Снимите добавление в меню или настройте его.']); }
            }
            $attributes = $values;
            unset($attributes['add_menu'], $attributes['rows']);
            if (! Schema::hasColumn('data_types', 'generate_permissions')) { unset($attributes['generate_permissions']); }
            if (Schema::hasColumn('data_types', 'server_side')) { $attributes['server_side'] = true; }
            $attributes['details'] = '{}';
            $id = DB::table('data_types')->insertGetId($attributes);
            foreach ($values['rows'] as $row) {
                try { app(BreadMetadataService::class)->save($actor, $id, null, $row, null); }
                catch (ValidationException $error) {
                    throw ValidationException::withMessages(['rows' => $row['field'].': '.implode(' ', $error->validator->errors()->all())]);
                }
            }
            if ($values['generate_permissions']) {
                foreach (['browse', 'read', 'edit', 'add', 'delete'] as $action) {
                    Permission::firstOrCreate(['key' => $action.'_'.$values['name']], ['table_name' => $values['name']]);
                }
            }
            if ($menu) {
                DB::table('menu_items')->insert(['menu_id' => $menu->id, 'title' => $values['display_name_plural'],
                    'route' => 'voyager.'.$values['slug'].'.index', 'url' => '', 'target' => '_self',
                    'icon_class' => $values['icon'] ?? null, 'parent_id' => null, 'status' => true,
                    'order' => ((int) DB::table('menu_items')->where('menu_id', $menu->id)->max('order')) + 1]);
            }
            return $id;
        }, 3);
    }
}
