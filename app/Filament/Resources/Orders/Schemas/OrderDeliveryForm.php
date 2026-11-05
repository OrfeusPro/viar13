<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Services\Admin\OrderDeliveryEditor;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class OrderDeliveryForm
{
    public static function section(): Section
    {
        return Section::make('Получатель и доставка')->columnSpanFull()->columns(2)->schema(self::fields());
    }

    public static function fields(): array
    {
        $sync = fn ($get, $set) => app(OrderDeliveryEditor::class)->synchronize($get, $set);

        return [
            TextInput::make('delivery_edit.first_name')->label('Имя')->maxLength(255),
            TextInput::make('delivery_edit.last_name')->label('Фамилия')->maxLength(255),
            TextInput::make('delivery_edit.email')->label('Email')->email()->maxLength(255),
            // Keep the phone keyboard without Filament's restrictive regex: original normalizes formatting on save.
            TextInput::make('delivery_edit.phone')->label('Телефон заказчика')->type('tel')->maxLength(50),
            TextInput::make('delivery_edit.phone_rec')->label('Телефон получателя')->type('tel')->maxLength(50),
            Select::make('delivery_edit.sposob')->label('Способ доставки')
                ->options(OrderDeliveryEditor::methods())->live()->afterStateUpdated($sync),
            Select::make('delivery_edit.country')->label('Страна')->searchable()->preload()
                ->options(fn (): array => app(OrderDeliveryEditor::class)->countries())
                ->live()->afterStateUpdated($sync),
            Select::make('delivery_edit.pickup_workshop_id')->label('Самовывоз (мастерская VIAR)')
                ->options(fn ($get): array => app(OrderDeliveryEditor::class)->pickups($get('delivery_edit.country')))
                ->visible(fn ($get): bool => in_array($get('delivery_edit.sposob'), OrderDeliveryEditor::PICKUP_METHODS, true))
                ->live()->afterStateUpdated(function ($state, $get, $set): void {
                    $title = app(OrderDeliveryEditor::class)->pickups($get('delivery_edit.country'))[$state] ?? null;
                    if ($title !== null) {
                        $set('delivery_edit.address', $title);
                        $set('delivery_edit.city', $get('delivery_edit.sposob') === 'pickup_at_viar_workshop' ? null : $title);
                    }
                }),
            Select::make('delivery_edit.delivery_town_id')->label('Город доставки')
                ->options(fn ($get): array => app(OrderDeliveryEditor::class)->towns($get('delivery_edit.country')))
                ->visible(fn ($get): bool => $get('delivery_edit.sposob') === 'city_delivery')
                ->live()->afterStateUpdated(function ($state, $get, $set): void {
                    $title = app(OrderDeliveryEditor::class)->towns($get('delivery_edit.country'))[$state] ?? null;
                    if ($title !== null) {
                        $set('delivery_edit.city', $title);
                    }
                }),
            TextInput::make('delivery_edit.city')->label('Город')->maxLength(255)
                ->visible(fn ($get): bool => ! in_array($get('delivery_edit.sposob'), OrderDeliveryEditor::PICKUP_METHODS, true)),
            TextInput::make('delivery_edit.address')->label('Адрес')->maxLength(1000),
            TextInput::make('delivery_edit.postal_index')->label('Почтовый индекс')->maxLength(30),
            DatePicker::make('delivery_edit.when_send')->label('Когда получить'),
            TextInput::make('delivery_edit.deliv_price')->label('Цена за доставку')->numeric()->minValue(0),
            Textarea::make('delivery_edit.comment')->label('Комментарий')->maxLength(10000)->columnSpanFull(),
        ];
    }
}
