<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Services\Admin\VenipakCourierService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;

class VenipakCourierAction
{
    public static function allowed(): bool
    {
        $user = auth('filament')->user();

        return $user && $user->role?->name !== 'printing' && $user->hasPermission('edit_orders');
    }

    public static function make(): Action
    {
        $hours = fn (int $from, int $to): array => array_combine(VenipakCourierService::hours($from, $to), VenipakCourierService::hours($from, $to));
        $minutes = ['00' => '00', '15' => '15', '30' => '30', '45' => '45'];

        return Action::make('callVenipakCourier')->label('Вызов курьера')->icon('heroicon-o-truck')
            ->modalHeading('Вызов курьера Venipak')->modalSubmitActionLabel('Отправить')->modalWidth('4xl')
            ->visible(fn (): bool => self::allowed())->authorize(fn (): bool => self::allowed())
            ->fillForm(fn (): array => app(VenipakCourierService::class)->defaults())
            ->schema([
                Section::make('Отправитель')->columns(2)->schema([
                    TextInput::make('s_name')->label('Название')->maxLength(60),
                    TextInput::make('s_code')->label('Код предпр.')->maxLength(60),
                    Select::make('s_country')->label('Страна')->options(VenipakCourierService::COUNTRIES)->required(),
                    TextInput::make('s_address')->label('Адрес')->maxLength(50),
                    TextInput::make('s_city')->label('Город')->maxLength(40),
                    TextInput::make('s_post')->label('Почтовый код')->maxLength(8)
                        ->helperText('При указании неверного почтового кода посылка не будет взята у Отправителя.'),
                    TextInput::make('s_contact_p')->label('Контактное лицо')->maxLength(50),
                    TextInput::make('s_contact_t')->label('Телефон')->type('tel')->maxLength(30),
                    TextInput::make('contact_mail')->label('Эл. почта')->email()->maxLength(255),
                ]),
                Section::make('Отправление')->columns(2)->schema([
                    TextInput::make('svoris_s')->label('Вес')->numeric()->gt(0)->required(),
                    TextInput::make('turis_s')->label('Объем')->numeric()->minValue(0),
                    Select::make('pallets_s')->label('Кол-во паллет')->placeholder('-- Выберите --')
                        ->options([0 => 'Нет паллет'] + array_combine(range(1, 40), range(1, 40))),
                    TextInput::make('doknr_s')->label('№ док.')->maxLength(16),
                    TextInput::make('pastabos_s')->label('Примечания')->maxLength(160)->columnSpanFull(),
                ]),
                Section::make('Время исполнения')->columns(4)->schema([
                    DatePicker::make('metai_s')->label('Дата')->required()->columnSpanFull(),
                    Select::make('val_nuo_s')->label('С: часы')->options($hours(8, 22))->required(),
                    Select::make('min_nuo_s')->label('С: минуты')->options($minutes)->required(),
                    Select::make('val_iki_s')->label('По: часы')->options($hours(10, 23))->required(),
                    Select::make('min_iki_s')->label('По: минуты')->options($minutes)->required(),
                ]),
            ])
            ->action(function (array $data, Schema $schema): void {
                abort_unless(self::allowed(), 403);
                try {
                    $numbers = app(VenipakCourierService::class)->send($data);
                } catch (ValidationException $exception) {
                    Notification::make()->danger()->title('Ошибка Venipak')
                        ->body(collect($exception->errors())->flatten()->map(fn ($text): string => e($text))->implode('<br>'))->persistent()->send();
                    throw ValidationException::withMessages(collect($exception->errors())->mapWithKeys(
                        fn ($messages, $key): array => [$schema->getStatePath().'.'.$key => $messages]
                    )->all());
                }
                Notification::make()->success()->title('Вызов курьера принят')
                    ->body(collect($numbers)->map(fn ($number): string => '№: '.e($number))->implode('<br>'))->persistent()->send();
            });
    }
}
