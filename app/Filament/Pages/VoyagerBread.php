<?php

namespace App\Filament\Pages;

use App\Filament\Bread\BreadRegistry;
use App\Services\SeoMetaGeneration\SeoMetaContextResolver;
use App\Services\SeoMetaGeneration\SeoMetaGenerator;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Bread\BreadFileUpload as FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema as DatabaseSchema;
use Illuminate\Validation\ValidationException;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\HasMedia;
use stdClass;

class VoyagerBread extends Page
{
    use WithPagination;
    use WithFileUploads;
    use \App\Filament\Bread\BreadAltPanel;

    protected static ?string $slug = 'bread/{type}';

    protected static bool $shouldRegisterNavigation = true;

    protected string $view = 'filament.pages.voyager-bread';

    public string $type = '';

    public string $locale = 'en';

    public ?int $recordId = null;

    public bool $editing = false;

    public ?int $viewId = null;

    public array $data = [];

    public array $localeDrafts = [];

    public $mediaUpload = null;

    public array $mediaUploads = [];

    public array $mediaReplacements = [];

    public array $mediaProperties = [];

    public array $selectedMedia = [];

    public ?string $libraryField = null;

    public string $libraryFolder = '';

    public int $libraryPage = 1;

    public string $libraryNewFolder = '';

    public ?string $libraryRenameFolder = null;

    public string $libraryFolderName = '';

    public ?string $librarySourceFile = null;

    public string $libraryTargetName = '';

    public string $libraryTargetFolder = '';

    public ?string $libraryCropFile = null;

    public array $libraryCrop = ['x' => 0, 'y' => 0, 'width' => 1, 'height' => 1];

    public string $search = '';

    public ?int $filterType = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public static function getNavigationItems(): array
    {
        $registry = app(BreadRegistry::class);
        if (! DatabaseSchema::hasTable('menu_items') || ! DatabaseSchema::hasTable('menus')) {
            return [];
        }
        $menuId = DB::table('menus')->where('name', 'admin')->value('id');
        if (! $menuId) {
            return [];
        }
        $items = DB::table('menu_items')->where('menu_id', $menuId)->where('status', 1)->orderBy('order')->get();
        $titles = $items->pluck('title', 'id');
        $types = collect($registry->types())->keyBy('slug');
        $navigation = [];
        foreach ($items as $item) {
            $slug = null;
            if (preg_match('/^voyager\.([a-z0-9_-]+)\.(?:index|edit)$/', (string) $item->route, $match)) {
                $slug = $match[1];
            } elseif (preg_match('~^/admin/([a-z0-9_-]+)(?:[/?].*)?$~', (string) $item->url, $match)) {
                $slug = $match[1];
            }
            $type = $slug ? $types->get($slug) : null;
            if (! $type || $registry->isDedicated($type) || ! $registry->model($type) || ! $registry->permitted($type, 'browse')) {
                continue;
            }
            $group = $item->parent_id ? trim((string) ($titles->get($item->parent_id) ?: 'Контент')) : 'Контент Voyager';
            $url = static::getUrl(['type' => $type->slug]);
            if ($type->name === 'menus') {
                $menu = preg_match('~^/admin/menus/(\d+)/builder$~', (string) $item->url, $match)
                    ? (int) $match[1] : null;
                $url = VoyagerMenuItems::getUrl() . ($menu ? '?menu=' . $menu : '');
            }
            if (preg_match('~(?:\?|&)type=(\d+)~', (string) $item->url, $filter)) {
                $url .= '?type=' . $filter[1];
            }
            $navigation[] = NavigationItem::make(trim((string) $item->title) ?: $type->display_name_plural)
                ->key('bread-' . $item->id)
                ->group($group)
                ->sort((int) $item->order)
                ->url($url);
        }

        return $navigation;
    }

    public function mount(string $type): void
    {
        $this->type = $type;
        $bread = $this->bread();
        abort_unless($bread && ! $this->registry()->isDedicated($bread) && $this->registry()->permitted($bread, 'browse'), 403);
        $this->locale = (string) config('voyager.multilingual.default', 'en');
        $filter = request()->query('type');
        $this->filterType = is_scalar($filter) && ctype_digit((string) $filter) ? (int) $filter : null;
    }

    public function hydrate(): void
    {
        $bread = $this->bread();
        abort_unless($bread && ! $this->registry()->isDedicated($bread) && $this->registry()->permitted($bread, 'browse'), 403);
    }

    public function getTitle(): string | Htmlable
    {
        return (string) ($this->bread()?->display_name_plural ?: $this->type);
    }

    public function bread(): ?stdClass
    {
        return $this->registry()->type($this->type);
    }

    public function form(Schema $schema): Schema
    {
        $bread = $this->bread();
        if (! $bread || ! $this->editing) {
            return $schema->statePath('data')->components([]);
        }

        $operation = $this->recordId ? 'edit' : 'add';
        $model = $this->registry()->model($bread);
        if (! $model) {
            return $schema->statePath('data')->components([]);
        }

        $translated = method_exists($model, 'getTranslatableAttributes') ? $model->getTranslatableAttributes() : [];
        $relationships = collect($this->registry()->belongsToRows($bread, $operation))
            ->mapWithKeys(function (stdClass $row): array {
                $details = json_decode($row->details ?: '{}', true) ?: [];

                return [$details['column'] => [$row, $details]];
            });
        $components = [];
        foreach ($this->registry()->editableRows($bread, $operation) as $row) {
            $details = json_decode($row->details ?: '{}', true) ?: [];
            $label = $row->display_name ?: $row->field;
            if ($relationships->has($row->field)) {
                continue;
            }
            $component = match ($row->type) {
                'text_area' => Textarea::make($row->field),
                'code_editor' => Textarea::make($row->field)->rows(16),
                'rich_text_box' => RichEditor::make($row->field),
                'number' => TextInput::make($row->field)->numeric(),
                'checkbox' => Toggle::make($row->field),
                'multiple_checkbox' => CheckboxList::make($row->field)->options($details['options'] ?? []),
                'select_dropdown' => Select::make($row->field)->options($details['options'] ?? []),
                'date' => DatePicker::make($row->field),
                'timestamp' => DateTimePicker::make($row->field),
                'color' => ColorPicker::make($row->field),
                'image' => FileUpload::make($row->field)->image()->openable()->imagePreviewHeight(240)->extraAttributes(['style' => 'max-width: 360px;'])->disk('public')->directory($bread->name . '/' . date('FY'))->maxSize(10240),
                'file' => FileUpload::make($row->field)->disk('public')->directory($bread->name . '/' . date('FY'))->maxSize(102400),
                'multiple_images' => FileUpload::make($row->field)->image()->multiple()->disk('public')->directory($bread->name . '/' . date('FY'))->maxSize(10240),
                'media_picker' => FileUpload::make($row->field)->image()->multiple()->reorderable()->disk('public')
                    ->directory($this->libraryRoot($details) . '/' . date('FY'))->maxSize(10240)
                    ->disabled(! (bool) ($details['allow_upload'] ?? true))->dehydrated()
                    ->acceptedFileTypes($details['allowed'] ?? ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/bmp', 'image/avif'])
                    ->minFiles((int) ($details['min'] ?? 0))->maxFiles((int) ($details['max'] ?? 100) ?: 100)
                    ->belowContent(View::make('filament.components.bread-file-library')->viewData(['field' => $row->field])),
                'password' => TextInput::make($row->field)->password()->dehydrated(fn ($state): bool => filled($state)),
                default => TextInput::make($row->field),
            };
            if ($component) {
                $component->label($label . (in_array($row->field, $translated, true) ? ' (' . strtoupper($this->locale) . ')' : ''));
                if (array_key_exists('default', $details)) {
                    $component->default($details['default']);
                }
                if (($this->locale === config('voyager.multilingual.default', 'en') || ! in_array($row->field, $translated, true)) && $row->required && ($row->type !== 'password' || $this->recordId === null)) {
                    $component->required();
                }
                $components[$row->field] = $component;
            }
        }
        if ($this->locale === config('voyager.multilingual.default', 'en')) {
            foreach ($relationships as $column => [$row, $details]) {
                $table = $details['table'];
                $key = $details['key'];
                $name = $details['label'];
                $components[$row->field] = Select::make($column)
                    ->label($row->display_name ?: $column)
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => DB::table($table)
                        ->where($name, 'like', '%' . $search . '%')->limit(50)->pluck($name, $key)->all())
                    ->getOptionLabelUsing(fn ($value): ?string => $value === null ? null : DB::table($table)->where($key, $value)->value($name));
            }
            if ($this->recordId !== null) {
                foreach ($this->registry()->manyToManyRows($bread, $operation) as $relation) {
                    $row = $relation['row'];
                    $details = $relation['details'];
                    $table = $details['table'];
                    $name = $details['label'];
                    $components[$row->field] = Select::make('__pivot_' . $row->id)
                        ->label($row->display_name ?: $table)
                        ->multiple()->searchable()
                        ->getSearchResultsUsing(fn (string $search): array => DB::table($table)
                            ->where($name, 'like', '%' . $search . '%')->limit(50)->pluck($name, 'id')->all())
                        ->getOptionLabelsUsing(fn (array $values): array => DB::table($table)->whereIn('id', $values)->pluck($name, 'id')->all());
                }
            }
        }

        if ($this->recordId !== null && $model instanceof HasMedia) {
            foreach ($this->registry()->rows($bread) as $row) {
                if ($row->type === 'adv_media_files' && $row->edit) {
                    $components[$row->field] = View::make('filament.components.bread-media-section')
                        ->viewData(['field' => $row->field]);
                }
            }
        }

        $groups = [];
        $tabTitle = 'Main';
        foreach ($this->registry()->rows($bread) as $row) {
            if (! $row->{$operation}) {
                continue;
            }
            $details = json_decode($row->details ?: '{}', true) ?: [];
            if (filled($details['tab_title'] ?? null)) {
                $tabTitle = $details['tab_title'];
            }
            if (isset($components[$row->field])) {
                $groups[$tabTitle][] = $components[$row->field];
            }
        }
        $tabs = [];
        foreach ($groups as $title => $fields) {
            $tabs[] = Tab::make($title)->components($fields);
        }

        return $schema->statePath('data')->components(count($tabs) > 1
            ? [Tabs::make('BREAD')->tabs($tabs)->persistTabInQueryString()->columnSpanFull()]
            : array_values($components));
    }

