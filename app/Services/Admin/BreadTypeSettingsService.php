<?php

namespace App\Services\Admin;

use App\Filament\Bread\BreadRegistry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BreadTypeSettingsService
{
    public function scopes(object $type): array
    {
        $model = app(BreadRegistry::class)->model($type);
        if (! $model) { return []; }
        $scopes = [];
        foreach ((new \ReflectionClass($model))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (str_starts_with($method->name, 'scope') && strlen($method->name) > 5 && ! $method->isStatic()
                && $method->getNumberOfParameters() >= 1 && $method->getNumberOfRequiredParameters() <= 1) {
                $name = lcfirst(substr($method->name, 5));
                // Translation eager loading is not a row filter in the BREAD query.
                if (in_array($name, ['withTranslation', 'withTranslations'], true)) { continue; }
                $scopes[$name] = $name;
            }
        }
        return $scopes;
    }

    public function save(User $actor, int $id, array $input, string $fingerprint): void
    {
        app(BreadMetadataService::class)->authorize($actor);
        DB::transaction(function () use ($actor, $id, $input, $fingerprint): void {
            app(BreadMetadataService::class)->authorize($actor);
            $type = DB::table('data_types')->lockForUpdate()->find($id);
            abort_unless($type && app(BreadRegistry::class)->model($type), 409);
            if (app(BreadMetadataService::class)->fingerprint($type) !== $fingerprint) {
                throw ValidationException::withMessages(['slug' => 'Раздел изменён в другой вкладке. Откройте настройки заново.']);
            }
            $columns = array_values(array_diff(app(BreadRegistry::class)->columns($type), ['password', 'remember_token']));
            $values = validator($input, [
                'display_name_singular' => 'required|string|max:191', 'display_name_plural' => 'required|string|max:191',
                'slug' => ['required', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:[-_][a-z0-9]+)*$/', Rule::unique('data_types', 'slug')->ignore($id)],
                'icon' => 'nullable|string|max:191', 'description' => 'nullable|string|max:5000',
                'generate_permissions' => 'sometimes|boolean',
                'model_name' => ['sometimes', 'required', \Illuminate\Validation\Rule::in(array_unique([$type->model_name, ...array_keys(app(BreadCreationService::class)->models($type->name))]))],
                'order_column' => ['nullable', Rule::in($columns)], 'order_direction' => ['required', Rule::in(['asc', 'desc'])],
                'order_display_column' => ['nullable', Rule::in($columns)],
                'default_search_key' => ['nullable', Rule::in($columns)], 'scope' => ['nullable', Rule::in(array_keys($this->scopes($type)))],
            ])->validate();
            try { $details = json_decode($type->details ?: '{}', true, 512, JSON_THROW_ON_ERROR); }
            catch (\JsonException) { throw ValidationException::withMessages(['slug' => 'Существующие параметры раздела содержат некорректный JSON.']); }
            if (! is_array($details) || ! str_starts_with(ltrim($type->details ?: '{}'), '{')) {
                throw ValidationException::withMessages(['slug' => 'Существующие параметры раздела должны быть JSON-объектом.']);
            }
            foreach (['order_column', 'order_direction', 'default_search_key', 'scope', ... (array_key_exists('order_display_column', $input) ? ['order_display_column'] : [])] as $key) {
                $details[$key] = $values[$key] ?? null;
                unset($values[$key]);
            }
            $values['details'] = json_encode((object) $details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if (! \Illuminate\Support\Facades\Schema::hasColumn('data_types', 'generate_permissions')) { unset($values['generate_permissions']); }
            DB::table('data_types')->where('id', $id)->update($values);
            if ($values['generate_permissions'] ?? false) {
                foreach (['browse', 'read', 'add', 'edit', 'delete'] as $action) {
                    \App\Models\Permission::firstOrCreate(['key' => $action.'_'.$type->name], ['table_name' => $type->name]);
                }
            }
            if ($type->slug !== $values['slug'] && \Illuminate\Support\Facades\Schema::hasTable('menu_items')) {
                // Only references to this BREAD change; titles, icons, query strings and other menus stay intact.
                foreach (DB::table('menu_items')->lockForUpdate()->get(['id', 'route', 'url']) as $item) {
                    $changes = [];
                    if (preg_match('/^voyager\.'.preg_quote($type->slug, '/').'\.(index|create|edit|show|destroy)$/', (string) $item->route, $match)) {
                        $changes['route'] = 'voyager.'.$values['slug'].'.'.$match[1];
                    }
                    $url = preg_replace('~^/admin/'.preg_quote($type->slug, '~').'(?=/|\?|$)~', '/admin/'.$values['slug'], (string) $item->url);
                    if ($url !== (string) $item->url) { $changes['url'] = $url; }
                    if ($changes) { DB::table('menu_items')->where('id', $item->id)->update($changes); }
                }
            }
        }, 3);
    }
}
