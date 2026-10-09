<?php

namespace App\Services\Admin;

use App\Filament\Bread\BreadRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use stdClass;

class BreadTagCreationService
{
    public function enabled(array $details): bool
    {
        return in_array($details['taggable'] ?? false, [true, 1, '1', 'on'], true);
    }

    public function target(array $details, BreadRegistry $registry): ?stdClass
    {
        if (! $this->enabled($details)) { return null; }
        $target = collect($registry->types())->firstWhere('name', $details['table']);
        if (! $target || $registry->isDedicated($target) || filled($target->controller ?? null)
            || ! $registry->permitted($target, 'add') || ! $registry->model($target)) { return null; }
        $label = collect($registry->editableRows($target, 'add'))->firstWhere('field', $details['label']);
        return $label && in_array($label->type, ['text', 'text_area'], true) ? $target : null;
    }

    public function create(string $sourceSlug, int $rowId, ?int $recordId, array $data): int
    {
        // Fresh metadata and permissions: a mounted modal is not authorization.
        $registry = new BreadRegistry;
        $source = $registry->type($sourceSlug);
        $operation = $recordId === null ? 'add' : 'edit';
        abort_unless($source && $registry->permitted($source, $operation), 403);
        $sourceModel = $registry->model($source);
        abort_unless($sourceModel, 403);
        if ($recordId !== null) { $sourceModel->newQuery()->findOrFail($recordId); }
        $relation = collect($registry->manyToManyRows($source, $operation))->first(fn ($relation) => (int) $relation['row']->id === $rowId);
        abort_unless($relation, 403);
        $details = $relation['details'];
        $target = $this->target($details, $registry);
        abort_unless($target, 403);
        app(BreadMetadataService::class)->validateRelationship($source, $details);
        $model = $registry->model($target);
        $values = [];
        $rules = [];
        foreach ($registry->editableRows($target, 'add') as $row) {
            $options = json_decode($row->details ?: '{}', true) ?: [];
            $values[$row->field] = $row->field === $details['label'] ? ($data['label'] ?? null) : null;
            if ($row->field === $details['label'] && is_string($values[$row->field])) { $values[$row->field] = trim($values[$row->field]); }
            if ($row->field !== $details['label'] && array_key_exists('default', $options)
                && in_array($row->type, ['text', 'text_area', 'number', 'checkbox', 'select_dropdown', 'date', 'timestamp', 'color'], true)) {
                $values[$row->field] = $options['default'];
            }
            $rules[$row->field] = $options['validation']['add']['rule'] ?? $options['validation']['rule'] ?? ($row->required ? 'required' : 'nullable');
            if (in_array($row->type, \App\Filament\Bread\BreadFieldOptions::TYPES, true)) {
                $fieldOptions = app(\App\Filament\Bread\BreadFieldOptions::class);
                try {
                    $fieldOptions->validate($row->type, $options);
                    $compiled = app(\App\Filament\Bread\BreadValidation::class)->compile($fieldOptions->rules($options, 'add'), $model, $row->field, prefix: '');
                }
                catch (ValidationException) { throw ValidationException::withMessages(['label' => 'Параметры полей связанного раздела пока не поддерживаются.']); }
                $rules[$row->field] = array_merge(['nullable'], $compiled, $row->type === 'number' ? $fieldOptions->numberRules($options) : []);
            }

            if ($row->required) {
                $rules[$row->field] = array_merge(is_string($rules[$row->field]) ? explode('|', $rules[$row->field]) : (array) $rules[$row->field], ['required']);
            }
        }
        $labelRules = $rules[$details['label']];
        $rules[$details['label']] = array_merge(is_string($labelRules) ? explode('|', $labelRules) : (array) $labelRules, ['required', 'string', 'max:255']);
        try {
            validator($values, $rules)->validate();
        } catch (ValidationException $exception) {
            throw ValidationException::withMessages(['label' => 'Проверьте название и обязательные поля связанного раздела: '.implode(' ', $exception->validator->errors()->all())]);
        }
        // Voyager creates the target immediately; pivot changes wait for parent Save.
        try { return DB::transaction(function () use ($model, $values): int {
            $record = $model->newInstance();
            foreach ($values as $field => $value) {
                if ($value !== null) { $record->setAttribute($field, $value); }
            }
            $record->save();
            return (int) $record->getKey();
        }); } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            throw ValidationException::withMessages(['label' => 'Не удалось создать запись. Проверьте обязательные поля в связанном разделе.']);
        }
    }
}