    public function editUrl(int $id): string
    {
        return VoyagerBreadEdit::getUrl(['type' => $this->type, 'record' => $id])
            . ($this->filterType !== null ? '?type=' . $this->filterType : '');
    }

    public function listUrl(): string
    {
        return VoyagerBread::getUrl(['type' => $this->type])
            . ($this->filterType !== null ? '?type=' . $this->filterType : '');
    }

    public function cloneRecord(int $id): void
    {
        $bread = $this->requireBread('add');
        abort_unless($bread->name === 'gallery_items' && $this->registry()->permitted($bread, 'edit'), 403);
        $model = $this->registry()->model($bread);
        abort_unless($model, 409);
        $copy = DB::transaction(function () use ($bread, $model, $id) {
            $source = $model->newQuery()->findOrFail($id);
            $copy = $source->replicate()->unsetRelations();
            $rows = collect($this->registry()->rows($bread))->keyBy('field');
            foreach ($copy->getAttributes() as $field => $value) {
                if (in_array($rows->get($field)?->type, ['image', 'multiple_images', 'file'], true)) {
                    $copy->setAttribute($field, null);
                } elseif (in_array($field, ['title', 'name', 'slug', 'key'], true)) {
                    $copy->setAttribute($field, $value . ' (clone)');
                }
            }
            $copy->save();

            return $copy;
        });
        Notification::make()->title('Товар клонирован')->success()->send();
        $this->redirect($this->editUrl((int) $copy->getKey()));
    }

    public function seoMetaTarget(): ?array
    {
        $bread = $this->bread();
        $model = $bread ? $this->registry()->model($bread) : null;
        $target = $model ? app(SeoMetaContextResolver::class)->targetForModel(get_class($model)) : null;
        if (! $target || ($target['voyager_slug'] ?? null) !== $this->type) {
            return null;
        }
        $fields = array_column($this->registry()->editableRows($bread, 'edit'), 'field');

        return in_array($target['title_field'], $fields, true) && in_array($target['description_field'], $fields, true)
            ? $target : null;
    }

    public function generateSeoMeta(): void
    {
        $bread = $this->requireBread('edit');
        $target = $this->seoMetaTarget();
        abort_unless($this->editing && $this->recordId !== null && $target, 409);
        $record = $this->registry()->model($bread)->newQuery()->findOrFail($this->recordId);
        $resolver = app(SeoMetaContextResolver::class);
        try {
            $result = app(SeoMetaGenerator::class)->generate($record, $resolver->supportedLocales(), true);
            $translated = method_exists($record, 'getTranslatableAttributes') ? $record->getTranslatableAttributes() : [];
            DB::transaction(function () use ($bread, $record, $result, $target, $translated): void {
                foreach ($result['locales'] as $locale => $values) {
                    if (! in_array($locale, $this->registry()->locales(), true)) {
                        continue;
                    }
                    foreach (['meta_title' => $target['title_field'], 'meta_description' => $target['description_field']] as $key => $field) {
                        if (! isset($values[$key]) || ! is_string($values[$key])) {
                            continue;
                        }
                        if ($locale === config('voyager.multilingual.default', 'en')) {
                            $record->setAttribute($field, $values[$key]);
                        } elseif (in_array($field, $translated, true)) {
                            DB::table('translations')->updateOrInsert([
                                'table_name' => $bread->name, 'column_name' => $field,
                                'foreign_key' => $record->getKey(), 'locale' => $locale,
                            ], ['value' => $values[$key]]);
                        }
                    }
                }
                $record->save();
            });
            $values = $result['locales'][$this->locale] ?? [];
            foreach (['meta_title' => $target['title_field'], 'meta_description' => $target['description_field']] as $key => $field) {
                if (isset($values[$key])) {
                    $this->data[$field] = $values[$key];
                }
            }
            Notification::make()->title('SEO-метаданные сохранены')->success()->send();
        } catch (\Throwable $exception) {
            report($exception);
            Notification::make()->title('Не удалось сгенерировать SEO-метаданные')
                ->body('Проверьте настройку генератора и журнал ошибок.')->danger()->send();
        }
    }

    public function openCreate(): void
    {
        $this->localeDrafts = [];
        $this->altPanelOpen = false;
        $bread = $this->requireBread('add');
        abort_unless($this->registry()->model($bread), 409);
        abort_unless($this->registry()->hasFormFields($bread, 'add'), 409);
        $this->recordId = null;
        $this->viewId = null;
        $this->mediaUpload = null;
        $this->mediaUploads = [];
        $this->mediaReplacements = [];
        $this->locale = config('voyager.multilingual.default', 'en');
        $this->selectedMedia = [];
        $this->libraryField = null;
        $this->editing = true;
        $this->form->fill();
    }

