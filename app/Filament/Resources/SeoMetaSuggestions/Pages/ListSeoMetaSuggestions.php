<?php

namespace App\Filament\Resources\SeoMetaSuggestions\Pages;

use App\Filament\Resources\SeoMetaSuggestions\SeoMetaSuggestionResource;
use App\Filament\Resources\SeoMetaSuggestions\Widgets\SeoMetaSummary;
use App\Models\SeoMetaSuggestion;
use App\Services\SeoMetaGeneration\SeoMetaContextResolver;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\Rule;

class ListSeoMetaSuggestions extends ListRecords
{
    protected static string $resource = SeoMetaSuggestionResource::class;

    protected function getHeaderWidgets(): array { return [SeoMetaSummary::class]; }

    public function getTabs(): array
    {
        $counts = SeoMetaSuggestion::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $tabs = ['all' => Tab::make('Все')->badge($counts->sum())];
        foreach (SeoMetaSuggestionResource::statuses() as $status => $label) {
            $tabs[$status] = Tab::make($label)->badge($counts[$status] ?? 0)->modifyQueryUsing(fn ($query) => $query->where('status', $status));
        }
        return $tabs;
    }

    protected function getHeaderActions(): array
    {
        $locales = app(SeoMetaContextResolver::class)->supportedLocales();
        return [Action::make('scan')->label('Сканировать сущности')->icon('heroicon-o-magnifying-glass')
            ->visible(fn () => SeoMetaSuggestionResource::permitted('edit'))
            ->schema([
                Select::make('model')->label('Модель')->options(SeoMetaSuggestionResource::targets())->placeholder('Все модели')
                    ->rules([Rule::in(array_keys(SeoMetaSuggestionResource::targets()))]),
                Select::make('locale')->label('Язык')->options(array_combine($locales, $locales))->placeholder('Все языки')->rules([Rule::in($locales)]),
                TextInput::make('limit')->label('Лимит сущностей на модель')->numeric()->integer()->minValue(1)->maxValue(10000)->required()->default(1000),
                Toggle::make('only_empty')->label('Только с пустыми метаданными')->default(true),
                Toggle::make('force')->label('Обновить существующие предложения')->default(false),
            ])->action(function (array $data): void {
                SeoMetaSuggestionResource::requireEditPermission();
                $params = ['--limit' => $data['limit']];
                foreach (['model', 'locale'] as $key) { if (filled($data[$key] ?? null)) { $params['--' . $key] = $data[$key]; } }
                if ($data['only_empty'] ?? false) { $params['--only-empty'] = true; }
                if ($data['force'] ?? false) { $params['--force'] = true; }
                try {
                    $code = Artisan::call('seo-meta:scan', $params);
                    Notification::make()->title($code === 0 ? 'Сканирование завершено' : 'Сканирование не завершено')
                        ->body(trim(Artisan::output()))->color($code === 0 ? 'success' : 'danger')->send();
                    $this->resetTable();
                } catch (\Throwable $exception) {
                    report($exception);
                    Notification::make()->title('Ошибка сканирования. Проверьте журнал.')->danger()->send();
                }
            })];
    }
}
