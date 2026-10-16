<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Services\Admin\CompassLogReader;
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;

class Compass extends Page
{
    protected static ?string $slug = 'compass';
    protected static ?string $navigationLabel = 'Compass';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';
    protected string $view = 'filament.pages.compass';

    public string $iconSearch = '';
    public string $tab = 'resources';
    public ?string $logFile = null;
    public string $logSearch = '';
    public string $logLevel = '';
    public int $logPage = 1;
    public int $logPerPage = 10;
    #[\Livewire\Attributes\Locked]
    public array $logsToDelete = [];
    public string $commandSearch = '';
    public ?string $commandName = null;
    public string $commandArgs = '';
    public array $commandDrafts = [];
    public string $commandOutput = '';
    public ?int $commandExitCode = null;
    public ?string $lastCommandName = null;
    private ?array $commandCatalogue = null;

    public static function routes(\Filament\Panel $panel, ?\Filament\Pages\PageConfiguration $configuration = null): void
    {
        parent::routes($panel, $configuration);
        \Illuminate\Support\Facades\Route::get(static::getRoutePath($panel).'/download', \App\Http\Controllers\Admin\CompassLogDownloadController::class)
            ->middleware(static::getRouteMiddleware($panel))->withoutMiddleware(static::getWithoutRouteMiddleware($panel))
            ->name(static::getRelativeRouteName($panel).'.download');
    }

    public static function canAccess(): bool
    {
        $user = auth('filament')->user();
        return (app()->environment('local') || config('voyager.compass_in_production', false))
            && $user && method_exists($user, 'hasPermission') && $user->hasPermission('browse_compass');
    }

    public function mount(): void { abort_unless(static::canAccess(), 403); }
    public function hydrate(): void { abort_unless(static::canAccess(), 403); }
    public function getTitle(): string { return 'Compass'; }

    public function selectTab(string $tab): void
    {
        abort_unless(static::canAccess(), 403);
        abort_unless(in_array($tab, ['resources', 'commands', 'logs'], true), 422);
        $this->tab = $tab;
        if ($tab === 'logs' && $this->logFile === null) { $this->logFile = $this->logFiles()[0] ?? null; }
    }

    public function logFiles(): array { return app(CompassLogReader::class)->files(); }
    public function updatedLogFile(): void { $this->logPage = 1; }
    public function updatedLogSearch(): void { $this->logPage = 1; }
    public function updatedLogLevel(): void { $this->logPage = 1; }
    public function updatedLogPerPage(): void { $this->logPage = 1; }

    public function logData(): array
    {
        $empty = ['entries' => [], 'total' => 0, 'page' => 1, 'per_page' => $this->logPerPage, 'last_page' => 1];
        if ($this->logFile === null) { return $empty; }
        try {
            return app(CompassLogReader::class)->page($this->logFile, $this->logPage, $this->logPerPage,
                mb_substr($this->logSearch, 0, 255), $this->logLevel);
        } catch (ValidationException $error) { return $empty + ['error' => collect($error->errors())->flatten()->first()]; }
    }

    public function changeLogPage(int $page): void
    {
        abort_unless(static::canAccess(), 403);
        abort_unless($page > 0, 422);
        $this->logPage = min($page, $this->logData()['last_page']);
    }

    public function requestLogDeletion(bool $all = false): void
    {
        abort_unless(static::canAccess(), 403);
        $reader = app(CompassLogReader::class);
        $names = $all ? $reader->files() : ($this->logFile === null ? [] : [$this->logFile]);
        abort_unless($names !== [], 409);
        foreach ($names as $name) { $reader->checkedPath($name); }
        $this->logsToDelete = $names;
        $this->resetValidation('logDelete');
        $this->dispatch('open-modal', id: 'delete-compass-logs');
    }

    public function confirmLogDeletion(): void
    {
        abort_unless(static::canAccess(), 403);
        abort_unless($this->logsToDelete !== [], 409);
        $reader = app(CompassLogReader::class);
        try { $result = $reader->deleteFiles($this->logsToDelete); }
        catch (ValidationException) {
            $this->addError('logDelete', 'Список файлов изменился. Закройте окно и выберите журналы заново.');
            return;
        }
        $files = $reader->files();
        if (! in_array($this->logFile, $files, true)) { $this->logFile = $files[0] ?? null; }
        $this->logPage = 1;
        $this->logsToDelete = $result['failed'];
        if ($result['failed'] !== []) {
            $this->addError('logDelete', 'Удалено: '.count($result['deleted']).'. Не удалось удалить: '.count($result['failed']).'.');
            return;
        }
        $this->resetValidation('logDelete');
        $this->dispatch('close-modal', id: 'delete-compass-logs');
        Notification::make()->title('Журналы удалены: '.count($result['deleted']))->success()->send();
    }