    public function openEdit(int $id): void
    {
        $this->localeDrafts = [];
        $this->altPanelOpen = false;
        $bread = $this->requireBread('edit');
        $model = $this->registry()->model($bread);
        abort_unless($model, 409);
        abort_unless($this->registry()->hasFormFields($bread, 'edit'), 409);
        $record = $model->newQuery()->findOrFail($id);
        $this->mediaUpload = null;
        $this->mediaUploads = [];
        $this->mediaReplacements = [];
        $this->recordId = $id;
        $this->selectedMedia = [];
        $this->libraryField = null;
        $this->viewId = null;
        $this->editing = true;
        $this->fillRecord($record);
        $this->fillMediaProperties($record);
    }

    public function changeLocale(string $locale): void
    {
        abort_unless(in_array($locale, $this->registry()->locales(), true), 422);
        $this->libraryField = null;
        if ($this->viewId !== null) {
            $this->requireBread('read');
            $this->locale = $locale;

            return;
        }
        if ($this->recordId !== null) {
            $bread = $this->requireBread('edit');
            $record = $this->registry()->model($bread)?->newQuery()->findOrFail($this->recordId);
            abort_unless($record, 409);
            $previous = $this->data;
            $this->localeDrafts[$this->locale] = $previous;
            $this->locale = $locale;
            $this->fillRecord($record);
            $translated = method_exists($record, 'getTranslatableAttributes') ? $record->getTranslatableAttributes() : [];
            $values = $this->localeDrafts[$locale] ?? $this->data;
            // Shared values survive switches; translation drafts remain per language.
            foreach ($previous as $field => $value) {
                if (! in_array($field, $translated, true)) {
                    $values[$field] = $value;
                }
            }
            $this->form->fill($values);
        } else {
            $this->locale = $locale;
        }
    }

    public function save(): void
    {
        $bread = $this->requireBread($this->recordId ? 'edit' : 'add');
        abort_unless($this->editing && $this->registry()->model($bread), 409);
        abort_unless($this->registry()->hasFormFields($bread, $this->recordId ? 'edit' : 'add'), 409);
        $default = config('voyager.multilingual.default', 'en');
        abort_unless(in_array($this->locale, $this->registry()->locales(), true), 422);
        abort_if($this->recordId === null && $this->locale !== $default, 422);

        $model = $this->registry()->model($bread);
        $allowed = collect($this->registry()->editableRows($bread, $this->recordId ? 'edit' : 'add'));
        $translated = method_exists($model, 'getTranslatableAttributes') ? $model->getTranslatableAttributes() : [];
        $values = $this->form->getState();
        $original = $this->recordId ? $model->newQuery()->findOrFail($this->recordId) : null;
        foreach ($allowed->where('type', 'media_picker') as $row) {
            $details = json_decode($row->details ?: '{}', true) ?: [];
            $existing = json_decode((string) $original?->getAttribute($row->field), true) ?: [];
            foreach ((array) ($values[$row->field] ?? []) as $path) {
                if (! is_string($path)) {
                    throw ValidationException::withMessages(['data.' . $row->field => 'Некорректный путь изображения.']);
                }
                if (! in_array($path, $existing, true)) {
                    $this->validateLibraryFile($row->field, $path, $details);
                }
            }
        }
        $pivots = [];
        if ($this->recordId !== null && $this->locale === $default) {
            foreach ($this->registry()->manyToManyRows($bread, 'edit') as $relation) {
                $key = '__pivot_' . $relation['row']->id;
                $pivots[] = [$relation, array_values(array_unique(array_map('intval', (array) ($values[$key] ?? []))))];
            }
        }
        $relationshipColumns = collect($this->registry()->belongsToRows($bread, $this->recordId ? 'edit' : 'add'))
            ->map(fn (stdClass $row): ?string => (json_decode($row->details ?: '{}', true) ?: [])['column'] ?? null)
            ->filter()->all();
        $values = array_intersect_key($values, array_flip(array_merge($allowed->pluck('field')->all(), $relationshipColumns)));
        if ($this->locale === $default) {
            foreach ($this->registry()->belongsToRows($bread, $this->recordId ? 'edit' : 'add') as $relation) {
                $details = json_decode($relation->details ?: '{}', true) ?: [];
                $column = $details['column'] ?? null;
                if ($column && filled($values[$column] ?? null)
                    && ! DB::table($details['table'])->where($details['key'], $values[$column])->exists()) {
                    throw ValidationException::withMessages(['data.' . $column => 'Связанная запись не найдена.']);
                }
            }
        }

        DB::transaction(function () use ($bread, $model, $values, $default, $allowed, $pivots, $translated): void {
            $record = $this->recordId ? $model->newQuery()->lockForUpdate()->findOrFail($this->recordId) : $model->newInstance();
            if ($this->locale === $default) {
                foreach ($values as $field => $value) {
                    $row = $allowed->firstWhere('field', $field);
                    if ($row?->type === 'password') {
                        $value = Hash::make($value);
                    }
                    if (in_array($row?->type, ['multiple_images', 'media_picker'], true)) {
                        $value = json_encode(array_values($value ?? []), JSON_UNESCAPED_SLASHES);
                    } elseif ($row?->type === 'multiple_checkbox') {
                        $value = json_encode(array_combine((array) $value, (array) $value), JSON_UNESCAPED_SLASHES);
                    }
                    $record->setAttribute($field, $value);
                }
                $record->save();
                $this->recordId = (int) $record->getKey();
                foreach ($pivots as [$relation, $ids]) {
                    $details = $relation['details'];
                    $target = $details['table'];
                    if ($ids !== [] && DB::table($target)->whereIn('id', $ids)->count() !== count($ids)) {
                        throw ValidationException::withMessages(['data.__pivot_' . $relation['row']->id => 'Связанные записи не найдены.']);
                    }
                    $pivot = $details['pivot_table'];
                    $sourceKey = $relation['sourceKey'];
                    $targetKey = $relation['targetKey'];
                    DB::table($pivot)->where($sourceKey, $record->getKey())->delete();
                    foreach ($ids as $id) {
                        $attributes = [$sourceKey => $record->getKey(), $targetKey => $id];
                        if (DatabaseSchema::hasColumn($pivot, 'created_at')) {
                            $attributes['created_at'] = now();
                        }
                        if (DatabaseSchema::hasColumn($pivot, 'updated_at')) {
                            $attributes['updated_at'] = now();
                        }
                        DB::table($pivot)->insert($attributes);
                    }
                }
            } else {
                foreach ($values as $field => $value) {
                    if (! in_array($field, $translated, true)) {
                        $row = $allowed->firstWhere('field', $field);
                        if (! $row) {
                            continue;
                        }
                        if ($row->type === 'password') {
                            $value = Hash::make($value);
                        } elseif (in_array($row->type, ['multiple_images', 'media_picker'], true)) {
                            $value = json_encode(array_values($value ?? []), JSON_UNESCAPED_SLASHES);
                        } elseif ($row->type === 'multiple_checkbox') {
                            $value = json_encode(array_combine((array) $value, (array) $value), JSON_UNESCAPED_SLASHES);
                        }
                        $record->setAttribute($field, $value);
                        continue;
                    }
                    $key = ['table_name' => $bread->name, 'column_name' => $field, 'foreign_key' => $record->getKey(), 'locale' => $this->locale];
                    if (blank($value)) {
                        DB::table('translations')->where($key)->delete();
                    } else {
                        DB::table('translations')->updateOrInsert($key, ['value' => $value]);
                    }
                }
                $record->save();
            }
        });
        Notification::make()->title('Сохранено')->success()->send();
        $this->editing = false;
    }

    public function cancel(): void
    {
        $this->editing = false;
        $this->recordId = null;
        $this->viewId = null;
        $this->mediaUpload = null;
        $this->mediaUploads = [];
        $this->mediaReplacements = [];
        $this->selectedMedia = [];
        $this->libraryField = null;
    }

