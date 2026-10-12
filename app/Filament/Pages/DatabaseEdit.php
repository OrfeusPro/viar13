<?php

namespace App\Filament\Pages;

use App\Services\Admin\DatabaseTableEditingService;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

class DatabaseEdit extends DatabaseCreate
{
    protected static ?string $slug = 'database-tables/edit';
    protected string $view = 'filament.pages.database-edit';
    #[Locked] public string $originalTable = '';
    #[Locked] public string $schemaFingerprint = '';
    #[Locked] public array $origins = [];
    #[Locked] public array $dropSql = [];
    public string $dropConfirmation = '';
    #[Locked] public bool $showDeletion = false;

    public function getTitle(): string { return 'Редактирование таблицы: '.$this->originalTable; }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
        $table = request()->query('table');
        abort_unless(is_string($table) && $table !== '', 422);
        $service = app(DatabaseTableEditingService::class);
        $snapshot = $service->snapshot($table);
        $this->originalTable = $table;
        $this->showDeletion = request()->query('action') === 'delete';
        $this->schemaFingerprint = $service->fingerprint($snapshot);
        $definition = $service->definition($table, $snapshot);
        $columns = [];
        foreach ($definition['columns'] as $column) {
            $key = (string) \Illuminate\Support\Str::uuid();
            $this->origins[$key] = $column['old_name'];
            unset($column['old_name']); $columns[$key] = $column;
        }
        $this->form->fill(['name' => $table, 'columns' => $columns]);
        // Filament generates its own item UUIDs when filling a Repeater.
        $this->origins = [];
        foreach ($this->data['columns'] as $key => $column) { $this->origins[$key] = $column['name']; }
    }

    private function input(): array
    {
        // Repeater dehydrates UUID keys to numeric positions; capture origins first.
        $origins = array_map(fn ($key) => $this->origins[$key] ?? null, array_keys($this->data['columns'] ?? []));
        $data = $this->form->getState();
        foreach ($data['columns'] as $key => &$column) { $column['old_name'] = $origins[$key] ?? null; }
        unset($column);
        return $data;
    }

    private function mapErrors(callable $action): mixed
    {
        try { return $action(); }
        catch (ValidationException $exception) {
            $errors = [];
            foreach ($exception->errors() as $key => $messages) { $errors[str_starts_with($key, 'data.') ? $key : 'data.'.$key] = $messages; }
            throw ValidationException::withMessages($errors);
        }
    }

    public function preview(): void
    {
        abort_unless(static::canAccess(), 403);
        $this->resetValidation();
        $this->previewHash = null; $this->sql = []; $this->dropSql = [];
        $result = $this->mapErrors(fn () => app(DatabaseTableEditingService::class)->preview($this->originalTable, $this->input(), $this->schemaFingerprint));
        $this->sql = $result['sql'];
        $this->previewHash = hash('sha256', serialize($result['data']));
        if ($this->sql === []) { Notification::make()->title('Структура не изменена')->info()->send(); }
    }

    public function prepareSaveStructure(): void
    {
        $this->preview();
        if ($this->sql !== []) { $this->dispatch('open-modal', id: 'database-save-structure'); }
    }

    public function saveStructure(): void
    {
        abort_unless(static::canAccess(), 403);
        $service = app(DatabaseTableEditingService::class);
        $plan = $this->mapErrors(fn () => $service->plan($this->originalTable, $this->input(), $this->schemaFingerprint));
        if ($this->previewHash === null || ! hash_equals($this->previewHash, hash('sha256', serialize($plan['data'])))) {
            throw ValidationException::withMessages(['data.name' => 'Проверьте предварительный SQL после изменения формы.']);
        }
        $this->previewHash = null; $this->sql = []; $this->dropSql = [];
        try { $this->mapErrors(fn () => $service->apply($this->originalTable, $plan['data'], $this->schemaFingerprint)); }
        catch (\Illuminate\Database\QueryException $exception) {
            \Illuminate\Support\Facades\Log::warning('Database table alteration failed', ['table' => $this->originalTable, 'sqlstate' => $exception->errorInfo[0] ?? null]);
            throw ValidationException::withMessages(['data.name' => 'База отклонила изменение. Проверьте текущую структуру перед повтором: часть DDL могла выполниться.']);
        }
        Notification::make()->title('Структура сохранена')->success()->send();
        $this->redirect(DatabaseTables::getUrl().'?table='.rawurlencode($plan['data']['name']));
    }

    public function createTable(): void { abort(403); }

    public function previewDrop(): void
    {
        abort_unless(static::canAccess(), 403);
        $this->resetValidation();
        $this->dropSql = []; $this->sql = []; $this->previewHash = null;
        $this->dropSql = $this->mapErrors(fn () => app(DatabaseTableEditingService::class)->dropPreview($this->originalTable, $this->schemaFingerprint));
    }

    public function dropTable(): void
    {
        abort_unless(static::canAccess(), 403);
        $this->resetValidation();
        if ($this->dropSql === [] || $this->dropConfirmation !== $this->originalTable) {
            throw ValidationException::withMessages(['dropConfirmation' => 'Проверьте SQL удаления и введите точное имя таблицы.']);
        }
        $this->dropSql = []; $this->sql = []; $this->previewHash = null;
        try { $this->mapErrors(fn () => app(DatabaseTableEditingService::class)->drop($this->originalTable, $this->schemaFingerprint)); }
        catch (\Illuminate\Database\QueryException $exception) {
            \Illuminate\Support\Facades\Log::warning('Database table drop failed', ['table' => $this->originalTable, 'sqlstate' => $exception->errorInfo[0] ?? null]);
            throw ValidationException::withMessages(['dropConfirmation' => 'База отклонила удаление таблицы.']);
        }
        Notification::make()->title('Таблица удалена')->success()->send();
        $this->redirect(DatabaseTables::getUrl());
    }
}
