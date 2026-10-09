<?php

namespace App\Filament\Bread;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use stdClass;

class BreadRegistry
{
    private ?array $permissionKeys = null;

    public function types(): array
    {
        if (! Schema::hasTable('data_types')) {
            return [];
        }

        return $this->translateMetadata(DB::table('data_types')->orderBy('display_name_plural')->get()->all(), 'data_types', ['display_name_singular', 'display_name_plural']);
    }

    public function type(string $slug): ?stdClass
    {
        if (! Schema::hasTable('data_types')) {
            return null;
        }

        $type = DB::table('data_types')->where('slug', $slug)->first();
        return $type ? $this->translateMetadata([$type], 'data_types', ['display_name_singular', 'display_name_plural'])[0] : null;
    }

    public function rows(stdClass $type): array
    {
        return $this->translateMetadata(DB::table('data_rows')->where('data_type_id', $type->id)->orderBy('order')->get()->all(), 'data_rows', ['display_name']);
    }

    private function translateMetadata(array $rows, string $table, array $columns): array
    {
        $locale = app()->getLocale();
        if ($rows === [] || $locale === config('voyager.multilingual.default', 'en') || ! Schema::hasTable('translations')) { return $rows; }
        $lookup = collect($rows)->keyBy('id');
        foreach (DB::table('translations')->where('table_name', $table)->where('locale', $locale)
            ->whereIn('column_name', $columns)->whereIn('foreign_key', $lookup->keys()->all())->get() as $translation) {
            if ($translation->value !== '') { $lookup[$translation->foreign_key]->{$translation->column_name} = $translation->value; }
        }
        return $rows;
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

    public function permitted(stdClass $type, string $action, ?int $recordId = null): bool
    {
        if (! in_array($action, ['browse', 'read', 'add', 'edit', 'delete'], true)) {
            return false;
        }

        $user = auth('filament')->user();
        if (! $user || ! method_exists($user, 'hasPermission')) {
            return false;
        }
        // Voyager UserPolicy grants read/edit of one's own profile only.
        if ($type->name === 'users'
            && app(BreadPolicyOptions::class)->normalize($type->policy_name ?? null) === BreadPolicyOptions::USER
            && in_array($action, ['read', 'edit'], true)
            && $recordId !== null && $recordId === (int) $user->getKey()) {
            return true;
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
        return in_array($type->name, ['orders', 'canvas_slider', 'image_alt_suggestions', 'seo_meta_suggestions'], true);
    }

    public function editableRows(stdClass $type, string $operation): array
    {
        $columns = $this->columns($type);

        return array_values(array_filter($this->rows($type), static fn (stdClass $row): bool =>
            (bool) $row->{$operation}
            && in_array($row->field, $columns, true)
            && ! in_array($row->field, ['id', 'created_at', 'updated_at', 'remember_token'], true)
            && ! ($row->type === 'hidden' && $row->field === 'password')
            && in_array($row->type, ['text', 'text_area', 'rich_text_box', 'code_editor', 'number', 'coordinates', 'checkbox', 'multiple_checkbox', 'select_dropdown', 'select_multiple', 'radio_btn', 'time', 'markdown_editor', 'adv_json', 'adv_page_layout', 'adv_fields_group', 'adv_select_dropdown_tree', 'hidden', 'date', 'timestamp', 'color', 'image', 'file', 'multiple_images', 'media_picker', 'password'], true)
            && ($row->type !== 'password' || $type->name === 'users')
        ));
    }

    public function hasFormFields(stdClass $type, string $operation): bool
    {
        return $this->editableRows($type, $operation) !== []
            || $this->childrenRows($type, $operation) !== []
            || $this->belongsToRows($type, $operation) !== []
            || $this->manyToManyRows($type, $operation) !== []
            || ($this->model($type) instanceof HasMedia && collect($this->rows($type))->contains(static fn ($row) => $row->type === 'adv_image' && (bool) $row->{$operation}))
            || ($operation === 'edit' && $this->model($type) instanceof HasMedia
                && collect($this->rows($type))->contains(static fn (stdClass $row): bool => $row->type === 'adv_media_files' && (bool) $row->edit));
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
        $locales = array_values(array_unique(array_merge(
            [config('voyager.multilingual.default', 'en')],
            array_filter(config('voyager.multilingual.locales', []), static fn (string $locale): bool => $locale !== 'uk')
        )));
        // Keep configured languages and Voyager's original order for the catalogue.
        $order = array_flip(['en', 'ru', 'lv', 'ee', 'lt', 'de', 'pl']);
        $default = config('voyager.multilingual.default', 'en');
        usort($locales, static fn (string $a, string $b): int =>
            ($a === $default ? -1 : ($order[$a] ?? 99)) <=> ($b === $default ? -1 : ($order[$b] ?? 99)));

        return $locales;
    }

    public function childrenRows(stdClass $type, string $operation): array
    {
        $relations = [];
        foreach ($this->rows($type) as $row) {
            if ($row->type !== 'relationship' || ! ($row->{$operation} ?? false)) { continue; }
            $details = json_decode($row->details ?: '{}', true) ?: [];
            if (! in_array($details['type'] ?? null, ['hasOne', 'hasMany'], true)) { continue; }
            try { app(\App\Services\Admin\BreadMetadataService::class)->validateRelationship($type, $details); }
            catch (\Illuminate\Validation\ValidationException) { continue; }
            $model = $this->model((object) ['name' => $details['table'], 'model_name' => $details['model']]);
            if ($model) { $relations[] = compact('row', 'details', 'model'); }
        }
        return $relations;
    }

    public function childLabels(array $relation, mixed $source): array
    {
        if ($source === null || $source === '') { return []; }
        $details = $relation['details'];
        $query = $relation['model']->newQuery()->where($details['column'], $source)->orderBy($relation['model']->getKeyName());
        if ($details['type'] === 'hasOne') { $query->limit(1); }
        return $query->pluck($details['label'])->all();
    }
}
