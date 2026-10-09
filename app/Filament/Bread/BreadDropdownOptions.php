<?php

namespace App\Filament\Bread;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BreadDropdownOptions
{
    public function options(Model $model, string $field, array $details): array
    {
        $fail = fn () => throw ValidationException::withMessages(['details' => 'Проверьте options, default и источник dropdown (BelongsTo, key, label, where, List).']);
        $custom = $details['options'] ?? [];
        if (! is_array($custom) || array_filter($custom, fn ($label) => ! is_scalar($label)) !== []) { $fail(); }
        if (isset($details['default']) && (! is_scalar($details['default']) || (isset($details['relationship']) && str_contains((string) $details['default'], '@')))) { $fail(); }
        if (! isset($details['relationship'])) {
            if (! array_key_exists('options', $details)) { $fail(); }
            return $custom;
        }
        $config = $details['relationship']; $method = Str::camel($field);
        if (! is_array($config) || ! $this->callableMethod($model, $method)) { $fail(); }
        $relation = $model->{$method}();
        if (! $relation instanceof BelongsTo || $relation->getForeignKeyName() !== $field) { $fail(); }
        $related = $relation->getRelated();
        if ($model->getConnection()->getName() !== $related->getConnection()->getName()) { $fail(); }
        $schema = $related->getConnection()->getSchemaBuilder();
        foreach (['key', 'label'] as $name) {
            $column = $config[$name] ?? null;
            if (! is_string($column) || ! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column) || in_array($column, ['password', 'remember_token'], true) || ! $schema->hasColumn($related->getTable(), $column)) { $fail(); }
        }
        if ($config['key'] !== $relation->getOwnerKeyName()) { $fail(); }
        $where = $config['where'] ?? null;
        if ($where !== null && (! is_array($where) || ! array_is_list($where) || count($where) !== 2 || ! is_string($where[0]) || ! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $where[0]) || in_array($where[0], ['password', 'remember_token'], true) || ! $schema->hasColumn($related->getTable(), $where[0]) || ! is_scalar($where[1]))) { $fail(); }
        $query = $related->newQuery(); $list = $method.'List';
        if (method_exists($model, $list)) {
            if (! $this->callableMethod($model, $list)) { $fail(); }
            $records = $model->{$list}();
            if (! $records instanceof Collection) { $fail(); }
            $order = array_map('strval', $records->pluck($config['key'])->all());
            $query->whereIn($config['key'], $order);
        } elseif ($where !== null) { $query->where($where[0], $where[1]); }
        $records = $query->get();
        if (isset($order)) { $records = $records->sortBy(fn ($record) => array_search((string) $record->{$config['key']}, $order, true)); }
        $result = [];
        foreach ($custom as $key => $label) { $result[$key === '_empty_' ? '' : $key] = $label; }
        $seen = [];
        foreach ($records as $record) {
            $key = $record->{$config['key']}; $label = $record->{$config['label']};
            if (! is_scalar($key) || ! is_scalar($label) || (string) $key === '' || isset($seen[(string) $key])) { $fail(); }
            $seen[(string) $key] = true;
            $result[(string) $key] = (string) $label;
        }
        return $result;
    }

    private function callableMethod(Model $model, string $method): bool
    {
        if (! method_exists($model, $method)) { return false; }
        $reflection = new \ReflectionMethod($model, $method);
        return $reflection->isPublic() && ! $reflection->isStatic() && $reflection->getNumberOfRequiredParameters() === 0;
    }
}
