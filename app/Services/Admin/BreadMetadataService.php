<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Filament\Bread\BreadRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BreadMetadataService
{
    public const TYPES = ['text', 'text_area', 'rich_text_box', 'code_editor', 'number', 'coordinates', 'checkbox', 'multiple_checkbox',
        'select_dropdown', 'select_multiple', 'radio_btn', 'time', 'markdown_editor', 'adv_json', 'adv_page_layout', 'adv_fields_group', 'adv_image', 'adv_select_dropdown_tree', 'date', 'timestamp', 'color', 'image', 'file', 'multiple_images', 'media_picker', 'password', 'hidden', 'relationship'];
    public const FLAGS = ['required', 'browse', 'read', 'add', 'edit', 'delete'];

    public function authorize(User $actor): void
    {
        abort_unless($actor->hasPermission('browse_admin') && $actor->hasPermission('browse_bread'), 403);
    }

    public function fingerprint(object $row): string
    {
        return hash('sha256', json_encode((array) $row));
    }

    public function save(User $actor, int $typeId, ?int $rowId, array $input, ?string $fingerprint): int
    {
        $this->authorize($actor);
        return DB::transaction(function () use ($actor, $typeId, $rowId, $input, $fingerprint): int {
            $this->authorize($actor);
            $type = DB::table('data_types')->lockForUpdate()->find($typeId);
            abort_unless($type && app(BreadRegistry::class)->model($type), 409);
            $row = $rowId ? DB::table('data_rows')->where('data_type_id', $typeId)->lockForUpdate()->find($rowId) : null;
            abort_if($rowId && ! $row, 404);
            if ($row && $this->fingerprint($row) !== $fingerprint) {
                throw ValidationException::withMessages(['details' => 'Настройки изменены в другой вкладке. Откройте поле заново.']);
            }
            $rules = ['field' => ['required', 'regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/', 'max:191'],
                'display_name' => 'required|string|max:191', 'type' => ['required', Rule::in(array_unique([...self::TYPES, ...($row ? [$row->type] : [])]))],
                'order' => 'required|integer|min:0|max:100000', 'details' => 'nullable|string|max:50000'];
            foreach (self::FLAGS as $flag) { $rules[$flag] = 'required|boolean'; }
            $values = validator(array_merge($input, $row ? ['field' => $row->field] : []), $rules)->validate();
            if (DB::table('data_rows')->where('data_type_id', $typeId)->where('field', $values['field'])->when($rowId, fn ($q) => $q->where('id', '!=', $rowId))->exists()) {
                throw ValidationException::withMessages(['field' => 'Это поле уже настроено.']);
            }
            try { $details = json_decode($values['details'] ?: '{}', true, 512, JSON_THROW_ON_ERROR); }
            catch (\JsonException) { throw ValidationException::withMessages(['details' => 'Некорректный JSON.']); }
            if (! is_array($details) || ! str_starts_with(ltrim($values['details'] ?: '{}'), '{')) {
                throw ValidationException::withMessages(['details' => 'Ожидается JSON-объект.']);
            }
            if (in_array($values['field'], ['password', 'remember_token'], true)) {
                if ($values['browse'] || $values['read'] || ! in_array($values['type'], ['password', 'hidden'], true)) {
                    throw ValidationException::withMessages(['type' => 'Секретные поля нельзя отображать или переводить в обычный текст.']);
                }
            }
            if ($values['type'] === 'password' && ($type->name !== 'users' || $values['field'] !== 'password')) {
                throw ValidationException::withMessages(['type' => 'Пароль поддерживается только для users.password.']);
            }
            if ($values['type'] === 'relationship') {
                $this->validateRelationship($type, $details);
            } elseif ($values['type'] === 'adv_image') {
                if (! app(BreadRegistry::class)->model($type) instanceof \Spatie\MediaLibrary\HasMedia) {
                    throw ValidationException::withMessages(['type' => 'Для adv_image нужна модель с поддержкой Spatie Media Library.']);
                }
            } elseif (! Schema::hasColumn($type->name, $values['field']) && ! ($row && $row->type === $values['type'])) {
                throw ValidationException::withMessages(['field' => 'Сначала добавьте физическую колонку миграцией.']);
            }
            app(\App\Filament\Bread\BreadFormLayout::class)->validate($details);
            if (in_array($values['type'], ['time', 'date', 'timestamp'], true)) { app(\App\Filament\Bread\BreadTemporal::class)->validate($values['type'], $details); }
            if ($values['type'] === 'checkbox') { app(\App\Filament\Bread\BreadCheckbox::class)->validate($details); }
            if ($values['type'] === 'multiple_checkbox') { app(\App\Filament\Bread\BreadMultiCheckbox::class)->validate($details); }
            if (in_array($values['type'], ['image', 'multiple_images'], true)) { app(\App\Filament\Bread\BreadImageUpload::class)->validate($details); }
            if ($values['type'] === 'rich_text_box') { app(\App\Filament\Bread\BreadRichEditor::class)->validate($details); }
            app(\App\Filament\Bread\BreadSlug::class)->configuration($values['field'], $values['type'], $details, DB::table('data_rows')->where('data_type_id', $typeId)->get());
            app(\App\Filament\Bread\BreadFieldOptions::class)->validate($values['type'], $details);
            if (in_array($values['type'], \App\Filament\Bread\BreadFieldOptions::TYPES, true)) {
                foreach (['add', 'edit'] as $operation) {
                    app(\App\Filament\Bread\BreadValidation::class)->compile(app(\App\Filament\Bread\BreadFieldOptions::class)->rules($details, $operation), app(BreadRegistry::class)->model($type), $values['field']);
                }
            }
            if ($values['type'] === 'code_editor') {
                app(\App\Filament\Bread\BreadCodeEditor::class)->validate($details);
            }
            if ($values['type'] === 'coordinates') {
                app(\App\Filament\Bread\BreadCoordinates::class)->validateConfiguration(app(BreadRegistry::class)->model($type), $values['field'], $details);
            }
            if ($values['type'] === 'adv_page_layout') {
                $layout = app(\App\Filament\Bread\BreadPageLayout::class);
                $layout->catalog($details);
                if (array_key_exists('default', $details) && $layout->document($details['default']) === null) {
                    throw ValidationException::withMessages(['details' => 'default для layout должен быть JSON-массивом секций.']);
                }
            }
            if ($values['type'] === 'adv_json') {
                $jsonRows = app(\App\Filament\Bread\BreadJsonRows::class);
                $jsonRows->configuredFields($details);
                if (array_key_exists('default', $details) && $jsonRows->document($details['default']) === null) {
                    throw ValidationException::withMessages(['details' => 'default для adv_json должен содержать fields и rows.']);
                }
            }
            if ($values['type'] === 'adv_fields_group' && ! app(\App\Filament\Bread\BreadFieldsGroup::class)->document($details)) {
                throw ValidationException::withMessages(['details' => 'fields: укажите колонки с type text/number/textarea и label.']);
            }
            if ($values['type'] === 'adv_select_dropdown_tree') {
                if (isset($details['default']) && (! is_scalar($details['default']) || str_contains((string) $details['default'], '@'))) {
                    throw ValidationException::withMessages(['details' => 'Для дерева укажите обычное значение default; вызовы Class@method не поддерживаются.']);
                }
                app(\App\Filament\Bread\BreadTreeOptions::class)->options(app(BreadRegistry::class)->model($type), $values['field'], $details);
            }
            if (in_array($values['field'], ['id', 'created_at', 'updated_at'], true)
                && (($values['add'] && ! ($row?->add ?? false)) || ($values['edit'] && ! ($row?->edit ?? false)))) {
                throw ValidationException::withMessages(['edit' => 'Служебные поля недоступны для ручного редактирования.']);
            }
            if ($values['type'] === 'select_multiple' && isset($details['relationship'])) {
                app(\App\Filament\Bread\BreadSelectRelation::class)->options(app(BreadRegistry::class)->model($type), $values['field'], $details);
                if (isset($details['default'])) { throw ValidationException::withMessages(['details' => 'default для select_multiple.relationship не поддерживается; начальные значения берутся из связи.']); }
            }
            if ($values['type'] === 'select_dropdown') {
                app(\App\Filament\Bread\BreadDropdownOptions::class)->options(app(BreadRegistry::class)->model($type), $values['field'], $details);
            }
            if (in_array($values['type'], ['multiple_checkbox', 'select_multiple', 'radio_btn'], true) && ! ($values['type'] === 'select_multiple' && isset($details['relationship'])) && (! isset($details['options']) || ! is_array($details['options']) || array_filter($details['options'], fn ($label) => ! is_scalar($label)) !== [])) {
                throw ValidationException::withMessages(['details' => 'Укажите объект options с подписями вариантов.']);
            }
            $values['details'] = json_encode((object) $details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($row) { DB::table('data_rows')->where('id', $rowId)->update($values); return $rowId; }
            return DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, ...$values]);
        }, 3);
    }

    public function validateRelationship(object $type, array $details): void
    {
        $fail = fn () => throw ValidationException::withMessages(['details' => 'Проверьте тип связи, таблицу, column/key/label и pivot_table.']);
        if (! in_array($details['type'] ?? null, ['belongsTo', 'belongsToMany', 'hasOne', 'hasMany'], true)) { $fail(); }
        foreach (['label', 'column', 'key'] as $key) {
            if (in_array($details[$key] ?? null, ['password', 'remember_token'], true)) { $fail(); }
        }
        foreach (['table', 'label'] as $key) {
            if (! is_string($details[$key] ?? null) || ! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $details[$key])) { $fail(); }
        }
        if (! Schema::hasTable($details['table']) || ! Schema::hasColumn($details['table'], $details['label'])) { $fail(); }
        if ($details['type'] === 'belongsTo') {
            foreach (['column', 'key'] as $key) {
                if (! is_string($details[$key] ?? null) || ! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $details[$key])) { $fail(); }
            }
            if (! Schema::hasColumn($type->name, $details['column']) || ! Schema::hasColumn($details['table'], $details['key'])) { $fail(); }
        } elseif (in_array($details['type'], ['hasOne', 'hasMany'], true)) {
            foreach (['column', 'key'] as $key) {
                if (! is_string($details[$key] ?? null) || ! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $details[$key])) { $fail(); }
            }
            if (! Schema::hasColumn($details['table'], $details['column']) || ! Schema::hasColumn($type->name, $details['key'])) { $fail(); }
            $class = $details['model'] ?? null;
            if (! is_string($class) || ! is_subclass_of($class, \Illuminate\Database\Eloquent\Model::class)) { $fail(); }
            $reflection = new \ReflectionClass($class);
            if ($reflection->isAbstract() || ($reflection->getConstructor()?->getNumberOfRequiredParameters() ?? 0) > 0) { $fail(); }
            $related = (object) ['name' => $details['table'], 'model_name' => $details['model'] ?? null];
            if (! app(BreadRegistry::class)->model($related)) { $fail(); }
        } else {
            $pivot = $details['pivot_table'] ?? null;
            if (! is_string($pivot) || ! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $pivot) || ! Schema::hasTable($pivot)
                || ! Schema::hasColumn($details['table'], 'id')
                || ! Schema::hasColumns($pivot, [Str::singular($type->name).'_id', Str::singular($details['table']).'_id'])) { $fail(); }
        }
    }
}
