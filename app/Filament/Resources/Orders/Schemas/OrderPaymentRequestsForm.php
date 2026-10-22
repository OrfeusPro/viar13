<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Orders;
use App\Services\Payment\OrderPaymentRequestService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;

class OrderPaymentRequestsForm
{
    public static function createAction(): Action
    {
        return Action::make('createPaymentRequest')->label('Создать платёж')
            ->modalHeading(fn (Orders $record): string => 'Создать платёж для заказа №'.$record->id)
            ->modalSubmitActionLabel('Создать платёж')
            ->schema([
                TextInput::make('amount')->label('Сумма, EUR')->numeric()->minValue(0.01)->maxValue(999999.99)->required(),
                TextInput::make('purpose')->label('Назначение платежа')->maxLength(255),
            ])
            ->fillForm(fn (Orders $record): array => ['amount' => number_format((float) ($record->sale_price ?: $record->price), 2, '.', '')])
            ->authorize(fn (Orders $record): bool => auth('filament')->user()?->can('update', $record) ?? false)
            ->action(function (Orders $record, array $data): void {
                abort_unless(auth('filament')->user()?->can('update', $record), 403);
                app(OrderPaymentRequestService::class)->createForOrder($record, $data, auth('filament')->user());
                $record->unsetRelation('order_payment_requests');
                Notification::make()->success()->title('Заявка на оплату создана')->send();
            });
    }

    public static function section(): Section
    {
        return Section::make('Заявки на оплату')->key('paymentRequests')->columnSpanFull()
            ->schema([
                Placeholder::make('payment_requests_display')->label('Заявки на оплату')->hiddenLabel()->content(fn (Orders $record) => view('filament.orders.payment-requests', [
                    'requests' => $record->order_payment_requests()->latest('id')->get(),
                ])),
            ])->footerActions([self::createAction()]);
    }
}
