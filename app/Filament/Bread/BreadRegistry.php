<?php

namespace App\Filament\Bread;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use stdClass;

class BreadRegistry
{
    private ?array $permissionKeys = null;

    public function types(): array
    {
        if (! Schema::hasTable('data_types')) {
            return [];
        }

        return DB::table('data_types')->orderBy('display_name_plural')->get()->all();
    }

    public function type(string $slug): ?stdClass
    {
        if (! Schema::hasTable('data_types')) {
            return null;
        }

        return DB::table('data_types')->where('slug', $slug)->first();
    }

    public function rows(stdClass $type): array
    {
        return DB::table('data_rows')->where('data_type_id', $type->id)->orderBy('order')->get()->all();
    }

    public function model(stdClass $type): ?Model
    {
        $class = $type->model_name;
        if (! is_string($class) || ! class_exists($class) || ! is_subclass_of($class, Model::class)) {
            return null;
        }

        $model = new $class;

        if (strcasecmp($model->getTable(), $type->name) === 0 && Schema::hasTable($type->name)) {
            $model->setTable($type->name);
        }

        return $model->getTable() === $type->name && Schema::hasTable($type->name) ? $model : null;
    }

    public function columns(stdClass $type): array
    {
        return Schema::hasTable($type->name) ? Schema::getColumnListing($type->name) : [];
    }

    public function permitted(stdClass $type, string $action): bool
    {
        if (! in_array($action, ['browse', 'read', 'add', 'edit', 'delete'], true)) {
            return false;
        }

        $user = auth('filament')->user();
        if (! $user || ! method_exists($user, 'hasPermission')) {
            return false;
        }
        if ($this->permissionKeys === null) {
            $roleIds = $user->roles()->pluck('roles.id')->all();
            if ($user->role_id) {
                $roleIds[] = $user->role_id;
            }
            $this->permissionKeys = $roleIds === [] ? [] : DB::table('permissions')
                ->join('permission_role', 'permissions.id', '=', 'permission_role.permission_id')
                ->whereIn('permission_role.role_id', array_unique($roleIds))
                ->pluck('permissions.key')->all();
        }

        return in_array($action . '_' . $type->name, $this->permissionKeys, true);
    }

    public function isDedicated(stdClass $type): bool
    {
        return in_array($type->name, ['orders', 'canvas_slider', 'image_alt_suggestions'], true);
    }

    public function editableRows(stdClass $type, string $operation): array
    {
        $columns = $this->columns($type);

        return array_values(array_filter($this->rows($type), static fn (stdClass $row): bool =>
            (bool) $row->{$operation}
            && in_array($row->field, $columns, true)
            && ! in_array($row->field, ['id', 'created_at', 'updated_at'], true)
            && in_array($row->type, ['text', 'text_area', 'rich_text_box', 'code_editor', 'number', 'checkbox', 'multiple_checkbox', 'select_dropdown', 'date', 'timestamp', 'color', 'image', 'file', 'multiple_images', 'media_picker', 'password'], true)
            && ($row->type !== 'password' || $type->name === 'users')
        ));
    }

    public function belongsToRows(stdClass $type, string $operation): array
    {
        $columns = $this->columns($type);

        return array_values(array_filter($this->rows($type), static function (stdClass $row) use ($columns, $operation): bool {
            if ($row->type !== 'relationship' || ! $row->{$operation}) {
                return false;
            }
            $details = json_decode($row->details ?: '{}', true) ?: [];

            return ($details['type'] ?? null) === 'belongsTo'
                && in_array($details['column'] ?? null, $columns, true)
                && isset($details['table'], $details['key'], $details['label'])
                && Schema::hasTable($details['table'])
                && Schema::hasColumns($details['table'], [$details['key'], $details['label']]);
        }));
    }

    public function manyToManyRows(stdClass $type, string $operation): array
    {
        $sourceKey = Str::singular($type->name) . '_id';
        $relations = [];
        foreach ($this->rows($type) as $row) {
            if ($row->type !== 'relationship' || ! $row->{$operation}) {
                continue;
            }
            $details = json_decode($row->details ?: '{}', true) ?: [];
            if (($details['type'] ?? null) !== 'belongsToMany' || empty($details['table']) || empty($details['pivot_table']) || empty($details['label'])) {
                continue;
            }
            $targetKey = Str::singular($details['table']) . '_id';
            if (! Schema::hasTable($details['table']) || ! Schema::hasTable($details['pivot_table'])
                || ! Schema::hasColumns($details['table'], ['id', $details['label']])
                || ! Schema::hasColumns($details['pivot_table'], [$sourceKey, $targetKey])) {
                continue;
            }
            $relations[] = compact('row', 'details', 'sourceKey', 'targetKey');
        }

        return $relations;
    }

    public function locales(): array
    {
        return array_values(array_unique(array_merge(
            [config('voyager.multilingual.default', 'en')],
            array_filter(config('voyager.multilingual.locales', []), static fn (string $locale): bool => $locale !== 'uk')
        )));
    }
}
