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
use Illuminate\Validation\ValidationException;

class OrderArtistActions
{
    public static function updateMediaStatus(): Action
    {
        return Action::make('updateOrderMediaStatus')->label('Обновить статус изображения')
            ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false)
            ->action(function ($record, array $arguments): void {
                $author = auth('filament')->user();
                abort_unless($author?->can('update', $record), 403);
                try {
                    $data = validator($arguments, [
                        'type' => ['required', 'in:sketch,painting'],
                        'image_id' => ['required', 'integer', 'min:1'],
                        'status' => ['required', 'integer', 'exists:a_painter_images_status,id'],
                    ])->validate();
                    abort_if($author->role?->name === 'printing' && $data['type'] !== 'painting', 403);
                    app(OrderArtistService::class)->updateMediaStatus($record, (int) $data['image_id'], $data['type'], $data['status']);
                    Notification::make()->success()->title('Статус изображения обновлён')->send();
                } catch (ValidationException $exception) {
                    Notification::make()->warning()->title('Изменение не сохранено')
                        ->body(collect($exception->errors())->flatten()->first())->send();
                }
            });
    }

    public static function updateField(): Action
    {
        return Action::make('updateOrderArtistField')
            ->label('Обновить данные художника')
            ->authorize(fn ($record): bool => auth('filament')->user()?->role?->name !== 'printing'
                && (auth('filament')->user()?->can('update', $record) ?? false))
            ->action(function ($record, array $arguments): void {
                abort_unless(auth('filament')->user()?->role?->name !== 'printing'
                    && auth('filament')->user()?->can('update', $record), 403);
                try {
                    $input = validator($arguments, ['field' => ['required', 'string'], 'value' => ['present']])->validate();
                    app(OrderArtistService::class)->updateField($record, $input['field'], $input['value']);
                    Notification::make()->success()->title('Данные художника обновлены')->send();
                } catch (ValidationException $exception) {
                    Notification::make()->warning()->title('Изменение не сохранено')
                        ->body(collect($exception->errors())->flatten()->first())->send();
                }
            });
    }

    public static function paintingStatuses(): Action
    {
        return Action::make('managePaintingStatuses')->label('Статусы картин')
            ->modalHeading(fn ($record): string => 'Статусы картин заказа №'.$record->id)
            ->schema([
                Repeater::make('images')->label('Готовые картины')->schema([
                    Hidden::make('id'),
                    TextInput::make('image')->label('Файл')->disabled()->dehydrated(false),
                    Select::make('status')->label('Статус')->nullable()
                        ->options(fn (): array => APainterImagesStatus::query()->orderBy('id')->pluck('title', 'id')->all()),
                ])->addable(false)->deletable(false)->reorderable(false),
            ])
            ->fillForm(fn ($record): array => ['images' => $record->order_painter_images
                ->where('is_img_painter', 1)->map(fn ($image): array => [
                    'id' => $image->id, 'image' => basename((string) $image->image), 'status' => $image->status,
                ])->values()->all()])
            ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false)
            ->action(function ($record, array $data): void {
                abort_unless(auth('filament')->user()?->can('update', $record), 403);
                app(OrderArtistService::class)->updatePaintingStatuses($record, $data);
                Notification::make()->success()->title('Статусы картин обновлены')->send();
            });
    }

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

    public static function userOptions(int $roleId): array
    {
        return User::query()->where('role_id', $roleId)->orderBy('id')->get()
            ->mapWithKeys(fn (User $user): array => [$user->id => trim(($user->nick ? $user->nick.' - ' : '').$user->email)])
            ->all();
    }

    private static function userSelect(string $name, string $label, int $roleId): Select
    {
        return Select::make($name)
            ->label($label)
            ->options(fn (): array => self::userOptions($roleId))
            ->searchable()
            ->preload()
            ->placeholder('Не назначен');
    }
}
