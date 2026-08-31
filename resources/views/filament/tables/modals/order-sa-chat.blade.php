@php
    $canEdit = auth('filament')->user()?->can('update', $record) ?? false;
    $groups = $record->saMessages->groupBy('conversation_id');
    $conversations = $record->saConversations->keyBy('conversation_id');
    $ids = $groups->keys()->merge($conversations->keys())->unique();
    $statuses = ['sent' => 'Отправлено', 'delivered' => 'Доставлено', 'read' => 'Прочитано', 'failed' => 'Ошибка', 'received' => 'Получено',
        'pending' => 'Передаётся интеграции', 'queued' => 'Принято интеграцией, доставка не подтверждена',
        'uat_suppressed' => 'Тест UAT — не отправлено', 'delivery_unknown' => 'Результат отправки неизвестен'];
@endphp
<div wire:poll.5s.visible x-data x-on:order-chat-updated.window="if ($event.detail.orderId === {{ (int) $record->id }}) $wire.$refresh()" style="max-height: 65vh; overflow-y: auto; padding-right: 8px;">
    <p class="adm-sa-meta" style="margin-bottom: 8px;">Автообновление: каждые 5 секунд, пока чат виден. Обновлено: {{ $refreshedAt }}</p>
    @forelse($ids as $conversationId)
        @php
            $conversation = $conversations->get($conversationId);
            $messages = $groups->get($conversationId, collect());
        @endphp
        <section class="adm-sa-conversation {{ ! $canEdit || ! $conversation ? 'adm-sa-conversation--readonly' : '' }}" wire:key="sa-conversation-{{ $record->id }}-{{ md5((string) $conversationId) }}">
            <div class="adm-sa-context">
                <div>Режим бота SA: <strong class="adm-sa-mode--{{ $conversation?->bot_mode }}">{{ strtoupper((string) ($conversation?->bot_mode ?: 'n/a')) }}</strong></div>
                <div class="adm-sa-meta">Диалог: {{ $conversationId ?: 'Без привязки' }}</div>
                @if($conversation?->unread_for_manager)
                    <div style="color: #b45309; margin: 6px 0;">Не прочитан менеджером</div>
                    @if($canEdit)
                        @php
                            $snapshot = app(\App\Services\Admin\OrderSaChatService::class)->readToken($record, $conversation);
                        @endphp
                        <x-filament::button type="button" size="xs" color="gray" x-on:click="$wire.acknowledge({{ \Illuminate\Support\Js::from($snapshot) }})" wire:loading.attr="disabled">Отметить диалог прочитанным</x-filament::button>
                    @endif
                @elseif($conversation)
                    <div style="font-size: 12px; color: #64748b;">Прочитан менеджером</div>
                @else
                    <div style="font-size: 12px; color: #b45309;">Нет подтверждённой привязки диалога к этому заказу. История сохранена.</div>
                @endif
            </div>
            <h3 class="adm-chat-title adm-sa-title">Чат WhatsApp (SA Интеграция)</h3>
            <div class="adm-chat-scroll adm-sa-history" role="region" aria-label="История WhatsApp {{ $conversationId }}" tabindex="0">
            @forelse($messages as $message)
                @php
                    $from = json_decode((string) $message->from_json, true);
                    $sender = $message->direction === 'inbound' ? 'Клиент' : match(strtolower((string) data_get($from, 'type', ''))) {
                        'bot' => 'Бот', 'system' => 'Система', default => 'Менеджер',
                    };
                    $attachments = json_decode((string) $message->attachments_json, true);
                @endphp
                <article wire:key="sa-message-{{ $message->id }}" class="adm-chat-message">
                    <div class="adm-chat-message-head">
                        <strong>{{ $sender }}</strong>
                        <span class="adm-chat-message-date">{{ optional($message->sent_at ?? $message->created_at)->format('d.m.Y H:i:s') }}</span>
                    </div>
                    <div class="adm-chat-message-text">{{ $message->text }}</div>
                    @if($message->status)
                        <div style="font-size: 12px; color: {{ $message->status === 'failed' ? '#dc2626' : '#64748b' }};">Статус WhatsApp: {{ $statuses[$message->status] ?? $message->status }}</div>
                    @endif
                    @foreach(is_array($attachments) ? $attachments : [] as $attachment)
                        @php
                            $file = \App\Support\Admin\SaChatAttachment::describe($attachment);
                        @endphp
                        @if($file['url'])
                            <a href="{{ $file['url'] }}" target="_blank" rel="noopener noreferrer" style="display: inline-block; margin: 8px 8px 0 0; color: #2563eb; overflow-wrap: anywhere;">
                                @if($file['image'])
                                    <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}" loading="lazy" referrerpolicy="no-referrer" style="max-width: 200px; max-height: 180px; object-fit: contain;" x-on:error.once="$el.src = @js(order_image_placeholder())">
                                @endif
                                <span>{{ $file['name'] }}</span>
                            </a>
                        @else
                            <div style="font-size: 12px; color: #b45309;">{{ $file['name'] }} — файл недоступен или отклонён.</div>
                        @endif
                    @endforeach
                </article>
            @empty
                <p>Нет истории сообщений WhatsApp.</p>
            @endforelse
            </div>
            @if($conversation && $canEdit)
                @livewire('admin.order-chat-composer', ['orderId' => (int) $record->id, 'stream' => 'sa', 'conversationId' => (int) $conversation->id], key('sa-composer-'.$record->id.'-'.$conversation->id))
            @endif
        </section>
    @empty
        <p>Нет истории сообщений WhatsApp.</p>
    @endforelse
    @if(isset($commands) && $commands->isNotEmpty())
        <details class="adm-sa-log"><summary>Последние команды SA</summary>
        @foreach($commands as $command)
            <p style="font-size: 12px; overflow-wrap: anywhere;">{{ $command->created_at }} · {{ $command->event_type }} · {{ $command->status }} · {{ $command->event_id }}</p>
        @endforeach
        </details>
    @endif
    <p style="font-size: 12px; color: #64748b;">Прочтение менеджером не меняет статусы доставки WhatsApp. Команды UAT не отправляются; повтор той же команды не создаёт повторную отправку.</p>
</div>
