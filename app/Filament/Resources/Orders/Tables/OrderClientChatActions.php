<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Orders;
use App\Services\Admin\OrderClientChatService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class OrderClientChatActions
{
    public static function history(): Action
    {
        return Action::make('viewClientChat')
            ->label('Чат с клиентом')
            ->modalHeading(fn ($record): string => 'Чат с клиентом · заказ №'.$record->id)
            ->modalWidth('4xl')
            ->registerModalActions([self::reply(), self::read()])
            ->modalContent(function (Orders $record) {
                $record->load(['order_user_comments' => fn ($query) => $query->oldest('created_at')->orderBy('id'),
                    'order_user_comments.author', 'order_painter_images.statusDefinition']);

                return view('filament.tables.modals.order-client-chat', ['record' => $record]);
            })
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Закрыть')
            ->action(fn (): null => null)
            ->authorize(fn ($record): bool => auth('filament')->user()?->can('view', $record) ?? false)
            ->extraAttributes(['class' => 'hidden']);
    }

    public static function reply(): Action
    {
        return Action::make('sendClientChatMessage')
            ->label('Ответить клиенту')
            ->modalHeading(fn ($record): string => 'Ответ клиенту · заказ №'.$record->id)
            ->modalDescription(fn (): string => 'Сообщение сразу появится в кабинете клиента. '.(
                config('admin_migration.client_chat_notifications_enabled', false)
                    ? 'Email и CRM webhook включены.'
                    : 'Email и CRM webhook отключены на период UAT.'))
            ->schema([
                Select::make('thread_type')->label('Переписка')
                    ->options(['general' => 'Общая', 'sketch' => 'По наброску', 'painter' => 'По картине'])
                    ->required()->live()->afterStateUpdated(fn ($set) => $set('image_id', null)),
                Select::make('image_id')->label('Изображение')
                    ->options(function ($record, $get): array {
                        $flag = $get('thread_type') === 'sketch' ? 'is_img_sketch' : 'is_img_painter';

                        return $record->order_painter_images()->where($flag, 1)->get()->mapWithKeys(
                            fn ($image): array => [$image->id => '#'.$image->id.' · '.basename((string) $image->image)],
                        )->all();
                    })
                    ->visible(fn ($get): bool => $get('thread_type') !== 'general')
                    ->required(fn ($get): bool => $get('thread_type') !== 'general'),
                Textarea::make('comment')->label('Сообщение клиенту')->required()->rows(5)->maxLength(10000),
            ])
            ->fillForm(fn (array $arguments): array => [
                'thread_type' => $arguments['thread_type'] ?? 'general',
                'image_id' => $arguments['image_id'] ?? null,
            ])
            ->modalSubmitActionLabel('Отправить сообщение')
            ->action(function ($record, array $data): void {
                $result = app(OrderClientChatService::class)->send($record, auth('filament')->user(), $data);
                $record->unsetRelation('order_user_comments');
                $notification = Notification::make()->title('Сообщение сохранено в чате клиента');
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
        return Action::make('readClientChatMessage')
            ->label('Отметить прочитанным')
            ->action(function ($record, array $arguments): void {
                $data = validator($arguments, ['message_id' => ['required', 'integer', 'min:1']])->validate();
                app(OrderClientChatService::class)->markMessageAsRead($record, auth('filament')->user(), (int) $data['message_id']);
                $record->unsetRelation('order_user_comments');
                Notification::make()->success()->title('Сообщение отмечено прочитанным')->send();
            })
            ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false)
            ->extraAttributes(['class' => 'hidden']);
    }
}
