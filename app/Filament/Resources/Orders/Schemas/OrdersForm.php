<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Services\Admin\UpdateOrderService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrdersForm
{
    public static function configure(Schema $schema): Schema
    {
        $delivery = collect(OrderDeliveryForm::fields())->keyBy(fn ($field): string => $field->getName())->all();
        $parameters = collect(OrderDetailsForm::parameterFields())->keyBy(fn ($field): string => $field->getName())->all();
        $recipientFields = ['delivery_edit.email', 'delivery_edit.first_name', 'delivery_edit.last_name',
            'delivery_edit.phone', 'delivery_edit.phone_rec', 'delivery_edit.comment'];

        return $schema->extraAttributes(['class' => 'viar-order-edit-form'])
            ->columns(['default' => 1, 'lg' => 2])
            ->components([
                Group::make([
                    Section::make('Данные пользователя')->columns(1)->schema([
                        OrderDetailsForm::manager(),
                        Textarea::make('admin_comment')->label('Комментарий админа')->rows(1),
                        ...array_map(fn (string $name) => $delivery[$name], $recipientFields),
                        Group::make([$parameters['a_order_from'], $parameters['catid']])
                            ->extraAttributes(['class' => 'viar-order-sales-fields']),
                    ]),
                    Section::make('Оплата')->extraAttributes(['class' => 'viar-order-panel-muted'])->columns(1)->schema([
                        Select::make('payment')->label('Оплата')->options([
                            'cash_in_office' => 'Наличными при получении в офисе',
                            'on_delivery' => 'Оплата во время доставки', 'transfer' => 'Оплата перечислением',
                            'online_paysera' => 'Онлайн банкинг', 'google_pay' => 'Google Pay', 'apple_pay' => 'Apple Pay',
                            'paypalOnetimePayment' => 'PayPal', 'creditcart' => 'Картой онлайн', 'prepayment' => 'Предоплата',
                        ]),
                        Select::make('payment_status')->label('Статус оплаты')->live()
                            ->extraInputAttributes(fn ($state): array => ['class' => 'viar-create-payment-status', 'data-payment-status' => $state])
                            ->options([
                                'not_payed' => 'Не оплачено', 'prepayment' => 'Предоплата', 'payed' => 'Оплачено',
                            ])->required(),
                        TextInput::make('prepayment_price')->label('Предоплата')->numeric()->minValue(0),
                    ]),
                    OrderPaymentRequestsForm::section()->extraAttributes(['class' => 'viar-order-panel-muted']),
                    Section::make('Доставка')->extraAttributes(['class' => 'viar-order-panel-muted'])->columns(1)->schema(array_values(array_diff_key($delivery, array_flip($recipientFields)))),
                    Section::make('Данные заказа')->columns(1)->schema([$parameters['sale_eur'], $parameters['sale_percent'], $parameters['approved_date']]),
                    OrderDetailsForm::company()->columns(1),
                    Section::make('Дополнительные параметры')->columns(1)->collapsible()->collapsed()->schema([
                        Select::make('status')->label('Статус')->options(array_combine(UpdateOrderService::STATUSES, [
                            'Новый', 'На рассмотрении', 'В процессе', 'В производстве', 'Отправлен', 'Отправлен на Лубанас 65', 'Завершён',
                        ]))->required(),
                        TextInput::make('price')->label('Цена')->numeric()->minValue(0)->required(),
                        TextInput::make('sale_price')->label('Цена со скидкой')->numeric()->minValue(0),
                    ]),
                    ...OrderInformationForm::sections(),
                ])->columnSpan(1),
                Group::make([
                    OrderMediaForm::section(),
                    Section::make('Фото коммент и текст клиента')->columnSpanFull()->schema([
                        Placeholder::make('client_image_comments')->label('Комментарии клиента к изображениям')->hiddenLabel()
                            ->content(fn ($record) => view('filament.orders.client-image-comments', [
                                'record' => $record,
                                'comments' => $record->order_user_comments()->oldest('created_at')->oldest('id')->get(),
                            ])),
                    ]),
                    OrderItemsForm::section(),
                ])->columnSpan(1),
            ]);
    }
}
