<?php

namespace App\Filament\Pages;

use App\Filament\Bread\BreadRegistry;
use App\Filament\Resources\CanvasSliders\CanvasSliderResource;
use App\Filament\Resources\ImageAltSuggestions\ImageAltSuggestionResource;
use App\Filament\Resources\Orders\OrdersResource;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class VoyagerCatalog extends Page
{
    protected static ?string $slug = 'voyager-catalog';

    protected static ?string $navigationLabel = 'Все разделы Voyager';

    protected string $view = 'filament.pages.voyager-catalog';

    public static function canAccess(): bool
    {
        $user = auth('filament')->user();

        return $user && method_exists($user, 'hasPermission') && $user->hasPermission('browse_admin');
    }

    public function getTitle(): string | Htmlable
    {
        return 'Разделы Voyager';
    }

    public function sections(): array
    {
        $registry = app(BreadRegistry::class);

        return collect($registry->types())
            ->filter(fn ($type): bool => $registry->permitted($type, 'browse'))
            ->map(fn ($type): array => [
                'name' => $type->display_name_plural ?: $type->slug,
                'slug' => $type->slug,
                'available' => ! $registry->isDedicated($type) && (bool) $registry->model($type),
                'dedicated' => $registry->isDedicated($type),
                'url' => match ($type->name) {
                    'orders' => OrdersResource::getUrl(),
                    'canvas_slider' => CanvasSliderResource::getUrl(),
                    'image_alt_suggestions' => ImageAltSuggestionResource::getUrl(),
                    'menus' => VoyagerMenuItems::getUrl(),
                    default => $registry->model($type) ? VoyagerBread::getUrl(['type' => $type->slug]) : null,
                },
            ])->values()->all();
    }
}