    public function mediaSections(): array
    {
        if (! $this->editing || $this->recordId === null) {
            return [];
        }
        $bread = $this->requireBread('edit');
        $record = $this->registry()->model($bread)?->newQuery()->findOrFail($this->recordId);
        if (! $record instanceof HasMedia) {
            return [];
        }

        $sections = [];
        foreach ($this->registry()->rows($bread) as $row) {
            if ($row->type !== 'adv_media_files' || ! $row->edit) {
                continue;
            }
            $details = json_decode($row->details ?: '{}', true) ?: [];
            $sections[] = [
                'field' => $row->field,
                'label' => $row->display_name ?: $row->field,
                'extra' => $details['extra_fields'] ?? [],
                'files' => $record->getMedia($row->field),
            ];
        }

        return $sections;
    }

    public function uploadMedia(string $field): void
    {
        $record = $this->mediaRecord($field);
        $files = $this->mediaUploads[$field] ?? ($this->mediaUpload ? [$this->mediaUpload] : []);
        $this->resetValidation();
        try {
            validator(['files' => $files], ['files' => 'required|array|min:1|max:20', 'files.*' => 'required|file|mimes:jpg,jpeg,png,webp,gif|max:10240'])->validate();
        } catch (ValidationException $exception) {
            unset($this->mediaUploads[$field]);
            $this->mediaUpload = null;
            throw $exception;
        }
        $added = [];
        try {
            foreach ($files as $file) {
                $added[] = $record->addMedia($file->getRealPath())->usingFileName($file->getClientOriginalName())
                    ->toMediaCollection($field, 'public');
            }
        } catch (\Throwable $exception) {
            foreach ($added as $media) {
                $media->delete();
            }
            throw $exception;
        }
        $this->mediaUpload = null;
        unset($this->mediaUploads[$field]);
        Notification::make()->title('Файлы добавлены')->success()->send();
    }

    public function moveMedia(string $field, int $mediaId, int $direction): void
    {
        $record = $this->mediaRecord($field);
        abort_unless(in_array($direction, [-1, 1], true), 422);
        DB::transaction(function () use ($record, $field, $mediaId, $direction): void {
            $files = $record->media()->where('collection_name', $field)->orderBy('order_column')->orderBy('id')->lockForUpdate()->get();
            $index = $files->search(fn ($media): bool => (int) $media->id === $mediaId);
            abort_unless($index !== false, 404);
            $target = $index + $direction;
            if ($target < 0 || $target >= $files->count()) {
                return;
            }
            $ordered = $files->all();
            [$ordered[$index], $ordered[$target]] = [$ordered[$target], $ordered[$index]];
            foreach ($ordered as $position => $media) {
                $media->order_column = $position + 1;
                $media->save();
            }
        });
    }

    public function replaceMedia(string $field, int $mediaId): void
    {
        $record = $this->mediaRecord($field);
        $old = $record->getMedia($field)->firstWhere('id', $mediaId);
        abort_unless($old, 404);
        $this->validate(['mediaReplacements.' . $mediaId => 'required|file|mimes:jpg,jpeg,png,webp,gif|max:10240']);
        $file = $this->mediaReplacements[$mediaId];
        $new = $record->addMedia($file->getRealPath())->usingFileName($file->getClientOriginalName())
            ->withCustomProperties($old->custom_properties)->toMediaCollection($field, 'public');
        $new->order_column = $old->order_column;
        $new->save();
        $old->delete();
        $this->mediaProperties[$new->id] = $this->mediaProperties[$old->id] ?? $old->custom_properties;
        $this->selectedMedia[$field] = array_map(fn ($id) => (int) $id === $mediaId ? (string) $new->id : (string) $id, $this->selectedMedia[$field] ?? []);
        unset($this->mediaProperties[$old->id], $this->mediaReplacements[$old->id]);
        Notification::make()->title('Файл заменён')->success()->send();
    }

    public function saveMediaProperties(string $field, int $mediaId): void
    {
        $record = $this->mediaRecord($field);
        $row = collect($this->registry()->rows($this->bread()))->first(fn (stdClass $row): bool => $row->field === $field && $row->type === 'adv_media_files');
        $details = json_decode($row->details ?: '{}', true) ?: [];
        $keys = array_merge(['title', 'alt'], array_keys($details['extra_fields'] ?? []));
        $media = $record->getMedia($field)->firstWhere('id', $mediaId);
        abort_unless($media, 404);
        $this->resetValidation(array_merge(['mediaProperties'], array_map(fn ($key) => 'mediaProperties.' . $mediaId . '.' . $key, $keys)));
        foreach ($keys as $key) {
            $value = $this->mediaProperties[$mediaId][$key] ?? null;
            if ($value !== null && (! is_string($value) || mb_strlen($value) > 2000)) {
                throw ValidationException::withMessages(['mediaProperties' => 'Некорректное значение поля медиа.']);
            }
            $definition = $details['extra_fields'][$key] ?? [];
            if (($definition['type'] ?? '') === 'dropdown' && filled($value)) {
                $options = [];
                foreach ($definition['options'] ?? [] as $option => $label) {
                    $options[] = (string) (is_int($option) ? $label : $option);
                }
                if (! in_array($value, $options, true)) {
                    throw ValidationException::withMessages(['mediaProperties.' . $mediaId . '.' . $key => 'Выберите значение из списка.']);
                }
            }
            $media->setCustomProperty($key, $value);
        }
        $media->save();
        Notification::make()->title('Подписи сохранены')->success()->send();
    }

    public function deleteMedia(string $field, int $mediaId): void
    {
        $record = $this->mediaRecord($field);
        $media = $record->getMedia($field)->firstWhere('id', $mediaId);
        abort_unless($media, 404);
        $media->delete();
        unset($this->mediaProperties[$mediaId]);
        $this->selectedMedia[$field] = array_values(array_diff($this->selectedMedia[$field] ?? [], [(string) $mediaId]));
        Notification::make()->title('Файл удалён')->success()->send();
    }

    public function selectAllMedia(string $field): void
    {
        $record = $this->mediaRecord($field);
        $this->selectedMedia[$field] = $record->getMedia($field)->pluck('id')->map(fn ($id) => (string) $id)->all();
    }

    public function clearMediaSelection(string $field): void
    {
        $this->mediaRecord($field);
        $this->selectedMedia[$field] = [];
    }

    public function deleteSelectedMedia(string $field): void
    {
        $record = $this->mediaRecord($field);
        $ids = array_values(array_unique(array_map('intval', (array) ($this->selectedMedia[$field] ?? []))));
        abort_unless($ids !== [], 422);
        $files = $record->getMedia($field)->whereIn('id', $ids);
        abort_unless($files->count() === count($ids), 404);
        foreach ($files as $media) {
            $media->delete();
            unset($this->mediaProperties[$media->id], $this->mediaReplacements[$media->id]);
        }
        $this->selectedMedia[$field] = [];
        Notification::make()->title('Выбранные файлы удалены')->success()->send();
    }

    private function libraryDetails(string $field): array
    {
        $bread = $this->requireBread($this->recordId ? 'edit' : 'add');
        abort_unless($this->editing && in_array($this->locale, $this->registry()->locales(), true), 403);
        $row = collect($this->registry()->editableRows($bread, $this->recordId ? 'edit' : 'add'))
            ->first(fn ($row) => $row->field === $field && $row->type === 'media_picker');
        abort_unless($row, 403);

        return json_decode($row->details ?: '{}', true) ?: [];
    }

