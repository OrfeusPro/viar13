<?php

namespace App\Filament\Pages;

use App\Services\Admin\DatabaseInspectionService;
use Filament\Pages\Page;
use Livewire\Attributes\Locked;

class DatabaseTables extends Page
{
    protected static ?string $slug = 'database-tables';
    protected static ?string $navigationLabel = 'База данных';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-circle-stack';
    protected string $view = 'filament.pages.database-tables';
    public string $search = '';
    #[Locked] public ?string $selectedTable = null;
    #[Locked] public ?string $breadDeletionTable = null;
    #[Locked] public ?string $breadDeletionFingerprint = null;
    public string $breadConfirmation = '';

    public static function canAccess(): bool
    {
        $user = auth('filament')->user();
        return $user && $user->hasPermission('browse_admin') && $user->hasPermission('browse_database');
    }

    public function getTitle(): string { return 'База данных'; }
    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
        $table = request()->query('table');
        if ($table !== null) {
            abort_unless(is_string($table), 422);
            $this->inspect($table);
            $this->search = $table;
        }
    }
    public function hydrate(): void { abort_unless(static::canAccess(), 403); }

    public function tables(): array
    {
        abort_unless(static::canAccess(), 403);
        $tables = app(DatabaseInspectionService::class)->tables($this->search);
        $registry = app(\App\Filament\Bread\BreadRegistry::class);
        $types = collect($registry->types())->keyBy('name');
        foreach ($tables as &$table) { $table['browse_url'] = isset($types[$table['name']]) ? $registry->browseUrl($types[$table['name']]) : null; }
        unset($table);
        return $tables;
    }

    public function prepareBreadDeletion(string $table): void
    {
        abort_unless(static::canAccess(), 403);
        $service = app(\App\Services\Admin\BreadDeletionService::class);
        $snapshot = $service->snapshot($table);
        $this->resetValidation();
        $this->breadDeletionTable = $table;
        $this->breadDeletionFingerprint = $service->fingerprint($snapshot);
        $this->breadConfirmation = '';
        $this->dispatch('open-modal', id: 'delete-database-bread');
    }

    public function deleteBread(): void
    {
        abort_unless(static::canAccess() && BreadMetadata::canAccess(), 403);
        if ($this->breadDeletionTable === null || $this->breadDeletionFingerprint === null || $this->breadConfirmation !== $this->breadDeletionTable) {
            throw \Illuminate\Validation\ValidationException::withMessages(['breadConfirmation' => 'Введите точное имя таблицы.']);
        }
        try { app(\App\Services\Admin\BreadDeletionService::class)->delete($this->breadDeletionTable, $this->breadDeletionFingerprint); }
        catch (\Illuminate\Database\QueryException $exception) {
            \Illuminate\Support\Facades\Log::warning('BREAD deletion failed', ['table' => $this->breadDeletionTable, 'sqlstate' => $exception->errorInfo[0] ?? null]);
            throw \Illuminate\Validation\ValidationException::withMessages(['breadConfirmation' => 'База отклонила удаление BREAD. Изменения отменены.']);
        }
        $this->breadDeletionTable = null; $this->breadDeletionFingerprint = null; $this->breadConfirmation = '';
        $this->dispatch('close-modal', id: 'delete-database-bread');
        \Filament\Notifications\Notification::make()->title('BREAD удалён')->success()->send();
    }

    public function inspect(string $table): void
    {
        abort_unless(static::canAccess(), 403);
        app(DatabaseInspectionService::class)->describe($table);
        $this->selectedTable = $table;
    }

    public function structure(): ?array
    {
        abort_unless(static::canAccess(), 403);
        return $this->selectedTable === null ? null : app(DatabaseInspectionService::class)->describe($this->selectedTable);
    }

    public function closeStructure(): void
    {
        abort_unless(static::canAccess(), 403);
        $this->selectedTable = null;
    }
}
