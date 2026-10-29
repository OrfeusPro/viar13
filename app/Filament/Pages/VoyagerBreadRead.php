<?php

namespace App\Filament\Pages;

use App\Filament\Bread\BreadRegistry;
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
}
