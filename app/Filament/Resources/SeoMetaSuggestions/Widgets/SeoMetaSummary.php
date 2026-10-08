<?php

namespace App\Filament\Resources\SeoMetaSuggestions\Widgets;

use App\Filament\Resources\SeoMetaSuggestions\SeoMetaSuggestionResource;
use App\Models\SeoMetaSuggestion;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SeoMetaSummary extends StatsOverviewWidget
{
    public static function canView(): bool { return SeoMetaSuggestionResource::canViewAny(); }

    protected function getStats(): array
    {
        $counts = SeoMetaSuggestion::query()->selectRaw("COUNT(*) AS total, SUM(CASE WHEN current_meta_title IS NULL OR current_meta_title = '' THEN 1 ELSE 0 END) AS missing_title, SUM(CASE WHEN current_meta_description IS NULL OR current_meta_description = '' THEN 1 ELSE 0 END) AS missing_description, SUM(CASE WHEN current_meta_title IS NULL OR current_meta_title = '' OR current_meta_description IS NULL OR current_meta_description = '' THEN 1 ELSE 0 END) AS missing_any")->first();
        return [Stat::make('Записей', $counts->total ?? 0), Stat::make('Без Meta Title', $counts->missing_title ?? 0),
            Stat::make('Без Meta Description', $counts->missing_description ?? 0), Stat::make('Без любого поля', $counts->missing_any ?? 0)];
    }
}