    private function libraryRoot(array $details): string
    {
        $root = trim((string) ($details['base_path'] ?? $this->type), '/');
        abort_unless($root !== '' && ! str_contains($root, '..') && ! str_contains($root, '\\') && ! str_contains($root, ':') && ! str_contains($root, "\0"), 422);

        return $root;
    }

    private function libraryPathAllowed(string $path, string $root): bool
    {
        return ($path === $root || str_starts_with($path, $root . '/'))
            && ! str_contains($path, '..') && ! str_contains($path, '\\') && ! str_contains($path, ':')
            && ! preg_match('/[\x00-\x1F\x7F]/', $path);
    }

    public function openFileLibrary(string $field): void
    {
        $details = $this->libraryDetails($field);
        $this->libraryField = $field;
        $this->libraryFolder = $this->libraryRoot($details);
        $this->libraryPage = 1;
        $this->libraryNewFolder = '';
        $this->libraryRenameFolder = null;
        $this->librarySourceFile = null;
        $this->libraryCropFile = null;
        $this->resetValidation('libraryNewFolder');
    }

    public function createLibraryFolder(): void
    {
        abort_unless($this->libraryField !== null, 409);
        $details = $this->libraryDetails($this->libraryField);
        abort_unless(($details['show_folders'] ?? false) && ($details['allow_create_folder'] ?? false), 403);
        abort_unless($this->libraryPathAllowed($this->libraryFolder, $this->libraryRoot($details)), 422);
        $this->resetValidation('libraryNewFolder');
        $name = trim($this->libraryNewFolder);
        validator(['libraryNewFolder' => $name], [
            'libraryNewFolder' => ['required', 'string', 'max:100', 'regex:/\A[\pL\pN][\pL\pN _-]*\z/u'],
        ], ['libraryNewFolder.regex' => 'Используйте буквы, цифры, пробелы, дефис или подчёркивание.'])->validate();
        if (preg_match('/\A(?:CON|PRN|AUX|NUL|COM[0-9]|LPT[0-9])\z/i', $name)) {
            throw ValidationException::withMessages(['libraryNewFolder' => 'Это имя зарезервировано системой.']);
        }
        $disk = Storage::disk('public');
        $path = $this->libraryFolder . '/' . $name;
        if ($disk->exists($path)) {
            throw ValidationException::withMessages(['libraryNewFolder' => 'Папка или файл с таким именем уже существует.']);
        }
        if (! $disk->makeDirectory($path)) {
            throw ValidationException::withMessages(['libraryNewFolder' => 'Не удалось создать папку.']);
        }
        $this->libraryNewFolder = '';
        Notification::make()->title('Папка создана')->success()->send();
    }

    public function browseFileLibrary(string $folder, int $page = 1): void
    {
        abort_unless($this->libraryField !== null, 409);
        $details = $this->libraryDetails($this->libraryField);
        $root = $this->libraryRoot($details);
        abort_unless($this->libraryPathAllowed($folder, $root) && $page > 0, 422);
        abort_unless($folder === $root || ($details['show_folders'] ?? false), 403);
        $this->libraryFolder = $folder;
        $this->libraryPage = $page;
    }

    public function fileLibrary(): array
    {
        if ($this->libraryField === null) {
            return [];
        }
        $details = $this->libraryDetails($this->libraryField);
        $root = $this->libraryRoot($details);
        abort_unless($this->libraryPathAllowed($this->libraryFolder, $root), 422);
        $disk = Storage::disk('public');
        $allowed = $details['allowed'] ?? [];
        $extensions = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif', 'bmp' => 'image/bmp', 'avif' => 'image/avif'];
        $files = array_values(array_filter($disk->files($this->libraryFolder), function ($path) use ($allowed, $extensions): bool {
            $mime = $extensions[strtolower(pathinfo($path, PATHINFO_EXTENSION))] ?? null;

            return $mime !== null && ($allowed === [] || in_array($mime, $allowed, true));
        }));
        sort($files);

        return [
            'root' => $root, 'folder' => $this->libraryFolder,
            'canCreateFolder' => (bool) (($details['show_folders'] ?? false) && ($details['allow_create_folder'] ?? false)),
            'canRelocate' => (bool) (($details['allow_rename'] ?? false) || ($details['allow_move'] ?? false)),
            'canCrop' => (bool) ($details['allow_crop'] ?? false),
            'canRenameFolder' => (bool) (($details['show_folders'] ?? false) && ($details['allow_rename'] ?? false)),
            'canDeleteFolder' => (bool) (($details['show_folders'] ?? false) && ($details['allow_delete'] ?? false)),
            'parent' => $this->libraryFolder === $root ? null : dirname($this->libraryFolder),
            'folders' => ($details['show_folders'] ?? false) ? $disk->directories($this->libraryFolder) : [],
            'files' => array_map(fn ($path) => ['path' => $path, 'url' => $disk->url($path)], array_slice($files, ($this->libraryPage - 1) * 24, 24)),
            'hasNext' => count($files) > $this->libraryPage * 24,
        ];
    }

    private function checkedLibraryDirectory(string $path, string $action): array
    {
        abort_unless($this->libraryField !== null, 409);
        $details = $this->libraryDetails($this->libraryField);
        abort_unless(($details['show_folders'] ?? false) && ($details[$action] ?? false), 403);
        $root = $this->libraryRoot($details);
        abort_unless($path !== $root && $this->libraryPathAllowed($path, $root), 422);
        $disk = Storage::disk('public');
        abort_unless($disk->directoryExists($path), 404);
        $resolved = realpath($disk->path($path));
        $resolvedRoot = realpath($disk->path($root));
        $diskRoot = realpath($disk->path(''));
        $normalize = static function (string $value): string {
            $value = rtrim(str_replace('\\', '/', $value), '/');
            return PHP_OS_FAMILY === 'Windows' ? strtolower($value) : $value;
        };
        abort_unless($resolved && $resolvedRoot && $diskRoot && ! is_link($disk->path($path))
            && ! is_link($disk->path($root))
            && str_starts_with($normalize($resolved), $normalize($resolvedRoot) . '/')
            && str_starts_with($normalize($resolvedRoot), $normalize($diskRoot) . '/'), 422);

        return $details;
    }

    public function openLibraryFolderRename(string $path): void
    {
        $this->checkedLibraryDirectory($path, 'allow_rename');
        $this->libraryRenameFolder = $path;
        $this->libraryFolderName = basename($path);
        $this->resetValidation('libraryFolderName');
    }

