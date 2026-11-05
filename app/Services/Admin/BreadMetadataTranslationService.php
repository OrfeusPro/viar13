<?php

namespace App\Services\Admin;

use App\Filament\Bread\BreadRegistry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class BreadMetadataTranslationService
{
    public function existing(int $typeId, array $rowIds): array
    {
        if (! Schema::hasTable('translations')) { return []; }
        return DB::table('translations')->where(function ($query) use ($typeId, $rowIds) {
            $query->where(fn ($q) => $q->where('table_name', 'data_types')->where('foreign_key', $typeId)
                ->whereIn('column_name', ['display_name_singular', 'display_name_plural']))
                ->orWhere(fn ($q) => $q->where('table_name', 'data_rows')->whereIn('foreign_key', $rowIds)->where('column_name', 'display_name'));
        })->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
    }

    public function fingerprint(int $typeId, array $rowIds): string
    {
        return hash('sha256', json_encode($this->existing($typeId, $rowIds)));
    }

    public function save(User $actor, int $typeId, array $rowIds, array $drafts, string $snapshot): void
    {
        app(BreadMetadataService::class)->authorize($actor);
        if ($this->fingerprint($typeId, $rowIds) !== $snapshot) {
            throw ValidationException::withMessages(['metadataTranslations' => 'Переводы изменены в другой вкладке. Откройте редактор заново.']);
        }
        $locales = app(BreadRegistry::class)->locales();
        $base = config('voyager.multilingual.default', 'en');
        foreach ($drafts as $locale => $draft) {
            if (! in_array($locale, $locales, true)) { throw ValidationException::withMessages(['metadataTranslations' => 'Неизвестный язык.']); }
            if ($locale === $base) { continue; }
            $values = validator($draft, ['display_name_singular' => 'nullable|string|max:191',
                'display_name_plural' => 'nullable|string|max:191', 'rows' => 'array', 'rows.*' => 'nullable|string|max:191'])->validate();
            foreach (array_keys($values['rows'] ?? []) as $id) {
                if (! in_array((string) $id, array_map('strval', $rowIds), true)) {
                    throw ValidationException::withMessages(['metadataTranslations' => 'Подпись относится к другому разделу.']);
                }
            }
            if (! Schema::hasTable('translations')) {
                throw ValidationException::withMessages(['metadataTranslations' => 'Таблица translations недоступна.']);
            }
            foreach (['display_name_singular', 'display_name_plural'] as $column) {
                if (array_key_exists($column, $values)) { $this->write('data_types', $typeId, $column, $locale, $values[$column]); }
            }
            foreach ($values['rows'] ?? [] as $id => $label) { $this->write('data_rows', (int) $id, 'display_name', $locale, $label); }
        }
    }

    private function write(string $table, int $id, string $column, string $locale, ?string $value): void
    {
        $key = ['table_name' => $table, 'foreign_key' => $id, 'column_name' => $column, 'locale' => $locale];
        if ($value === null || $value === '') { DB::table('translations')->where($key)->delete(); }
        else { DB::table('translations')->updateOrInsert($key, ['value' => $value]); }
    }
}
