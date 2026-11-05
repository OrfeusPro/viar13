<?php

namespace App\Filament\Resources\SaConversations\Pages;

use App\Filament\Resources\SaConversations\SaConversationResource;
use Filament\Resources\Pages\ListRecords;

class ListSaConversations extends ListRecords
{
    protected static string $resource = SaConversationResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\Action::make('simulator')->label('SA Simulator')->icon('heroicon-o-beaker')
            ->visible(fn () => \App\Filament\Pages\SaSimulator::canAccess())
            ->url(fn () => \App\Filament\Pages\SaSimulator::getUrl())];
    }
}
