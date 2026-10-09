<?php

namespace App\Filament\Bread;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BreadTreeOptions
{
    public function options(Model $model, string $field, array $details): array
    {
        $fail = fn () => throw ValidationException::withMessages(['details' => 'Проверьте options или relationship дерева (BelongsTo, label, where).']);
        $custom = $details['options'] ?? [];
        if (! is_array($custom) || array_filter($custom, fn ($label) => ! is_scalar($label)) !== []) { $fail(); }
        if (! isset($details['relationship'])) { return $custom; }
        $config = $details['relationship'];
        $method = Str::camel($field);
        if (! is_array($config) || ! $this->callableMethod($model, $method)) { $fail(); }
        $relation = $model->{$method}();
        if (! $relation instanceof BelongsTo || $relation->getOwnerKeyName() !== 'id' || $relation->getForeignKeyName() !== $field) { $fail(); }
        $related = $relation->getRelated();
        $label = $config['label'] ?? null;
        if (! is_string($label) || in_array($label, ['password', 'remember_token'], true) || ! Schema::hasColumns($related->getTable(), ['id', $label])) { $fail(); }
        $listMethod = $method.'List';
        if ($this->callableMethod($model, $listMethod)) {
            $records = $model->{$listMethod}();
            if (! $records instanceof \Illuminate\Support\Collection) { $fail(); }
            // Keep model scopes/SoftDeletes even when a custom list supplies candidates.
            $ids = $records->pluck('id')->all();
            $query = $related->newQuery()->whereIn('id', $ids);
        } else { $query = $related->newQuery(); }
        if (isset($config['where'])) {
            $where = $config['where'];
            if (! is_array($where) || count($where) !== 2 || ! is_string($where[0] ?? null) || ! Schema::hasColumn($related->getTable(), $where[0]) || ! is_scalar($where[1] ?? null)) { $fail(); }
            $query->where($where[0], $where[1]);
        }
        if (Schema::hasColumn($related->getTable(), 'order')) { $query->orderBy('order'); }
        $records = $query->orderBy('id')->get();
        $result = [];
        foreach ($custom as $key => $text) { $result[$key === '_empty_' ? '' : $key] = $text; }
        $result[0] = '-----';
        $hasParent = Schema::hasColumn($related->getTable(), 'parent_id');
        if (! $hasParent) {
            foreach ($records as $record) { $result[$record->id] = (string) $record->{$label}; }
            return $result;
        }
        $children = $records->groupBy(fn ($record) => empty($record->parent_id) ? 'root' : (string) $record->parent_id);
        $visited = [];
        $walk = function (string $parent, int $depth) use (&$walk, &$visited, &$result, $children, $label): void {
            if ($depth > 100) { return; }
            foreach ($children->get($parent, collect()) as $record) {
                if (isset($visited[$record->id])) { continue; }
                $visited[$record->id] = true;
                $result[$record->id] = str_repeat('— ', $depth).$record->{$label};
                $walk((string) $record->id, $depth + 1);
            }
        };
        $walk('root', 0);
        return $result;
    }

    private function callableMethod(Model $model, string $method): bool
    {
        if (! method_exists($model, $method)) { return false; }
        $reflection = new \ReflectionMethod($model, $method);
        return $reflection->isPublic() && ! $reflection->isStatic() && $reflection->getNumberOfRequiredParameters() === 0;
    }
}
