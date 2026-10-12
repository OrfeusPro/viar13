<?php

namespace App\Services\Admin;

use App\Filament\Bread\BreadRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use stdClass;

class BreadOrderingService
{
    public function configuration(stdClass $bread): ?array
    {
        $registry = app(BreadRegistry::class);
        if ($registry->isDedicated($bread) || ! $registry->model($bread)) { return null; }
        $details = json_decode($bread->details ?? '{}', true);
        $order = $details['order_column'] ?? null;
        $label = $details['order_display_column'] ?? null;
        $columns = collect(Schema::getColumns($bread->name))->keyBy('name');
        if (! is_string($order) || ! is_string($label) || ! $columns->has($order) || ! $columns->has($label)
            || in_array($order, ['id', 'password', 'remember_token'], true)
            || in_array($label, ['password', 'remember_token'], true)
            || ! in_array(strtolower($columns[$order]['type_name']), ['tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint'], true)
            || ! empty($columns[$order]['generation'])) { return null; }
        $row = collect($registry->rows($bread))->firstWhere('field', $label);
        return ['column' => $order, 'label' => $label, 'image' => $row?->type === 'image',
            'direction' => ($details['order_direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc'];
    }

    public function query(stdClass $bread, ?int $type = null): Builder
    {
        $model = app(BreadRegistry::class)->model($bread);
        abort_unless($model, 404);
        $query = $model->newQuery();
        $details = json_decode($bread->details ?? '{}', true);
        $scope = $details['scope'] ?? null;
        if (is_string($scope) && isset(app(BreadTypeSettingsService::class)->scopes($bread)[$scope])) { $query->{$scope}(); }
        if ($type !== null && Schema::hasColumn($bread->name, 'id_type')) { $query->where($bread->name.'.id_type', $type); }
        return $query;
    }

    public function snapshot($records, array $configuration): string
    {
        return hash('sha256', serialize([$configuration, $records->map(fn ($record) =>
            [$record->getKey(), $record->getRawOriginal($configuration['column']), $record->getRawOriginal($configuration['label'])])->all()]));
    }

    public function move(stdClass $bread, ?int $type, string $snapshot, int $id, int $position): array
    {
        $configuration = $this->configuration($bread);
        abort_unless($configuration, 404);
        $query = $this->query($bread, $type);
        return $query->getModel()->getConnection()->transaction(function () use ($bread, $type, $query, $configuration, $snapshot, $id, $position): array {
            $records = $query->orderBy($configuration['column'], $configuration['direction'])->orderBy($query->getModel()->getKeyName())->lockForUpdate()->get();
            if (! hash_equals($this->snapshot($records, $configuration), $snapshot)) {
                throw ValidationException::withMessages(['ordering' => 'Список изменился. Обновите страницу перед перемещением.']);
            }
            $ids = $records->modelKeys();
            $index = array_search($id, $ids, true);
            if ($index === false || $position < 0 || $position >= count($ids)) {
                throw ValidationException::withMessages(['ordering' => 'Выберите запись и позицию из текущего списка.']);
            }
            foreach ($records as $record) { abort_unless(app(BreadRegistry::class)->permitted($bread, 'edit', (int) $record->getKey()), 403); }
            if ($index !== $position) {
                array_splice($ids, $index, 1);
                array_splice($ids, $position, 0, [$id]);
                $lookup = $records->keyBy(fn ($record) => $record->getKey());
                foreach ($ids as $offset => $key) {
                    $record = $lookup[$key];
                    $record->{$configuration['column']} = $offset + 1;
                    if (! $record->save()) { throw ValidationException::withMessages(['ordering' => 'Модель отклонила сохранение порядка.']); }
                }
            }
            $fresh = $this->query($bread, $type)->orderBy($configuration['column'], $configuration['direction'])->orderBy($query->getModel()->getKeyName())->get();
            return ['snapshot' => $this->snapshot($fresh, $configuration)];
        });
    }
}
