<?php

namespace App\Filament\Bread;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BreadValidation
{
    private function fail(): never
    {
        throw ValidationException::withMessages(['details' => 'Неподдерживаемое или некорректное правило validation.']);
    }

    public function accepts(string $rule): bool
    {
        if (preg_match('/^(?:unique|exists):[a-zA-Z_][a-zA-Z0-9_]*(?:,[a-zA-Z_][a-zA-Z0-9_]*)?$/', $rule)) { return true; }
        if (preg_match('/^(?:same|different|required_with|required_without|required_with_all|required_without_all):[a-zA-Z_][a-zA-Z0-9_]*(?:,[a-zA-Z_][a-zA-Z0-9_]*)*$/', $rule)) { return true; }
        if (preg_match('/^(?:required_if|required_unless):[a-zA-Z_][a-zA-Z0-9_]*,[^|]+$/', $rule)) { return true; }
        if (str_starts_with($rule, 'regex:') || str_starts_with($rule, 'not_regex:')) {
            $pattern = substr($rule, strpos($rule, ':') + 1);
            if (strlen($pattern) > 1000 || @preg_match($pattern, '') === false) { $this->fail(); }
            return true;
        }
        return false;
    }

    public function compile(array $rules, Model $model, string $field, ?int $recordId = null, ?string $locale = null, string $prefix = 'data.'): array
    {
        $result = []; $schema = $model->getConnection()->getSchemaBuilder();
        foreach ($rules as $rule) {
            [$name, $params] = array_pad(explode(':', $rule, 2), 2, null);
            if (in_array($name, ['unique', 'exists'], true)) {
                $parts = explode(',', $params); $table = $parts[0]; $column = $parts[1] ?? $field;
                if (! $schema->hasColumn($table, $column) || in_array($column, ['password', 'remember_token'], true)) { $this->fail(); }
                if ($name === 'unique' && $table !== $model->getTable()) { $this->fail(); }
                $connection = $model->getConnection()->getName();
                if ($name === 'unique' && $locale !== null && $locale !== config('voyager.multilingual.default', 'en')
                    && method_exists($model, 'getTranslatableAttributes') && in_array($field, $model->getTranslatableAttributes(), true)) {
                    if ($column !== $field || ! $schema->hasColumns('translations', ['value', 'table_name', 'column_name', 'locale', 'foreign_key'])) { $this->fail(); }
                    $compiled = Rule::unique($connection.'.translations', 'value')->where('table_name', $table)->where('column_name', $field)->where('locale', $locale);
                    if ($recordId !== null) { $compiled->ignore($recordId, 'foreign_key'); }
                } else {
                    $compiled = $name === 'unique' ? Rule::unique($connection.'.'.$table, $column) : Rule::exists($connection.'.'.$table, $column);
                    if ($name === 'unique' && $recordId !== null) { $compiled->ignore($recordId, $model->getKeyName()); }
                }
                $result[] = $compiled; continue;
            }
            if (in_array($name, ['same', 'different', 'required_if', 'required_unless', 'required_with', 'required_without', 'required_with_all', 'required_without_all'], true)) {
                $parts = explode(',', $params);
                $count = in_array($name, ['required_if', 'required_unless'], true) ? 1 : count($parts);
                if (in_array($name, ['same', 'different'], true) && count($parts) !== 1) { $this->fail(); }
                for ($i = 0; $i < $count; $i++) {
                    if (! $schema->hasColumn($model->getTable(), $parts[$i]) || in_array($parts[$i], ['password', 'remember_token'], true)) { $this->fail(); }
                    $parts[$i] = $prefix.$parts[$i];
                }
                $result[] = $name.':'.implode(',', $parts); continue;
            }
            $result[] = $rule;
        }
        return $result;
    }
}