    public function renameLibraryFolder(): void
    {
        abort_unless($this->libraryRenameFolder !== null, 409);
        $source = $this->libraryRenameFolder;
        $this->checkedLibraryDirectory($source, 'allow_rename');
        $this->resetValidation('libraryFolderName');
        $name = trim($this->libraryFolderName);
        validator(['libraryFolderName' => $name], ['libraryFolderName' => ['required', 'string', 'max:100', 'regex:/\A[\pL\pN][\pL\pN _-]*\z/u']])->validate();
        if (preg_match('/\A(?:CON|PRN|AUX|NUL|COM[0-9]|LPT[0-9])\z/i', $name)) {
            throw ValidationException::withMessages(['libraryFolderName' => 'Это имя зарезервировано системой.']);
        }
        $target = dirname($source) . '/' . $name;
        $disk = Storage::disk('public');
        $count = \Illuminate\Support\Facades\Cache::lock('bread-library:' . sha1($target), 120)->block(5, function () use ($disk, $source, $target): int {
            if ($disk->exists($target)) {
                throw ValidationException::withMessages(['libraryFolderName' => 'Укажите новое имя, которое ещё не занято.']);
            }
            $files = $disk->allFiles($source);
            $folders = $disk->allDirectories($source);
            if (count($files) + count($folders) > 500) {
                throw ValidationException::withMessages(['libraryFolderName' => 'В папке больше 500 объектов. Переименуйте вложенные папки отдельно.']);
            }
            $createdFiles = [];
            $createdFolders = [];
            try {
                $destinations = array_merge([$target], array_map(fn ($path) => $target . substr($path, strlen($source)), $folders));
                usort($destinations, fn ($a, $b) => strlen($a) <=> strlen($b));
                foreach ($destinations as $folder) {
                    if (! $disk->makeDirectory($folder)) throw new \RuntimeException('Unable to create destination directory.');
                    $createdFolders[] = $folder;
                }
                foreach ($files as $file) {
                    $destination = $target . substr($file, strlen($source));
                    $createdFiles[] = $destination;
                    if (! $disk->copy($file, $destination)) throw new \RuntimeException('Unable to copy library file.');
                }

                return DB::transaction(fn () => app(\App\Filament\Bread\BreadLibraryReferences::class)->replace($source, $target, $this->registry(), true));
            } catch (\Throwable $exception) {
                foreach ($createdFiles as $file) $disk->delete($file);
                foreach (array_reverse($createdFolders) as $folder) @rmdir($disk->path($folder));
                throw $exception;
            }
        });
        $this->data = \App\Filament\Bread\BreadLibraryReferences::replaceValue($this->data, $source, $target, true);
        if ($this->libraryFolder === $source || str_starts_with($this->libraryFolder, $source . '/')) {
            $this->libraryFolder = $target . substr($this->libraryFolder, strlen($source));
        }
        $this->libraryRenameFolder = null;
        $this->librarySourceFile = null;
        $this->libraryCropFile = null;
        Notification::make()->title('Новое имя папки применено')->body('Обновлено ссылок: ' . $count . '. Исходная папка сохранена для старых ссылок в контенте.')->success()->send();
    }

    public function deleteEmptyLibraryFolder(string $path): void
    {
        $this->checkedLibraryDirectory($path, 'allow_delete');
        $this->resetValidation('libraryFolderName');
        $disk = Storage::disk('public');
        \Illuminate\Support\Facades\Cache::lock('bread-library:' . sha1($path), 30)->block(5, function () use ($disk, $path): void {
            // rmdir is atomic and non-recursive: a new or hidden file also prevents deletion.
            if ($disk->files($path) !== [] || $disk->directories($path) !== [] || ! @rmdir($disk->path($path))) {
                throw ValidationException::withMessages(['libraryFolderName' => 'Удаление возможно только для пустой папки без вложенных папок.']);
            }
        });
        if ($this->libraryFolder === $path) $this->libraryFolder = dirname($path);
        if ($this->libraryRenameFolder === $path) $this->libraryRenameFolder = null;
        Notification::make()->title('Пустая папка удалена')->success()->send();
    }

    public function openLibraryRelocation(string $path): void
    {
        abort_unless($this->libraryField !== null, 409);
        $details = $this->libraryDetails($this->libraryField);
        abort_unless(($details['allow_rename'] ?? false) || ($details['allow_move'] ?? false), 403);
        $this->validateLibraryFile($this->libraryField, $path, $details);
        $this->librarySourceFile = $path;
        $this->libraryTargetName = basename($path);
        $this->libraryTargetFolder = dirname($path);
        $this->resetValidation('libraryTargetName');
    }

    public function relocateLibraryFile(): void
    {
        abort_unless($this->libraryField !== null && $this->librarySourceFile !== null, 409);
        $details = $this->libraryDetails($this->libraryField);
        $source = $this->librarySourceFile;
        $this->validateLibraryFile($this->libraryField, $source, $details);
        $name = trim($this->libraryTargetName);
        $folder = trim($this->libraryTargetFolder, '/');
        validator(['libraryTargetName' => $name], [
            'libraryTargetName' => ['required', 'string', 'max:200', 'regex:/\A[\pL\pN][\pL\pN _().-]*\z/u'],
        ])->validate();
        if (str_contains($name, '..') || preg_match('/\A(?:CON|PRN|AUX|NUL|COM[0-9]|LPT[0-9])(?:\.|\z)/i', $name)
            || str_ends_with($name, '.') || strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== strtolower(pathinfo($source, PATHINFO_EXTENSION))) {
            throw ValidationException::withMessages(['libraryTargetName' => 'Некорректное имя; расширение изображения менять нельзя.']);
        }
        abort_unless($this->libraryPathAllowed($folder, $this->libraryRoot($details)), 422);
        abort_unless($name === basename($source) || ($details['allow_rename'] ?? false), 403);
        abort_unless($folder === dirname($source) || ($details['allow_move'] ?? false), 403);
        $target = $folder . '/' . $name;
        $disk = Storage::disk('public');
        abort_unless($disk->directoryExists($folder), 404);
        if ($source === $target || $disk->exists($target)) {
            throw ValidationException::withMessages(['libraryTargetName' => 'Укажите новый путь, который ещё не занят.']);
        }
        $count = \Illuminate\Support\Facades\Cache::lock('bread-library:' . sha1($target), 60)->block(5, function () use ($disk, $source, $target): int {
            if ($disk->exists($target)) {
                throw ValidationException::withMessages(['libraryTargetName' => 'Файл с таким именем уже существует.']);
            }
            if (! $disk->copy($source, $target)) {
                throw ValidationException::withMessages(['libraryTargetName' => 'Не удалось скопировать файл.']);
            }
            try {
                return DB::transaction(fn () => app(\App\Filament\Bread\BreadLibraryReferences::class)->replace($source, $target, $this->registry()));
            } catch (\Throwable $exception) {
                $disk->delete($target);
                throw $exception;
            }
        });
        // Keep the original for URLs embedded in rich text, settings, or external content.
        $this->data = \App\Filament\Bread\BreadLibraryReferences::replaceValue($this->data, $source, $target);
        $this->librarySourceFile = null;
        $this->resetValidation('libraryTargetName');
        Notification::make()->title('Файл доступен по новому пути')->body('Обновлено ссылок: ' . $count . '. Исходник сохранён для старых ссылок в контенте.')->success()->send();
    }

    public function chooseLibraryFile(string $field, string $path): void
    {
        $details = $this->libraryDetails($field);
        $this->validateLibraryFile($field, $path, $details);
        $values = (array) ($this->data[$field] ?? []);
        if (in_array($path, $values, true)) {
            return;
        }
        if (count($values) >= ((int) ($details['max'] ?? 100) ?: 100)) {
            throw ValidationException::withMessages(['data.' . $field => 'Достигнут лимит изображений.']);
        }
        $values[(string) Str::uuid()] = $path;
        $this->data[$field] = $values;
        $this->resetValidation('data.' . $field);
    }

    public function openLibraryCrop(string $path): void
    {
        abort_unless($this->libraryField !== null, 409);
        $details = $this->libraryDetails($this->libraryField);
        abort_unless($details['allow_crop'] ?? false, 403);
        $this->validateLibraryFile($this->libraryField, $path, $details);
        $info = getimagesizefromstring(Storage::disk('public')->get($path));
        $this->libraryCropFile = $path;
        $this->libraryCrop = ['x' => 0, 'y' => 0, 'width' => $info[0], 'height' => $info[1]];
        $this->resetValidation('libraryCrop');
    }

    public function libraryCropPreview(): array
    {
        abort_unless($this->libraryField !== null && $this->libraryCropFile !== null, 409);
        $details = $this->libraryDetails($this->libraryField);
        abort_unless($details['allow_crop'] ?? false, 403);
        $this->validateLibraryFile($this->libraryField, $this->libraryCropFile, $details);
        $disk = Storage::disk('public');
        $info = getimagesizefromstring($disk->get($this->libraryCropFile));
        $path = implode('/', array_map(rawurlencode(...), explode('/', $this->libraryCropFile)));

        return ['url' => $disk->url($path), 'width' => $info[0], 'height' => $info[1]];
    }

