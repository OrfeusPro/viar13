<?php

namespace App\Filament\Bread;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BreadSelectRelation
{
    private function callableMethod(Model $model, string $method): bool
    {
        if (! method_exists($model, $method)) { return false; }
        $reflection = new \ReflectionMethod($model, $method);
        return $reflection->isPublic() && ! $reflection->isStatic() && $reflection->getNumberOfRequiredParameters() === 0;
    }

    public function configuration(Model $model, string $field, array $details): array
    {
        $fail = fn () => throw ValidationException::withMessages(['details' => 'Некорректная настройка select_multiple.relationship (метод, key, label, editablePivotFields).']);
        $config = $details['relationship'] ?? null;
        $method = Str::camel($field);
        if (! is_array($config) || ! $this->callableMethod($model, $method)) { $fail(); }
        $relation = $model->{$method}();
        if (! $relation instanceof BelongsToMany && ! $relation instanceof HasMany) { $fail(); }
        if (method_exists($model, 'getTranslatableAttributes') && in_array($field, $model->getTranslatableAttributes(), true)) { $fail(); }
        $related = $relation->getRelated();
        if ($model->getConnection()->getName() !== $related->getConnection()->getName()) { $fail(); }
        $schema = $related->getConnection()->getSchemaBuilder();
        foreach (['key', 'label'] as $name) {
            $column = $config[$name] ?? null;
            if (! is_string($column) || ! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column)
                || in_array($column, ['password', 'remember_token'], true) || ! $schema->hasColumn($related->getTable(), $column)) { $fail(); }
        }
        $pivot = $config['editablePivotFields'] ?? [];
        if (! is_array($pivot) || ! array_is_list($pivot) || count($pivot) !== count(array_unique($pivot, SORT_REGULAR))) { $fail(); }
        if ($pivot !== [] && ! $relation instanceof BelongsToMany) { $fail(); }
        foreach ($pivot as $column) {
            if (! is_string($column) || ! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column)
                || in_array($column, ['id', 'password', 'remember_token', 'created_at', 'updated_at', $relation->getForeignPivotKeyName(), $relation->getRelatedPivotKeyName()], true)
                || ! $model->getConnection()->getSchemaBuilder()->hasColumn($relation->getTable(), $column)) { $fail(); }
        }
        if ($pivot !== []) { $relation->withPivot($pivot); }
        $cast = $model->getCasts()[$field] ?? null;
        if ($cast !== null && ! in_array($cast, ['string', 'array', 'json'], true)) { $fail(); }
        $columns = $model->getConnection()->getSchemaBuilder()->getColumns($model->getTable());
        $storageType = collect($columns)->firstWhere('name', $field)['type_name'] ?? null;
        if (! in_array(strtolower((string) $storageType), ['json', 'text', 'longtext', 'mediumtext', 'tinytext', 'varchar', 'char', 'clob'], true)) { $fail(); }
        return compact('relation', 'related', 'method', 'config', 'pivot');
    }

    public function options(Model $model, string $field, array $details): array
    {
        $source = $this->configuration($model, $field, $details);
        $query = $source['related']->newQuery();
        $list = $source['method'].'List';
        if (method_exists($model, $list)) {
            if (! $this->callableMethod($model, $list)) { throw ValidationException::withMessages(['details' => 'Метод List должен быть публичным без обязательных аргументов.']); }
            $records = $model->{$list}();
            if (! $records instanceof Collection) { throw ValidationException::withMessages(['details' => 'Метод List должен возвращать коллекцию моделей.']); }
            $query->whereIn($source['config']['key'], $records->pluck($source['config']['key'])->all());
        }
        $options = [];
        $candidates = $query->get();
        if (isset($records)) {
            $order = array_map('strval', $records->pluck($source['config']['key'])->all());
            $candidates = $candidates->sortBy(fn ($record) => array_search((string) $record->{$source['config']['key']}, $order, true));
        }
        foreach ($candidates as $record) {
            $key = $record->{$source['config']['key']}; $label = $record->{$source['config']['label']};
            if (! is_scalar($key) || ! is_scalar($label) || (string) $key === '' || isset($options[(string) $key])) {
                throw ValidationException::withMessages(['details' => 'Ключи вариантов должны быть уникальными, подписи — текстовыми.']);
            }
            $options[(string) $key] = (string) $label;
        }
        return $options;
    }

    public function state(Model $model, string $field, array $details): ?array
    {
        $source = $this->configuration($model, $field, $details);
        $value = $model->exists ? $model->getRawOriginal($field) : null;
        if ($value === null || $value === '') {
            $items = $model->exists ? $source['relation']->get() : collect();
            if ($source['pivot'] === []) { return ['ids' => $items->pluck($source['config']['key'])->map(fn ($key) => (string) $key)->all()]; }
            return ['rows' => $items->map(fn ($item) => ['key' => (string) $item->{$source['config']['key']}, 'attributes' => array_combine($source['pivot'], array_map(fn ($column) => $item->pivot?->{$column}, $source['pivot']))])->all()];
        }
        try { $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR); } catch (\JsonException) { return null; }
        if (! is_array($value)) { return null; }
        if ($source['pivot'] === []) {
            if (! array_is_list($value) || array_filter($value, fn ($key) => ! is_string($key) && ! is_int($key)) !== []) { return null; }
            return ['ids' => array_map('strval', $value)];
        }
        $rows = [];
        foreach ($value as $key => $attributes) {
            if (! is_array($attributes) || array_diff(array_keys($attributes), $source['pivot']) !== []) { return null; }
            foreach ($attributes as $cell) { if ($cell !== null && ! is_scalar($cell)) { return null; } }
            $rows[] = ['key' => (string) $key, 'attributes' => array_replace(array_fill_keys($source['pivot'], null), $attributes)];
        }
        return ['rows' => $rows];
    }

    public function encode(Model $model, string $field, array $details, mixed $state, string $path, bool $required): string
    {
        $fail = fn () => throw ValidationException::withMessages([$path => 'Некорректные варианты или дополнительные значения связи.']);
        try { $source = $this->configuration($model, $field, $details); $options = $this->options($model, $field, $details); }
        catch (ValidationException) { $fail(); }
        $part = $source['pivot'] === [] ? 'ids' : 'rows';
        if (! is_array($state) || ! is_array($state[$part] ?? null) || array_diff(array_keys($state), [$part]) !== [] || count($state[$part]) > 500 || ($required && $state[$part] === [])) { $fail(); }
        $result = [];
        $seen = [];
        foreach ($state[$part] as $item) {
            if ($part === 'rows' && ! is_array($item)) { $fail(); }
            $key = $part === 'ids' ? $item : ($item['key'] ?? null);
            if ((! is_string($key) && ! is_int($key)) || ! array_key_exists((string) $key, $options) || isset($seen[(string) $key])) { $fail(); }
            $seen[(string) $key] = true;
            if ($part === 'ids') { $result[] = $key; continue; }
            if (! is_array($item) || array_diff(array_keys($item), ['key', 'attributes']) !== [] || ! is_array($item['attributes'] ?? null) || array_diff(array_keys($item['attributes']), $source['pivot']) !== []) { $fail(); }
            foreach ($item['attributes'] as $cell) { if (($cell !== null && ! is_scalar($cell)) || (is_float($cell) && ! is_finite($cell)) || (is_string($cell) && strlen($cell) > 50000)) { $fail(); } }
            $result[(string) $key] = array_replace(array_fill_keys($source['pivot'], null), $item['attributes']);
        }
        // No pivot sync: legacy type=select_multiple writes JSON to its column.
        if ($model->exists && $this->state($model, $field, $details) == $state && $model->getRawOriginal($field) !== null && $model->getRawOriginal($field) !== '') { return $model->getRawOriginal($field); }
        return json_encode($part === 'rows' && $result !== [] ? (object) $result : $result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
}
