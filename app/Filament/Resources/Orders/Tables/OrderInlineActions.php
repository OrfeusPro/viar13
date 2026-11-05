<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Orders;
use App\Services\Admin\OrderInvoiceService;
use App\Services\Admin\OrderLifecycleService;
use App\Services\Admin\OrderPaymentService;
use App\Services\Admin\OrderUserService;
use App\Services\Admin\UpdateOrderVrNumberService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderInlineActions
{
    public static function updateFirm(): Action
    {
        return Action::make('updateOrderInvoiceFirm')->label('Обновить данные фирмы')
            ->authorize(fn (Orders $record): bool => auth('filament')->user()?->role?->name !== 'printing'
                && (auth('filament')->user()?->can('update', $record) ?? false))
            ->action(function (Orders $record, array $arguments): void {
                abort_unless(auth('filament')->user()?->role?->name !== 'printing'
                    && auth('filament')->user()?->can('update', $record), 403);
                try {
                    $rules = [];
                    foreach (['name', 'reg_num', 'addr', 'bank', 'vat_num', 'bank_code', 'office_addr', 'acc_num'] as $field) {
                        $rules[$field] = [in_array($field, ['name', 'addr'], true) ? 'required' : 'nullable', 'string',
                            'max:'.(in_array($field, ['addr', 'office_addr'], true) ? 500 : 255)];
                    }
                    $data = validator($arguments, $rules)->validate();
                    app(OrderInvoiceService::class)->generate($record, $data);
                    Notification::make()->success()->title('Данные фирмы сохранены в PDF-счёте')->send();
                } catch (ValidationException $exception) {
                    Notification::make()->warning()->title('Данные фирмы не сохранены')
                        ->body(collect($exception->errors())->flatten()->first())->send();
                }
            });
    }

    public static function update(): Action
    {
        return Action::make('updateOrderInlineField')->label('Обновить поле заказа')
            ->authorize(fn (Orders $record): bool => auth('filament')->user()?->can('update', $record) ?? false)
            ->action(function (Orders $record, array $arguments): void {
                abort_unless(auth('filament')->user()?->can('update', $record), 403);
                try {
                    $data = validator($arguments, [
                        'field' => ['required', Rule::in(['status', 'delivery_date', 'payment_status', 'prepayment_price', 'pdf_locale', 'client_status', 'vr_number', 'vr_prefix'])],
                        'value' => ['present', 'nullable', 'string', 'max:255'],
                    ])->validate();
                    abort_if(auth('filament')->user()?->role?->name === 'printing'
                        && in_array($data['field'], ['payment_status', 'prepayment_price', 'vr_number', 'vr_prefix'], true), 403);
                    $value = $data['value'];
                    match ($data['field']) {
                        'status' => app(OrderLifecycleService::class)->updateStatus($record, (string) $value),
                        'delivery_date' => app(OrderLifecycleService::class)->updateDeliveryDate($record, $value),
                        'payment_status' => app(OrderPaymentService::class)->updateStatus($record, (string) $value),
                        'prepayment_price' => app(OrderPaymentService::class)->updatePrepayment($record, $value),
                        'pdf_locale' => app(OrderUserService::class)->updatePdfLocale($record, (string) $value),
                        'client_status' => app(OrderUserService::class)->updateClientStatus($record, (string) $value),
                        'vr_number' => app(UpdateOrderVrNumberService::class)->update($record, $value),
                        'vr_prefix' => app(UpdateOrderVrNumberService::class)->generate($record, (string) $value),
                    };
                    $record->refresh();
                    Notification::make()->success()->title('Изменение сохранено')->send();
                } catch (ValidationException $exception) {
                    Notification::make()->warning()->title('Изменение не сохранено')
                        ->body(collect($exception->errors())->flatten()->first())->send();
                }
            });
    }
}
