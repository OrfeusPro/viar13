<?php

namespace App\Livewire\Admin;

use App\Models\Orders;
use App\Models\User;
use App\Services\Admin\OrderClientChatService;
use App\Services\Admin\OrderPainterChatService;
use App\Services\Admin\OrderSaCommandService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class OrderChatComposer extends Component
{
    #[Locked]
    public int $orderId;

    #[Locked]
    public string $stream;

    #[Locked]
    public string $threadType = 'general';

    #[Locked]
    public ?int $imageId = null;

    #[Locked]
    public ?int $conversationId = null;

    #[Locked]
    public array $commandTokens = [];

    #[Locked]
    public bool $requiresReview = false;

    public string $text = '';

    public bool $handoff = false;

    public function mount(int $orderId, string $stream, string $threadType = 'general', ?int $imageId = null, ?int $conversationId = null): void
    {
        $this->orderId = $orderId;
        $this->stream = $stream;
        $this->threadType = $threadType;
        $this->imageId = $imageId;
        $this->conversationId = $conversationId;
        validator(compact('stream', 'threadType'), [
            'stream' => ['required', Rule::in(['client', 'painter', 'sa'])],
            'threadType' => ['required', Rule::in(['general', 'sketch', 'painter'])],
        ])->validate();
        $order = $this->authorizedOrder();
        if ($stream === 'client' && $threadType !== 'general') {
            $flag = $threadType === 'sketch' ? 'is_img_sketch' : 'is_img_painter';
            abort_unless($order->order_painter_images()->whereKey($imageId)->where($flag, 1)->exists(), 403);
        }
        if ($stream === 'sa') {
            abort_unless($conversationId !== null, 403);
            $this->renewCommandTokens($order);
        }
    }

    public function send(): void
    {
        $order = $this->authorizedOrder();
        $this->resetErrorBag();
        $this->text = trim($this->text);
        $this->validate(['text' => ['required', 'string', 'max:10000']], [], ['text' => 'Сообщение']);
        if ($this->stream === 'sa') {
            $this->executeSa($order, 'send');

            return;
        }
        try {
            $result = $this->stream === 'client'
                ? app(OrderClientChatService::class)->send($order, $this->author(), [
                    'comment' => $this->text, 'thread_type' => $this->threadType, 'image_id' => $this->imageId,
                ])
                : app(OrderPainterChatService::class)->send($order, $this->author(), ['comment' => $this->text]);
        } catch (ValidationException $exception) {
            $this->addError('command', collect($exception->errors())->flatten()->first());

            return;
        }
        $notification = Notification::make()->title('Сообщение сохранено');
        if ($result['notificationsSuppressed']) {
            $notification->success()->body('Email и CRM webhook не отправлялись.');
        } elseif (! $result['mailSent'] || ! $result['webhookSent']) {
            $notification->warning()->body('Часть уведомлений не доставлена. Текст сохранён — не отправляйте повторно.');
        } else {
            $notification->success();
        }
        $notification->send();
        $this->text = '';
        $this->dispatch('order-chat-updated', orderId: $this->orderId);
    }

    public function bot(string $action): void
    {
        $order = $this->authorizedOrder();
        abort_unless($this->stream === 'sa', 403);
        validator(compact('action'), ['action' => ['required', Rule::in(['pause_bot', 'resume_bot', 'handoff_to_manager'])]])->validate();
        $this->resetErrorBag();
        $this->executeSa($order, $action);
    }

    private function executeSa(Orders $order, string $action): void
    {
        if ($this->requiresReview) {
            $this->addError('command', 'Сначала проверьте результат предыдущей команды в журнале. Повторная отправка заблокирована.');

            return;
        }
        try {
            $result = app(OrderSaCommandService::class)->execute($order, $this->author(), [
                'token' => $this->commandTokens[$action], 'action' => $action,
                'text' => $action === 'send' ? $this->text : null, 'handoff' => $action === 'send' && $this->handoff,
            ]);
        } catch (ValidationException $exception) {
            $this->addError('command', collect($exception->errors())->flatten()->first());

            return;
        }
        $safeToContinue = in_array($result['status'], ['accepted', 'uat_suppressed'], true);
        $description = match ($result['status']) {
            'uat_suppressed' => 'Тест сохранён. Ничего не отправлено; кабинет клиента и режим бота не изменены.',
            'accepted' => 'Интеграция приняла команду. Это ещё не подтверждение доставки WhatsApp.',
            'partial' => 'Сообщение принято, но Handoff не подтверждён. Не отправляйте текст повторно.',
            'state_conflict' => 'Связь или режим диалога изменились. Проверьте результат команды, не повторяйте отправку.',
            default => 'Результат не подтверждён. Проверьте журнал перед новой командой; автоматического повтора нет.',
        };
        $notification = Notification::make()->title($result['duplicate'] ? 'Повтор не отправлен' : 'Команда зарегистрирована')->body($description);
        $safeToContinue ? $notification->success() : $notification->warning();
        $notification->send();
        if ($safeToContinue) {
            if ($action === 'send') {
                $this->text = '';
                $this->handoff = false;
            }
            $this->renewCommandTokens($order);
        } else {
            $this->requiresReview = true;
            $this->addError('command', $description);
        }
        $this->dispatch('order-chat-updated', orderId: $this->orderId);
    }

    private function renewCommandTokens(Orders $order): void
    {
        foreach (['send', 'pause_bot', 'resume_bot', 'handoff_to_manager'] as $action) {
            $this->commandTokens[$action] = app(OrderSaCommandService::class)->token($order, $this->author(), $this->conversationId);
        }
    }

    private function author(): User
    {
        $author = auth('filament')->user();
        abort_unless($author && $author->canAccessPanel(Filament::getPanel('admin')), 403);

        return $author;
    }

    private function authorizedOrder(): Orders
    {
        $order = Orders::query()->findOrFail($this->orderId);
        Gate::forUser($this->author())->authorize('update', $order);

        return $order;
    }

    public function render()
    {
        $order = $this->authorizedOrder();

        return view('filament.tables.modals.order-chat-composer', ['order' => $order]);
    }
}
