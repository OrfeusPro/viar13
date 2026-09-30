<?php

namespace App\Filament\Bread;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BreadLibraryReferences
{
    public static function replaceValue(mixed $value, string $source, string $target, bool $directory = false): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($item) => self::replaceValue($item, $source, $target, $directory), $value);
        }

        if ($directory && is_string($value) && str_starts_with($value, $source . '/')) {
            return $target . substr($value, strlen($source));
        }

        return $value === $source ? $target : $value;
    }

    private function replaceStored(?string $value, string $source, string $target, bool $directory): ?string
    {
        if ($value === $source || ($directory && is_string($value) && str_starts_with($value, $source . '/'))) {
            return self::replaceValue($value, $source, $target, $directory);
        }
        $decoded = json_decode($value ?? '', true);
        if (! is_array($decoded)) {
            return $value;
        }
        $updated = self::replaceValue($decoded, $source, $target, $directory);

        return $updated === $decoded ? $value : json_encode($updated, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    /** Run inside the relocation transaction; check every affected type before writing. */
    public function replace(string $source, string $target, BreadRegistry $registry, bool $directory = false): int
    {
        $updates = [];
        foreach ($registry->types() as $type) {
            if (! Schema::hasTable($type->name) || ! Schema::hasColumn($type->name, 'id')) {
                continue;
            }
            $columns = $registry->columns($type);
            foreach ($registry->rows($type) as $row) {
                if (! in_array($row->type, ['image', 'file', 'multiple_images', 'media_picker'], true)
                    || ! in_array($row->field, $columns, true)) {
                    continue;
                }
                // Include JSON-escaped slashes and Unicode by examining complete stored values.
                foreach (DB::table($type->name)->whereNotNull($row->field)->select('id', $row->field)->lazyById(200) as $record) {
                    $before = $record->{$row->field};
                    $after = $this->replaceStored($before, $source, $target, $directory);
                    if ($after !== $before) {
                        abort_unless($row->edit && $registry->permitted($type, 'edit'), 403, 'Нет права изменять все связанные разделы.');
                        $locked = DB::table($type->name)->where('id', $record->id)->lockForUpdate()->first([$row->field]);
                        if ($locked) {
                            $after = $this->replaceStored($locked->{$row->field}, $source, $target, $directory);
                            if ($after !== $locked->{$row->field}) {
                                $updates[] = [$type->name, $record->id, $row->field, $after];
                            }
                        }
                    }
                }
                if (Schema::hasTable('translations')) {
                    foreach (DB::table('translations')->where('table_name', $type->name)->where('column_name', $row->field)->lockForUpdate()->get() as $translation) {
                        $after = $this->replaceStored($translation->value, $source, $target, $directory);
                        if ($after !== $translation->value) {
                            abort_unless($row->edit && $registry->permitted($type, 'edit'), 403, 'Нет права изменять все связанные разделы.');
                            $updates[] = ['translations', $translation->id, 'value', $after];
                        }
                    }
                }
            }
        }
        foreach ($updates as [$table, $id, $field, $value]) {
            DB::table($table)->where('id', $id)->update([$field => $value]);
        }

        return count($updates);
    }
}
