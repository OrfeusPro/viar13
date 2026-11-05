<?php

namespace App\Filament\Bread;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Validation\ValidationException;

class BreadCoordinates
{
    public function supported(Model $model, string $field): bool
    {
        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $field)) { return false; }
        $connection = $model->getConnection();
        if (! in_array($connection->getDriverName(), ['mysql', 'mariadb', 'sqlite'], true)) { return false; }
        foreach ($connection->getSchemaBuilder()->getColumns($model->getTable()) as $column) {
            if ($column['name'] === $field) { return in_array(strtolower($column['type_name']), ['point', 'geometry'], true); }
        }
        return false;
    }

    public function validateConfiguration(Model $model, string $field, array $details): void
    {
        if (! $this->supported($model, $field)) {
            throw ValidationException::withMessages(['details' => 'coordinates требует физическую колонку POINT или GEOMETRY. Текст/JSON не поддерживаются.']);
        }
        if (method_exists($model, 'getTranslatableAttributes') && in_array($field, $model->getTranslatableAttributes(), true)) {
            throw ValidationException::withMessages(['details' => 'Пространственная колонка coordinates не может быть переводимой.']);
        }
        if (isset($details['onChange'])) {
            throw ValidationException::withMessages(['details' => 'Legacy JavaScript onChange для координат ещё не перенесён.']);
        }
        if (array_key_exists('default', $details) && $details['default'] !== null && $details['default'] !== '') {
            throw ValidationException::withMessages(['details' => 'Используйте nullable POINT; автоматическая запись центра карты как default не поддерживается.']);
        }
    }

    public function read(Model $model, string $field): ?string
    {
        if (! $model->exists || ! $this->supported($model, $field)) { return null; }
        $column = $model->getConnection()->getQueryGrammar()->wrap($field);
        return $model->newQuery()->whereKey($model->getKey())->selectRaw('ST_AsText('.$column.') AS coordinate_wkt')->value('coordinate_wkt');
    }

    public function state(?string $wkt): ?array
    {
        if ($wkt === null || $wkt === '') { return ['lat' => null, 'lng' => null]; }
        $number = '[+-]?(?:\d+(?:\.\d*)?|\.\d+)(?:[eE][+-]?\d+)?';
        if (! preg_match('/^POINT\s*\(\s*('.$number.')\s+('.$number.')\s*\)$/i', trim($wkt), $matches)) { return null; }
        $lat = (float) $matches[2]; $lng = (float) $matches[1];
        if (! is_finite($lat) || ! is_finite($lng) || abs($lat) > 90 || abs($lng) > 180) { return null; }
        return ['lat' => $lat, 'lng' => $lng];
    }

    public function encode(Model $model, string $field, mixed $state, string $path, bool $required): ?Expression
    {
        $this->validateConfiguration($model, $field, []);
        $fail = fn () => throw ValidationException::withMessages([$path => 'Укажите широту от −90 до 90 и долготу от −180 до 180.']);
        if (! is_array($state) || array_diff(array_keys($state), ['lat', 'lng']) !== []) { $fail(); }
        $lat = $state['lat'] ?? null; $lng = $state['lng'] ?? null;
        if (($lat === null || $lat === '') && ($lng === null || $lng === '')) {
            if ($required) { $fail(); }
            return null;
        }
        foreach ([$lat, $lng] as $value) {
            if ((! is_int($value) && ! is_float($value) && ! is_string($value)) || ! is_numeric($value) || ! is_finite((float) $value)) { $fail(); }
        }
        if (abs((float) $lat) > 90 || abs((float) $lng) > 180) { $fail(); }
        // json_encode produces locale-independent numeric literals after validation.
        $lat = json_encode((float) $lat, JSON_THROW_ON_ERROR);
        $lng = json_encode((float) $lng, JSON_THROW_ON_ERROR);
        return $model->getConnection()->raw("ST_GeomFromText('POINT({$lng} {$lat})')");
    }

    public function summary(?string $wkt): string
    {
        $point = $this->state($wkt);
        return $point === null ? (string) $wkt : ($point['lat'] === null ? '' : $point['lat'].', '.$point['lng']);
    }
}
