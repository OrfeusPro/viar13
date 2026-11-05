<?php

namespace App\Filament\Bread;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class BreadPageLayout
{
    public function token(string $type, string|int $key): string
    {
        return json_encode([$type, (string) $key], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function catalog(array $details): array
    {
        $fail = fn () => throw ValidationException::withMessages(['details' => 'Некорректные источники layout_fields/block_model/form_model.']);
        $catalog = [];
        $fields = $details['layout_fields'] ?? [];
        if (! is_array($fields)) { $fail(); }
        foreach ($fields as $key => $title) {
            if (! is_string($title) || $title === '' || (string) $key === '') { $fail(); }
            $catalog[$this->token('Field', $key)] = ['type' => 'Field', 'key' => (string) $key, 'title' => $title.' ('.$key.')', 'icon' => 'layout-icon voyager-receipt'];
        }
        foreach (['Block' => 'block_model', 'Form' => 'form_model'] as $type => $setting) {
            if (! isset($details[$setting])) { continue; }
            $class = $details[$setting];
            if (! is_string($class) || ! class_exists($class) || ! is_subclass_of($class, Model::class)) { $fail(); }
            $model = new $class;
            $schema = Schema::connection($model->getConnectionName());
            if (! $schema->hasTable($model->getTable())) { $fail(); }
            foreach (['status', 'title', 'key', ...($type === 'Block' ? ['order'] : [])] as $column) {
                if (! $schema->hasColumn($model->getTable(), $column)) { $fail(); }
            }
            $query = $model->newQuery()->where('status', 1);
            if ($type === 'Block') { $query->orderBy('order'); }
            foreach ($query->get(['title', 'key']) as $item) {
                if (! is_scalar($item->key) || (string) $item->key === '' || ! is_string($item->title)) { $fail(); }
                $token = $this->token($type, $item->key);
                if (isset($catalog[$token])) { $fail(); }
                $catalog[$token] = ['type' => $type, 'key' => $item->key, 'title' => $item->title.' ('.$item->key.')', 'icon' => 'layout-icon '.($type === 'Block' ? 'voyager-puzzle' : 'voyager-window-list')];
            }
        }
        return $catalog;
    }

    public function document(mixed $value): ?array
    {
        if ($value === null || $value === '') { return []; }
        if (is_string($value)) {
            if (! str_starts_with(ltrim($value), '[')) { return null; }
            try { $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR); } catch (\JsonException) { return null; }
        }
        if (! is_array($value) || ! array_is_list($value)) { return null; }
        foreach ($value as $section) {
            if (! is_array($section) || ! in_array($section['type'] ?? null, ['Field', 'Block', 'Form'], true)
                || ! is_scalar($section['key'] ?? null) || ! is_string($section['title'] ?? null)
                || (isset($section['icon']) && ! is_string($section['icon']))) { return null; }
        }
        return $value;
    }

    public function state(mixed $value): mixed
    {
        $document = $this->document($value);
        return $document === null ? $value : ['rows' => array_map(fn ($item) => ['choice' => $this->token($item['type'], $item['key'])], $document)];
    }

    public function options(array $details, array $document): array
    {
        $options = [];
        foreach ($this->catalog($details) as $token => $item) { $options[$token] = $item['type'].' · '.$item['title']; }
        foreach ($document as $item) { $options[$this->token($item['type'], $item['key'])] ??= $item['type'].' · '.$item['title'].' [сохранённая секция]'; }
        return $options;
    }

    public function encode(array $details, mixed $original, mixed $state, string $path): string
    {
        $document = $this->document($original);
        $fail = fn () => throw ValidationException::withMessages([$path => 'Некорректный список секций страницы. Сохранение отменено.']);
        if ($document === null || ! is_array($state) || ! is_array($state['rows'] ?? null) || count($state['rows']) > 500) { $fail(); }
        try { $catalog = $this->catalog($details); } catch (ValidationException) { $fail(); }
        $existing = [];
        foreach ($document as $item) { $existing[$this->token($item['type'], $item['key'])][] = $item; }
        $result = [];
        foreach ($state['rows'] as $row) {
            if (! is_array($row) || array_keys($row) !== ['choice'] || ! is_string($row['choice'])) { $fail(); }
            $token = $row['choice'];
            if (! empty($existing[$token])) { $result[] = array_shift($existing[$token]); }
            elseif (isset($catalog[$token])) { $result[] = $catalog[$token]; }
            else { $fail(); }
        }
        if (is_string($original) && $original !== '' && $result === $document) { return $original; }
        return json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    public function summary(mixed $value): string
    {
        $document = $this->document($value);
        return $document === null ? (is_scalar($value) ? (string) $value : '')
            : implode('; ', array_map(fn ($item) => $item['type'].' · '.$item['title'], $document));
    }
}
