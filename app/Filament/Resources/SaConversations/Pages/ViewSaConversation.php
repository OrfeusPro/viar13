<?php

namespace App\Filament\Resources\SaConversations\Pages;

use App\Filament\Resources\Orders\OrdersResource;
use App\Filament\Resources\SaConversations\SaConversationResource;
use App\Models\Orders;
use App\Models\SaMessage;
use App\Services\Admin\SaInboxService;
use App\Services\Admin\OrderSaCommandService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ViewSaConversation extends ViewRecord
{
    protected static string $resource = SaConversationResource::class;
    protected string $view = 'filament.pages.sa-conversation';

    public string $replyText = '';
    public bool $replyHandoff = false;
    #[\Livewire\Attributes\Locked]
    public ?string $replyToken = null;

    public function mount(int|string $record): void
    {
        parent::mount($record);
        if ($this->editable() && $this->getRecord()->channel === 'whatsapp') {
            $this->replyToken = app(OrderSaCommandService::class)->inboxToken($this->getRecord(), auth('filament')->user());
        }
    }

    public function newReply(): void
    {
        SaInboxService::authorize(auth('filament')->user(), 'edit');
        $this->replyToken = app(OrderSaCommandService::class)->inboxToken($this->getRecord()->fresh(), auth('filament')->user());
        $this->replyText = '';
        $this->replyHandoff = false;
        $this->resetValidation();
    }

    public function sendReply(): void
    {
        SaInboxService::authorize(auth('filament')->user(), 'edit');
        $this->replyText = trim($this->replyText);
        $this->validate(['replyText' => 'required|string|max:10000', 'replyHandoff' => 'boolean']);
        $this->command(['action' => 'send', 'token' => $this->replyToken, 'text' => $this->replyText, 'handoff' => $this->replyHandoff], true);
    }

    public function controlBot(string $action, string $token): void
    {
        SaInboxService::authorize(auth('filament')->user(), 'edit');
        validator(compact('action', 'token'), ['action' => 'required|in:pause_bot,resume_bot,handoff_to_manager', 'token' => 'required|string|max:4096'])->validate();
        $this->command(compact('action', 'token'));
    }

    public function getTitle(): string { return 'Диалог: '.($this->getRecord()->client_name ?: $this->getRecord()->conversation_id); }
    public function editable(): bool { return auth('filament')->user()?->hasPermission('edit_orders') ?? false; }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('simulator')->label('SA Simulator')->icon('heroicon-o-beaker')
                ->visible(fn () => \App\Filament\Pages\SaSimulator::canAccess())->url(fn () => \App\Filament\Pages\SaSimulator::getUrl()),
            Action::make('list')->label('К списку')->color('gray')->url(SaConversationResource::getUrl()),
            Action::make('order')->label('Открыть заказ')->visible(fn () => (bool) $this->getRecord()->orders_id)
                ->url(fn () => OrdersResource::getUrl('edit', ['record' => $this->getRecord()->orders_id])),
            Action::make('bind')->label('Привязать заказ')->icon('heroicon-o-link')->visible(fn () => $this->editable() && ! $this->getRecord()->orders_id)
                ->schema([TextInput::make('order_id')->label('ID заказа')->required()->integer()->minValue(1)->exists('orders', 'id')])
                ->action(function ($data): void {
                    $this->run(fn () => app(SaInboxService::class)->bind($this->getRecord(), auth('filament')->user(), (int) $data['order_id']));
                }),
            Action::make('createOrder')->label('Создать заказ')->icon('heroicon-o-plus')->requiresConfirmation()
                ->modalDescription('Создаст заказ для клиента диалога и перенесёт историю. Товары и параметры заказа можно заполнить после создания.')
                ->visible(fn () => $this->editable() && ! $this->getRecord()->orders_id && Gate::forUser(auth('filament')->user())->allows('create', Orders::class))
                ->action(function (): void {
                    $this->run(function () {
                        $order = app(SaInboxService::class)->createOrder($this->getRecord(), auth('filament')->user());
                        $this->redirect(OrdersResource::getUrl('edit', ['record' => $order->id]));
                    });
                }),
            Action::make('reply')->label('Ответить')->icon('heroicon-o-paper-airplane')->iconButton()->tooltip('Ответить в отдельном окне')->visible(fn () => $this->editable() && $this->getRecord()->channel === 'whatsapp')
                ->schema([\Filament\Forms\Components\Hidden::make('token')->required(), Textarea::make('text')->label('Сообщение')->required()->maxLength(10000), Toggle::make('handoff')->label('Передать менеджеру после ответа')])
                ->fillForm(fn () => ['token' => app(OrderSaCommandService::class)->inboxToken($this->getRecord(), auth('filament')->user())])
                ->action(function (array $data, Action $action): void {
                    if (! in_array($this->command($data + ['action' => 'send']), ['accepted', 'uat_suppressed'], true)) {
                        $action->halt();
                    }
                }),
            Action::make('bot')->label('Управление ботом')->icon('heroicon-o-cpu-chip')->iconButton()->tooltip('Управление ботом в отдельном окне')->visible(fn () => $this->editable() && $this->getRecord()->channel === 'whatsapp')
                ->schema([\Filament\Forms\Components\Hidden::make('token')->required(), \Filament\Forms\Components\Select::make('action')->label('Команда')->required()
                    ->options(['pause_bot' => 'Пауза', 'resume_bot' => 'Включить', 'handoff_to_manager' => 'Передать менеджеру'])])
                ->fillForm(fn () => ['token' => app(OrderSaCommandService::class)->inboxToken($this->getRecord(), auth('filament')->user())])
                ->action(function (array $data, Action $action): void {
                    if (! in_array($this->command($data), ['accepted', 'uat_suppressed'], true)) {
                        $action->halt();
                    }
                }),
        ];
    }

    private function command(array $data, bool $fromComposer = false): ?string
    {
        $status = null;
        $this->run(function () use ($data, $fromComposer, &$status) {
            $result = app(OrderSaCommandService::class)->executeInbox($this->getRecord(), auth('filament')->user(), $data);
            $status = $result['status'];
            Notification::make()->title(match ($result['status']) {
                'accepted' => 'Запрос принят сервисом SA', 'uat_suppressed' => 'Тестовая команда: внешняя отправка отключена',
                'uncertain' => 'Результат отправки не подтверждён. Проверьте историю перед повтором.',
                'partial' => 'Сообщение принято, команда бота не подтверждена',
                'state_conflict' => 'Диалог изменился во время выполнения команды',
                default => 'Результат команды: '.$result['status'],
            })->color(in_array($result['status'], ['accepted', 'uat_suppressed'], true) ? 'success' : 'warning')->send();
            // Keep the same token and draft for uncertain results: retries must remain idempotent.
            if ($fromComposer && in_array($result['status'], ['accepted', 'uat_suppressed'], true)) {
                $this->newReply();
            }
        }, false);
        return $status;
    }

    private function run(callable $operation, bool $notify = true): void
    {
        SaInboxService::authorize(auth('filament')->user(), 'edit');
        try {
            $operation();
            $this->record = $this->getRecord()->fresh();
            if ($notify) { Notification::make()->success()->title('Выполнено')->send(); }
        } catch (ValidationException $exception) {
            Notification::make()->warning()->title('Не выполнено')->body(collect($exception->errors())->flatten()->first())->send();
        }
    }

    public function acknowledge(string $snapshot): void
    {
        validator(compact('snapshot'), ['snapshot' => 'required|string|max:4096'])->validate();
        $this->run(fn () => app(SaInboxService::class)->acknowledge($this->getRecord(), auth('filament')->user(), $snapshot));
    }

    public function history(): array
    {
        SaInboxService::authorize(auth('filament')->user());
        $this->record = $this->getRecord()->fresh();
        return ['conversation' => $this->getRecord(), 'messages' => SaMessage::where('conversation_id', $this->getRecord()->conversation_id)
            ->orderByRaw('COALESCE(sent_at, created_at) asc')->orderBy('id')->get(),
            'commands' => \App\Models\SaEvent::where('dedupe_key', 'like', 'filament-sa:%:'.$this->getRecord()->id.':%')->latest('id')->limit(20)->get()];
    }
}
