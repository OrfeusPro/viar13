<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Services\Admin\UpdateOrderService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrdersForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Управление заказом')
                    ->columns(2)
                    ->schema([
                        Select::make('manager_id')
                            ->label('Менеджер')
                            ->relationship('manager', 'email')
                            ->searchable()
                            ->preload(),
                        Select::make('status')
                            ->label('Статус')
                            ->options(array_combine(UpdateOrderService::STATUSES, [
                                'Новый', 'На рассмотрении', 'В процессе', 'В производстве',
                                'Отправлен', 'Отправлен на Лубанас 65', 'Завершён',
                            ]))
                            ->required(),
                        Select::make('payment_status')
                            ->label('Статус оплаты')
                            ->options([
                                'not_payed' => 'Не оплачено',
                                'prepayment' => 'Предоплата',
                                'payed' => 'Оплачено',
                            ])->required(),
                        Select::make('payment')
                            ->label('Способ оплаты')
                            ->options([
                                'cash_in_office' => 'Наличными в офисе',
                                'on_delivery' => 'При доставке',
                                'transfer' => 'Банковский перевод',
                                'online_paysera' => 'Paysera',
                                'google_pay' => 'Google Pay',
                                'apple_pay' => 'Apple Pay',
                                'paypalOnetimePayment' => 'PayPal',
                                'creditcart' => 'Карта онлайн',
                                'prepayment' => 'Предоплата',
                            ]),
                        TextInput::make('price')->label('Цена')->numeric()->minValue(0)->required(),
                        TextInput::make('sale_price')->label('Цена со скидкой')->numeric()->minValue(0),
                        TextInput::make('prepayment_price')->label('Предоплата')->numeric()->minValue(0),
                        Textarea::make('admin_comment')->label('Комментарий администратора')->columnSpanFull(),
                    ]),
            ]);
    }
}
