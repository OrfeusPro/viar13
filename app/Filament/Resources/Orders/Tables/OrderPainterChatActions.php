<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Orders;
use App\Services\Admin\OrderPainterChatService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class OrderPainterChatActions
{
    public static function history(): Action
    {
        return Action::make('viewPainterChat')
            ->label('Чат с художником')
            ->modalHeading(fn ($record): string => 'Чат с художником · заказ №'.$record->id)
            ->modalWidth('4xl')
            ->extraModalWindowAttributes(['class' => 'adm-chat-modal adm-chat-modal--painter'])
            ->registerModalActions([self::reply(), self::read()])
            ->modalContent(function (Orders $record) {
                $record->load(['orders_chats' => fn ($query) => $query->oldest('created_at')->orderBy('id')]);

                return view('filament.tables.modals.order-painter-chat', ['record' => $record]);
            })
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->action(fn (): null => null)
            ->authorize(fn ($record): bool => auth('filament')->user()?->can('view', $record) ?? false)
            ->extraAttributes(['class' => 'hidden']);
    }

    public static function reply(): Action
    {
        return Action::make('sendPainterChatMessage')
            ->label('Ответить художнику')
            ->modalHeading(fn ($record): string => 'Ответ художнику · заказ №'.$record->id)
            ->modalDescription(fn (): string => 'Общий чат заказа. Сообщение сразу появится в кабинете назначенного художника. '.(
                config('admin_migration.painter_chat_notifications_enabled', false)
                    ? 'Email и CRM webhook включены.'
                    : 'Email и CRM webhook отключены на период UAT.'))
            ->schema([Textarea::make('comment')->label('Сообщение художнику')->required()->rows(5)->maxLength(10000)])
            ->modalSubmitActionLabel('Отправить сообщение')
            ->action(function ($record, array $data): void {
                $result = app(OrderPainterChatService::class)->send($record, auth('filament')->user(), $data);
                $record->unsetRelation('orders_chats');
                $notification = Notification::make()->title('Сообщение сохранено в чате художника');
                if ($result['notificationsSuppressed']) {
                    $notification->success()->body('Email и CRM webhook не отправлялись.');
                } elseif (! $result['mailSent'] || ! $result['webhookSent']) {
                    $notification->warning()->body('Часть внешних уведомлений не доставлена. Не отправляйте сообщение повторно: оно уже сохранено.');
                } else {
                    $notification->success();
                }
                $notification->send();
            })
            ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false)
            ->extraAttributes(['class' => 'hidden']);
    }

    public static function read(): Action
    {
        return Action::make('readPainterChatMessage')
            ->label('Отметить прочитанным')
            ->action(function ($record, array $arguments): void {
                $data = validator($arguments, ['message_id' => ['required', 'integer', 'min:1']])->validate();
                app(OrderPainterChatService::class)->markMessageAsRead($record, auth('filament')->user(), (int) $data['message_id']);
                $record->unsetRelation('orders_chats');
                Notification::make()->success()->title('Сообщение отмечено прочитанным')->send();
            })
            ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false)
            ->extraAttributes(['class' => 'hidden']);
    }
}
