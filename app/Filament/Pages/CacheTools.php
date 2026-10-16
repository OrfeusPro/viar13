<?php

namespace App\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CacheTools extends Page
{
    protected static ?string $slug = 'cache-tools';
    protected static ?string $navigationLabel = 'Кеш приложения';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';
    protected string $view = 'filament.pages.cache-tools';

    public static function canAccess(): bool
    {
        $user = auth('filament')->user();
        return $user && method_exists($user, 'hasPermission') && $user->hasPermission('browse_compass');
    }

    public function mount(): void { abort_unless(static::canAccess(), 403); }
    public function hydrate(): void { abort_unless(static::canAccess(), 403); }
    public function getTitle(): string { return 'Кеш приложения'; }

    public function run(string $operation): void
    {
        abort_unless(static::canAccess(), 403);
        $commands = match ($operation) {
            'clear' => ['cache:clear'],
            'build' => ['config:cache', 'route:cache'],
            default => throw ValidationException::withMessages(['operation' => 'Выберите доступную операцию кеша.']),
        };
        $completed = [];
        foreach ($commands as $command) {
            $exception = null;
            try { $exitCode = Artisan::call($command); }
            catch (\Throwable $error) { $exitCode = -1; $exception = $error::class; }
            if ($exitCode !== 0) {
                // Command output and exception messages can contain configuration secrets.
                Log::warning('Admin cache command failed', ['command' => $command, 'exit_code' => $exitCode, 'exception' => $exception]);
                Notification::make()->title('Не удалось выполнить '.$command)->danger()
                    ->body($completed === [] ? 'Операция не завершена.' : 'Уже выполнено: '.implode(', ', $completed).'.')->send();
                return;
            }
            $completed[] = $command;
        }
        Notification::make()->title($operation === 'clear' ? 'Кеш приложения очищен' : 'Кеш конфигурации и маршрутов собран')->success()->send();
    }
}
