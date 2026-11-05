<?php

namespace App\Filament\Resources\CanvasSliders;

use App\Filament\Resources\CanvasSliders\Pages\ListCanvasSliders;
use App\Models\CanvasSlider;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use TCG\Voyager\Models\Translation;

class CanvasSliderResource extends Resource
{
    protected static ?string $model = CanvasSlider::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Слайдер холста';

    protected static ?string $modelLabel = 'слайд с холстом';

    protected static ?string $pluralModelLabel = 'слайды с холстом';

    protected static ?int $navigationSort = 41;

    public static function canViewAny(): bool
    {
        return static::permitted('browse_canvas_slider');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('title')->label('Заголовок')->limit(60),
                TextColumn::make('hero_subtitle')->label('Подзаголовок (EN)')->limit(80),
            ])
            ->recordActions([
                Action::make('editHeroSubtitle')
                    ->label('Подзаголовок и переводы')
                    ->visible(fn (): bool => static::permitted('edit_canvas_slider'))
                    ->fillForm(function (CanvasSlider $record): array {
                        $data = ['en' => $record->hero_subtitle];
                        foreach (static::locales() as $locale) {
                            if ($locale === 'en') {
                                continue;
                            }
                            $data[$locale] = $record->translations()
                                ->where('column_name', 'hero_subtitle')
                                ->where('locale', $locale)
                                ->value('value');
                        }
                        return $data;
                    })
                    ->schema(array_map(
                        fn (string $locale) => TextInput::make($locale)
                            ->label(strtoupper($locale))
                            ->maxLength(2000),
                        static::locales()
                    ))
                    ->action(function (CanvasSlider $record, array $data): void {
                        abort_unless(static::permitted('edit_canvas_slider'), 403);
                        $record->hero_subtitle = $data['en'] ?? null;
                        $record->save();
                        foreach (static::locales() as $locale) {
                            if ($locale === 'en') {
                                continue;
                            }
                            $key = [
                                'table_name' => $record->getTable(),
                                'column_name' => 'hero_subtitle',
                                'foreign_key' => $record->getKey(),
                                'locale' => $locale,
                            ];
                            $value = trim((string) ($data[$locale] ?? ''));
                            if ($value === '') {
                                Translation::query()->where($key)->delete();
                            } else {
                                Translation::query()->updateOrCreate($key, ['value' => $value]);
                            }
                        }
                        Notification::make()->title('Подзаголовок сохранён')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListCanvasSliders::route('/')];
    }

    private static function locales(): array
    {
        return array_keys((array) config('laravellocalization.supportedLocales', []));
    }

    private static function permitted(string $permission): bool
    {
        $user = auth()->user();
        return $user && method_exists($user, 'hasPermission') && $user->hasPermission($permission);
    }
}
