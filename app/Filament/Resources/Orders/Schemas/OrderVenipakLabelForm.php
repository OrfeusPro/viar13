<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Services\Admin\VenipakPickupCatalog;
use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;

class OrderVenipakLabelForm
{
    public const INSURANCE_OPTIONS = [
        '10' => 'Ответственность перевозчика: стандарт ~10 евро за 1 кг отгрузки',
        '100' => 'до 100 EUR за всю посылку', '200' => 'до 200 EUR за всю посылку',
        '300' => 'до 300 EUR за всю посылку', '400' => 'до 400 EUR за всю посылку',
        '500' => 'до 500 EUR за всю посылку', '1000' => 'до 1000 EUR за всю посылку',
        '2000' => 'до 2000 EUR за всю посылку', '3000' => 'до 3000 EUR за всю посылку',
        '4000' => 'до 4000 EUR за всю посылку', '5000' => 'до 5000 EUR за всю посылку',
        '10000' => 'до 10000 EUR за всю посылку',
    ];

    public static function insurancePrice(mixed $amount): string
    {
        // The original form calculates a quote only; its controller does not send insur to Venipak.
        $price = isset(self::INSURANCE_OPTIONS[(string) $amount]) && (int) $amount > 10
            ? round((int) $amount * 0.3 / 100, 2) : 0;

        return rtrim(rtrim(number_format($price, 2, '.', ''), '0'), '.').' EUR';
    }

