<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Services\Admin\OrderLifecycleService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

class OrderLifecycleActions
{
    public static function updateStatus(): Action
    {
        return Action::make('updateOrderStatus')
            ->label('Статус заказа')
            ->modalHeading(fn ($record): string => 'Статус заказа №'.$record->id)
            ->modalSubmitActionLabel('Обновить статус')
            ->schema([
                Select::make('status')->label('Статус')->options(self::statusOptions())->required(),
            ])
            ->fillForm(fn ($record, array $arguments): array => [
                'status' => $arguments['status'] ?? $record->status,
            ])
            ->action(function ($record, array $data): void {
                $result = app(OrderLifecycleService::class)->updateStatus($record, $data['status']);
                $notification = Notification::make()->success()->title('Статус заказа обновлён');
                if ($result['notifications_suppressed'] && in_array($result['status'], ['sended', 'send_lubanas'], true)) {
                    $notification->body('Письмо клиенту отключено на период UAT.');
                }
                $notification->send();
            })
            ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false);
    }

    public static function updateDeliveryDate(): Action
    {
        return Action::make('updateOrderDeliveryDate')
            ->label('Желаемая дата доставки')
            ->modalHeading(fn ($record): string => 'Дата доставки заказа №'.$record->id)
            ->modalSubmitActionLabel('Обновить')
            ->schema([
                DatePicker::make('delivery_date')->label('Желаемая дата доставки')->nullable(),
            ])
            ->fillForm(function ($record): array {
                $delivery = json_decode((string) $record->delivery, true) ?: [];

                return ['delivery_date' => $delivery['when_send'] ?? null];
            })
            ->action(function ($record, array $data): void {
                app(OrderLifecycleService::class)->updateDeliveryDate($record, $data['delivery_date'] ?? null);
                Notification::make()->success()->title('Дата доставки обновлена')->send();
            })
            ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false);
    }

    public static function viewClient(): Action
    {
        return Action::make('viewOrderClient')
            ->label('Просмотр клиента')
            ->modalHeading(fn ($record): string => 'Клиент заказа №'.$record->id)
            ->modalContent(fn ($record) => view('filament.tables.modals.order-client-card', [
                'record' => $record,
                'user' => $record->user,
            ]))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Закрыть')
            ->authorize(fn ($record): bool => auth('filament')->user()?->can('view', $record) ?? false);
    }

    public static function delete(): Action
    {
        return Action::make('deleteOrder')
            ->label('Удалить')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(fn ($record): string => 'Удалить заказ №'.$record->id.'?')
            ->modalDescription('Заказ будет удалён. Использованные бонусы будут возвращены клиенту по правилам Voyager.')
            ->modalSubmitActionLabel('Удалить заказ')
            ->action(function ($record): void {
                app(OrderLifecycleService::class)->delete($record);
                Notification::make()->success()->title('Заказ удалён')->send();
            })
            ->authorize(fn ($record): bool => auth('filament')->user()?->can('delete', $record) ?? false);
    }

    public static function statusOptions(): array
    {
        return [
            'watching' => 'На рассмотрении',
            'pegging' => 'В процессе',
            'in_production' => 'В производстве',
            'sended' => 'Отправлен',
            'send_lubanas' => 'Отправлен на Лубанас 65',
            'completed' => 'Завершён',
        ];
    }
}
