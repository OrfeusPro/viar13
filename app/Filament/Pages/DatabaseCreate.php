<?php

namespace App\Filament\Pages;

use App\Services\Admin\DatabaseTableCreationService;
use App\Services\Admin\DatabaseModelGenerationService;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Field;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

class DatabaseCreate extends Page
{
    protected static ?string $slug = 'database-tables/create';
    protected static bool $shouldRegisterNavigation = false;
    protected string $view = 'filament.pages.database-create';
    public array $data = [];
    #[Locked] public array $sql = [];
    #[Locked] public ?string $previewHash = null;
    #[Locked] public ?array $modelPreview = null;

    public static function canAccess(): bool { return DatabaseTables::canAccess(); }
    public function getTitle(): string { return 'Создание таблицы'; }
    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
        $this->form->fill(['name' => '', 'columns' => [
            ['name' => 'id', 'type' => 'integer', 'length' => '', 'nullable' => false, 'unsigned' => true,
                'auto_increment' => true, 'index' => 'PRIMARY', 'default' => null],
        ]]);
    }
    public function hydrate(): void { abort_unless(static::canAccess(), 403); }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            TextInput::make('name')->label('Имя таблицы')->required()->maxLength(64),
            Checkbox::make('create_model')->label('Создать модель для таблицы')->default(false)
                ->visible(fn (): bool => ! ($this instanceof DatabaseEdit)),
            Repeater::make('columns')->label('Колонки')->minItems(1)->maxItems(100)->addable(false)
                ->reorderable(! ($this instanceof DatabaseEdit))
                ->extraAttributes(['class' => 'viar-database-columns'])
                ->table([
                    TableColumn::make('Имя')->markAsRequired(), TableColumn::make('Тип')->markAsRequired(),
                    TableColumn::make('Длина'), TableColumn::make('Not Null'), TableColumn::make('Unsigned'),
                    TableColumn::make('Auto increment'), TableColumn::make('Индекс'), TableColumn::make('По умолчанию'),
                ])->schema([
                TextInput::make('name')->label('Имя колонки')->required(),
                Select::make('type')->label('Тип')->options(DatabaseTableCreationService::typeOptions())
                    ->disableOptionWhen(fn (string $value): bool => in_array($value, ['enum', 'set'], true)
                        || (in_array($value, DatabaseTableCreationService::MYSQL_ONLY, true) && ! in_array(\Illuminate\Support\Facades\DB::getDriverName(), ['mysql', 'mariadb'], true)))
                    ->live()->afterStateUpdated(function (Set $set, ?string $state): void {
                        $set('default', null);
                        $set('length', '');
                        $set('unsigned', false);
                        $set('auto_increment', false);
                        if (in_array($state, ['tinytext', 'text', 'mediumtext', 'longtext', 'tinyblob', 'blob', 'mediumblob', 'longblob', 'json'], true)) { $set('index', null); }
                    })->default('integer')->required(),
                TextInput::make('length')->label('Длина / precision,scale')->placeholder(fn (Get $get): string => match ($get('type')) {
                    'tinytext', 'tinyblob' => '255', 'text', 'blob' => '65535',
                    'mediumtext', 'mediumblob' => '16777215', 'longtext', 'longblob' => '4294967295',
                    'timestamp', 'datetime', 'time' => '0', default => '',
                })->readOnly(fn (Get $get): bool => in_array($get('type'), ['tinytext', 'text', 'mediumtext', 'longtext', 'tinyblob', 'blob', 'mediumblob', 'longblob', 'timestamp', 'datetime', 'time'], true)),
                Field::make('nullable')->label('Not Null')->view('filament.forms.database-not-null')->default(true)->rules(['boolean']),
                Checkbox::make('unsigned')->label('Unsigned')->default(false),
                Checkbox::make('auto_increment')->label('AUTO INCREMENT')->default(false),
                Select::make('index')->label('Индекс')->placeholder('Нет')->options(['PRIMARY' => 'PRIMARY', 'UNIQUE' => 'UNIQUE', 'INDEX' => 'INDEX'])
                    ->dehydrated(true)
                    ->disabled(fn (Get $get): bool => in_array($get('type'), ['tinytext', 'text', 'mediumtext', 'longtext', 'tinyblob', 'blob', 'mediumblob', 'longblob', 'json'], true)),
                TextInput::make('default')->label('По умолчанию')
                    ->dehydrated(true)
                    ->disabled(fn (Get $get): bool => in_array($get('type'), ['tinytext', 'text', 'mediumtext', 'longtext', 'tinyblob', 'blob', 'mediumblob', 'longblob', 'json'], true))
                    ->type(fn (Get $get): string => match ($get('type')) {
                        'date' => 'date', 'time' => 'time',
                        'integer', 'bigint', 'mediumint', 'smallint', 'tinyint', 'decimal', 'float', 'double', 'boolean', 'year' => 'number',
                        default => 'text',
                    })->step(fn (Get $get): string => $get('type') === 'time' ? '1' : 'any'),
            ]),
        ]);
    }

    private function definition(): array
    {
        try {
            $state = $this->form->getState();
            $definition = app(DatabaseTableCreationService::class)->validate($state);
            $definition['create_model'] = (bool) ($state['create_model'] ?? false);
            return $definition;
        }
        catch (ValidationException $exception) {
            $errors = [];
            foreach ($exception->errors() as $key => $messages) { $errors[str_starts_with($key, 'data.') ? $key : 'data.'.$key] = $messages; }
            throw ValidationException::withMessages($errors);
        }
    }

    public function preview(): void
    {
        abort_unless(static::canAccess(), 403);
        $this->previewHash = null; $this->sql = []; $this->modelPreview = null;
        $definition = $this->definition();
        $model = $definition['create_model'] ? app(DatabaseModelGenerationService::class)->preview($definition['name']) : null;
        $result = app(DatabaseTableCreationService::class)->preview($definition);
        $this->sql = $result['sql'];
        $this->modelPreview = $model;
        $this->previewHash = hash('sha256', json_encode($definition));
    }

    public function addTimestamps(): void { $this->addTimestampColumns(['created_at', 'updated_at']); }
    public function addSoftDeletes(): void { $this->addTimestampColumns(['deleted_at']); }

    public function addColumn(): void
    {
        abort_unless(static::canAccess(), 403);
        if (count($this->data['columns'] ?? []) >= 100) { throw ValidationException::withMessages(['data.columns' => 'Допускается не более 100 колонок.']); }
        $this->data['columns'][(string) \Illuminate\Support\Str::uuid()] = ['name' => '', 'type' => 'integer', 'length' => '',
            'nullable' => true, 'unsigned' => false, 'auto_increment' => false, 'index' => null, 'default' => null];
        $this->previewHash = null; $this->sql = [];
    }

    private function addTimestampColumns(array $names): void
    {
        abort_unless(static::canAccess(), 403);
        $columns = $this->data['columns'] ?? [];
        $existing = array_map(fn (array $column): string => strtolower(trim($column['name'] ?? '')), $columns);
        $newNames = array_filter($names, fn (string $name): bool => ! in_array($name, $existing, true));
        if (count($columns) + count($newNames) > 100) { throw ValidationException::withMessages(['data.columns' => 'Допускается не более 100 колонок.']); }
        foreach ($newNames as $name) {
            $columns[(string) \Illuminate\Support\Str::uuid()] = ['name' => $name, 'type' => 'timestamp', 'length' => '',
                'nullable' => true, 'unsigned' => false, 'auto_increment' => false, 'index' => null, 'default' => null];
        }
        $this->data['columns'] = $columns;
        $this->previewHash = null; $this->sql = [];
    }

    public function createTable(): void
    {
        abort_unless(static::canAccess(), 403);
        $definition = $this->definition();
        if ($this->previewHash === null || ! hash_equals($this->previewHash, hash('sha256', json_encode($definition)))) {
            throw ValidationException::withMessages(['data.name' => 'Проверьте предварительный SQL после изменения формы.']);
        }
        if ($definition['create_model']) {
            $model = app(DatabaseModelGenerationService::class)->preview($definition['name']);
            if ($model !== $this->modelPreview) {
                throw ValidationException::withMessages(['data.create_model' => 'Настройки модели изменились. Повторите предварительный просмотр.']);
            }
        }
        try { app(DatabaseTableCreationService::class)->create($definition); }
        catch (\Illuminate\Database\QueryException $exception) {
            $this->previewHash = null; $this->sql = [];
            \Illuminate\Support\Facades\Log::warning('Database table creation failed', ['table' => $definition['name'], 'sqlstate' => $exception->errorInfo[0] ?? null]);
            throw ValidationException::withMessages(['data.name' => 'База отклонила создание. Проверьте текущую структуру и ограничения выбранных типов перед повтором.']);
        }
        $model = $this->modelPreview;
        $this->previewHash = null; $this->sql = []; $this->modelPreview = null;
        if ($definition['create_model']) {
            try { app(DatabaseModelGenerationService::class)->generate($definition['name'], $model); }
            catch (ValidationException $exception) {
                Notification::make()->title('Таблица создана, модель не создана')->body('Проверьте файл модели. Повторно создавать таблицу не нужно.')->warning()->send();
                throw ValidationException::withMessages(['data.create_model' => 'Таблица уже создана. '.implode(' ', $exception->errors()['data.create_model'] ?? [])]);
            }
        }
        Notification::make()->title('Таблица создана')->success()->send();
        $this->redirect(DatabaseTables::getUrl().'?table='.rawurlencode($definition['name']));
    }
}
