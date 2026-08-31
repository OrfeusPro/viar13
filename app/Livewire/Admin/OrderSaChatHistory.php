<?php

namespace App\Livewire\Admin;

use App\Models\Orders;
use App\Models\SaEvent;
use App\Models\User;
use App\Services\Admin\OrderSaChatService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Isolate]
class OrderSaChatHistory extends Component
{
    #[Locked]
    public int $orderId;

    public function mount(int $orderId): void
    {
        $this->orderId = $orderId;
        $this->authorizedOrder();
    }

    public function acknowledge(string $snapshot): void
    {
        $order = $this->authorizedOrder();
        Gate::forUser($this->author())->authorize('update', $order);
        try {
            validator(compact('snapshot'), ['snapshot' => ['required', 'string', 'max:4096']])->validate();
            app(OrderSaChatService::class)->markAsRead($order, $this->author(), $snapshot);
            Notification::make()->success()->title('Диалог отмечен прочитанным')->send();
            $this->dispatch('order-sa-read', orderId: $this->orderId);
        } catch (ValidationException $exception) {
            Notification::make()->warning()->title('Прочтение не изменено')
                ->body(collect($exception->errors())->flatten()->first())->send();
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
        Gate::forUser($this->author())->authorize('view', $order);

        return $order;
    }

    public function render()
    {
        $record = $this->authorizedOrder();
        $record->load(['saConversations', 'saMessages' => fn ($query) => $query
            ->orderByRaw('COALESCE(sent_at, created_at) asc')->orderBy('id')]);

        return view('filament.tables.modals.order-sa-chat', [
            'record' => $record,
            'commands' => SaEvent::query()->where('dedupe_key', 'like', 'filament-sa:'.$record->id.':%')->latest('id')->limit(10)->get(),
            'refreshedAt' => now()->format('H:i:s'),
        ]);
    }
}
