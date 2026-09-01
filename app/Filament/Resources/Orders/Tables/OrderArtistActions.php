<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\APainterImagesStatus;
use App\Models\User;
use App\Services\Admin\OrderArtistService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;

class OrderArtistActions
{
    public static function manage(): Action
    {
        return Action::make('manageArtist')
            ->label('Художник и печать')
            ->modalHeading(fn ($record): string => 'Художник и печать заказа №'.$record->id)
            ->modalSubmitActionLabel('Сохранить')
            ->modalWidth('4xl')
            ->schema([
                Section::make('Исполнители')
                    ->columns(2)
                    ->schema([
                        self::userSelect('painter_id', 'Художник', 3),
                        self::userSelect('printing_id', 'Менеджер печати', 6),
                        DatePicker::make('painter_endtime')->label('Время на заказ'),
                        Toggle::make('painter_payed')->label('Заказ оплачен / выполнен'),
                        Toggle::make('is_show_painter_images')
                            ->label('Показывать картины клиенту')
                            ->helperText('При UAT письмо клиенту отключено конфигурацией.'),
                    ]),
                Repeater::make('images')
                    ->label('Статусы набросков и картин')
                    ->schema([
                        Hidden::make('id'),
                        TextInput::make('image')->label('Файл')->disabled()->dehydrated(false),
                        Select::make('status')
                            ->label('Статус')
                            ->options(fn (): array => APainterImagesStatus::query()->orderBy('id')->pluck('title', 'id')->all())
                            ->nullable(),
                    ])
                    ->columns(2)
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->visible(fn ($record): bool => $record->order_painter_images->isNotEmpty()),
            ])
            ->fillForm(fn ($record): array => [
                'painter_id' => $record->painterAssignment?->user_id,
                'printing_id' => $record->printingAssignment?->user_id,
                'painter_endtime' => filled($record->painter_endtime) ? substr((string) $record->painter_endtime, 0, 10) : null,
                'painter_payed' => (bool) $record->painter_payed,
                'is_show_painter_images' => (bool) $record->is_show_painter_images,
                'images' => $record->order_painter_images->map(fn ($image): array => [
                    'id' => $image->id,
                    'image' => basename((string) $image->image),
                    'status' => $image->status,
                ])->values()->all(),
            ])
            ->action(function ($record, array $data): void {
                $result = app(OrderArtistService::class)->update($record, $data);
                Notification::make()->success()->title('Данные художника и печати обновлены')
                    ->body($result['notifications_suppressed'] ? 'Внешние письма отключены для UAT.' : null)
                    ->send();
            })
            ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false);
    }

    private static function userSelect(string $name, string $label, int $roleId): Select
    {
        return Select::make($name)
            ->label($label)
            ->options(fn (): array => User::query()->where('role_id', $roleId)->orderBy('email')->get()
                ->mapWithKeys(fn (User $user): array => [$user->id => trim(($user->nick ? $user->nick.' — ' : '').$user->email)])
                ->all())
            ->searchable()
            ->preload()
            ->placeholder('Не назначен');
    }
}
