<?php

namespace App\Filament\Resources\ImageAltSuggestions;

use App\Filament\Resources\ImageAltSuggestions\Pages\ListImageAltSuggestions;
use App\Jobs\GenerateImageAltJob;
use App\Models\ImageAltSuggestion;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Eloquent\Model;

class ImageAltSuggestionResource extends Resource
{
    protected static ?string $model = ImageAltSuggestion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'ALT изображений';

    protected static ?string $modelLabel = 'ALT-предложение';

    protected static ?string $pluralModelLabel = 'ALT-предложения';

    protected static ?int $navigationSort = 40;

    public static function canViewAny(): bool
    {
        return static::permitted('browse');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                ImageColumn::make('preview_url')->label('Изображение')->square(),
                TextColumn::make('image_path')->label('Файл')->searchable()->limit(50)->copyable(),
                TextColumn::make('page_url')->label('Страница')->searchable()->limit(40),
                TextColumn::make('locale')->label('Язык')->sortable(),
                TextColumn::make('status')->label('Статус')->badge()->sortable(),
                TextColumn::make('suggested_alt')->label('Предложенный ALT')->searchable()->limit(60),
                TextColumn::make('approved_alt')->label('Одобренный ALT')->limit(60),
                TextColumn::make('error')->label('Ошибка')->limit(40)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Обновлено')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(array_combine(ImageAltSuggestion::statuses(), ImageAltSuggestion::statuses())),
                SelectFilter::make('locale')->options(fn (): array => ImageAltSuggestion::query()
                    ->whereNotNull('locale')->distinct()->orderBy('locale')->pluck('locale', 'locale')->all()),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Одобрить')
                    ->visible(fn (): bool => static::permitted('edit'))
                    ->fillForm(fn (ImageAltSuggestion $record): array => [
                        'alt' => $record->approved_alt ?? $record->suggested_alt,
                        'title' => $record->approved_title ?? $record->suggested_title,
                    ])
                    ->schema([
                        TextInput::make('alt')->label('ALT')->maxLength((int) config('alt_generation.limits.alt_max', 125)),
                        TextInput::make('title')->label('Title')->maxLength((int) config('alt_generation.limits.title_max', 70)),
                    ])
                    ->action(function (ImageAltSuggestion $record, array $data): void {
                        abort_unless(static::permitted('edit'), 403);
                        $record->approved_alt = $data['alt'] ?? null;
                        $record->approved_title = $data['title'] ?? null;
                        $record->setStatus(ImageAltSuggestion::STATUS_APPROVED);
                        $record->reviewed_by = auth()->id();
                        $record->reviewed_at = now();
                        $record->save();
                        Notification::make()->title('ALT одобрен')->success()->send();
                    }),
                Action::make('apply')
                    ->label('Применить')
                    ->visible(fn (ImageAltSuggestion $record): bool => static::permitted('edit') && $record->status === ImageAltSuggestion::STATUS_APPROVED)
                    ->requiresConfirmation()
                    ->action(function (ImageAltSuggestion $record): void {
                        abort_unless(static::permitted('edit'), 403);
                        $code = Artisan::call('alt:apply', [
                            '--ids' => (string) $record->id,
                            '--source' => 'admin',
                            '--applied-by' => (string) auth()->id(),
                        ]);
                        $record->refresh();
                        Notification::make()
                            ->title($code === 0 && $record->status === ImageAltSuggestion::STATUS_APPLIED
                                ? 'ALT применён' : 'ALT не применён — проверьте ошибку предложения')
                            ->color($code === 0 && $record->status === ImageAltSuggestion::STATUS_APPLIED ? 'success' : 'danger')
                            ->send();
                    }),
                Action::make('reject')
                    ->label('Отклонить')
                    ->visible(fn (): bool => static::permitted('edit'))
                    ->requiresConfirmation()
                    ->action(function (ImageAltSuggestion $record): void {
                        abort_unless(static::permitted('edit'), 403);
                        $record->setStatus(ImageAltSuggestion::STATUS_REJECTED);
                        $record->reviewed_by = auth()->id();
                        $record->reviewed_at = now();
                        $record->save();
                    }),
                Action::make('regenerate')
                    ->label('Перегенерировать')
                    ->visible(fn (): bool => static::permitted('edit'))
                    ->requiresConfirmation()
                    ->action(function (ImageAltSuggestion $record): void {
                        abort_unless(static::permitted('edit'), 403);
                        if (!$record->imageable_type || !$record->imageable_id
                            || !is_subclass_of($record->imageable_type, Model::class)
                            || !$record->imageable_type::query()->whereKey($record->imageable_id)->exists()) {
                            Notification::make()->title('Не указан источник изображения')->danger()->send();
                            return;
                        }
                        $record->setStatus(ImageAltSuggestion::STATUS_NEW);
                        $record->error = null;
                        $record->save();
                        GenerateImageAltJob::dispatch((int) $record->id);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListImageAltSuggestions::route('/')];
    }

    private static function permitted(string $action): bool
    {
        $user = auth()->user();
        return $user && method_exists($user, 'hasPermission')
            && ($user->hasPermission($action . '_alt_suggestions')
                || $user->hasPermission($action . '_image_alt_suggestions'));
    }
}
