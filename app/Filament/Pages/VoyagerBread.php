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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use App\Filament\Bread\BreadJsonRows;
use App\Filament\Bread\BreadPageLayout;
use App\Filament\Bread\BreadCoordinates;
use App\Filament\Bread\BreadCodeEditor;
use App\Filament\Bread\BreadSelectRelation;
use App\Filament\Bread\BreadFieldOptions;
use App\Filament\Bread\BreadFormLayout;
use App\Filament\Bread\BreadFieldsGroup;
use Filament\Schemas\Components\Fieldset;
use App\Filament\Bread\BreadTreeOptions;
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
    use \App\Filament\Bread\BreadRolePermissions;
    use \App\Filament\Bread\BreadUserActions;
    use \App\Filament\Bread\BreadSingleImage;

    protected static ?string $slug = 'bread/{type}';

    protected static bool $shouldRegisterNavigation = true;

    protected string $view = 'filament.pages.voyager-bread';

    public string $type = '';

    public string $locale = 'en';

    public ?int $recordId = null;

    public bool $editing = false;

    public ?int $viewId = null;

    public array $data = [];

    public array $slugTracking = [];

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
                ->icon(\App\Filament\Bread\BreadNavigationIcon::resolve($item->icon_class ?: ($type->icon ?? null), $type->slug))
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
        if ($bread?->name === 'seo_meta_suggestions') {
            abort_unless($this->registry()->permitted($bread, 'browse'), 403);
            $this->redirect(\App\Filament\Resources\SeoMetaSuggestions\SeoMetaSuggestionResource::getUrl());
            return;
        }
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

    public function getSubheading(): ?string
    {
        return filled($this->bread()?->description ?? null) ? (string) $this->bread()->description : null;
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
                'code_editor' => app(BreadCodeEditor::class)->component($row->field, $details, $model),
                'rich_text_box' => app(\App\Filament\Bread\BreadRichEditor::class)->component($row->field, $details),
                'number' => TextInput::make($row->field)->numeric(),
                'coordinates' => $this->coordinatesComponent($row, $details),
                'checkbox' => app(\App\Filament\Bread\BreadCheckbox::class)->component($row->field, $details),
                'multiple_checkbox' => $this->multiCheckboxComponent($row, $details),
                'select_dropdown' => $this->dropdownComponent($row, $details),
                'select_multiple' => isset($details['relationship']) ? $this->selectRelationComponent($row, $details) : Select::make($row->field)->options($details['options'] ?? [])->multiple()->searchable(),
                'radio_btn' => Radio::make($row->field)->options($details['options'] ?? []),
                'time' => TimePicker::make($row->field)->format('H:i:s')->rules(['nullable', 'date_format:H:i:s']),
                'markdown_editor' => MarkdownEditor::make($row->field),
                'hidden' => Hidden::make($row->field),
                'adv_json' => $this->jsonRowsComponent($row, $details),
                'adv_page_layout' => $this->layoutComponent($row, $details),
                'adv_fields_group' => $this->fieldsGroupComponent($row, $details),
                'adv_select_dropdown_tree' => $this->treeComponent($row, $details),
                'date' => DatePicker::make($row->field),
                'timestamp' => DateTimePicker::make($row->field),
                'color' => ColorPicker::make($row->field),
                'image' => $this->imageUploadComponent($row, $details),
                'file' => FileUpload::make($row->field)->disk('public')->directory($bread->name . '/' . date('FY'))->maxSize(102400),
                'multiple_images' => $this->imageUploadComponent($row, $details),
                'media_picker' => FileUpload::make($row->field)->image()->multiple()->compactImagePreviews(true)->reorderable()->disk('public')
                    ->directory($this->libraryRoot($details) . '/' . date('FY'))->maxSize(10240)
                    ->disabled(! (bool) ($details['allow_upload'] ?? true))->dehydrated()
                    ->acceptedFileTypes($details['allowed'] ?? ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/bmp', 'image/avif'])
                    ->minFiles((int) ($details['min'] ?? 0))->maxFiles((int) ($details['max'] ?? 100) ?: 100)
                    ->belowContent(View::make('filament.components.bread-file-library')->viewData(['field' => $row->field])),
                'password' => TextInput::make($row->field)->password()->dehydrated(fn ($state): bool => filled($state)),
                default => TextInput::make($row->field),
            };
            if ($component) {
                if ($bread->name === 'users') {
                    $label = match ($row->field) {
                        'first_name' => 'Имя', 'last_name' => 'Фамилия', 'phone' => 'Телефон',
                        'address' => 'Адрес', 'screenshot' => 'PrintScreen', 'bonuses' => 'К-во бонусов',
                        'invited' => 'Кто пригласил', 'inv_sale_code' => 'Код для приглашения',
                        'active_coupon' => 'Подарочная карта (купон)', 'is_facebook_sale' => 'Скидка за Facebook активна?',
                        default => $label,
                    };
                    if ($row->field === 'client_status') {
                        $component = Select::make('client_status')->options(fn (): array => DB::table('user_types')->orderBy('id')->pluck('name', 'id')->all())
                            ->rules(['nullable', 'integer', 'exists:user_types,id']);
                    } elseif ($row->field === 'is_facebook_sale') {
                        $component = Select::make($row->field)->options([1 => 'Да', 0 => 'Нет'])->rules(['nullable', 'in:0,1']);
                    } elseif ($row->field === 'email') {
                        $component = TextInput::make('email')->email()->rules([
                            \Illuminate\Validation\Rule::unique('users', 'email')->ignore($this->recordId),
                        ]);
                    }
                    if ($row->field === 'role_id' && $this->recordId === (int) auth('filament')->id()) {
                        $component->disabled()->dehydrated(false);
                    }
                }
                $component->label($label . (in_array($row->field, $translated, true) ? ' (' . strtoupper($this->locale) . ')' : ''));
                if (array_key_exists('default', $details) && ! in_array($row->type, ['checkbox', 'multiple_checkbox', 'adv_json', 'adv_fields_group', 'adv_page_layout', 'coordinates'], true)) {
                    $component->default($details['default']);
                }
                if (($this->locale === config('voyager.multilingual.default', 'en') || ! in_array($row->field, $translated, true)) && $row->required && ($row->type !== 'password' || $this->recordId === null)) {
                    $component->required();
                }
                if ($component instanceof \Filament\Forms\Components\Field) {
                    app(BreadFieldOptions::class)->apply($component, $row->type, $details, $operation, $this->locale !== config('voyager.multilingual.default', 'en') && in_array($row->field, $translated, true), $model, $this->recordId, $this->locale);
                }
                $components[$row->field] = $component;
            }
        }
        foreach ($this->registry()->editableRows($bread, $operation) as $row) {
            $details = json_decode($row->details ?: '{}', true) ?: [];
            if (! array_key_exists('slugify', $details)) { continue; }
            try { $slug = app(\App\Filament\Bread\BreadSlug::class)->configuration($row->field, $row->type, $details, $this->registry()->editableRows($bread, $operation)); }
            catch (ValidationException) {
                if (isset($components[$row->field])) { $components[$row->field]->helperText('Настройка slugify несовместима. Автогенерация отключена; доступен ручной ввод.'); }
                continue;
            }
            if (isset($components[$slug['origin']])) { $components[$slug['origin']]->live(debounce: 350); }
        }
        foreach ($relationships as $column => [$row, $details]) {
                $table = $details['table'];
                $key = $details['key'];
                $name = $details['label'];
                $components[$row->field] = Select::make($column)
                    ->label($row->display_name ?: $column)
                    ->options(fn (): array => DB::table($table)->orderBy($key)->limit(50)->pluck($name, $key)->all())
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => DB::table($table)
                        ->where($name, 'like', '%' . $search . '%')->orderBy($key)->limit(50)->pluck($name, $key)->all())
                    ->getOptionLabelUsing(fn ($value): ?string => $value === null ? null : DB::table($table)->where($key, $value)->value($name));
                if ($bread->name === 'users' && $column === 'role_id' && $this->recordId === (int) auth('filament')->id()) {
                    $components[$row->field]->disabled()->dehydrated(false)->helperText('Собственную роль изменить нельзя.');
                }
        }
                foreach ($this->registry()->manyToManyRows($bread, $operation) as $relation) {
                    $row = $relation['row'];
                    $details = $relation['details'];
                    $table = $details['table'];
                    $name = $details['label'];
                    $components[$row->field] = Select::make('__pivot_' . $row->id)
                        ->label($row->display_name ?: $table)
                        ->options(fn (): array => DB::table($table)->orderBy('id')->limit(50)->pluck($name, 'id')->all())
                        ->multiple()->searchable()
                        ->getSearchResultsUsing(fn (string $search): array => DB::table($table)
                            ->where($name, 'like', '%' . $search . '%')->orderBy('id')->limit(50)->pluck($name, 'id')->all())
                        ->getOptionLabelsUsing(fn (array $values): array => DB::table($table)->whereIn('id', $values)->pluck($name, 'id')->all());
                    $tagService = app(\App\Services\Admin\BreadTagCreationService::class);
                    if ($tagService->target($details, $this->registry())) {
                        $rowId = (int) $row->id;
                        $components[$row->field]
                            ->helperText('Кнопка «+» создаёт запись сразу. Её привязка сохраняется вместе с формой.')
                            ->createOptionForm([TextInput::make('label')->label($row->display_name ?: $name)->required()->maxLength(255)])
                            ->createOptionModalHeading('Создать связанную запись')
                            ->createOptionUsing(function (array $data, ?Schema $schema = null) use ($tagService, $rowId): int {
                                try { return $tagService->create($this->type, $rowId, $this->recordId, $data); }
                                catch (ValidationException $exception) {
                                    $path = $schema?->getStatePath();
                                    throw ValidationException::withMessages([($path ? $path.'.' : '').'label' => $exception->errors()['label'] ?? ['Проверьте настройку связи.']]);
                                }
                            });
                    } elseif ($tagService->enabled($details)) {
                        $components[$row->field]->helperText('Создание здесь недоступно: нужны права добавления, текстовая подпись и общий BREAD связанного раздела.');
                    }
                }

        $childRelations = $this->registry()->childrenRows($bread, $operation);
        $parentRecord = $childRelations !== [] && $this->recordId !== null ? $model->newQuery()->findOrFail($this->recordId) : null;
        foreach ($childRelations as $relation) {
            $row = $relation['row'];
            $components[$row->field] = View::make('filament.components.bread-child-relation')->viewData([
                'label' => $row->display_name ?: $row->field,
                'labels' => $this->registry()->childLabels($relation, $parentRecord?->getAttribute($relation['details']['key'])),
                'creating' => $parentRecord === null,
            ]);
        }
        if ($this->recordId !== null && $model instanceof HasMedia) {
            foreach ($this->registry()->rows($bread) as $row) {
                if ($row->type === 'adv_media_files' && $row->edit) {
                    $components[$row->field] = View::make('filament.components.bread-media-section')
                        ->viewData(['field' => $row->field]);
                }
            }
        }
        if ($model instanceof HasMedia) {
            foreach ($this->registry()->rows($bread) as $row) {
                if ($row->type === 'adv_image' && $row->{$operation}) {
                    $components[$row->field] = View::make('filament.components.bread-single-image')->viewData(['field' => $row->field]);
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
                $components[$row->field]->columnSpan(app(BreadFormLayout::class)->span($details));
                $groups[$tabTitle][] = $components[$row->field];
            }
        }
        $tabs = [];
        foreach ($groups as $title => $fields) {
            $tabs[] = Tab::make($title)->columns(BreadFormLayout::COLUMNS)->components($fields);
        }

        $fields = count($tabs) > 1
            ? [Tabs::make('BREAD')->tabs($tabs)->extraAttributes(['class' => 'viar-bread-tabs'])->persistTabInQueryString()->columnSpanFull()]
            : array_values($components);
        if ($bread->name === 'roles') {
            $fields[] = View::make('filament.components.role-permissions')->columnSpanFull();
        }
        if ($bread->name === 'users') {
            $fields[] = Select::make('__user_locale')->columnSpanFull()->label('Язык пользователя')
                ->options(array_combine($this->registry()->locales(), $this->registry()->locales()))
                ->default(config('app.locale', 'en'))->required()
                ->rules([\Illuminate\Validation\Rule::in($this->registry()->locales())]);
            if ($this->recordId !== null) {
                $fields[] = View::make('filament.components.client-account-summary')->viewData([
                    'client' => $model->newQuery()->findOrFail($this->recordId),
                ])->columnSpanFull();
            }
        }

        return $schema->statePath('data')->columns(BreadFormLayout::COLUMNS)->components($fields);
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
        $this->slugTracking = [];
        $this->singleImageUploads = [];
        $this->singleImageProperties = [];
        $this->rolePermissionIds = [];
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
        $this->initializeSlugTracking();
    }

    public function openEdit(int $id): void
    {
        $this->slugTracking = [];
        $this->localeDrafts = [];
        $this->altPanelOpen = false;
        $bread = $this->requireBread('edit');
        $model = $this->registry()->model($bread);
        abort_unless($model, 409);
        abort_unless($this->registry()->hasFormFields($bread, 'edit'), 409);
        $record = $model->newQuery()->findOrFail($id);
        $this->rolePermissionIds = $bread->name === 'roles'
            ? DB::table('permission_role')->where('role_id', $id)->pluck('permission_id')->map(fn ($id): string => (string) $id)->all()
            : [];
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
        $this->fillSingleImages($record);
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

    public function updatedData(mixed $value, string $field): void
    {
        if (! $this->editing || ! is_string($value)) { return; }
        $bread = $this->bread();
        $rows = $this->registry()->editableRows($bread, $this->recordId ? 'edit' : 'add');
        $model = $this->registry()->model($bread);
        $translated = method_exists($model, 'getTranslatableAttributes') ? $model->getTranslatableAttributes() : [];
        foreach ($rows as $row) {
            try { $config = app(\App\Filament\Bread\BreadSlug::class)->configuration($row->field, $row->type, json_decode($row->details ?: '{}', true) ?: [], $rows); }
            catch (ValidationException) { continue; }
            if ($config === null || $config['origin'] !== $field) { continue; }
            // A translated source must not overwrite a shared URL in another locale.
            if ($this->locale !== config('voyager.multilingual.default', 'en') && in_array($field, $translated, true) && ! in_array($row->field, $translated, true)) { continue; }
            $key = $this->locale.':'.$row->field;
            if (($this->data[$row->field] ?? '') === '' || ($this->data[$row->field] ?? null) === null || $config['force'] || ($this->slugTracking[$key] ?? false)) {
                $this->data[$row->field] = app(\App\Filament\Bread\BreadSlug::class)->generate($value);
                $this->slugTracking[$key] = true;
            }
        }
    }

    private function initializeSlugTracking(): void
    {
        foreach ($this->registry()->editableRows($this->bread(), $this->recordId ? 'edit' : 'add') as $row) {
            $details = json_decode($row->details ?: '{}', true) ?: [];
            if (! isset($details['slugify'])) { continue; }
            $key = $this->locale.':'.$row->field;
            $this->slugTracking[$key] ??= ($this->data[$row->field] ?? '') === '' || ($this->data[$row->field] ?? null) === null;
        }
    }

    // Server-only journal: submitted strings can never authorize file deletion.
    private array $createdImageFiles = [];

    public function save(): void
    {
        $this->createdImageFiles = [];
        $recordId = $this->recordId;
        $draft = $this->data;
        try {
            $this->saveBreadRecord();
        } catch (\Throwable $error) {
            $this->recordId = $recordId;
            foreach ($this->createdImageFiles as $field => $batches) {
                foreach ($batches as [$disk, $paths]) {
                    try {
                        if (! Storage::disk($disk)->delete($paths)) {
                            report(new \RuntimeException('Failed to clean up BREAD image upload.'));
                        }
                    } catch (\Throwable $cleanupError) { report($cleanupError); }
                }
                // Filament deletes temporary uploads after storing them. Keep
                // existing selections; the new upload must be selected again.
                $this->data[$field] = array_filter((array) ($draft[$field] ?? []), 'is_string');
            }
            throw $error;
        } finally {
            $this->createdImageFiles = [];
        }
        Notification::make()->title('Сохранено')->success()->send();
    }

    private function saveBreadRecord(): void
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
        $userLocale = $bread->name === 'users' ? ($values['__user_locale'] ?? null) : null;
        $rolePermissions = $this->validatedRolePermissions();
        $original = $this->recordId ? $model->newQuery()->findOrFail($this->recordId) : null;
        $singleImages = $this->validatedSingleImages($bread, $model);
        foreach ($allowed->where('type', 'select_dropdown') as $row) {
            if (! array_key_exists($row->field, $values) || blank($values[$row->field])) { continue; }
            $options = $this->dropdownOptions($row, json_decode($row->details ?: '{}', true) ?: []);
            validator(['data.'.$row->field => $values[$row->field]], ['data.'.$row->field => [\Illuminate\Validation\Rule::in(array_keys($options))]])->validate();
        }
        foreach ($allowed->where('type', 'adv_select_dropdown_tree') as $row) {
            if (! array_key_exists($row->field, $values) || blank($values[$row->field])) { continue; }
            $options = $this->treeOptions($row, json_decode($row->details ?: '{}', true) ?: []);
            validator([$row->field => $values[$row->field]], [$row->field => [\Illuminate\Validation\Rule::in(array_keys($options))]])->validate();
        }
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
            foreach ($this->registry()->manyToManyRows($bread, $this->recordId === null ? 'add' : 'edit') as $relation) {
                $key = '__pivot_' . $relation['row']->id;
                $pivots[] = [$relation, array_values(array_unique(array_map('intval', (array) ($values[$key] ?? []))))];
            }
        $relationshipColumns = collect($this->registry()->belongsToRows($bread, $this->recordId ? 'edit' : 'add'))
            ->map(fn (stdClass $row): ?string => (json_decode($row->details ?: '{}', true) ?: [])['column'] ?? null)
            ->filter()->all();
        $values = array_intersect_key($values, array_flip(array_merge($allowed->pluck('field')->all(), $relationshipColumns)));
        foreach ($this->registry()->belongsToRows($bread, $this->recordId ? 'edit' : 'add') as $relation) {
                $details = json_decode($relation->details ?: '{}', true) ?: [];
                $column = $details['column'] ?? null;
                if ($column && filled($values[$column] ?? null)
                    && ! DB::table($details['table'])->where($details['key'], $values[$column])->exists()) {
                    throw ValidationException::withMessages(['data.' . $column => 'Связанная запись не найдена.']);
                }
        }

        DB::transaction(function () use ($bread, $model, $values, $default, $allowed, $pivots, $translated, $relationshipColumns, $rolePermissions, $userLocale, $singleImages): void {
            $record = $this->recordId ? $model->newQuery()->lockForUpdate()->findOrFail($this->recordId) : $model->newInstance();
            if ($bread->name === 'users' && $this->recordId === (int) auth('filament')->id()) {
                // Match VoyagerUserController: self-edit must preserve all role assignments.
                unset($values['role_id']);
                $pivots = array_filter($pivots, static fn (array $pivot): bool => $pivot[0]['details']['table'] !== 'roles');
            }
            if ($bread->name === 'users') {
                $settings = $record->settings ?? [];
                $settings['locale'] = $userLocale;
                $record->settings = $settings;
            }
            if ($this->locale === $default) {
                foreach ($values as $field => $value) {
                    $row = $allowed->firstWhere('field', $field);
                    if ($row?->type === 'password') {
                        $value = Hash::make($value);
                    }
                    if ($this->isSelectRelation($row)) {
                        $value = app(BreadSelectRelation::class)->encode($record, $field, json_decode($row->details ?: '{}', true) ?: [], $value, 'data.'.$field, (bool) $row->required);
                    } elseif (in_array($row?->type, ['multiple_images', 'media_picker', 'select_multiple'], true)) {
                        $value = json_encode(array_values($value ?? []), JSON_UNESCAPED_SLASHES);
                    } elseif ($row?->type === 'multiple_checkbox') {
                        $value = app(\App\Filament\Bread\BreadMultiCheckbox::class)->encode($value, json_decode($row->details ?: '{}', true) ?: [], 'data.'.$field, $record->getRawOriginal($field));
                    } elseif ($row?->type === 'coordinates') {
                        $value = $this->coordinatesValue($record, $row, $value);
                    } elseif (in_array($row?->type, ['adv_json', 'adv_fields_group', 'adv_page_layout'], true)) {
                        $jsonDetails = json_decode($row->details ?: '{}', true) ?: [];
                        $value = app(match ($row->type) { 'adv_json' => BreadJsonRows::class, 'adv_page_layout' => BreadPageLayout::class, default => BreadFieldsGroup::class })->encode($jsonDetails, $record->getAttribute($field) ?? ($jsonDetails['default'] ?? null), $value, 'data.'.$field);
                    }
                    if (in_array($row?->type, ['code_editor', 'multiple_checkbox'], true) || $this->isSelectRelation($row)) { app(BreadCodeEditor::class)->assign($record, $field, $value, 'data.'.$field); }
                    else { $record->setAttribute($field, $value); }
                }
                $record->save();
                $this->recordId = (int) $record->getKey();
            } else {
                foreach ($values as $field => $value) {
                    if (! in_array($field, $translated, true)) {
                        $row = $allowed->firstWhere('field', $field);
                        if (! $row && ! in_array($field, $relationshipColumns, true)) {
                            continue;
                        }
                        if ($row?->type === 'password') {
                            $value = Hash::make($value);
                        } elseif ($this->isSelectRelation($row)) {
                            $value = app(BreadSelectRelation::class)->encode($record, $field, json_decode($row->details ?: '{}', true) ?: [], $value, 'data.'.$field, (bool) $row->required);
                        } elseif (in_array($row?->type, ['multiple_images', 'media_picker', 'select_multiple'], true)) {
                            $value = json_encode(array_values($value ?? []), JSON_UNESCAPED_SLASHES);
                        } elseif ($row?->type === 'multiple_checkbox') {
                            $value = app(\App\Filament\Bread\BreadMultiCheckbox::class)->encode($value, json_decode($row->details ?: '{}', true) ?: [], 'data.'.$field, $record->getRawOriginal($field));
                        } elseif ($row?->type === 'coordinates') {
                            $value = $this->coordinatesValue($record, $row, $value);
                        } elseif (in_array($row?->type, ['adv_json', 'adv_fields_group', 'adv_page_layout'], true)) {
                            $jsonDetails = json_decode($row->details ?: '{}', true) ?: [];
                            $value = app(match ($row->type) { 'adv_json' => BreadJsonRows::class, 'adv_page_layout' => BreadPageLayout::class, default => BreadFieldsGroup::class })->encode($jsonDetails, $record->getAttribute($field) ?? ($jsonDetails['default'] ?? null), $value, 'data.'.$field);
                        }
                        if (in_array($row?->type, ['code_editor', 'multiple_checkbox'], true) || $this->isSelectRelation($row)) { app(BreadCodeEditor::class)->assign($record, $field, $value, 'data.'.$field); }
                        else { $record->setAttribute($field, $value); }
                        continue;
                    }
                    $key = ['table_name' => $bread->name, 'column_name' => $field, 'foreign_key' => $record->getKey(), 'locale' => $this->locale];
                    $row = $allowed->firstWhere('field', $field);
                    if ($row?->type === 'select_multiple') { $value = json_encode(array_values($value ?? []), JSON_UNESCAPED_UNICODE); }
                    if ($row?->type === 'multiple_checkbox') { $value = app(\App\Filament\Bread\BreadMultiCheckbox::class)->encode($value, json_decode($row->details ?: '{}', true) ?: [], 'data.'.$field, DB::table('translations')->where($key)->value('value')); }
                    if (in_array($row?->type, ['adv_json', 'adv_fields_group', 'adv_page_layout'], true)) {
                        $originalJson = DB::table('translations')->where($key)->lockForUpdate()->value('value');
                        $value = app(match ($row->type) { 'adv_json' => BreadJsonRows::class, 'adv_page_layout' => BreadPageLayout::class, default => BreadFieldsGroup::class })->encode(json_decode($row->details ?: '{}', true) ?: [], $originalJson, $value, 'data.'.$field);
                    }
                    if (blank($value)) {
                        DB::table('translations')->where($key)->delete();
                    } else {
                        DB::table('translations')->updateOrInsert($key, ['value' => $value]);
                    }
                }
                $record->save();
            }
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
            if ($rolePermissions !== null) {
                DB::table('permission_role')->where('role_id', $record->getKey())->delete();
                foreach ($rolePermissions as $permissionId) {
                    DB::table('permission_role')->insert(['role_id' => $record->getKey(), 'permission_id' => $permissionId]);
                }
            }
            if ($singleImages !== []) { $this->persistSingleImages($record, $singleImages); }
        });
        $this->editing = false;
    }

    public function cancel(): void
    {
        $this->singleImageUploads = [];
        $this->singleImageProperties = [];
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
        $record->getConnection()->transaction(function () use ($record, $field, $files): void {
            $record->newQuery()->whereKey($record->getKey())->lockForUpdate()->firstOrFail();
            $journal = new \App\Filament\Bread\BreadMediaJournal($record->getConnection());
            foreach ($files as $file) {
                $journal->add($record, $file, $field);
            }
            $journal->enforceCollectionLimit($record, $field);
            $record->getConnection()->afterCommit(function () use ($field): void {
                $this->mediaUpload = null;
                unset($this->mediaUploads[$field]);
            });
        });
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
        $record->getConnection()->transaction(function () use ($record, $field, $mediaId, $file): void {
            $record->newQuery()->whereKey($record->getKey())->lockForUpdate()->firstOrFail();
            $old = $record->media()->where('collection_name', $field)->whereKey($mediaId)->lockForUpdate()->firstOrFail();
            $journal = new \App\Filament\Bread\BreadMediaJournal($record->getConnection());
            $new = $journal->add($record, $file, $field, $old->custom_properties);
            $new->order_column = $old->order_column;
            $new->save();
            $journal->retire($old);
            $journal->enforceCollectionLimit($record, $field);
            $record->getConnection()->afterCommit(function () use ($field, $mediaId, $old, $new): void {
                $this->mediaProperties[$new->id] = $this->mediaProperties[$old->id] ?? $old->custom_properties;
                $this->selectedMedia[$field] = array_map(fn ($id) => (int) $id === $mediaId ? (string) $new->id : (string) $id, $this->selectedMedia[$field] ?? []);
                unset($this->mediaProperties[$old->id], $this->mediaReplacements[$old->id]);
            });
        });
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
        $this->retireMediaRecords($record, $field, [$mediaId], function () use ($field, $mediaId): void {
            unset($this->mediaProperties[$mediaId], $this->mediaReplacements[$mediaId]);
            $this->selectedMedia[$field] = array_values(array_diff((array) ($this->selectedMedia[$field] ?? []), [(string) $mediaId]));
        });
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
        $this->retireMediaRecords($record, $field, $ids, function () use ($field, $ids): void {
            foreach ($ids as $id) { unset($this->mediaProperties[$id], $this->mediaReplacements[$id]); }
            $this->selectedMedia[$field] = [];
        });
        Notification::make()->title('Выбранные файлы удалены')->success()->send();
    }

    private function retireMediaRecords($record, string $field, array $ids, \Closure $onCommit): void
    {
        $record->getConnection()->transaction(function () use ($record, $field, $ids, $onCommit): void {
            $record->newQuery()->whereKey($record->getKey())->lockForUpdate()->firstOrFail();
            $files = $record->media()->where('collection_name', $field)->whereKey($ids)->orderBy('id')->lockForUpdate()->get();
            // Check the whole selection before retiring even its first item.
            abort_unless($files->count() === count($ids), 404);
            $journal = new \App\Filament\Bread\BreadMediaJournal($record->getConnection());
            foreach ($files as $media) { $journal->retire($media); }
            $record->getConnection()->afterCommit($onCommit);
        });
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
        $children = collect($this->registry()->childrenRows($bread, 'read'))->keyBy(fn (array $relation): string => $relation['row']->field);
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
            if ($row->type === 'relationship' && $children->has($row->field)) {
                $relation = $children->get($row->field);
                $fields[] = ['label' => $row->display_name ?: $row->field,
                    'value' => implode(', ', $this->registry()->childLabels($relation, $record->getAttribute($relation['details']['key'])))];
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
            if (in_array($row->type, ['adv_media_files', 'adv_image'], true) && $record instanceof HasMedia) {
                $fields[] = ['label' => $row->display_name ?: $row->field,
                    'type' => 'adv_media_files', 'value' => ($row->type === 'adv_image' ? $record->getMedia($row->field)->take(1) : $record->getMedia($row->field))->map->getUrl()->all()];

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
            if ($row->type === 'multiple_checkbox') { $fields[array_key_last($fields)]['value'] = app(\App\Filament\Bread\BreadMultiCheckbox::class)->summary($value, json_decode($row->details ?: '{}', true) ?: []); }
            if ($row->type === 'checkbox') {
                $fields[array_key_last($fields)]['value'] = app(\App\Filament\Bread\BreadCheckbox::class)->caption($value, json_decode($row->details ?: '{}', true) ?: []);
            }
            if (in_array($row->type, ['select_dropdown', 'select_multiple', 'radio_btn'], true)) {
                $fields[array_key_last($fields)]['value'] = $this->optionFieldLabel($row, $value, $record);
            }
            if ($row->type === 'coordinates') {
                $fields[array_key_last($fields)]['value'] = app(BreadCoordinates::class)->supported($record, $row->field)
                    ? app(BreadCoordinates::class)->summary(app(BreadCoordinates::class)->read($record, $row->field)) : 'Неподдерживаемая пространственная колонка';
            }
            if ($row->type === 'adv_json') { $fields[array_key_last($fields)]['value'] = app(BreadJsonRows::class)->summary($value); }
            if ($row->type === 'adv_page_layout') { $fields[array_key_last($fields)]['value'] = app(BreadPageLayout::class)->summary($value); }
            if ($row->type === 'adv_fields_group') { $fields[array_key_last($fields)]['value'] = app(BreadFieldsGroup::class)->summary($value); }
            if ($row->type === 'adv_select_dropdown_tree') {
                try { $options = app(BreadTreeOptions::class)->options($record, $row->field, json_decode($row->details ?: '{}', true) ?: []); }
                catch (ValidationException) { $options = []; }
                $fields[array_key_last($fields)]['value'] = $options[$value] ?? $value;
            }
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
        $children = collect($this->registry()->childrenRows($bread, 'browse'))->keyBy(fn (array $relation): string => $relation['row']->field);
        $columns = [];
        $select = ['id'];
        foreach ($this->registry()->rows($bread) as $row) {
            if (! $row->browse || $row->type === 'password') {
                continue;
            }
            if (in_array($row->type, ['adv_image'], true) && $model instanceof HasMedia) {
                $columns[] = ['field' => $row->field, 'label' => $row->display_name ?: $row->field, 'type' => 'adv_media_files'];
            } elseif (in_array($row->field, $physical, true)) {
                $columns[] = ['field' => $row->field, 'label' => $row->display_name ?: $row->field, 'type' => $row->type, 'details' => json_decode($row->details ?: '{}', true) ?: []];
                $select[] = $row->field;
            } elseif ($row->type === 'relationship' && $relations->has($row->field)) {
                $details = json_decode($row->details ?: '{}', true) ?: [];
                $columns[] = ['field' => $row->field, 'label' => $row->display_name ?: $row->field, 'type' => 'relationship'];
                $select[] = $details['column'];
            } elseif ($row->type === 'relationship' && $many->has($row->field)) {
                $columns[] = ['field' => $row->field, 'label' => $row->display_name ?: $row->field, 'type' => 'relationship'];
            } elseif ($row->type === 'relationship' && $children->has($row->field)) {
                $columns[] = ['field' => $row->field, 'label' => $row->display_name ?: $row->field, 'type' => 'relationship'];
                $select[] = $children[$row->field]['details']['key'];
            } elseif (in_array($row->type, ['adv_media_files', 'adv_image'], true) && $model instanceof HasMedia) {
                $columns[] = ['field' => $row->field, 'label' => $row->display_name ?: $row->field, 'type' => 'adv_media_files'];
            }
        }
        if (! collect($columns)->contains(fn (array $column): bool => $column['field'] === 'id')) {
            array_unshift($columns, ['field' => 'id', 'label' => 'ID', 'type' => 'text']);
        }

        $settings = json_decode($bread->details ?? '{}', true);
        $settings = is_array($settings) ? $settings : [];
        $scope = $settings['scope'] ?? null;
        $scopes = app(\App\Services\Admin\BreadTypeSettingsService::class)->scopes($bread);
        if (is_string($scope) && isset($scopes[$scope])) {
            $builder = $model->newModelQuery();
            $builder->{$scope}();
            $query = $builder->toBase();
        } else {
            $query = DB::table($bread->name);
        }
        $query->select(array_map(fn ($field) => $bread->name.'.'.$field, array_values(array_unique($select))));
        if ($this->filterType !== null && in_array('id_type', $physical, true)) {
            $query->where($bread->name.'.id_type', $this->filterType);
        }
        $searchable = collect($this->registry()->rows($bread))
            ->filter(fn (stdClass $row): bool => $row->browse && in_array($row->field, $physical, true) && in_array($row->type, ['text', 'text_area'], true))
            ->take(4)->pluck('field')->all();
        $defaultSearch = $settings['default_search_key'] ?? null;
        if (is_string($defaultSearch) && in_array($defaultSearch, $physical, true)
            && ! in_array($defaultSearch, ['password', 'remember_token'], true)) {
            $searchable = [$defaultSearch];
        }
        if ($this->search !== '' && $searchable !== []) {
            $search = $this->search;
            $query->where(function ($builder) use ($searchable, $search, $bread): void {
                foreach ($searchable as $field) {
                    $builder->orWhere($bread->name.'.'.$field, 'like', '%' . $search . '%');
                }
            });
        }

        $order = $settings['order_column'] ?? null;
        $direction = $settings['order_direction'] ?? 'desc';
        if (! is_string($order) || ! in_array($order, $physical, true) || in_array($order, ['password', 'remember_token'], true)) {
            $order = 'id';
            $direction = 'desc';
        }
        $query->reorder($bread->name.'.'.$order, $direction === 'asc' ? 'asc' : 'desc');
        if ($order !== 'id') { $query->orderByDesc($bread->name.'.id'); }
        $records = $query->paginate(25);
        $ids = $records->getCollection()->pluck('id')->all();
        if ($ids !== []) {
            foreach ($children as $field => $relation) {
                $details = $relation['details'];
                $keys = $records->getCollection()->pluck($details['key'])->filter(fn ($value) => $value !== null && $value !== '')->unique()->all();
                $labels = $relation['model']->newQuery()->whereIn($details['column'], $keys)->orderBy($relation['model']->getKeyName())
                    ->get([$details['column'], $details['label']])->groupBy($details['column']);
                foreach ($records as $record) {
                    $values = $labels->get($record->{$details['key']})?->pluck($details['label']) ?? collect();
                    if ($details['type'] === 'hasOne') { $values = $values->take(1); }
                    $record->{$field} = $values->implode(', ');
                }
            }
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
                            $collection = $mediaRecords->get($record->id)?->getMedia($field);
                            $single = collect($this->registry()->rows($bread))->contains(fn ($row) => $row->field === $field && $row->type === 'adv_image');
                            $record->{$field} = ($single ? $collection?->take(1) : $collection)?->map->getUrl()->all() ?: [];
                        }
                    }
                }
            }
        }

        $selectSources = collect();
        if (collect($this->registry()->rows($bread))->contains(fn ($row) => $row->browse && ($this->isSelectRelation($row) || $row->type === 'select_dropdown'))) {
            $selectSources = $model->newQuery()->whereKey($records->pluck('id'))->get()->keyBy('id');
        }
        foreach ($this->registry()->rows($bread) as $row) {
            if (! $row->browse || ! in_array($row->type, ['multiple_checkbox', 'select_dropdown', 'select_multiple', 'radio_btn', 'adv_json', 'adv_fields_group', 'adv_page_layout'], true) || ! in_array($row->field, $select, true)) { continue; }
            foreach ($records as $record) { $record->{$row->field} = in_array($row->type, ['adv_json', 'adv_fields_group', 'adv_page_layout'], true) ? app(match ($row->type) { 'adv_json' => BreadJsonRows::class, 'adv_page_layout' => BreadPageLayout::class, default => BreadFieldsGroup::class })->summary($record->{$row->field}) : $this->optionFieldLabel($row, $record->{$row->field}, $selectSources->get($record->id)); }
        }
        foreach ($this->registry()->rows($bread) as $row) {
            if (! $row->browse || $row->type !== 'coordinates' || ! in_array($row->field, $select, true)) { continue; }
            $codec = app(BreadCoordinates::class);
            $points = collect();
            if ($codec->supported($model, $row->field)) {
                $column = $model->getConnection()->getQueryGrammar()->wrap($row->field);
                $points = $model->newQuery()->whereKey($records->pluck('id'))->select('id')
                    ->selectRaw('ST_AsText('.$column.') AS coordinate_wkt')->get()->keyBy('id');
            }
            foreach ($records as $record) {
                $record->{$row->field} = $codec->supported($model, $row->field)
                    ? $codec->summary($points->get($record->id)?->coordinate_wkt) : 'Неподдерживаемая пространственная колонка';
            }
        }
        $treeRows = collect($this->registry()->rows($bread))->filter(fn ($row) => $row->browse && $row->type === 'adv_select_dropdown_tree' && in_array($row->field, $select, true));
        if ($treeRows->isNotEmpty()) {
            $sources = $model->newQuery()->whereKey($records->pluck('id'))->get()->keyBy('id');
            foreach ($treeRows as $row) {
                foreach ($records as $record) {
                    try { $options = app(BreadTreeOptions::class)->options($sources[$record->id], $row->field, json_decode($row->details ?: '{}', true) ?: []); }
                    catch (ValidationException) { $options = []; }
                    $record->{$row->field} = $options[$record->{$row->field}] ?? $record->{$row->field};
                }
            }
        }
        return ['columns' => $columns, 'rows' => $records];
    }

    private function treeComponent(stdClass $row, array $details): Select|Textarea
    {
        try { $this->treeOptions($row, $details); }
        catch (ValidationException) {
            return Textarea::make($row->field)->disabled()->dehydrated(false)->helperText('Источник дерева несовместим. Значение сохраняется без изменений.');
        }
        return Select::make($row->field)->searchable()->options(fn () => $this->treeOptions($row, $details));
    }

    private function dropdownComponent(stdClass $row, array $details): Select|Textarea
    {
        try { $this->dropdownOptions($row, $details); }
        catch (ValidationException) {
            return Textarea::make($row->field)->disabled()->dehydrated(false)->helperText('Источник списка несовместим. Значение сохраняется без изменений.');
        }
        return Select::make($row->field)->searchable()->options(fn () => $this->dropdownOptions($row, $details));
    }

    private function multiCheckboxComponent(stdClass $row, array $details): CheckboxList|Textarea
    {
        $codec = app(\App\Filament\Bread\BreadMultiCheckbox::class);
        $model = $this->registry()->model($this->bread());
        $value = null;
        if ($this->recordId !== null) {
            $record = $model->newQuery()->findOrFail($this->recordId);
            $translated = method_exists($record, 'getTranslatableAttributes') ? $record->getTranslatableAttributes() : [];
            $value = $this->locale !== config('voyager.multilingual.default', 'en') && in_array($row->field, $translated, true)
                ? DB::table('translations')->where(['table_name' => $record->getTable(), 'column_name' => $row->field, 'foreign_key' => $record->getKey(), 'locale' => $this->locale])->value('value')
                : $record->getAttribute($row->field);
        }
        try {
            $codec->validate($details);
            $cast = $model->getCasts()[$row->field] ?? null;
            if (($cast !== null && ! in_array($cast, ['string', 'array', 'json'], true)) || $codec->state($value, $details) === null) { throw ValidationException::withMessages(['details' => 'Некорректные данные группы.']); }
        } catch (ValidationException) {
            return Textarea::make($row->field)->disabled()->dehydrated(false)->helperText('Настройки или сохранённые варианты несовместимы. Значение сохраняется без изменений.');
        }
        return CheckboxList::make($row->field)->options($details['options'])->default($codec->state(null, $details));
    }

    private function imageUploadComponent(stdClass $row, array $details): FileUpload
    {
        $multiple = $row->type === 'multiple_images';
        $upload = FileUpload::make($row->field)->image()->multiple($multiple)->compactImagePreviews($multiple)
            ->disk('public')->directory($this->bread()->name.'/'.date('FY'))->maxSize(10240);
        try { app(\App\Filament\Bread\BreadImageUpload::class)->validate($details); }
        catch (ValidationException) { return $upload->disabled()->dehydrated(false)->helperText('Настройки обработки изображения несовместимы. Сохранённые файлы не изменяются.'); }
        return $upload->saveUploadedFileUsing(fn (FileUpload $component, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file) => app(\App\Filament\Bread\BreadImageUpload::class)->store($file, $component->getDiskName(), $component->getDirectory() ?? '', $details, 'data.'.$row->field,
            function (string $disk, array $paths) use ($row): void { $this->createdImageFiles[$row->field][] = [$disk, $paths]; }));
    }

    private function dropdownOptions(stdClass $row, array $details): array
    {
        $model = $this->registry()->model($this->bread());
        if ($this->recordId !== null) { $model = $model->newQuery()->findOrFail($this->recordId); }
        return app(\App\Filament\Bread\BreadDropdownOptions::class)->options($model, $row->field, $details);
    }

    private function treeOptions(stdClass $row, array $details): array
    {
        $model = $this->registry()->model($this->bread());
        if ($this->recordId !== null) { $model = $model->newQuery()->findOrFail($this->recordId); }
        return app(BreadTreeOptions::class)->options($model, $row->field, $details);
    }

    private function isSelectRelation(?stdClass $row): bool
    {
        return $row?->type === 'select_multiple' && isset((json_decode($row->details ?: '{}', true) ?: [])['relationship']);
    }

    private function selectRelationState($record, stdClass $row, array $details): mixed
    {
        try { return app(BreadSelectRelation::class)->state($record, $row->field, $details) ?? $record->getRawOriginal($row->field); }
        catch (ValidationException) { return 'Несовместимая конфигурация связи'; }
    }

    private function selectRelationComponent(stdClass $row, array $details): Fieldset|Textarea
    {
        $model = $this->registry()->model($this->bread());
        $record = $this->recordId === null ? $model : $model->newQuery()->findOrFail($this->recordId);
        $codec = app(BreadSelectRelation::class);
        try {
            $source = $codec->configuration($record, $row->field, $details);
            $options = $codec->options($record, $row->field, $details);
            $state = $codec->state($record, $row->field, $details);
        } catch (ValidationException) { $state = null; }
        if ($state === null) {
            return Textarea::make($row->field)->disabled()->dehydrated(false)->helperText('Несовместимый формат или источник связи. Значение сохранено без изменений.');
        }
        if ($source['pivot'] === []) {
            $inputs = [Select::make('ids')->label('Варианты')->multiple()->searchable()->options($options)->default($state['ids'])];
        } else {
            $fields = [Select::make('key')->label('Вариант')->options($options)->searchable()->required()];
            foreach ($source['pivot'] as $column) { $fields[] = TextInput::make('attributes.'.$column)->label($column)->maxLength(50000); }
            $inputs = [Repeater::make('rows')->schema($fields)->defaultItems(0)->default($state['rows'])->maxItems(500)->addActionLabel('Добавить вариант')];
        }
        return Fieldset::make($row->display_name ?: $row->field)->statePath($row->field)->schema($inputs)->columns(1);
    }

    private function coordinatesState($record, stdClass $row): mixed
    {
        $codec = app(BreadCoordinates::class);
        if (! $codec->supported($record, $row->field)) { return 'Неподдерживаемая пространственная колонка'; }
        $wkt = $codec->read($record, $row->field);
        return $codec->state($wkt) ?? $wkt;
    }

    private function coordinatesValue($record, stdClass $row, mixed $value): mixed
    {
        $codec = app(BreadCoordinates::class);
        try { $codec->validateConfiguration($record, $row->field, json_decode($row->details ?: '{}', true) ?: []); }
        catch (ValidationException) {
            throw ValidationException::withMessages(['data.'.$row->field => 'Конфигурация координат несовместима. Сохранение отменено.']);
        }
        return $codec->encode($record, $row->field, $value, 'data.'.$row->field, (bool) $row->required);
    }

    private function coordinatesComponent(stdClass $row, array $details): Fieldset|Textarea
    {
        $model = $this->registry()->model($this->bread());
        $record = $this->recordId === null ? $model : $model->newQuery()->findOrFail($this->recordId);
        $codec = app(BreadCoordinates::class);
        try { $codec->validateConfiguration($model, $row->field, $details); }
        catch (ValidationException) {
            return Textarea::make($row->field)->disabled()->dehydrated(false)
                ->helperText('Несовместимая конфигурация координат. Значение сохранено без изменений.');
        }
        if ($codec->state($codec->read($record, $row->field)) === null) {
            return Textarea::make($row->field)->disabled()->dehydrated(false)
                ->helperText('Геометрия отличается от POINT. Значение сохранено без изменений.');
        }
        return Fieldset::make($row->display_name ?: $row->field)->statePath($row->field)->columns(2)->schema([
            TextInput::make('lat')->label('Широта')->numeric()->minValue(-90)->maxValue(90),
            TextInput::make('lng')->label('Долгота')->numeric()->minValue(-180)->maxValue(180),
            View::make('filament.components.bread-coordinates-note')->columnSpanFull(),
        ]);
    }

    private function groupState(mixed $value, array $details): mixed
    {
        $codec = app(BreadFieldsGroup::class);
        $document = $codec->document($value === null || $value === '' ? $details : $value);
        return $document ? $codec->values($document) : $value;
    }

    private function fieldsGroupComponent(stdClass $row, array $details): Fieldset|Textarea
    {
        $original = null;
        if ($this->recordId !== null) {
            $record = $this->registry()->model($this->bread())->newQuery()->findOrFail($this->recordId);
            $translated = method_exists($record, 'getTranslatableAttributes') && in_array($row->field, $record->getTranslatableAttributes(), true);
            $original = $translated && $this->locale !== config('voyager.multilingual.default', 'en')
                ? DB::table('translations')->where(['table_name' => $this->bread()->name, 'column_name' => $row->field, 'foreign_key' => $this->recordId, 'locale' => $this->locale])->value('value')
                : $record->getAttribute($row->field);
        }
        $document = app(BreadFieldsGroup::class)->document($original === null || $original === '' ? $details : $original);
        if (! $document) {
            return Textarea::make($row->field)->disabled()->dehydrated(false)
                ->helperText('Несовместимый формат группы. Значение сохранено без изменений.');
        }
        $inputs = [];
        foreach ($document['fields'] as $key => $field) {
            $input = $field['type'] === 'textarea' ? Textarea::make($key) : TextInput::make($key);
            $input->label($field['label'])->default($field['value'] ?? null);
            if ($field['type'] === 'number') { $input->numeric(); } else { $input->maxLength(50000); }
            $inputs[] = $input;
        }
        return Fieldset::make($row->display_name ?: $row->field)->statePath($row->field.'.values')->schema($inputs)->columns(2);
    }

    private function layoutComponent(stdClass $row, array $details): Repeater|Textarea
    {
        $original = $details['default'] ?? null;
        if ($this->recordId !== null) {
            $record = $this->registry()->model($this->bread())->newQuery()->findOrFail($this->recordId);
            $translated = method_exists($record, 'getTranslatableAttributes') && in_array($row->field, $record->getTranslatableAttributes(), true);
            $original = $translated && $this->locale !== config('voyager.multilingual.default', 'en')
                ? DB::table('translations')->where(['table_name' => $this->bread()->name, 'column_name' => $row->field, 'foreign_key' => $this->recordId, 'locale' => $this->locale])->value('value')
                : ($record->getAttribute($row->field) ?? ($details['default'] ?? null));
        }
        $codec = app(BreadPageLayout::class);
        $document = $codec->document($original);
        try { $options = $document === null ? null : $codec->options($details, $document); }
        catch (ValidationException) { $options = null; }
        if ($options === null) {
            return Textarea::make($row->field)->disabled()->dehydrated(false)
                ->helperText('Несовместимый формат или источник секций. Значение сохранено без изменений.');
        }
        return Repeater::make($row->field.'.rows')->schema([
            Select::make('choice')->label('Секция')->options($options)->searchable()->required(),
        ])->defaultItems(0)->default($codec->state($original)['rows'])->maxItems(500)
            ->addActionLabel('Добавить секцию')->reorderableWithButtons()->reorderableWithDragAndDrop();
    }

    private function jsonRowsComponent(stdClass $row, array $details): Repeater|Textarea
    {
        $original = $details['default'] ?? null;
        if ($this->recordId !== null) {
            $record = $this->registry()->model($this->bread())->newQuery()->findOrFail($this->recordId);
            $translated = method_exists($record, 'getTranslatableAttributes') && in_array($row->field, $record->getTranslatableAttributes(), true);
            $original = $translated && $this->locale !== config('voyager.multilingual.default', 'en')
                ? DB::table('translations')->where(['table_name' => $this->bread()->name, 'column_name' => $row->field, 'foreign_key' => $this->recordId, 'locale' => $this->locale])->value('value')
                : ($record->getAttribute($row->field) ?? ($details['default'] ?? null));
        }
        $json = app(BreadJsonRows::class);
        $document = $json->document($original);
        if ($document === null) {
            return Textarea::make($row->field)->disabled()->dehydrated(false)
                ->helperText('Сохранённый JSON имеет другой формат. Значение сохранено без изменений; редактор строк недоступен.');
        }
        $inputs = [];
        try { $fields = $json->fields($details, $document); }
        catch (ValidationException) {
            return Textarea::make($row->field)->disabled()->dehydrated(false)
                ->helperText('Настройка json_fields несовместима с редактором строк. Сохранённое значение не изменяется.');
        }
        foreach ($fields as $key => $label) {
            $inputs[] = TextInput::make($key)->label($label)->maxLength(50000);
        }
        return Repeater::make($row->field.'.rows')->schema($inputs)->columns(2)
            ->defaultItems(0)->default(array_values($document['rows']))->maxItems(500)
            ->addActionLabel('Добавить строку')->reorderableWithButtons()->reorderableWithDragAndDrop();
    }

    private function optionFieldLabel(stdClass $row, mixed $value, ?\Illuminate\Database\Eloquent\Model $sourceRecord = null): string
    {
        $details = json_decode($row->details ?: '{}', true) ?: [];
        $options = $details['options'] ?? [];
        if ($row->type === 'multiple_checkbox') { return app(\App\Filament\Bread\BreadMultiCheckbox::class)->summary($value, $details); }
        if ($row->type === 'select_dropdown') {
            try { $options = app(\App\Filament\Bread\BreadDropdownOptions::class)->options($sourceRecord ?? $this->registry()->model($this->bread()), $row->field, $details); }
            catch (ValidationException) { $options = []; }
        }
        if ($this->isSelectRelation($row)) {
            try { $options = app(BreadSelectRelation::class)->options($sourceRecord ?? $this->registry()->model($this->bread()), $row->field, $details); }
            catch (ValidationException) { $options = []; }
            $values = is_array($value) ? $value : (json_decode((string) $value, true) ?: []);
            $values = empty($details['relationship']['editablePivotFields']) ? $values : array_keys($values);
            return implode(', ', array_map(fn ($key) => is_scalar($key) ? (string) ($options[$key] ?? $key) : '', $values));
        }

        $values = $row->type === 'select_multiple' ? (is_array($value) ? $value : (json_decode((string) $value, true) ?: [])) : [$value];
        return implode(', ', array_map(static fn ($key): string => is_scalar($key) ? (string) ($options[$key] ?? $key) : '', $values));
    }

    private function fillRecord($record): void
    {
        $bread = $this->bread();
        $values = [];
        foreach ($this->registry()->editableRows($bread, 'edit') as $row) {
            if ($this->locale === config('voyager.multilingual.default', 'en') || ! (method_exists($record, 'getTranslatableAttributes') && in_array($row->field, $record->getTranslatableAttributes(), true))) {
                $details = json_decode($row->details ?: '{}', true) ?: [];
                $value = $record->getAttribute($row->field) ?? ($row->type === 'checkbox' ? app(\App\Filament\Bread\BreadCheckbox::class)->initial($details) : ($details['default'] ?? null));
                $values[$row->field] = match ($row->type) {
                    'password' => null,
                    'code_editor' => app(BreadCodeEditor::class)->state($record, $row->field, $details),
                    'coordinates' => $this->coordinatesState($record, $row),
                    'adv_json' => app(BreadJsonRows::class)->document($value) ?? $value,
                    'adv_page_layout' => app(BreadPageLayout::class)->state($value),
                    'adv_fields_group' => $this->groupState($value, $details),
                    'select_multiple' => $this->isSelectRelation($row) ? $this->selectRelationState($record, $row, $details) : (is_array($value) ? array_values($value) : (json_decode((string) $value, true) ?: [])),
                    'multiple_images', 'media_picker' => is_array($value) ? array_values($value) : (json_decode((string) $value, true) ?: []),
                    'multiple_checkbox' => app(\App\Filament\Bread\BreadMultiCheckbox::class)->state($record->getAttribute($row->field), $details) ?? $record->getAttribute($row->field),
                    default => $value,
                };
            } elseif (method_exists($record, 'getTranslatableAttributes') && in_array($row->field, $record->getTranslatableAttributes(), true)) {
                $values[$row->field] = DB::table('translations')->where([
                    'table_name' => $bread->name, 'column_name' => $row->field,
                    'foreign_key' => $record->getKey(), 'locale' => $this->locale,
                ])->value('value');
                if ($row->type === 'select_multiple') { $values[$row->field] = json_decode($values[$row->field] ?? '[]', true) ?: []; }
                if ($row->type === 'multiple_checkbox') { $values[$row->field] = app(\App\Filament\Bread\BreadMultiCheckbox::class)->state($values[$row->field], json_decode($row->details ?: '{}', true) ?: []) ?? $values[$row->field]; }
                if ($row->type === 'adv_json') { $values[$row->field] = app(BreadJsonRows::class)->document($values[$row->field]) ?? $values[$row->field]; }
                if ($row->type === 'adv_page_layout') { $values[$row->field] = app(BreadPageLayout::class)->state($values[$row->field]); }
                if ($row->type === 'adv_fields_group') { $values[$row->field] = $this->groupState($values[$row->field], json_decode($row->details ?: '{}', true) ?: []); }
            }
        }
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
        if ($bread->name === 'users') {
            $values['__user_locale'] = $record->locale ?? config('app.locale', 'en');
        }
        $this->form->fill($values);
        $this->initializeSlugTracking();
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
