<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema as DatabaseSchema;
use Illuminate\Validation\ValidationException;
use Livewire\WithPagination;

class VoyagerUiTranslations extends Page
{
    use WithPagination;

    protected static ?string $slug = 'ui-translations';

    protected static ?string $navigationLabel = 'Переводы интерфейса';

    protected string $view = 'filament.pages.voyager-ui-translations';

    public string $group = '';

    public string $locale = 'ru';

    public ?string $key = null;

    public string $search = '';

    public array $data = [];

    public static function canAccess(): bool
    {
        $user = auth('filament')->user();

        return DatabaseSchema::hasTable('ltm_translations')
            && $user && method_exists($user, 'hasPermission') && $user->hasPermission('browse_admin');
    }

    public static function getNavigationItems(): array
    {
        if (! static::canAccess() || ! DatabaseSchema::hasTable('menu_items')) {
            return [];
        }
        $navigation = [NavigationItem::make('Переводы интерфейса')->url(static::getUrl())->sort(2)];
        $items = DB::table('menu_items')->where('status', 1)
            ->where('url', 'like', '/admin/translations/view/%')->orderBy('order')->get();
        foreach ($items as $item) {
            if (preg_match('~^/admin/translations/view/([a-zA-Z0-9_-]+)$~', (string) $item->url, $match)) {
                $navigation[] = NavigationItem::make((string) $item->title)
                    ->key('ui-translations-' . $item->id)
                    ->group('Переводы')
                    ->sort((int) $item->order)
                    ->url(static::getUrl() . '?group=' . urlencode($match[1]));
            }
        }

        return $navigation;
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
        $this->group = DB::table('ltm_translations')->where('group', 'homepage_new')->exists()
            ? 'homepage_new' : (string) DB::table('ltm_translations')->orderBy('group')->value('group');
        $requested = request()->query('group');
        if (is_string($requested) && in_array($requested, $this->groups(), true)) {
            $this->group = $requested;
        }
    }

    public function hydrate(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    public function getTitle(): string | Htmlable
    {
        return 'Переводы интерфейса';
    }

    public function groups(): array
    {
        return DB::table('ltm_translations')->distinct()->orderBy('group')->pluck('group')->all();
    }

    public function locales(): array
    {
        return DB::table('ltm_translations')->distinct()->orderBy('locale')->pluck('locale')->all();
    }

    public function selectGroup(string $group): void
    {
        abort_unless(in_array($group, $this->groups(), true), 404);
        $this->group = $group;
        $this->key = null;
        $this->resetPage();
    }

    public function selectLocale(string $locale): void
    {
        abort_unless(in_array($locale, $this->locales(), true), 404);
        $this->locale = $locale;
        if ($this->key !== null) {
            $this->openEdit($this->key);
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function entries(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $keys = DB::table('ltm_translations')->where('group', $this->group)->select('key')->distinct();
        $query = DB::query()->fromSub($keys, 'translation_keys')
            ->leftJoin('ltm_translations as selected', function ($join): void {
                $join->on('selected.key', '=', 'translation_keys.key')
                    ->where('selected.group', $this->group)
                    ->where('selected.locale', $this->locale);
            })
            ->select('translation_keys.key', 'selected.value');
        if ($this->search !== '') {
            $query->where('translation_keys.key', 'like', '%' . addcslashes($this->search, '%_\\') . '%');
        }

        return $query->orderBy('translation_keys.key')->paginate(30);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components($this->key === null ? [] : [
            Textarea::make('value')->label(strtoupper($this->locale) . ': ' . $this->key)
                ->rows(12)->maxLength(200000),
        ]);
    }

    public function openEdit(string $key): void
    {
        abort_unless(DB::table('ltm_translations')->where('group', $this->group)->where('key', $key)->exists(), 404);
        $this->key = $key;
        $this->form->fill(['value' => DB::table('ltm_translations')->where('group', $this->group)
            ->where('key', $key)->where('locale', $this->locale)->value('value')]);
    }

    public function cancel(): void
    {
        $this->key = null;
    }

    public function save(): void
    {
        abort_unless(static::canAccess() && $this->key !== null, 403);
        abort_unless(in_array($this->group, $this->groups(), true) && in_array($this->locale, $this->locales(), true), 404);
        abort_unless(DB::table('ltm_translations')->where('group', $this->group)->where('key', $this->key)->exists(), 404);
        if (! preg_match('/\A[a-zA-Z0-9_-]+\z/', $this->group)
            || ! preg_match('/\A[a-zA-Z0-9_-]+\z/', $this->locale)) {
            throw ValidationException::withMessages(['data.value' => 'Недопустимое имя словаря или языка.']);
        }

        $value = (string) ($this->form->getState()['value'] ?? '');
        $json = $this->group === '_json';
        $path = $json ? lang_path($this->locale . '.json') : lang_path($this->locale . DIRECTORY_SEPARATOR . $this->group . '.php');
        if (! File::isFile($path)) {
            throw ValidationException::withMessages(['data.value' => 'Файл словаря отсутствует. Публикация невозможна.']);
        }
        $contents = $json ? json_decode(File::get($path), true) : require $path;
        if (! is_array($contents)) {
            throw ValidationException::withMessages(['data.value' => 'Словарь имеет неверный формат.']);
        }
        $identity = ['locale' => $this->locale, 'group' => $this->group, 'key' => $this->key];
        $existing = DB::table('ltm_translations')->where($identity)->get();
        if ($existing->count() > 1) {
            throw ValidationException::withMessages(['data.value' => 'Найдены дубли ключа перевода. Исправьте данные перед сохранением.']);
        }
        $current = $json ? ($contents[$this->key] ?? null) : Arr::get($contents, $this->key);
        if ($current === $value && $existing->first()?->value === $value) {
            $this->key = null;
            Notification::make()->title('Перевод уже актуален')->success()->send();

            return;
        }
        if ($json) {
            $contents[$this->key] = $value;
        } else {
            Arr::set($contents, $this->key, $value);
        }
        $published = $json
            ? json_encode($contents, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n"
            : "<?php\n\nreturn " . var_export($contents, true) . ";\n";
        $temporary = tempnam(dirname($path), '.translation-');
        if ($temporary === false) {
            throw ValidationException::withMessages(['data.value' => 'Не удалось создать временный файл словаря.']);
        }
        try {
            if (File::put($temporary, $published) === false) {
                throw new \RuntimeException('Failed to write translation dictionary.');
            }
            DB::transaction(function () use ($path, $temporary, $value, $identity, $existing): void {
                if ($existing->isNotEmpty()) {
                    DB::table('ltm_translations')->where('id', $existing->first()->id)
                        ->update(['value' => $value, 'status' => 0, 'updated_at' => now()]);
                } else {
                    DB::table('ltm_translations')->insert($identity + [
                        'value' => $value, 'status' => 0, 'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
                if (! @rename($temporary, $path)) {
                    throw new \RuntimeException('Failed to publish translation dictionary.');
                }
            });
        } finally {
            if (File::exists($temporary)) {
                File::delete($temporary);
            }
        }
        $this->key = null;
        Notification::make()->title('Перевод сохранён и опубликован')->success()->send();
    }
}
