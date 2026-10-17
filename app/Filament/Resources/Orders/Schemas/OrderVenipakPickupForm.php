<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Services\Admin\VenipakPickupCatalog;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;

class OrderVenipakPickupForm
{
    public static function destinationField(): Select
    {
        return Select::make('destination')->label('Направление')
            ->options(['address' => 'На адрес', 'pickup' => 'На отделение'])
            ->required()->live()->afterStateUpdated(function ($get, $set): void {
                if ($get('destination') === 'pickup') {
                    self::initialize($get, $set);
                }
            });
    }

    public static function countryField(): Select
    {
        return Select::make('r_country')->label('Страна')->required()
            ->options(VenipakPickupCatalog::COUNTRIES)->live()
            ->afterStateUpdated(fn ($get, $set) => self::reset($get, $set));
    }

    public static function fields(): array
    {
        return [
            Hidden::make('pickup_match')->dehydrated(false),
            ...array_map(fn (string $name) => Hidden::make($name), [
                'g_city_pickup', 'g_address_pickup', 'g_post_pickup', 'g_name_pickup', 'g_code_pickup',
            ]),
            Select::make('pickup_city')->label('Выбор города для пик-ап пункта')
                ->placeholder('Все города')->searchable()->live()->dehydrated(false)
                ->options(fn ($get): array => $get('destination') === 'pickup' ? app(VenipakPickupCatalog::class)->cities($get('r_country')) : [])
                ->afterStateUpdated(function ($get, $set): void {
                    $id = $get('pickup_city') ? array_key_first(app(VenipakPickupCatalog::class)->options($get('r_country'), $get('pickup_city'))) : null;
                    $set('pickup_point', $id);
                    self::select($get, $set, $id);
                })
                ->visible(fn ($get): bool => $get('destination') === 'pickup'),
            Select::make('pickup_point')->label('Выбор пик-ап пункта')
                ->columnSpanFull()
                ->placeholder('Выберите пункт')->searchable()->live()->required()->dehydrated(false)
                ->options(fn ($get): array => $get('destination') === 'pickup' ? app(VenipakPickupCatalog::class)->options($get('r_country'), $get('pickup_city')) : [])
                ->helperText(fn ($get): ?string => $get('destination') === 'pickup' && app(VenipakPickupCatalog::class)->points($get('r_country')) === []
                    ? 'Список пунктов недоступен или пуст. Проверьте страну и настройки Venipak; повторите через 30 секунд.' : null)
                ->afterStateHydrated(function ($get, $set): void {
                    if ($get('destination') === 'pickup') {
                        self::initialize($get, $set);
                    }
                })
                ->afterStateUpdated(fn ($get, $set, $state) => self::select($get, $set, $state))
                ->visible(fn ($get): bool => $get('destination') === 'pickup'),
        ];
    }

    public static function initialize($get, $set): void
    {
        $id = app(VenipakPickupCatalog::class)->match($get('r_country'), $get('pickup_match') ?: $get('g_address_pickup'));
        $set('pickup_city', null);
        $set('pickup_point', $id);
        self::select($get, $set, $id);
    }

    public static function reset($get, $set): void
    {
        $set('pickup_city', null);
        $set('pickup_point', null);
        foreach (['g_city_pickup', 'g_address_pickup', 'g_post_pickup', 'g_name_pickup', 'g_code_pickup'] as $key) {
            $set($key, '');
        }
    }

    private static function select($get, $set, ?string $id): void
    {
        if ($id === null) {
            foreach (['g_city_pickup', 'g_address_pickup', 'g_post_pickup', 'g_name_pickup', 'g_code_pickup'] as $key) {
                $set($key, '');
            }

            return;
        }
        foreach (app(VenipakPickupCatalog::class)->selection($get('r_country'), $id) as $key => $value) {
            $set($key, $value);
        }
    }
}
