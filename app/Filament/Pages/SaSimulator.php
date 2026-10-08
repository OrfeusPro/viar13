<?php

namespace App\Filament\Pages;

use App\Services\Admin\SaSimulatorService;
use Filament\Pages\Page;
use Illuminate\Validation\ValidationException;

class SaSimulator extends Page
{
    protected static ?string $slug = 'sa-simulator';
    protected static ?string $navigationLabel = 'SA Simulator';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';
    protected string $view = 'filament.pages.sa-simulator';

    public static function canAccess(): bool { return SaSimulatorService::permitted(auth('filament')->user()); }
    public function getTitle(): string { return 'Симулятор SA вебхуков'; }
    public function defaults(): array
    {
        abort_unless(static::canAccess(), 403);
        return app(\App\Http\Controllers\Admin\AdminSaIntegrationController::class)->resolveSimulatorDefaults();
    }
    public function executeSimulator(string $endpoint, string $payload): array
    {
        abort_unless(static::canAccess(), 403);
        try { return app(SaSimulatorService::class)->execute(auth('filament')->user(), $endpoint, $payload); }
        catch (ValidationException $exception) {
            return ['status' => 'error', 'http_code' => 422, 'error' => ['code' => 'VALIDATION_ERROR',
                'message' => 'Проверьте запрос.', 'details' => $exception->errors()]];
        }
    }
}
