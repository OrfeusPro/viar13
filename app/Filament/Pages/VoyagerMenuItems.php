<?php

namespace App\Filament\Pages;

use App\Filament\Bread\BreadRegistry;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ColorPicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema as DatabaseSchema;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class VoyagerMenuItems extends Page
{
    protected static ?string $slug = 'menu-items';

    protected static ?string $navigationLabel = 'Пункты меню';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bars-3';

    protected string $view = 'filament.pages.voyager-menu-items';

    public int $menuId = 0;

    public ?int $itemId = null;

    public string $locale = 'en';

    public bool $editing = false;

    public array $data = [];

    public array $titleTranslations = [];

    #[\Livewire\Attributes\Locked]
    public ?int $pendingItemDeletion = null;

    public bool $menuEditing = false;
    public ?int $editingMenuId = null;
    public string $menuName = '';

    public static function canAccess(): bool
    {
        return static::permitted('browse');
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
        $this->menuId = (int) (DB::table('menus')->where('name', 'admin')->value('id') ?: DB::table('menus')->value('id'));
        $requested = request()->query('menu');
        if (is_scalar($requested) && ctype_digit((string) $requested)
            && DB::table('menus')->where('id', (int) $requested)->exists()) {
            $this->menuId = (int) $requested;
        }
        $this->locale = config('voyager.multilingual.default', 'en');
    }

    public function hydrate(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    public function getTitle(): string | Htmlable
    {
        return 'Пункты меню';
    }

    public function menus(): array
    {
        return DB::table('menus')->orderBy('name')->pluck('name', 'id')->all();
    }

    public function items(): array
    {
        return DB::table('menu_items')->where('menu_id', $this->menuId)
            ->orderBy('parent_id')->orderBy('order')->get()->all();
    }

    public function menuTree(): array
    {
        $items = collect($this->items())->keyBy('id');
        $children = $items->groupBy(fn ($item) => $item->parent_id && $items->has($item->parent_id)
            && $item->parent_id !== $item->id ? $item->parent_id : 0);
        $seen = [];
        $build = function (object $item) use (&$build, &$seen, $children, $items): ?array {
            if (isset($seen[$item->id])) { return null; }
            $seen[$item->id] = true;
            return ['item' => $item, 'missing_parent' => $item->parent_id && ! $items->has($item->parent_id), 'children' => collect($children->get($item->id, []))
                ->map(fn ($child) => $build($child))->filter()->values()->all()];
        };
        $tree = [];
        foreach ($children->get(0, []) as $item) { $tree[] = $build($item); }
        // Keep legacy orphan/cyclic records visible rather than silently losing them.
        foreach ($items as $item) {
            if (! isset($seen[$item->id])) { $tree[] = $build($item); }
        }
        return $tree;
    }

    public function reorderItems(int $menu, array $tree): bool
    {
        abort_unless(static::permitted('edit'), 403);
        abort_unless($menu === $this->menuId, 422);
        $fail = static fn () => throw ValidationException::withMessages(['menuOrder' => 'Передайте все пункты выбранного меню один раз; максимум пять уровней вложенности.']);
        $rows = [];
        $walk = function (array $nodes, ?int $parent, int $depth) use (&$walk, &$rows, $fail): void {
            if (! array_is_list($nodes) || ($nodes !== [] && $depth > 5)) { $fail(); }
            foreach ($nodes as $index => $node) {
                if (! is_array($node) || array_diff(array_keys($node), ['id', 'children']) !== []
                    || ! isset($node['id']) || (! is_int($node['id']) && ! (is_string($node['id']) && ctype_digit($node['id'])))) { $fail(); }
                $id = (int) $node['id'];
                if ($id < 1 || isset($rows[$id]) || count($rows) >= 2000 || ! is_array($node['children'] ?? [])) { $fail(); }
                $rows[$id] = ['parent_id' => $parent, 'order' => $index + 1];
                $walk($node['children'] ?? [], $id, $depth + 1);
            }
        };
        $walk($tree, null, 1);
        $name = DB::transaction(function () use ($menu, $rows, $fail): string {
            $record = DB::table('menus')->where('id', $menu)->lockForUpdate()->first();
            abort_unless($record, 404);
            $existing = DB::table('menu_items')->where('menu_id', $menu)->lockForUpdate()->get(['id', 'parent_id'])->keyBy('id');
            $ids = $existing->keys()->map(fn ($id) => (int) $id)->all();
            if (count($ids) !== count($rows) || array_diff($ids, array_keys($rows)) !== []) { $fail(); }
            foreach ($rows as $id => $values) {
                // Recovery roots are displayed at the top without changing their stored parent.
                // An unrelated drag must not silently turn a legacy orphan into a real root.
                $originalParent = $existing[$id]->parent_id;
                if ($values['parent_id'] === null && $originalParent && ! $existing->has($originalParent)) {
                    $values['parent_id'] = $originalParent;
                }
                DB::table('menu_items')->where('menu_id', $menu)->where('id', $id)->update($values + ['updated_at' => now()]);
            }
            return $record->name;
        });
        Cache::forget('voyager_menu_'.$name);
        $this->resetValidation('menuOrder');
        Notification::make()->title('Порядок меню сохранён')->success()->send();
        return true;
    }

    public function selectMenu(int $id): void
    {
        abort_unless(DB::table('menus')->where('id', $id)->exists(), 404);
        $this->menuId = $id;
        $this->pendingItemDeletion = null;
        $this->editing = false;
        $this->itemId = null;
        $this->menuEditing = false;
    }

    public function openCreateMenu(): void
    {
        abort_unless(static::permitted('add'), 403);
        $this->editingMenuId = null;
        $this->menuName = '';
        $this->menuEditing = true;
        $this->editing = false;
        $this->resetValidation('menuName');
    }

    public function openRenameMenu(): void
    {
        abort_unless(static::permitted('edit'), 403);
        $name = DB::table('menus')->where('id', $this->menuId)->value('name');
        abort_unless($name !== null, 404);
        $this->editingMenuId = $this->menuId;
        $this->menuName = $name;
        $this->menuEditing = true;
        $this->editing = false;
        $this->resetValidation('menuName');
    }

    public function saveMenuName(): void
    {
        abort_unless($this->menuEditing && static::permitted($this->editingMenuId === null ? 'add' : 'edit'), 403);
        $this->menuName = trim($this->menuName);
        $this->validate(['menuName' => ['required', 'string', 'max:255', Rule::unique('menus', 'name')->ignore($this->editingMenuId)]]);
        $oldName = null;
        if ($this->editingMenuId !== null) {
            $oldName = DB::table('menus')->where('id', $this->editingMenuId)->value('name');
            abort_unless($oldName !== null, 404);
            DB::table('menus')->where('id', $this->editingMenuId)->update(['name' => $this->menuName, 'updated_at' => now()]);
            $this->menuId = $this->editingMenuId;
        } else {
            $this->menuId = DB::table('menus')->insertGetId(['name' => $this->menuName, 'created_at' => now(), 'updated_at' => now()]);
        }
        if ($oldName !== null) { Cache::forget('voyager_menu_'.$oldName); }
        Cache::forget('voyager_menu_'.$this->menuName);
        $this->menuEditing = false;
        $this->itemId = null;
        Notification::make()->title('Меню сохранено')->success()->send();
    }

    public function cancelMenu(): void
    {
        $this->menuEditing = false;
        $this->editingMenuId = null;
        $this->resetValidation('menuName');
    }

    public function deleteMenu(int $id): void
    {
        abort_unless(static::permitted('delete'), 403);
        abort_unless($id === $this->menuId, 422);
        $name = DB::transaction(function () use ($id): string {
            $menu = DB::table('menus')->where('id', $id)->lockForUpdate()->first();
            abort_unless($menu, 404);
            $itemIds = DB::table('menu_items')->where('menu_id', $id)->pluck('id');
            if (DatabaseSchema::hasTable('translations') && $itemIds->isNotEmpty()) {
                DB::table('translations')->where('table_name', 'menu_items')->whereIn('foreign_key', $itemIds)->delete();
            }
            DB::table('menu_items')->where('menu_id', $id)->delete();
            DB::table('menus')->where('id', $id)->delete();
            return $menu->name;
        });
        Cache::forget('voyager_menu_'.$name);
        $this->menuId = (int) (DB::table('menus')->where('name', 'admin')->value('id')
            ?: DB::table('menus')->orderBy('name')->orderBy('id')->value('id'));
        $this->itemId = null;
        $this->editing = false;
        $this->menuEditing = false;
        $this->editingMenuId = null;
        $this->menuName = '';
        $this->data = [];
        $this->resetValidation();
        $this->dispatch('close-modal', id: 'delete-selected-menu');
        Notification::make()->title('Меню удалено')->success()->send();
    }

    private function parentOptions(): array
    {
        $options = DB::table('menu_items')->where('menu_id', $this->menuId)
            ->when($this->itemId, fn ($query) => $query->where('id', '!=', $this->itemId))
            ->orderBy('title')->pluck('title', 'id')->all();
        $parent = $this->itemId ? DB::table('menu_items')->where('menu_id', $this->menuId)
            ->where('id', $this->itemId)->value('parent_id') : null;
        if ($parent && $parent !== $this->itemId && ! array_key_exists($parent, $options)) {
            $options[$parent] = 'Отсутствующий родитель #'.$parent.' (текущая связь)';
        }
        return $options;
    }

    public function form(Schema $schema): Schema
    {
        if (! $this->editing || ! static::permitted($this->itemId ? 'edit' : 'add')) {
            return $schema->statePath('data')->components([]);
        }
        return $schema->statePath('data')->components([
            TextInput::make('title')->label(fn (): string => 'Заголовок '.strtoupper($this->locale))
                ->required(fn (): bool => $this->locale === config('voyager.multilingual.default', 'en'))->maxLength(255),
            Select::make('parent_id')->label('Родитель')->options(fn (): array => $this->parentOptions())->nullable(),
            TextInput::make('order')->label('Порядок')->integer()->required(),
            Select::make('link_type')->label('Тип ссылки')->options(['url' => 'Статический URL', 'route' => 'Именованный маршрут'])
                ->required()->live(),
            TextInput::make('url')->label('URL')->maxLength(2048)
                ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get): bool => $get('link_type') === 'url'),
            TextInput::make('route')->label('Маршрут')->maxLength(255)
                ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get): bool => $get('link_type') === 'route'),
            Textarea::make('parameters')->label('Параметры маршрута (JSON)')->rows(3)->rules(['nullable', 'json'])
                ->placeholder('{"key": "value"}')
                ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get): bool => $get('link_type') === 'route'),
            Select::make('target')->label('Открывать')->options(['_self' => 'В этом окне', '_blank' => 'В новом окне']),
            TextInput::make('icon_class')->label('Иконка')->maxLength(255)
                ->hintAction(\Filament\Actions\Action::make('iconCatalogue')->label('Каталог иконок')
                    ->url(fn (): string => Compass::getUrl().'#fonts')->openUrlInNewTab()->visible(fn (): bool => Compass::canAccess()))
                ->helperText('Класс Voyager (voyager-photo) или Heroicon (heroicon-o-photo). Если оставить пустым, иконка выбирается по разделу.'),
            ColorPicker::make('color')->label('Цвет')->hexColor()->nullable()->rules(['nullable', 'regex:/^#[0-9a-fA-F]{6}$/D']),
            Toggle::make('status')->label('Активен'),
        ]);
    }

    public function openCreate(): void
    {
        abort_unless(static::permitted('add'), 403);
        abort_unless(DB::table('menus')->where('id', $this->menuId)->exists(), 404);
        $this->menuEditing = false;
        $this->itemId = null;
        $this->locale = config('voyager.multilingual.default', 'en');
        $this->editing = true;
        $this->titleTranslations = array_fill_keys(app(BreadRegistry::class)->locales(), null);
        $order = DB::table('menu_items')->whereNull('parent_id')->max('order');
        $this->resetValidation();
        $this->form->fill(['order' => $order === null ? 1 : (int) $order + 1, 'status' => true, 'target' => '_self', 'link_type' => 'url', 'color' => '#000000']);
    }

    public function openEdit(int $id): void
    {
        abort_unless(static::permitted('edit'), 403);
        $item = DB::table('menu_items')->where('menu_id', $this->menuId)->where('id', $id)->first();
        abort_unless($item, 404);
        $this->itemId = $id;
        $this->menuEditing = false;
        $this->editing = true;
        $this->locale = config('voyager.multilingual.default', 'en');
        $this->resetValidation();
        $this->fillItem($item);
    }

    public function changeLocale(string $locale): void
    {
        abort_unless($this->editing && static::permitted($this->itemId ? 'edit' : 'add'), 403);
        abort_unless(in_array($locale, app(BreadRegistry::class)->locales(), true), 422);
        abort_unless(in_array($this->locale, app(BreadRegistry::class)->locales(), true), 422);
        $this->titleTranslations[$this->locale] = $this->data['title'] ?? null;
        $this->locale = $locale;
        $this->data['title'] = $this->titleTranslations[$locale] ?? null;
        $this->resetValidation('data.title');
    }

    public function save(): void
    {
        abort_unless($this->editing && static::permitted($this->itemId ? 'edit' : 'add'), 403);
        abort_unless(DB::table('menus')->where('id', $this->menuId)->exists(), 404);
        $locales = app(BreadRegistry::class)->locales();
        abort_unless(in_array($this->locale, $locales, true), 422);
        $values = $this->form->getState();
        $default = config('voyager.multilingual.default', 'en');
        $this->titleTranslations[$this->locale] = $values['title'] ?? null;
        $rules = ['titleTranslations' => ['array:'.implode(',', $locales)]];
        $names = [];
        foreach ($locales as $language) {
            $rules['titleTranslations.'.$language] = [$language === $default ? 'required' : 'nullable', 'string', 'max:255'];
            $names['titleTranslations.'.$language] = 'Заголовок '.strtoupper($language);
        }
        validator(['titleTranslations' => $this->titleTranslations], $rules, [], $names)->validate();
        $values['title'] = $this->titleTranslations[$default];
        $savedId = DB::transaction(function () use ($values, $default, $locales): int {
            $parent = $values['parent_id'] ?? null;
            if ($parent !== null && ! DB::table('menu_items')->where('menu_id', $this->menuId)->where('id', $parent)->exists()) {
                $originalParent = $this->itemId ? DB::table('menu_items')->where('menu_id', $this->menuId)
                    ->where('id', $this->itemId)->value('parent_id') : null;
                if (! $originalParent || (int) $originalParent !== (int) $parent) {
                    throw ValidationException::withMessages(['data.parent_id' => 'Родитель не найден в этом меню.']);
                }
            }
            if ($parent !== null && $this->itemId !== null) {
                $ancestor = (int) $parent;
                $seen = [];
                while ($ancestor !== 0) {
                    if ($ancestor === $this->itemId || isset($seen[$ancestor])) {
                        throw ValidationException::withMessages(['data.parent_id' => 'Нельзя создать цикл в меню.']);
                    }
                    $seen[$ancestor] = true;
                    $ancestor = (int) DB::table('menu_items')->where('menu_id', $this->menuId)->where('id', $ancestor)->value('parent_id');
                }
            }
            $parameters = ($values['link_type'] ?? 'url') === 'route' ? ($values['parameters'] ?? null) : '';
            if (filled($parameters) && json_decode($parameters) === null && trim($parameters) !== 'null') {
                throw ValidationException::withMessages(['data.parameters' => 'Параметры должны быть JSON.']);
            }
            $attributes = collect($values)->only(['title', 'parent_id', 'order', 'url', 'route', 'parameters', 'target', 'icon_class', 'color', 'status'])->all();
            if (($values['link_type'] ?? 'url') === 'route') {
                $attributes['url'] = '';
            } else {
                $attributes['route'] = null;
                $attributes['parameters'] = '';
            }
            if ($this->itemId) {
                $this->requireItem();
                DB::table('menu_items')->where('id', $this->itemId)->update($attributes + ['updated_at' => now()]);
                $id = $this->itemId;
            } else {
                $id = DB::table('menu_items')->insertGetId($attributes + ['menu_id' => $this->menuId, 'created_at' => now(), 'updated_at' => now()]);
            }
            foreach ($locales as $language) {
                // Voyager skips empty translations, retaining an existing value.
                if ($language === $default || empty($this->titleTranslations[$language])) { continue; }
                DB::table('translations')->updateOrInsert([
                    'table_name' => 'menu_items', 'column_name' => 'title', 'foreign_key' => $id, 'locale' => $language,
                ], ['value' => $this->titleTranslations[$language]]);
            }
            return $id;
        });
        $this->itemId = $savedId;
        $this->forgetMenuCache();
        $this->editing = false;
        Notification::make()->title('Пункт меню сохранён')->success()->send();
    }

    public function cancel(): void
    {
        $this->editing = false;
        $this->itemId = null;
        $this->titleTranslations = [];
        $this->data = [];
        $this->resetValidation();
    }

    public function deleteItem(int $id): void
    {
        abort_unless(static::permitted('delete'), 403);
        DB::transaction(function () use ($id): void {
            $item = DB::table('menu_items')->where('menu_id', $this->menuId)->where('id', $id)->lockForUpdate()->first();
            abort_unless($item, 404);
            DB::table('translations')->where('table_name', 'menu_items')->where('foreign_key', $id)->delete();
            DB::table('menu_items')->where('id', $id)->delete();
        });
        $this->forgetMenuCache();
        $this->pendingItemDeletion = null;
        $this->dispatch('close-modal', id: 'delete-menu-item');
        if ($this->itemId === $id) { $this->cancel(); }
        Notification::make()->title('Пункт меню удалён')->success()->send();
    }

    public function requestDeleteItem(int $id): void
    {
        abort_unless(static::permitted('delete'), 403);
        abort_unless(DB::table('menu_items')->where('menu_id', $this->menuId)->where('id', $id)->exists(), 404);
        $this->pendingItemDeletion = $id;
        $this->dispatch('open-modal', id: 'delete-menu-item');
    }

    public function pendingDeleteItem(): ?object
    {
        return $this->pendingItemDeletion === null ? null : DB::table('menu_items')->where('menu_id', $this->menuId)
            ->where('id', $this->pendingItemDeletion)->first();
    }

    public function confirmDeleteItem(): void
    {
        abort_unless(static::permitted('delete'), 403);
        abort_unless($this->pendingItemDeletion !== null, 409);
        $this->deleteItem($this->pendingItemDeletion);
    }

    public function cancelDeleteItem(): void
    {
        $this->dismissDeleteItem();
        $this->dispatch('close-modal', id: 'delete-menu-item');
    }

    public function dismissDeleteItem(): void
    {
        $this->pendingItemDeletion = null;
    }

    private function fillItem(object $item): void
    {
        $locales = app(BreadRegistry::class)->locales();
        $this->titleTranslations = array_fill_keys($locales, null);
        foreach (DB::table('translations')->where('table_name', 'menu_items')->where('column_name', 'title')
            ->where('foreign_key', $item->id)->whereIn('locale', $locales)->get() as $translation) {
            $this->titleTranslations[$translation->locale] = $translation->value;
        }
        $this->titleTranslations[config('voyager.multilingual.default', 'en')] = $item->title;
        $this->form->fill(['link_type' => filled($item->route) ? 'route' : 'url', ...(array) $item,
            'title' => $this->titleTranslations[$this->locale] ?? null]);
    }

    private function requireItem(): void
    {
        abort_unless(DB::table('menu_items')->where('menu_id', $this->menuId)->where('id', $this->itemId)->exists(), 404);
    }

    private function forgetMenuCache(): void
    {
        $name = DB::table('menus')->where('id', $this->menuId)->value('name');
        if ($name !== null) { Cache::forget('voyager_menu_'.$name); }
    }

    private static function permitted(string $action): bool
    {
        if (! in_array($action, ['browse', 'add', 'edit', 'delete'], true) || ! DatabaseSchema::hasTable('permissions')) {
            return false;
        }
        $user = auth('filament')->user();

        return $user && method_exists($user, 'hasPermission') && $user->hasPermission($action . '_menus');
    }
}
