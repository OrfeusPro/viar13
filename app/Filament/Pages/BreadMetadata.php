<?php

namespace App\Filament\Pages;

use App\Filament\Bread\BreadRegistry;
use App\Services\Admin\BreadMetadataService;
use App\Services\Admin\BreadTypeSettingsService;
use App\Services\Admin\BreadCreationService;
use Filament\Forms\Components\Repeater;
use Illuminate\Support\Str;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;

class BreadMetadata extends Page
{
    protected static ?string $slug = 'bread-metadata';
    protected static ?string $navigationLabel = 'Настройки BREAD';
    protected static bool $shouldRegisterNavigation = false;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected string $view = 'filament.pages.bread-metadata';
    #[Locked] public int $typeId = 0;
    #[Locked] public ?int $rowId = null;
    #[Locked] public ?string $fingerprint = null;
    public bool $editing = false;
    #[Locked] public bool $editingSettings = false;
    #[Locked] public bool $creatingSection = false;
    #[Locked] public string $creationSnapshot = '';
    #[Locked] public string $creationTable = '';
    public array $data = [];
    #[Locked] public bool $fullEditor = false;
    #[Locked] public array $originalRows = [];
    #[Locked] public string $schemaSnapshot = '';
    public array $fieldRows = [];
    #[Locked] public array $newFields = [];
    #[Locked] public string $translationSnapshot = '';
    public array $metadataTranslations = [];
    #[Locked] public string $metadataLocale = 'en';
    public bool $relationshipEditor = false;
    #[Locked] public ?string $relationshipRow = null;
    public array $relationship = [];
    #[Locked] public array $deletedRelationships = [];

    public static function canAccess(): bool
    {
        $user = auth('filament')->user();
        return $user && $user->hasPermission('browse_admin') && $user->hasPermission('browse_bread');
    }