    public function cropLibraryFile(): void
    {
        abort_unless($this->libraryField !== null && $this->libraryCropFile !== null, 409);
        $this->resetValidation(['libraryCrop', 'libraryCrop.x', 'libraryCrop.y', 'libraryCrop.width', 'libraryCrop.height']);
        $field = $this->libraryField;
        $details = $this->libraryDetails($field);
        abort_unless($details['allow_crop'] ?? false, 403);
        $source = $this->libraryCropFile;
        $this->validateLibraryFile($field, $source, $details);
        validator(['libraryCrop' => $this->libraryCrop], [
            'libraryCrop' => 'required|array:x,y,width,height',
            'libraryCrop.x' => 'required|integer|min:0', 'libraryCrop.y' => 'required|integer|min:0',
            'libraryCrop.width' => 'required|integer|min:1', 'libraryCrop.height' => 'required|integer|min:1',
        ])->validate();
        $values = (array) ($this->data[$field] ?? []);
        $selected = in_array($source, $values, true);
        if (! $selected && count($values) >= ((int) ($details['max'] ?? 100) ?: 100)) {
            throw ValidationException::withMessages(['libraryCrop' => 'Достигнут лимит изображений поля.']);
        }
        $disk = Storage::disk('public');
        $result = app(\App\Filament\Bread\BreadImageCrop::class)->crop($disk->get($source),
            (int) $this->libraryCrop['x'], (int) $this->libraryCrop['y'],
            (int) $this->libraryCrop['width'], (int) $this->libraryCrop['height'], (int) ($details['quality'] ?? 90));
        $target = dirname($source) . '/crop-' . Str::uuid() . '.' . $result['extension'];
        if (! $disk->put($target, $result['bytes'])) {
            throw ValidationException::withMessages(['libraryCrop' => 'Не удалось записать новый файл.']);
        }
        try {
            $this->validateLibraryFile($field, $target, $details);
            if ($selected) {
                $this->data[$field] = \App\Filament\Bread\BreadLibraryReferences::replaceValue($values, $source, $target);
            } else {
                $this->chooseLibraryFile($field, $target);
            }
        } catch (\Throwable $exception) {
            $disk->delete($target);
            throw $exception;
        }
        $this->libraryCropFile = null;
        $this->resetValidation('libraryCrop');
        Notification::make()->title('Обрезанная копия выбрана')->body('Нажмите «Сохранить» для записи поля. Оригинал сохранён.')->success()->send();
    }

