<?php

namespace App\Filament\Pages;

use App\Filament\Bread\BreadRegistry;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Illuminate\Contracts\Support\Htmlable;

class VoyagerBreadRead extends VoyagerBread
{
    protected static ?string $slug = 'bread/{type}/{record}/view';

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationItems(): array
    {
        return [];
    }

    public function mount(string $type, int|string|null $record = null): void
    {
        abort_unless($record !== null && ctype_digit((string) $record) && (int) $record > 0, 404);
        $this->viewId = (int) $record;
        parent::mount($type);
        $this->openView((int) $record);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Просмотр: '.($this->bread()?->display_name_singular ?: $this->type).' #'.$this->viewId;
    }

    public function cancel(): void
    {
        $this->redirect(app(BreadRegistry::class)->browseUrl($this->bread()) ?? Filament::getUrl());
    }

    protected function getHeaderActions(): array
    {
        $registry = app(BreadRegistry::class);
        $bread = $this->bread();
        if ($bread?->name !== 'users') {
            return [];
        }

        $id = $this->viewId;

        return [
            Action::make('editClient')->label('Редактировать')->icon('heroicon-o-pencil-square')
                ->url(VoyagerBreadEdit::getUrl(['type' => $this->type, 'record' => $id]))
                ->visible(fn (): bool => $registry->permitted($bread, 'edit', $id)),
            Action::make('deleteClient')->label('Удалить')->color('danger')->requiresConfirmation()
                ->modalHeading('Удалить пользователя?')
                ->authorize(fn (): bool => $registry->permitted($bread, 'delete', $id))
                ->action(function () use ($id, $registry, $bread): void {
                    $this->deleteRecord($id);
                    if (! $registry->model($bread)?->newQuery()->find($id)) {
                        $this->cancel();
                    }
                }),
            Action::make('listClients')->label('Вернуться к списку')->color('gray')
                ->url(fn (): ?string => $registry->browseUrl($bread))
                ->visible(fn (): bool => $registry->permitted($bread, 'browse')),
        ];
    }
}