    public function dismissLogDeletion(): void { $this->logsToDelete = []; $this->resetValidation('logDelete'); }

    public function cancelLogDeletion(): void
    {
        $this->dismissLogDeletion();
        $this->dispatch('close-modal', id: 'delete-compass-logs');
    }

    private function commandCatalogue(): array
    {
        if ($this->commandCatalogue !== null) { return $this->commandCatalogue; }
        $commands = [];
        foreach (Artisan::all() as $key => $command) {
            if ($command->isHidden()) { continue; }
            $name = $command->getName() ?: $key;
            $commands[$name] = $command->getDescription();
        }
        ksort($commands, SORT_STRING);
        return $this->commandCatalogue = $commands;
    }

    public function commands(): array
    {
        $search = mb_strtolower(trim(mb_substr($this->commandSearch, 0, 255)));
        return array_filter($this->commandCatalogue(), fn ($description, $name) => $search === ''
            || mb_stripos($name.' '.$description, $search) !== false, ARRAY_FILTER_USE_BOTH);
    }

    public function selectCommand(string $name): void
    {
        abort_unless(static::canAccess(), 403);
        validator(['commandName' => $name], ['commandName' => ['required', \Illuminate\Validation\Rule::in(array_keys($this->commandCatalogue()))]])->validate();
        if ($this->commandName !== null) { $this->commandDrafts[$this->commandName] = $this->commandArgs; }
        $this->commandName = $name;
        $draft = $this->commandDrafts[$name] ?? '';
        $this->commandArgs = is_string($draft) ? $draft : '';
        $this->resetValidation(['commandName', 'commandArgs']);
    }

    public function runCommand(): void
    {
        abort_unless(static::canAccess(), 403);
        $this->validate([
            'commandName' => ['required', 'string', \Illuminate\Validation\Rule::in(array_keys($this->commandCatalogue()))],
            'commandArgs' => ['string', 'max:65535'],
        ]);
        $this->commandDrafts[$this->commandName] = $this->commandArgs;
        $this->lastCommandName = $this->commandName;
        $this->commandOutput = '';
        $line = $this->commandName.($this->commandArgs === '' ? '' : ' '.$this->commandArgs);
        try {
            $this->commandExitCode = Artisan::call($line);
            $this->commandOutput = Artisan::output();
        } catch (\Throwable $error) {
            $this->commandExitCode = 1;
            $this->commandOutput = $error->getMessage();
        }
        $this->commandCatalogue = null;
        $notification = Notification::make()->title($this->commandExitCode === 0 ? 'Команда выполнена' : 'Команда завершилась ошибкой');
        ($this->commandExitCode === 0 ? $notification->success() : $notification->danger())->send();
    }

    public function clearCommandOutput(): void
    {
        $this->commandOutput = '';
        $this->commandExitCode = null;
        $this->lastCommandName = null;
    }

    public function icons(): array
    {
        $search = strtolower(trim(mb_substr($this->iconSearch, 0, 255)));
        return array_values(array_filter(array_keys($this->iconCharacters()), fn ($name) => $search === '' || str_contains($name, $search)));
    }

    public function iconCharacter(string $name): string
    {
        return $this->iconCharacters()[$name] ?? '';
    }

    private function iconCharacters(): array
    {
        static $characters;
        if ($characters !== null) { return $characters; }
        $pattern = '/\.(voyager-[a-z0-9-]+):before\s*\{\s*content:\s*"'.preg_quote('\\', '/').'([0-9a-f]+)"/i';
        preg_match_all($pattern, file_get_contents(public_path('vendor/voyager-icons/icons.css')), $matches, PREG_SET_ORDER);
        $characters = [];
        foreach ($matches as $match) { $characters[$match[1]] = mb_chr(hexdec($match[2]), 'UTF-8'); }
        return $characters;
    }
}
