<?php

namespace App\Filament\Bread;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BreadPivotKeys
{
    public function resolve(object $type, array $details): array
    {
        $keys = [
            'sourceKey' => $details['foreign_pivot_key'] ?? Str::singular($type->name).'_id',
            'targetKey' => $details['related_pivot_key'] ?? Str::singular($details['table'] ?? '').'_id',
            'parentKey' => $details['parent_key'] ?? 'id',
            'relatedKey' => $details['key'] ?? 'id',
        ];
        foreach ([$details['table'] ?? null, $details['pivot_table'] ?? null, $details['label'] ?? null, ...array_values($keys)] as $name) {
            if (! is_string($name) || ! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)
                || in_array($name, ['password', 'remember_token'], true)) {
                throw ValidationException::withMessages(['details' => 'Проверьте ключи связи и промежуточной таблицы.']);
            }
        }
        if (! Schema::hasColumns($type->name, [$keys['parentKey']])
            || ! Schema::hasColumns($details['table'], [$keys['relatedKey'], $details['label']])
            || ! Schema::hasColumns($details['pivot_table'], [$keys['sourceKey'], $keys['targetKey']])) {
            throw ValidationException::withMessages(['details' => 'Колонки ключей связи не найдены.']);
        }
        return $keys;
    }
}