    public function getTitle(): string { return $this->fullEditor ? 'Редактирование BREAD: '.$this->getSettingsContext()?->name : 'Настройки BREAD'; }
    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
        $this->typeId = (int) (DB::table('data_types')->orderBy('display_name_plural')->value('id') ?: 0);
        $section = request()->query('section');
        $create = request()->query('create');
        if (is_scalar($section) && ctype_digit((string) $section)) { $this->selectType((int) $section); $this->openFullEditor(); }
        if (is_string($create) && $create !== '') { $this->openSectionCreation($create); }
    }
    public function hydrate(): void { abort_unless(static::canAccess(), 403); }
    public function types(): array
    {
        abort_unless(static::canAccess(), 403);
        return DB::table('data_types')->orderBy('display_name_plural')->get()->all();
    }
    public function rows(): array
    {
        abort_unless(static::canAccess(), 403);
        return DB::table('data_rows')->where('data_type_id', $this->typeId)->orderBy('order')->orderBy('id')->get()->all();
    }
    public function columns(): array
    {
        $type = DB::table('data_types')->find($this->typeId);
        return $type ? app(BreadRegistry::class)->columns($type) : [];
    }
    public function selectType(int $id): void
    {
        abort_unless(static::canAccess(), 403);
        abort_unless(DB::table('data_types')->where('id', $id)->exists(), 404);
        $fullEditor = $this->fullEditor;
        $this->typeId = $id;
        $this->cancel();
        if ($fullEditor) { $this->openFullEditor(); }
    }
    public function openEdit(int $id): void
    {
        abort_unless(static::canAccess(), 403);
        $this->creatingSection = false; $this->fullEditor = false;
        $this->editingSettings = false;
        $row = DB::table('data_rows')->where('data_type_id', $this->typeId)->find($id);
        abort_unless($row, 404);
        $this->rowId = $id;
        $this->fingerprint = app(BreadMetadataService::class)->fingerprint($row);
        $this->editing = true;
        $values = (array) $row;
        foreach (BreadMetadataService::FLAGS as $flag) { $values[$flag] = (bool) $row->{$flag}; }
        $values['details'] = $row->details ?: '{}';
        $this->form->fill($values);
        $this->resetErrorBag();
    }
    public function openCreate(): void
    {
        abort_unless(static::canAccess(), 403);
        $this->creatingSection = false; $this->fullEditor = false;
        $this->editingSettings = false;
        $this->rowId = null;
        $this->fingerprint = null;
        $this->editing = true;
        $this->form->fill(['type' => 'text', 'details' => '{}', 'order' => ((int) DB::table('data_rows')->where('data_type_id', $this->typeId)->max('order')) + 1,
            'required' => false, 'browse' => true, 'read' => true, 'add' => true, 'edit' => true, 'delete' => true]);
        $this->resetErrorBag();
    }
    public function form(Schema $schema): Schema
    {
        if ($this->creatingSection) {
            $service = app(BreadCreationService::class);
            $type = (object) ['name' => $this->creationTable];
            $controllers = app(\App\Filament\Bread\BreadControllerOptions::class);
            $policies = app(\App\Filament\Bread\BreadPolicyOptions::class);
            return $schema->statePath('data')->columns(2)->components([
                TextInput::make('name')->label('Таблица')->disabled()->dehydrated(),
                Select::make('model_name')->label('Модель')->options($service->models($this->creationTable))->required()
                    ->helperText('Существующая модель выбранной таблицы. Если вариантов нет, сначала добавьте совместимую модель в проект.'),
                TextInput::make('display_name_singular')->label('Название одной записи')->required(fn () => ! $this->fullEditor || $this->metadataLocale === config('voyager.multilingual.default', 'en'))->maxLength(191),
                TextInput::make('display_name_plural')->label('Название раздела')->required(fn () => ! $this->fullEditor || $this->metadataLocale === config('voyager.multilingual.default', 'en'))->maxLength(191),
                TextInput::make('slug')->label('URL раздела')->required()->maxLength(191),
                TextInput::make('icon')->label('Иконка')->maxLength(191),
                Select::make('controller')->label('Контроллер')->options($controllers->options($type))
                    ->placeholder('Стандартный BREAD')->searchable()
                    ->disabled(! $controllers->supports($type, null))->dehydrated($controllers->supports($type, null))
                    ->helperText('Доступны перенесённые обработчики выбранной таблицы.'),
                Select::make('policy_name')->label('Политика')->options($policies->options($type))
                    ->placeholder('Стандартные права BREAD')->searchable()
                    ->disabled(! $policies->supports($type, null))->dehydrated($policies->supports($type, null))
                    ->helperText('Права ролей назначаются отдельно. Для пользователей доступна политика собственного профиля.'),
                Textarea::make('description')->label('Описание')->rows(3)->columnSpanFull(),
                Toggle::make('generate_permissions')->label('Создать разрешения BREAD')
                    ->helperText('Создаются определения browse/read/edit/add/delete. Ролям они автоматически не назначаются.'),
                Toggle::make('add_menu')->label('Добавить пункт в меню admin'),
                Repeater::make('rows')->label('Поля таблицы')->addable(false)->deletable(false)->reorderable(false)->columns(3)->columnSpanFull()
                    ->schema([
                        TextInput::make('field')->label('Колонка')->disabled()->dehydrated(),
                        TextInput::make('sql_type')->label('Тип в БД')->disabled()->dehydrated(false),
                        TextInput::make('display_name')->label('Подпись')->required(),
                        Select::make('type')->label('Тип поля')->options(array_combine(BreadMetadataService::TYPES, BreadMetadataService::TYPES))->required(),
                        TextInput::make('order')->label('Порядок')->numeric()->required(),
                        Toggle::make('required')->label('Обязательное'),
                        Toggle::make('browse')->label('В списке'), Toggle::make('read')->label('При просмотре'),
                        Toggle::make('add')->label('При создании'), Toggle::make('edit')->label('При редактировании'), Toggle::make('delete')->label('При удалении'),
                        Textarea::make('details')->label('Параметры (JSON)')->rows(2)->required()->columnSpanFull(),
                    ]),
            ]);
        }
        if ($this->editingSettings) {
            $type = DB::table('data_types')->find($this->typeId);
            $columns = array_values(array_diff($this->columns(), ['password', 'remember_token']));
            $columns = array_combine($columns, $columns);
            return $schema->statePath('data')->columns(2)->components([
                TextInput::make('name')->label('Таблица')->disabled()->dehydrated(false),
                Select::make('model_name')->label('Модель')->options($type ? [$type->model_name => $type->model_name, ...app(BreadCreationService::class)->models($type->name)] : [])->required()
                    ->helperText('Существующие модели этой таблицы. Как в оригинальном редакторе BREAD, классы подготавливаются отдельно.'),
                TextInput::make('display_name_singular')->label('Название одной записи')->required(fn () => ! $this->fullEditor || $this->metadataLocale === config('voyager.multilingual.default', 'en'))->maxLength(191),
                TextInput::make('display_name_plural')->label('Название раздела')->required(fn () => ! $this->fullEditor || $this->metadataLocale === config('voyager.multilingual.default', 'en'))->maxLength(191),
                TextInput::make('slug')->label('URL раздела')->required()->maxLength(191)
                    ->helperText('После изменения старые прямые ссылки перестанут работать. Связанные ссылки меню обновятся.'),
                TextInput::make('icon')->label('Иконка')->maxLength(191)->helperText('Например: voyager-images или heroicon-o-photo.'),
                Textarea::make('description')->label('Описание')->rows(3)->maxLength(5000)->columnSpanFull(),
                Select::make('controller')->label('Контроллер')
                    ->options($type ? app(\App\Filament\Bread\BreadControllerOptions::class)->options($type) : [])
                    ->placeholder('Стандартный BREAD')->searchable()
                    ->disabled(! $type || ! app(\App\Filament\Bread\BreadControllerOptions::class)->supports($type, $type->controller ?? null))
                    ->dehydrated($type && app(\App\Filament\Bread\BreadControllerOptions::class)->supports($type, $type->controller ?? null))
                    ->helperText($type && app(\App\Filament\Bread\BreadControllerOptions::class)->supports($type, $type->controller ?? null)
                        ? 'Выбор обработчика раздела. Доступны перенесённые варианты для этой таблицы.'
                        : 'Текущее значение сохранено. Выбор станет доступен после переноса этого обработчика.'),
                Select::make('policy_name')->label('Политика')
                    ->options($type ? app(\App\Filament\Bread\BreadPolicyOptions::class)->options($type) : [])
                    ->placeholder('Стандартные права BREAD')->searchable()
                    ->disabled(! $type || ! app(\App\Filament\Bread\BreadPolicyOptions::class)->supports($type, $type->policy_name ?? null))
                    ->dehydrated($type && app(\App\Filament\Bread\BreadPolicyOptions::class)->supports($type, $type->policy_name ?? null))
                    ->helperText($type && app(\App\Filament\Bread\BreadPolicyOptions::class)->supports($type, $type->policy_name ?? null)
                        ? 'Определяет доступ к действиям раздела. Права ролей меняются отдельно.'
                        : 'Текущее значение сохранено. Выбор станет доступен после переноса этой политики.'),
                Toggle::make('generate_permissions')->label('Генерировать разрешения')->disabled(! \Illuminate\Support\Facades\Schema::hasColumn('data_types', 'generate_permissions'))->dehydrated(\Illuminate\Support\Facades\Schema::hasColumn('data_types', 'generate_permissions'))->helperText('Создаёт определения прав BREAD. Ролям права автоматически не назначаются. Выключение не удаляет существующие права.'),
                Toggle::make('server_side')->label('Серверная пагинация')->disabled()->dehydrated(false)->helperText('Текущее значение Voyager; новый список пока всегда использует серверную пагинацию.'),
                Select::make('order_column')->label('Колонка сортировки')->options($columns)->placeholder('ID по убыванию'),
                Select::make('order_display_column')->label('Колонка подписи при упорядочивании')->options($columns)->placeholder('Не задана'),
                Select::make('order_direction')->label('Направление сортировки')->options(['asc' => 'По возрастанию', 'desc' => 'По убыванию'])->required(),
                Select::make('default_search_key')->label('Колонка поиска')->options($columns)->placeholder('По видимым текстовым полям'),
                Select::make('scope')->label('Фильтр модели (scope)')->options($type ? app(BreadTypeSettingsService::class)->scopes($type) : [])->placeholder('Без фильтра')
                    ->helperText('Доступны существующие публичные scopes без обязательных дополнительных параметров. Настройки списка применяются в общей BREAD-странице.'),
            ]);
        }
        $options = array_combine(BreadMetadataService::TYPES, BreadMetadataService::TYPES);
        if (isset($this->data['type'])) { $options[$this->data['type']] = $this->data['type']; }
        $fields = [TextInput::make('field')->label('Поле')->required()->disabled($this->rowId !== null)
            ->helperText('Имя существующей колонки или виртуального поля связи. Имя сохранённого поля не меняется.'),
            TextInput::make('display_name')->label('Подпись')->required(), Select::make('type')->label('Тип поля')->options($options)->required(),
            TextInput::make('order')->label('Порядок')->numeric()->required(),
            Textarea::make('details')->label('Параметры (JSON)')->rows(8)->required()->columnSpanFull()
                ->helperText('Варианты: {"options":{"1":"Да","0":"Нет"}}. Связь: {"type":"belongsTo","table":"users","column":"user_id","key":"id","label":"email"}. Вкладка: tab_title; значение по умолчанию: default.')];
        foreach (['required' => 'Обязательное', 'browse' => 'В списке', 'read' => 'При просмотре', 'add' => 'При создании', 'edit' => 'При редактировании', 'delete' => 'При удалении'] as $flag => $label) {
            $fields[] = Toggle::make($flag)->label($label);
        }
        return $schema->statePath('data')->columns(2)->components($fields);
    }
    public function save(): void
    {
        abort_unless(static::canAccess() && $this->editing, 403);
        if ($this->fullEditor) {
            $this->captureMetadataLanguage();
            $values = $this->form->getState();
            $base = config('voyager.multilingual.default', 'en');
            foreach (['display_name_singular', 'display_name_plural'] as $key) { $values[$key] = $this->metadataTranslations[$base][$key]; }
            DB::transaction(function () use ($values): void {
                $drafts = $this->metadataTranslations;
                $type = DB::table('data_types')->lockForUpdate()->find($this->typeId);
                abort_unless($type, 404);
                $rows = DB::table('data_rows')->where('data_type_id', $this->typeId)->orderBy('order')->orderBy('id')->lockForUpdate()->get();
                if ($rows->mapWithKeys(fn ($row) => [(string) $row->id => app(BreadMetadataService::class)->fingerprint($row)])->all() !== $this->originalRows
                    || app(BreadCreationService::class)->snapshot($type->name) !== $this->schemaSnapshot) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['fieldRows' => 'Поля или структура изменены в другой вкладке. Откройте редактор заново.']);
                }
                $received = array_map('strval', array_keys($this->fieldRows));
                $expected = [...array_values(array_diff($rows->pluck('id')->map(fn ($id) => (string) $id)->all(), $this->deletedRelationships)), ...array_keys($this->newFields)];
                sort($received); sort($expected);
                if ($received !== $expected) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['fieldRows' => 'Список полей изменён. Откройте редактор заново.']);
                }
                app(BreadTypeSettingsService::class)->save(auth('filament')->user(), $this->typeId, $values, $this->fingerprint);
                foreach ($rows as $row) {
                    if (in_array((string) $row->id, $this->deletedRelationships, true)) { continue; }
                    $input = $this->fieldRows[$row->id];
                    $input['display_name'] = $this->metadataTranslations[config('voyager.multilingual.default', 'en')]['rows'][$row->id];
                    if ($input === $this->editableRow($row)) { continue; }
                    try { app(BreadMetadataService::class)->save(auth('filament')->user(), $this->typeId, $row->id, $input, $this->originalRows[$row->id]); }
                    catch (\Illuminate\Validation\ValidationException $error) {
                        $messages = [];
                        foreach ($error->errors() as $key => $errors) { $messages['fieldRows.'.$row->id.'.'.$key] = array_map(fn ($message) => $row->field.': '.$message, $errors); }
                        throw \Illuminate\Validation\ValidationException::withMessages($messages);
                    }
                }
                foreach ($this->newFields as $key => $field) {
                    $input = $this->fieldRows[$key];
                    $input['field'] = $field;
                    $input['display_name'] = $this->metadataTranslations[config('voyager.multilingual.default', 'en')]['rows'][$key];
                    $id = app(BreadMetadataService::class)->save(auth('filament')->user(), $this->typeId, null, $input, null);
                    foreach ($drafts as &$draft) {
                        if (array_key_exists($key, $draft['rows'] ?? [])) { $draft['rows'][$id] = $draft['rows'][$key]; unset($draft['rows'][$key]); }
                    }
                    unset($draft);
                }
                $translationService = app(\App\Services\Admin\BreadMetadataTranslationService::class);
                $translationService->save(auth('filament')->user(), $this->typeId, $rows->pluck('id')->all(),
                    array_map(fn ($draft) => [...$draft, 'rows' => array_intersect_key($draft['rows'] ?? [], $this->originalRows)], $drafts), $this->translationSnapshot);
                // New rows had no previous translations; save their drafts after the original-row stale check.
                $allIds = DB::table('data_rows')->where('data_type_id', $this->typeId)->pluck('id')->all();
                $translationService->save(auth('filament')->user(), $this->typeId, $allIds, $drafts,
                    $translationService->fingerprint($this->typeId, $allIds));
                if ($this->deletedRelationships !== []) {
                    if (\Illuminate\Support\Facades\Schema::hasTable('translations')) {
                        DB::table('translations')->where('table_name', 'data_rows')->whereIn('foreign_key', $this->deletedRelationships)->delete();
                    }
                    DB::table('data_rows')->where('data_type_id', $this->typeId)->where('type', 'relationship')->whereIn('id', $this->deletedRelationships)->delete();
                }
            });
            $this->openFullEditor();
            Notification::make()->success()->title('Настройки BREAD сохранены')->send();
            return;
        } elseif ($this->creatingSection) {
            $values = $this->form->getState();
            $values['name'] = $this->creationTable;
            $values['rows'] = array_values($values['rows']);
            $this->typeId = app(BreadCreationService::class)->create(auth('filament')->user(), $values, $this->creationSnapshot);
        } elseif ($this->editingSettings) {
            app(BreadTypeSettingsService::class)->save(auth('filament')->user(), $this->typeId, $this->form->getState(), $this->fingerprint);
        } else {
            app(BreadMetadataService::class)->save(auth('filament')->user(), $this->typeId, $this->rowId, $this->form->getState(), $this->fingerprint);
        }
        $this->cancel();
        Notification::make()->success()->title('Настройки BREAD сохранены')->send();
    }
    public function openSettings(): void
    {
        abort_unless(static::canAccess(), 403);
        $this->creatingSection = false; $this->fullEditor = false;
        $type = DB::table('data_types')->find($this->typeId);
        abort_unless($type && app(BreadRegistry::class)->model($type), 409);
        $this->rowId = null;
        $this->fingerprint = app(BreadMetadataService::class)->fingerprint($type);
        $this->editing = $this->editingSettings = true;
        $details = json_decode($type->details ?: '{}', true);
        $details = is_array($details) ? $details : [];
        $this->form->fill(array_merge((array) $type, [
            'order_column' => $details['order_column'] ?? null, 'order_direction' => $details['order_direction'] ?? 'desc',
            'order_display_column' => $details['order_display_column'] ?? null,
            'default_search_key' => $details['default_search_key'] ?? null, 'scope' => $details['scope'] ?? null,
        ]));
        $this->resetErrorBag();
    }
    public function getSettingsContext(): ?object { return DB::table('data_types')->find($this->typeId); }
    public function openFullEditor(): void
    {
        $this->openSettings();
        $this->relationshipEditor = false;
        $this->relationshipRow = null;
        $this->deletedRelationships = [];
        $this->fullEditor = true;
        $this->fieldRows = $this->originalRows = $this->newFields = [];
        foreach ($this->rows() as $row) {
            $this->fieldRows[$row->id] = $this->editableRow($row);
            $this->originalRows[$row->id] = app(BreadMetadataService::class)->fingerprint($row);
        }
        foreach (app(BreadCreationService::class)->defaults($this->getSettingsContext()->name) as $row) {
            if (in_array($row['field'], array_column($this->fieldRows, 'field'), true)) { continue; }
            $key = 'new_'.$row['field'];
            unset($row['sql_type']);
            $row['order'] = count($this->fieldRows) + 1;
            $this->fieldRows[$key] = $row;
            $this->newFields[$key] = $row['field'];
        }
        $this->metadataLocale = config('voyager.multilingual.default', 'en');
        $this->metadataTranslations = [];
        $this->captureMetadataLanguage();
        $translations = app(\App\Services\Admin\BreadMetadataTranslationService::class);
        $this->translationSnapshot = $translations->fingerprint($this->typeId, array_keys($this->originalRows));
        foreach ($translations->existing($this->typeId, array_keys($this->originalRows)) as $translation) {
            if ($translation['table_name'] === 'data_types') { $this->metadataTranslations[$translation['locale']][$translation['column_name']] = $translation['value']; }
            else { $this->metadataTranslations[$translation['locale']]['rows'][$translation['foreign_key']] = $translation['value']; }
        }
        $this->schemaSnapshot = app(BreadCreationService::class)->snapshot($this->getSettingsContext()->name);
    }
    private function captureMetadataLanguage(): void
    {
        $this->metadataTranslations[$this->metadataLocale] = [
            'display_name_singular' => $this->data['display_name_singular'] ?? '',
            'display_name_plural' => $this->data['display_name_plural'] ?? '',
            'rows' => array_map(fn ($row) => $row['display_name'] ?? '', $this->fieldRows),
        ];
    }
    public function switchMetadataLanguage(string $locale): void
    {
        abort_unless(static::canAccess() && $this->fullEditor && in_array($locale, app(BreadRegistry::class)->locales(), true), 403);
        $this->captureMetadataLanguage();
        $this->metadataLocale = $locale;
        foreach (['display_name_singular', 'display_name_plural'] as $key) { $this->data[$key] = $this->metadataTranslations[$locale][$key] ?? ''; }
        foreach ($this->fieldRows as $id => &$row) { $row['display_name'] = $this->metadataTranslations[$locale]['rows'][$id] ?? ''; }
        unset($row);
    }
    public function editorRows(): array
    {
        $rows = [];
        foreach ($this->fieldRows as $id => $row) { $rows[] = (object) ['id' => $id, ...$row]; }
        usort($rows, fn ($a, $b) => (int) $a->order <=> (int) $b->order);
        return $rows;
    }
    public function moveField(string $from, string $to): void
    {
        abort_unless(static::canAccess() && $this->fullEditor, 403);
        if (! isset($this->fieldRows[$from], $this->fieldRows[$to])) { abort(422); }
        if ($from === $to) { return; }
        $ids = array_map(fn ($row) => (string) $row->id, $this->editorRows());
        $ids = array_values(array_diff($ids, [$from]));
        array_splice($ids, array_search($to, $ids, true), 0, [$from]);
        foreach ($ids as $order => $id) { $this->fieldRows[$id]['order'] = $order + 1; }
    }
    public function relationshipTables(): array
    {
        abort_unless(static::canAccess(), 403);
        return \Illuminate\Support\Facades\Schema::getTableListing(\Illuminate\Support\Facades\Schema::getCurrentSchemaName(), false);
    }
    public function relationshipColumns(string $table): array
    {
        return in_array($table, $this->relationshipTables(), true) ? \Illuminate\Support\Facades\Schema::getColumnListing($table) : [];
    }
    public function openRelationship(?string $row = null): void
    {
        abort_unless(static::canAccess() && $this->fullEditor, 403);
        if ($row !== null) { abort_unless(isset($this->fieldRows[$row]) && $this->fieldRows[$row]['type'] === 'relationship', 404); }
        $this->relationshipRow = $row;
        $this->relationship = $row ? (json_decode($this->fieldRows[$row]['details'], true) ?: []) :
            ['type' => 'belongsTo', 'table' => '', 'model' => '', 'column' => '', 'key' => 'id', 'label' => '', 'pivot_table' => '', 'taggable' => false];
        $this->relationship['taggable'] = app(\App\Services\Admin\BreadTagCreationService::class)->enabled($this->relationship);
        $this->relationshipEditor = true;
    }
    public function stageRelationship(): void
    {
        abort_unless(static::canAccess() && $this->fullEditor && $this->relationshipEditor, 403);
        $rules = ['type' => 'required|in:belongsTo,belongsToMany,hasOne,hasMany',
            'table' => ['required', \Illuminate\Validation\Rule::in($this->relationshipTables())],
            'model' => 'nullable|string', 'column' => 'nullable|string', 'key' => 'required|string', 'label' => 'required|string',
            'pivot_table' => 'nullable|string', 'taggable' => 'boolean'];
        $values = validator(['relationship' => $this->relationship], collect($rules)->mapWithKeys(fn ($rule, $key) => ['relationship.'.$key => $rule])->all())->validate()['relationship'];
        $models = app(BreadCreationService::class)->models($values['table']);
        if (! isset($models[$values['model'] ?? ''])) {
            throw \Illuminate\Validation\ValidationException::withMessages(['relationship.model' => 'Выберите существующую модель связанной таблицы.']);
        }
        $values['pivot'] = $values['type'] === 'belongsToMany' ? '1' : '0';
        $values['taggable'] = $values['type'] === 'belongsToMany' && ($values['taggable'] ?? false) ? 'on' : '0';
        app(BreadMetadataService::class)->validateRelationship($this->getSettingsContext(), $values);
        $key = $this->relationshipRow;
        if ($key === null) {
            $field = strtolower(Str::singular($this->getSettingsContext()->name).'_'.$values['type'].'_'.Str::singular($values['table']).'_relationship');
            $original = $field;
            for ($index = 1; in_array($field, array_column($this->fieldRows, 'field'), true); $index++) { $field = $original.'_'.$index; }
            $key = 'new_'.$field;
            $this->newFields[$key] = $field;
            $this->fieldRows[$key] = ['field' => $field, 'type' => 'relationship', 'display_name' => Str::headline($values['table']),
                'order' => count($this->fieldRows) + 1, 'details' => '{}', 'required' => false, 'browse' => true, 'read' => true,
                'edit' => true, 'add' => true, 'delete' => false];
            $base = config('voyager.multilingual.default', 'en');
            $this->metadataTranslations[$base]['rows'][$key] = Str::headline($values['table']);
        }
        $details = json_decode($this->fieldRows[$key]['details'], true) ?: [];
        $this->fieldRows[$key]['details'] = json_encode(array_merge($details, $values), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->relationshipEditor = false;
        $this->resetErrorBag();
    }
    public function removeRelationship(string $id): void
    {
        abort_unless(static::canAccess() && $this->fullEditor && isset($this->fieldRows[$id]) && $this->fieldRows[$id]['type'] === 'relationship', 403);
        if (isset($this->newFields[$id])) { unset($this->newFields[$id]); }
        else { $this->deletedRelationships[] = $id; }
        unset($this->fieldRows[$id]);
        foreach ($this->metadataTranslations as &$draft) { unset($draft['rows'][$id]); }
        unset($draft);
        $this->relationshipEditor = false;
    }
    private function editableRow(object $row): array
    {
        $values = array_intersect_key((array) $row, array_flip(['field', 'display_name', 'type', 'order', 'details', ...BreadMetadataService::FLAGS]));
        foreach (BreadMetadataService::FLAGS as $flag) { $values[$flag] = (bool) $row->{$flag}; }
        $values['details'] = $row->details ?: '{}';
        return $values;
    }
    public function sqlColumns(): array
    {
        abort_unless(static::canAccess(), 403);
        $type = $this->getSettingsContext();
        if (! $type) { return []; }
        $indexes = \Illuminate\Support\Facades\Schema::getIndexes($type->name);
        return collect(\Illuminate\Support\Facades\Schema::getColumns($type->name))->map(function ($column) use ($indexes) {
            $column['key'] = '';
            foreach ($indexes as $index) {
                if (in_array($column['name'], $index['columns'], true)) {
                    $column['key'] = $index['primary'] ? 'PRI' : ($index['unique'] ? 'UNI' : 'INDEX');
                    if ($index['primary']) { break; }
                }
            }
            return $column;
        })->keyBy('name')->all();
    }
    public function availableTables(): array
    {
        abort_unless(static::canAccess(), 403);
        return app(BreadCreationService::class)->tables();
    }
    public function openSectionCreation(?string $table = null): void
    {
        abort_unless(static::canAccess(), 403);
        if ($table === '') { $this->cancel(); return; }
        $service = app(BreadCreationService::class);
        $table ??= $this->availableTables()[0] ?? null;
        abort_unless($table && in_array($table, $this->availableTables(), true), 409);
        $this->cancel();
        $this->creationTable = $table;
        $this->creationSnapshot = $service->snapshot($table);
        $this->editing = $this->creatingSection = true;
        $singular = Str::headline(Str::singular($table));
        $this->form->fill(['name' => $table, 'slug' => Str::slug(str_replace('_', '-', $table)),
            'display_name_singular' => $singular, 'display_name_plural' => Str::plural($singular),
            'model_name' => array_key_first($service->models($table)), 'generate_permissions' => true,
            'add_menu' => true, 'rows' => $service->defaults($table)]);
    }
    public function cancel(): void { $this->editing = false; $this->editingSettings = false; $this->creatingSection = false; $this->fullEditor = false; $this->creationTable = ''; $this->creationSnapshot = ''; $this->rowId = null; $this->fingerprint = null; $this->data = []; $this->resetErrorBag(); }
}