    public static function components(): array
    {
        return [Group::make()->extraAttributes(['class' => 'viar-venipak-form'])->schema([
            Section::make()->compact()->extraAttributes(['class' => 'viar-venipak-document'])->schema([
                TextInput::make('doc_no')->label('№ док. на посылку')->inlineLabel()->maxLength(16),
            ]),
            Grid::make(['default' => 1, 'lg' => 2])->schema([
                Section::make('Получатель')->compact()
                    ->columns(1)
                    ->schema(self::rows([
                        OrderVenipakPickupForm::destinationField(),
                        TextInput::make('g_name')->label('Название')->required()->maxLength(60),
                        TextInput::make('g_code')->label('Код компании')->maxLength(20),
                        OrderVenipakPickupForm::countryField(),
                        ...OrderVenipakPickupForm::fields(),
                        TextInput::make('g_city')->label('Город')->required()->maxLength(40)
                            ->visible(fn ($get): bool => $get('destination') === 'address'),
                        TextInput::make('g_address')->label('Улица')->required()->maxLength(100)
                            ->visible(fn ($get): bool => $get('destination') === 'address'),
                        TextInput::make('g_house')->label('Дом')->maxLength(15)
                            ->visible(fn ($get): bool => $get('destination') === 'address'),
                        TextInput::make('g_flat')->label('Квартира')->maxLength(15)
                            ->visible(fn ($get): bool => $get('destination') === 'address'),
                        TextInput::make('g_post')->label('Почтовый индекс')->required()->maxLength(12)
                            ->hintAction(Action::make('findRecipientPostcode')->label('Искать')
                                ->url('http://www.pasts.lv/lv/kategorija/pasta_indeksa_meklesana/')->openUrlInNewTab())
                            ->visible(fn ($get): bool => $get('destination') === 'address'),
                        TextInput::make('g_contact_p')->label('Контактное лицо')->required()->maxLength(80),
                        TextInput::make('g_contact_t')->label('Телефон')->type('tel')->required()->maxLength(30),
                        TextInput::make('email_receiver')->label('Эл. почта')->email()->maxLength(100),
                    ])),
                Section::make('Отправитель')->compact()
                    ->columns(1)
                    ->schema(self::rows([
                        TextInput::make('s_name')->label('Название')->required()->maxLength(60),
                        TextInput::make('s_code')->label('Код компании')->required()->maxLength(20),
                        Select::make('s_country')->label('Страна')->required()->options(VenipakPickupCatalog::COUNTRIES),
                        TextInput::make('s_city')->label('Город')->required()->maxLength(40),
                        TextInput::make('s_address')->label('Адрес')->required()->maxLength(100),
                        TextInput::make('s_post')->label('Почтовый индекс')->required()->maxLength(12)
                            ->hintAction(Action::make('findSenderPostcode')->label('Искать')
                                ->url('http://www.pasts.lv/lv/kategorija/pasta_indeksa_meklesana/')->openUrlInNewTab())
                            ->helperText('При указании неверного почтового кода посылка не будет взята у отправителя.'),
                        TextInput::make('s_contact_p')->label('Контактное лицо')->required()->maxLength(60),
                        TextInput::make('s_contact_t')->label('Телефон')->type('tel')->required()->maxLength(30),
                        TextInput::make('email_sender')->label('Эл. почта')->email()->maxLength(100),
                    ])),
            ]),
            Grid::make(['default' => 1, 'lg' => 2])->schema([
                Section::make('Комментарии')->compact()->schema(self::rows([
                    TextInput::make('door_code')->label('Дверной код')->maxLength(10),
                    TextInput::make('office_no')->label('Номер кабинета')->maxLength(10),
                    TextInput::make('warehous_no')->label('Номер склада')->maxLength(10),
                ])),
                Section::make('Время доставки')->compact()
                    ->columns(['default' => 1, 'sm' => 2])
                    ->schema([
                        Radio::make('delivery_type')
                            ->label('Срок доставки')
                            ->options([
                                'nwd' => 'Следующий рабочий день',
                                'nwd10' => 'Следующий рабочий день до 10:00',
                                'nwd12' => 'Следующий рабочий день до 12:00',
                                'nwd8_14' => 'Следующий рабочий день 8:00–14:00',
                                'nwd14_17' => 'Следующий рабочий день 14:00–17:00',
                                'nwd18_22' => 'Следующий рабочий день 18:00–22:00',
                                'sat' => 'Суббота',
                            ])->required(),
                        Radio::make('delivery_express')
                            ->label('Экспресс')
                            ->options(['0' => '48 часов (2 рабочих дня)', '1' => '24 часа (на следующий рабочий день)'])
                            ->required(),
                    ]),
                Section::make('Указания')->compact()->schema(self::rows([
                    TextInput::make('cod')->label('C.O.D.')->numeric()->minValue(0),
                    Select::make('cod_type')->label('Валюта COD')->options(['EUR' => 'EUR', 'PLN' => 'PLN', 'CZK' => 'CZK'])->required(),
                    Placeholder::make('return_documents')->label('Вернуть сопроводительные документы по адресу')
                        ->content('Адрес отправителя'),
                    Toggle::make('comment_call')->label('Позвонить перед доставкой'),
                    Toggle::make('four_hands')->label('Нужны четыре руки'),
                ])),
                Section::make('Страхование посылки')->compact()
                    ->columns(1)
                    ->schema(self::rows([
                        Select::make('insur')->label('Страховая стоимость посылки')
                            ->placeholder('Выберите')->options(self::INSURANCE_OPTIONS)
                            ->helperText('Предварительный расчёт стоимости. Выбор не оформляет страхование отправления.')
                            ->live()->dehydrated(false),
                        Placeholder::make('insurance_price')->label('Цена страховки')
                            ->content(fn ($get): string => self::insurancePrice($get('insur'))),
                    ])),
            ]),
            Section::make('Упаковки')->compact()
                ->schema([
                    Repeater::make('packages')
                        ->label('Упаковки')
                        ->minItems(1)
                        ->maxItems(20)
                        ->defaultItems(1)
                        ->addActionLabel('Добавить посылку')
                        ->addAction(fn (Action $action) => $action->action(function (Repeater $component): void {
                            $items = $component->getRawState() ?? [];
                            $previous = $items === [] ? ['weight' => null, 'volume' => null, 'pallet' => '0'] : end($items);
                            $key = $component->generateUuid();
                            if ($key) {
                                $items[$key] = $previous;
                            } else {
                                $items[] = $previous;
                            }
                            $component->rawState($items);
                            $component->collapsed(false, shouldMakeComponentCollapsible: false);
                            $component->callAfterStateUpdated();
                        }))
                        ->columns(3)
                        ->schema([
                            TextInput::make('weight')->label('Вес, кг')->numeric()->minValue(0.01)->required(),
                            TextInput::make('volume')->label('Объём')->numeric()->minValue(0),
                            Select::make('pallet')->label('Паллет')->options([
                                '0' => 'Нет', '2' => '1.2m / 0.8m', '6' => '1.2m / 1m', '7' => '1.2m / 1.2m',
                                '3' => '0.8m / 0.6m', '4' => 'Другое',
                            ])->required(),
                        ]),
                ]),
        ])];
    }

    private static function rows(array $fields): array
    {
        foreach ($fields as $field) {
            if ($field instanceof Field) {
                $field->inlineLabel();
            }
        }

        return $fields;
    }
}
