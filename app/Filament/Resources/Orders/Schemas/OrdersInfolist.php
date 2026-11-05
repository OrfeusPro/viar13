<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrdersInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основные данные')->columns(3)->schema([
                    TextEntry::make('id')->label('ID'),
                    TextEntry::make('user.email')->label('Клиент'),
                    TextEntry::make('manager.email')->label('Менеджер')->placeholder('Не назначен'),
                    TextEntry::make('status')->label('Статус')->badge(),
                    TextEntry::make('payment_status')->label('Оплата')->badge(),
                    TextEntry::make('created_at')->label('Создан')->dateTime('d.m.Y H:i'),
                    TextEntry::make('price')->label('Цена')->money('EUR'),
                    TextEntry::make('sale_price')->label('Цена со скидкой')->money('EUR'),
                    TextEntry::make('prepayment_price')->label('Предоплата')->money('EUR'),
                    TextEntry::make('comment')->label('Комментарий клиента')->columnSpanFull(),
                    TextEntry::make('admin_comment')->label('Комментарий администратора')->columnSpanFull(),
                ]),
                Section::make('Доставка')->schema([
                    TextEntry::make('delivery')
                        ->label('Данные доставки')
                        ->formatStateUsing(fn ($state): string => json_encode(json_decode((string) $state, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: (string) $state)
                        ->fontFamily('mono'),
                ]),
                Section::make('Позиции')->schema([
                    TextEntry::make('items')
                        ->label('Состав заказа')
                        ->formatStateUsing(fn ($state): string => json_encode(json_decode((string) $state, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: (string) $state)
                        ->fontFamily('mono'),
                ]),
            ]);
    }
}
