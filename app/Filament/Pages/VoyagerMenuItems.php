<?php

namespace App\Filament\Pages;

use App\Filament\Bread\BreadRegistry;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema as DatabaseSchema;
use Illuminate\Validation\ValidationException;

class VoyagerMenuItems extends Page
{
    protected static ?string $slug = 'menu-items';

    protected static ?string $navigationLabel = 'Пункты меню';

    protected string $view = 'filament.pages.voyager-menu-items';

    public int $menuId = 0;

    public ?int $itemId = null;

    public string $locale = 'en';

    public bool $editing = false;

    public array $data = [];

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
        abort_unless($this->menuId, 404);
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

    public function selectMenu(int $id): void
    {
        abort_unless(DB::table('menus')->where('id', $id)->exists(), 404);
        $this->menuId = $id;
        $this->editing = false;
        $this->itemId = null;
    }

    public function form(Schema $schema): Schema
    {
        if (! $this->editing) {
            return $schema->statePath('data')->components([]);
        }
        if ($this->locale !== config('voyager.multilingual.default', 'en')) {
            return $schema->statePath('data')->components([
                TextInput::make('title')->label('Заголовок ' . strtoupper($this->locale))->maxLength(255),
            ]);
        }

        return $schema->statePath('data')->components([
            TextInput::make('title')->label('Заголовок EN')->required()->maxLength(255),
            Select::make('parent_id')->label('Родитель')->options(fn (): array => DB::table('menu_items')
                ->where('menu_id', $this->menuId)->when($this->itemId, fn ($query) => $query->where('id', '!=', $this->itemId))
                ->orderBy('title')->pluck('title', 'id')->all())->nullable(),
            TextInput::make('order')->label('Порядок')->numeric()->required(),
            TextInput::make('url')->label('URL')->maxLength(2048),
            TextInput::make('route')->label('Route')->maxLength(255),
            TextInput::make('parameters')->label('Параметры JSON'),
            Select::make('target')->label('Открывать')->options(['_self' => 'В этом окне', '_blank' => 'В новом окне']),
            TextInput::make('icon_class')->label('Иконка')->maxLength(255),
            Toggle::make('status')->label('Активен'),
        ]);
    }

    public function openCreate(): void
    {
        abort_unless(static::permitted('add'), 403);
        $this->itemId = null;
        $this->locale = config('voyager.multilingual.default', 'en');
        $this->editing = true;
        $this->form->fill(['order' => 1, 'status' => true, 'target' => '_self']);
    }

    public function openEdit(int $id): void
    {
        abort_unless(static::permitted('edit'), 403);
        $item = DB::table('menu_items')->where('menu_id', $this->menuId)->where('id', $id)->first();
        abort_unless($item, 404);
        $this->itemId = $id;
        $this->editing = true;
        $this->fillItem($item);
    }

    public function changeLocale(string $locale): void
    {
        abort_unless(in_array($locale, app(BreadRegistry::class)->locales(), true), 422);
        abort_unless($this->itemId !== null || $locale === config('voyager.multilingual.default', 'en'), 422);
        $this->locale = $locale;
        if ($this->itemId !== null) {
            $item = DB::table('menu_items')->where('menu_id', $this->menuId)->where('id', $this->itemId)->first();
            abort_unless($item, 404);
            $this->fillItem($item);
        }
    }

    public function save(): void
    {
        abort_unless($this->editing && static::permitted($this->itemId ? 'edit' : 'add'), 403);
        $values = $this->form->getState();
        $default = config('voyager.multilingual.default', 'en');
        DB::transaction(function () use ($values, $default): void {
            if ($this->locale !== $default) {
                abort_unless($this->itemId, 422);
                $this->requireItem();
                $key = ['table_name' => 'menu_items', 'column_name' => 'title', 'foreign_key' => $this->itemId, 'locale' => $this->locale];
                $title = trim((string) ($values['title'] ?? ''));
                if ($title === '') {
                    DB::table('translations')->where($key)->delete();
                } else {
                    DB::table('translations')->updateOrInsert($key, ['value' => $title]);
                }
            } else {
                $parent = $values['parent_id'] ?? null;
                if ($parent !== null && ! DB::table('menu_items')->where('menu_id', $this->menuId)->where('id', $parent)->exists()) {
                    throw ValidationException::withMessages(['data.parent_id' => 'Родитель не найден в этом меню.']);
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
                $parameters = $values['parameters'] ?? null;
                if (filled($parameters) && json_decode($parameters) === null && trim($parameters) !== 'null') {
                    throw ValidationException::withMessages(['data.parameters' => 'Параметры должны быть JSON.']);
                }
                $attributes = collect($values)->only(['title', 'parent_id', 'order', 'url', 'route', 'parameters', 'target', 'icon_class', 'status'])->all();
                if ($this->itemId) {
                    $this->requireItem();
                    DB::table('menu_items')->where('id', $this->itemId)->update($attributes + ['updated_at' => now()]);
                } else {
                    $this->itemId = DB::table('menu_items')->insertGetId($attributes + ['menu_id' => $this->menuId, 'created_at' => now(), 'updated_at' => now()]);
                }
            }
        });
        $this->editing = false;
        Notification::make()->title('Пункт меню сохранён')->success()->send();
    }

    public function cancel(): void
    {
        $this->editing = false;
        $this->itemId = null;
    }

    public function deleteItem(int $id): void
    {
        abort_unless(static::permitted('delete'), 403);
        $item = DB::table('menu_items')->where('menu_id', $this->menuId)->where('id', $id)->first();
        abort_unless($item, 404);
        abort_if(DB::table('menu_items')->where('parent_id', $id)->exists(), 422);
        DB::transaction(function () use ($id): void {
            DB::table('translations')->where('table_name', 'menu_items')->where('foreign_key', $id)->delete();
            DB::table('menu_items')->where('id', $id)->delete();
        });
        Notification::make()->title('Пункт меню удалён')->success()->send();
    }

    private function fillItem(object $item): void
    {
        if ($this->locale === config('voyager.multilingual.default', 'en')) {
            $this->form->fill((array) $item);

            return;
        }
        $this->form->fill(['title' => DB::table('translations')->where([
            'table_name' => 'menu_items', 'column_name' => 'title',
            'foreign_key' => $item->id, 'locale' => $this->locale,
        ])->value('value')]);
    }

    private function requireItem(): void
    {
        abort_unless(DB::table('menu_items')->where('menu_id', $this->menuId)->where('id', $this->itemId)->exists(), 404);
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
