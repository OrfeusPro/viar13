<?php

namespace App\Filament\Pages;

use App\Filament\Bread\BreadRegistry;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BreadTables extends Page
{
    protected static ?string $slug = 'bread-tables';
    protected static ?string $navigationLabel = 'BREAD — все таблицы';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-table-cells';
    protected string $view = 'filament.pages.bread-tables';
    public string $search = '';

    public static function canAccess(): bool { return BreadMetadata::canAccess(); }
    public function getTitle(): string { return 'BREAD — все таблицы'; }
    public function mount(): void { abort_unless(static::canAccess(), 403); }
    public function hydrate(): void { abort_unless(static::canAccess(), 403); }

    public function tables(): array
    {
        abort_unless(static::canAccess(), 403);
        $registry = app(BreadRegistry::class);
        $types = DB::table('data_types')->get()->keyBy('name');
        $names = Schema::getTableListing(Schema::getCurrentSchemaName(), false);
        sort($names, SORT_NATURAL | SORT_FLAG_CASE);
        $rows = [];
        foreach ($names as $name) {
            $type = $types->get($name);
            if ($this->search !== '' && ! str_contains(mb_strtolower($name.' '.($type->display_name_plural ?? '')), mb_strtolower($this->search))) { continue; }
            $available = $type && $registry->model($type);
            $url = null;
            if ($available && $registry->permitted($type, 'browse')) {
                $url = match ($type->name) {
                    'orders' => \App\Filament\Resources\Orders\OrdersResource::getUrl(),
                    'canvas_slider' => \App\Filament\Resources\CanvasSliders\CanvasSliderResource::getUrl(),
                    'image_alt_suggestions' => \App\Filament\Resources\ImageAltSuggestions\ImageAltSuggestionResource::getUrl(),
                    'seo_meta_suggestions' => \App\Filament\Resources\SeoMetaSuggestions\SeoMetaSuggestionResource::getUrl(),
                    'menus' => VoyagerMenuItems::getUrl(),
                    default => VoyagerBread::getUrl(['type' => $type->slug]),
                };
            }
            $rows[] = ['name' => $name, 'configured' => (bool) $type, 'title' => $type->display_name_plural ?? null,
                'slug' => $type->slug ?? null, 'available' => (bool) $available, 'browse_url' => $url,
                'edit_url' => $type ? BreadMetadata::getUrl().'?section='.$type->id : null,
                'create_url' => ! $type ? BreadMetadata::getUrl().'?create='.rawurlencode($name) : null];
        }
        return $rows;
    }
}
