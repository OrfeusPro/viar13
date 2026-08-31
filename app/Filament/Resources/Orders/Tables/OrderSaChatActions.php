<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Orders;
use App\Services\Admin\OrderSaChatService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class OrderSaChatActions
{
    public static function history(): Action
    {
        return Action::make('viewSaChat')
            ->label('WhatsApp чат (SA)')
            ->modalHeading(fn ($record): string => 'WhatsApp SA · заказ №'.$record->id)
            ->modalWidth('4xl')
            ->registerModalActions([self::read()])
            ->modalContent(function (Orders $record) {
                $record->load(['saConversations', 'saMessages' => fn ($query) => $query
                    ->orderByRaw('COALESCE(sent_at, created_at) asc')->orderBy('id')]);

                return view('filament.tables.modals.order-sa-chat', ['record' => $record]);
            })
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Закрыть')
            ->action(fn (): null => null)
            ->authorize(fn ($record): bool => auth('filament')->user()?->can('view', $record) ?? false)
            ->extraAttributes(['class' => 'hidden']);
    }

    public static function read(): Action
    {
        return Action::make('readSaConversation')
            ->label('Отметить диалог прочитанным')
            ->action(function ($record, array $arguments): void {
                try {
                    $data = validator($arguments, ['snapshot' => ['required', 'string', 'max:4096']])->validate();
                    app(OrderSaChatService::class)->markAsRead($record, auth('filament')->user(), $data['snapshot']);
                    $record->unsetRelation('saConversations');
                    Notification::make()->success()->title('Диалог отмечен прочитанным')->send();
                } catch (ValidationException $exception) {
                    Notification::make()->warning()->title('Прочтение не изменено')
                        ->body(collect($exception->errors())->flatten()->first())->send();
                }
            })
            ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false)
            ->extraAttributes(['class' => 'hidden']);
    }
}
