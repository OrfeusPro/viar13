<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Orders;
use App\Services\Admin\OrderSaChatService;
use App\Services\Admin\OrderSaCommandService;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
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
            ->extraModalWindowAttributes(['class' => 'adm-chat-modal adm-chat-modal--wide'])
            ->registerModalActions([self::read(), self::reply(), self::bot()])
            ->modalContent(fn (Orders $record) => view('filament.tables.modals.order-sa-chat-panel', ['record' => $record]))
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
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

    public static function reply(): Action
    {
        return self::command('sendSaMessage', 'Ответить в WhatsApp')
            ->schema([
                Hidden::make('token')->required(),
                Hidden::make('action')->default('send'),
                Textarea::make('text')->label('Сообщение')->required()->maxLength(10000)->rows(5),
                Toggle::make('handoff')->label('Передать диалог менеджеру (Handoff) после приёма сообщения')->default(false)
                    ->helperText('Если выключено, текущий режим бота сохраняется.'),
            ]);
    }

    public static function bot(): Action
    {
        return self::command('controlSaBot', 'Управление ботом')
            ->schema([
                Hidden::make('token')->required(),
                Select::make('action')->label('Команда')->required()->options([
                    'pause_bot' => 'Pause — приостановить', 'resume_bot' => 'Resume — включить',
                    'handoff_to_manager' => 'Handoff — передать менеджеру',
                ]),
            ]);
    }

    private static function command(string $name, string $label): Action
    {
        return Action::make($name)->label($label)
            ->modalHeading(fn ($record): string => $label.' · заказ №'.$record->id)
            ->modalDescription('UAT: внешние вызовы по умолчанию отключены. Тестовые ADM-FIL-UAT диалоги никогда не отправляются наружу. При включённой интеграции успешный приём не означает доставку WhatsApp.')
            ->fillForm(function ($record, array $arguments) use ($name): array {
                $data = validator($arguments, ['conversation_id' => ['required', 'integer', 'min:1']])->validate();

                return ['token' => app(OrderSaCommandService::class)->token($record, auth('filament')->user(), (int) $data['conversation_id']),
                    'action' => $name === 'sendSaMessage' ? 'send' : null, 'handoff' => false];
            })
            ->modalSubmitActionLabel('Подтвердить')
            ->action(function ($record, array $data) use ($name): void {
                // Hidden fields are not an authorization boundary.
                if ($name === 'sendSaMessage') {
                    $data['action'] = 'send';
                } else {
                    validator($data, ['action' => ['required', 'in:pause_bot,resume_bot,handoff_to_manager']])->validate();
                    unset($data['text'], $data['handoff']);
                }
                try {
                    $result = app(OrderSaCommandService::class)->execute($record, auth('filament')->user(), $data);
                } catch (ValidationException $exception) {
                    // Hidden token errors have no visible field to attach to.
                    if (! array_key_exists('token', $exception->errors())) {
                        throw $exception;
                    }
                    Notification::make()->warning()->title('Команда не выполнена')
                        ->body(collect($exception->errors())->flatten()->first())->send();

                    return;
                }
                $record->unsetRelation('saMessages')->unsetRelation('saConversations')->unsetRelation('order_user_comments');
                $description = match ($result['status']) {
                    'uat_suppressed' => 'Тест сохранён. Ничего не отправлено; кабинет клиента и режим бота не изменены.',
                    'accepted' => 'Интеграция приняла команду. Доставка WhatsApp подтверждается отдельным статусом.',
                    'partial' => 'Сообщение принято, но Handoff не подтверждён. Не отправляйте текст повторно.',
                    'state_conflict' => 'Связь или режим диалога изменились во время отправки. Нужна проверка состояния; не повторяйте отправку.',
                    default => 'Результат внешней операции не подтверждён. Автоматического повтора нет; проверьте журнал перед новой командой.',
                };
                $notification = Notification::make()->title($result['duplicate'] ? 'Команда уже зарегистрирована, повтор не отправлен' : 'Команда зарегистрирована')
                    ->body($description);
                in_array($result['status'], ['accepted', 'uat_suppressed'], true) ? $notification->success() : $notification->warning();
                $notification->send();
            })
            ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false)
            ->extraAttributes(['class' => 'hidden']);
    }
}
