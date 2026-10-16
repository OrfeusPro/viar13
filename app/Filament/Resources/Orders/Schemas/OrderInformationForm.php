<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Orders;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;

class OrderInformationForm
{
    public static function sections(): array
    {
        return [
            Section::make('Действующие скидки')->columnSpanFull()
                ->visible(fn (Orders $record): bool => self::sales($record) !== [])
                ->schema([
                    Placeholder::make('existing_sales')->hiddenLabel()->content(fn (Orders $record) => view('filament.orders.existing-sales', [
                        'sales' => self::sales($record),
                    ])),
                ]),
            Section::make('Информация о заказе')->columnSpanFull()
                ->visible(fn (Orders $record): bool => (int) $record->is_admin_order === 2 && self::contents($record) !== [])
                ->schema([
                    Placeholder::make('existing_content')->hiddenLabel()->content(fn (Orders $record) => view('filament.orders.existing-content', [
                        'contents' => self::contents($record),
                    ])),
                ]),
        ];
    }

    public static function sales(Orders $order): array
    {
        $source = is_array($order->all_sales) ? $order->all_sales : json_decode((string) $order->all_sales, true);
        $sales = [];
        foreach ([
            'facebook_sale' => ['Скидка facebook', ' %'],
            'used_bonuses' => ['Скидка за бонусы', ' €'],
            'used_coupon' => ['Скидка за купон', ' €'],
            'used_coupon_30_40' => ['Скидка на товар 30x40', null],
            'dates_sale' => ['Скидка за 2 даты', ' %'],
        ] as $key => [$label, $unit]) {
            if (! isset($source[$key]) || ! is_scalar($source[$key])) {
                continue;
            }
            $sales[$label] = $unit === null ? ((int) $source[$key] === 1 ? 'да' : 'нет')
                : ((float) $source[$key] != 0 ? $source[$key].$unit : 'нет');
        }

        return $sales;
    }

    public static function contents(Orders $order): array
    {
        $items = is_array($order->items) ? $order->items : json_decode((string) $order->items, true);
        $contents = [];
        foreach ((array) $items as $item) {
            if (is_array($item) && isset($item['content']) && is_string($item['content'])) {
                // The original textarea has no persistence handler; show its content without suggesting a save action.
                $plain = preg_replace('/<br\s*\/?\s*>/i', "\n", $item['content']);
                $contents[] = html_entity_decode(strip_tags($plain), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }

        return $contents;
    }
}