    private function validateLibraryFile(string $field, string $path, array $details): void
    {
        abort_unless($this->libraryPathAllowed($path, $this->libraryRoot($details)), 422);
        $disk = Storage::disk('public');
        abort_unless($disk->exists($path), 404);
        if ($disk->size($path) > 10 * 1024 * 1024) {
            throw ValidationException::withMessages(['data.' . $field => 'Изображение превышает 10 MB.']);
        }
        $image = @getimagesizefromstring($disk->get($path));
        $mime = $image['mime'] ?? null;
        $allowed = $details['allowed'] ?? ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/bmp', 'image/avif'];
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/bmp', 'image/avif'], true)
            || ($allowed !== [] && ! in_array($mime, $allowed, true))) {
            throw ValidationException::withMessages(['data.' . $field => 'Этот тип изображения недоступен для поля.']);
        }
    }

    public function openView(int $id): void
    {
        $bread = $this->requireBread('read');
        abort_unless($this->registry()->model($bread)?->newQuery()->find($id), 404);
        $this->viewId = $id;
        $this->editing = false;
        $this->recordId = null;
    }

    public function viewedFields(): array
    {
        if ($this->viewId === null) {
            return [];
        }
        $bread = $this->requireBread('read');
        $record = $this->registry()->model($bread)?->newQuery()->findOrFail($this->viewId);
        abort_unless($record, 404);
        $columns = $this->registry()->columns($bread);
        $translated = method_exists($record, 'getTranslatableAttributes') ? $record->getTranslatableAttributes() : [];
        $belongs = collect($this->registry()->belongsToRows($bread, 'read'))->keyBy('field');
        $many = collect($this->registry()->manyToManyRows($bread, 'read'))->keyBy(fn (array $relation): string => $relation['row']->field);
        $fields = [];
        foreach ($this->registry()->rows($bread) as $row) {
            if (! $row->read || $row->type === 'password') {
                continue;
            }
            if ($row->type === 'relationship' && $belongs->has($row->field)) {
                $details = json_decode($row->details ?: '{}', true) ?: [];
                $value = DB::table($details['table'])->where($details['key'], $record->getAttribute($details['column']))
                    ->value($details['label']);
                $fields[] = ['label' => $row->display_name ?: $row->field, 'value' => $value];

                continue;
            }
            if ($row->type === 'relationship' && $many->has($row->field)) {
                $relation = $many->get($row->field);
                $details = $relation['details'];
                $value = DB::table($details['pivot_table'] . ' as pivot')
                    ->join($details['table'] . ' as related', 'related.id', '=', 'pivot.' . $relation['targetKey'])
                    ->where('pivot.' . $relation['sourceKey'], $record->getKey())
                    ->pluck('related.' . $details['label'])->implode(', ');
                $fields[] = ['label' => $row->display_name ?: $row->field, 'value' => $value];

                continue;
            }
            if ($row->type === 'adv_media_files' && $record instanceof HasMedia) {
                $fields[] = ['label' => $row->display_name ?: $row->field,
                    'type' => 'adv_media_files', 'value' => $record->getMedia($row->field)->map->getUrl()->all()];

                continue;
            }
            if (! in_array($row->field, $columns, true)) {
                continue;
            }
            $value = $this->locale === config('voyager.multilingual.default', 'en') || ! in_array($row->field, $translated, true)
                ? $record->getAttribute($row->field)
                : DB::table('translations')->where([
                    'table_name' => $bread->name, 'column_name' => $row->field,
                    'foreign_key' => $record->getKey(), 'locale' => $this->locale,
                ])->value('value');
            $fields[] = ['label' => $row->display_name ?: $row->field, 'type' => $row->type, 'value' => $value];
        }

        return $fields;
    }

    public function deleteRecord(int $id): void
    {
        $bread = $this->requireBread('delete');
        $model = $this->registry()->model($bread);
        abort_unless($model, 409);
        $model->newQuery()->findOrFail($id)->delete();
        Notification::make()->title('Удалено')->success()->send();
    }

    public function records(): array
    {
        $bread = $this->bread();
        $model = $bread ? $this->registry()->model($bread) : null;
        if (! $bread || ! $this->registry()->permitted($bread, 'browse') || ! $model) {
            return [];
        }
        $physical = $this->registry()->columns($bread);
        $relations = collect($this->registry()->belongsToRows($bread, 'browse'))->keyBy('field');
        $many = collect($this->registry()->manyToManyRows($bread, 'browse'))->keyBy(fn (array $relation): string => $relation['row']->field);
        $columns = [];
        $select = ['id'];
        foreach ($this->registry()->rows($bread) as $row) {
            if (! $row->browse || $row->type === 'password') {
                continue;
            }
            if (in_array($row->field, $physical, true)) {
                $columns[] = ['field' => $row->field, 'label' => $row->display_name ?: $row->field, 'type' => $row->type];
                $select[] = $row->field;
            } elseif ($row->type === 'relationship' && $relations->has($row->field)) {
                $details = json_decode($row->details ?: '{}', true) ?: [];
                $columns[] = ['field' => $row->field, 'label' => $row->display_name ?: $row->field, 'type' => 'relationship'];
                $select[] = $details['column'];
            } elseif ($row->type === 'relationship' && $many->has($row->field)) {
                $columns[] = ['field' => $row->field, 'label' => $row->display_name ?: $row->field, 'type' => 'relationship'];
            } elseif ($row->type === 'adv_media_files' && $model instanceof HasMedia) {
                $columns[] = ['field' => $row->field, 'label' => $row->display_name ?: $row->field, 'type' => 'adv_media_files'];
            }
        }
        if (! collect($columns)->contains(fn (array $column): bool => $column['field'] === 'id')) {
            array_unshift($columns, ['field' => 'id', 'label' => 'ID', 'type' => 'text']);
        }

        $query = DB::table($bread->name)->select(array_values(array_unique($select)));
        if ($this->filterType !== null && in_array('id_type', $physical, true)) {
            $query->where('id_type', $this->filterType);
        }
        $searchable = collect($this->registry()->rows($bread))
            ->filter(fn (stdClass $row): bool => $row->browse && in_array($row->field, $physical, true) && in_array($row->type, ['text', 'text_area'], true))
            ->take(4)->pluck('field')->all();
        if ($this->search !== '' && $searchable !== []) {
            $search = $this->search;
            $query->where(function ($builder) use ($searchable, $search): void {
                foreach ($searchable as $field) {
                    $builder->orWhere($field, 'like', '%' . $search . '%');
                }
            });
        }

        $records = $query->orderByDesc('id')->paginate(25);
        $ids = $records->getCollection()->pluck('id')->all();
        if ($ids !== []) {
            $translated = method_exists($model, 'getTranslatableAttributes') ? $model->getTranslatableAttributes() : [];
            $visibleTranslated = collect($columns)->pluck('field')->intersect($translated)->all();
            if ($this->locale !== config('voyager.multilingual.default', 'en') && $visibleTranslated !== []) {
                $translations = DB::table('translations')->where('table_name', $bread->name)
                    ->where('locale', $this->locale)->whereIn('foreign_key', $ids)
                    ->whereIn('column_name', $visibleTranslated)->get();
                $lookup = $records->getCollection()->keyBy('id');
                foreach ($translations as $translation) {
                    $record = $lookup->get($translation->foreign_key);
                    if ($record) {
                        $record->{$translation->column_name} = $translation->value;
                    }
                }
            }
            foreach ($relations as $field => $row) {
                if (! collect($columns)->contains(fn (array $column): bool => $column['field'] === $field)) {
                    continue;
                }
                $details = json_decode($row->details ?: '{}', true) ?: [];
                $keys = $records->getCollection()->pluck($details['column'])->filter()->unique()->all();
                $labels = $keys === [] ? collect() : DB::table($details['table'])->whereIn($details['key'], $keys)
                    ->pluck($details['label'], $details['key']);
                foreach ($records as $record) {
                    $record->{$field} = $labels->get($record->{$details['column']});
                }
            }
            foreach ($many as $field => $relation) {
                if (! collect($columns)->contains(fn (array $column): bool => $column['field'] === $field)) {
                    continue;
                }
                $details = $relation['details'];
                $labels = DB::table($details['pivot_table'] . ' as pivot')
                    ->join($details['table'] . ' as related', 'related.id', '=', 'pivot.' . $relation['targetKey'])
                    ->whereIn('pivot.' . $relation['sourceKey'], $ids)
                    ->get(['pivot.' . $relation['sourceKey'] . ' as source_id', 'related.' . $details['label'] . ' as label'])
                    ->groupBy('source_id');
                foreach ($records as $record) {
                    $record->{$field} = $labels->get($record->id)?->pluck('label')->implode(', ') ?: null;
                }
            }
            if ($model instanceof HasMedia && DatabaseSchema::hasTable('media')) {
                $mediaColumns = collect($columns)->where('type', 'adv_media_files')->pluck('field')->all();
                if ($mediaColumns !== []) {
                    $mediaRecords = $model->newQuery()->with('media')->whereKey($ids)->get()->keyBy($model->getKeyName());
                    foreach ($records as $record) {
                        foreach ($mediaColumns as $field) {
                            $record->{$field} = $mediaRecords->get($record->id)?->getMedia($field)->map->getUrl()->all() ?: [];
                        }
                    }
                }
            }
        }

        return ['columns' => $columns, 'rows' => $records];
    }

    private function fillRecord($record): void
    {
        $bread = $this->bread();
        $values = [];
        foreach ($this->registry()->editableRows($bread, 'edit') as $row) {
            if ($this->locale === config('voyager.multilingual.default', 'en') || ! (method_exists($record, 'getTranslatableAttributes') && in_array($row->field, $record->getTranslatableAttributes(), true))) {
                $details = json_decode($row->details ?: '{}', true) ?: [];
                $value = $record->getAttribute($row->field) ?? ($details['default'] ?? null);
                $values[$row->field] = match ($row->type) {
                    'password' => null,
                    'multiple_images', 'media_picker' => json_decode((string) $value, true) ?: [],
                    'multiple_checkbox' => array_keys(json_decode((string) $value, true) ?: []),
                    default => $value,
                };
            } elseif (method_exists($record, 'getTranslatableAttributes') && in_array($row->field, $record->getTranslatableAttributes(), true)) {
                $values[$row->field] = DB::table('translations')->where([
                    'table_name' => $bread->name, 'column_name' => $row->field,
                    'foreign_key' => $record->getKey(), 'locale' => $this->locale,
                ])->value('value');
            }
        }
        if ($this->locale === config('voyager.multilingual.default', 'en')) {
            foreach ($this->registry()->belongsToRows($bread, 'edit') as $row) {
                $details = json_decode($row->details ?: '{}', true) ?: [];
                $values[$details['column']] = $record->getAttribute($details['column']);
            }
            foreach ($this->registry()->manyToManyRows($bread, 'edit') as $relation) {
                $details = $relation['details'];
                $values['__pivot_' . $relation['row']->id] = DB::table($details['pivot_table'])
                    ->where($relation['sourceKey'], $record->getKey())
                    ->pluck($relation['targetKey'])->all();
            }
        }
        $this->form->fill($values);
    }

    private function fillMediaProperties($record): void
    {
        $this->mediaProperties = [];
        if (! $record instanceof HasMedia) {
            return;
        }
        foreach ($this->registry()->rows($this->bread()) as $row) {
            if ($row->type !== 'adv_media_files' || ! $row->edit) {
                continue;
            }
            $details = json_decode($row->details ?: '{}', true) ?: [];
            $keys = array_merge(['title', 'alt'], array_keys($details['extra_fields'] ?? []));
            foreach ($record->getMedia($row->field) as $media) {
                foreach ($keys as $key) {
                    $this->mediaProperties[$media->id][$key] = $media->getCustomProperty($key);
                }
            }
        }
    }

    private function mediaRecord(string $field): HasMedia
    {
        $bread = $this->requireBread('edit');
        abort_unless($this->editing && $this->recordId !== null, 409);
        $allowed = collect($this->registry()->rows($bread))->contains(fn (stdClass $row): bool => $row->type === 'adv_media_files' && $row->edit && $row->field === $field);
        abort_unless($allowed, 403);
        $record = $this->registry()->model($bread)?->newQuery()->findOrFail($this->recordId);
        abort_unless($record instanceof HasMedia, 409);

        return $record;
    }

    private function requireBread(string $action): stdClass
    {
        $bread = $this->bread();
        abort_unless($bread && ! $this->registry()->isDedicated($bread) && $this->registry()->permitted($bread, $action), 403);

        return $bread;
    }

    private function registry(): BreadRegistry
    {
        return app(BreadRegistry::class);
    }
}
