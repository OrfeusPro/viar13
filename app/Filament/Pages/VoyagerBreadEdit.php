<?php

namespace App\Filament\Pages;

use Illuminate\Contracts\Support\Htmlable;

class VoyagerBreadEdit extends VoyagerBread
{
    protected static ?string $slug = 'bread/{type}/{record}/edit';

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationItems(): array
    {
        return [];
    }

    public function mount(string $type, int|string|null $record = null): void
    {
        abort_unless($record !== null && ctype_digit((string) $record), 404);
        $this->recordId = (int) $record;
        parent::mount($type);
        $this->openEdit((int) $record);
    }

    public function getTitle(): string | Htmlable
    {
        return 'Редактирование: ' . (($this->bread()?->display_name_singular ?? null) ?: ($this->bread()?->display_name_plural ?: $this->type)) . ' #' . $this->recordId;
    }

    public function save(): void
    {
        parent::save();
        $this->editing = true;
    }

    public function cancel(): void
    {
        $this->redirect($this->listUrl());
    }
}
