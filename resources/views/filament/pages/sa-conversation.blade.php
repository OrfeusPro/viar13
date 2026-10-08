<x-filament-panels::page>
    @php
        ['conversation' => $conversation, 'messages' => $messages, 'commands' => $commands] = $this->history();
        $snapshot = app(\App\Services\Admin\SaInboxService::class)->readToken($conversation, (int) $messages->max('id'));
        $lastMessageAt = $conversation->last_message_at ?? $messages->last()?->sent_at ?? $messages->last()?->created_at;
    @endphp
    <div wire:poll.10s style="display:grid;gap:20px;min-width:0;">
        <x-filament::section>
            <div style="display:flex;flex-wrap:wrap;gap:20px;align-items:center;overflow-wrap:anywhere;">
                <div><strong>{{ $conversation->client_name ?: 'Без имени' }}</strong><br>{{ $conversation->client_phone }}</div>
                <div>Канал: {{ $conversation->channel }}<br>Бот: {{ ['active' => 'Активен', 'paused' => 'Пауза', 'handoff_to_manager' => 'Менеджер'][$conversation->bot_mode] ?? ($conversation->bot_mode ?: 'Не задан') }}</div>
                <div>Последнее сообщение:<br>{{ optional($lastMessageAt)->format('d.m.Y H:i:s') ?: 'Нет сообщений' }}</div>
                <div>Диалог: {{ $conversation->conversation_id }}<br>Заказ:
                    @if($conversation->orders_id)
                        <a href="{{ \App\Filament\Resources\Orders\OrdersResource::getUrl('edit', ['record' => $conversation->orders_id]) }}" style="color:#2563eb;text-decoration:underline;">#{{ $conversation->orders_id }}</a>
                    @else Не создан @endif
                </div>
                <div>
                    <x-filament::badge :color="$conversation->unread_for_manager ? 'warning' : 'success'">{{ $conversation->unread_for_manager ? 'Непрочитано' : 'Прочитано' }}</x-filament::badge>
                    @if($conversation->unread_for_manager && $this->editable())
                        <x-filament::button size="sm" color="gray" style="margin-top:12px;" wire:loading.attr="disabled" x-on:click="$wire.acknowledge({{ \Illuminate\Support\Js::from($snapshot) }})">Отметить прочитанным</x-filament::button>
                    @endif
                </div>
            </div>
            @if($this->editable() && $conversation->channel === 'whatsapp')
                @php
                    $botToken = app(\App\Services\Admin\OrderSaCommandService::class)->inboxToken($conversation, auth('filament')->user());
                @endphp
                <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid #e2e8f0;" aria-label="Управление ботом">
                    @foreach(['resume_bot' => ['Включить бота', 'success', 'heroicon-o-play'], 'pause_bot' => ['Пауза', 'warning', 'heroicon-o-pause'], 'handoff_to_manager' => ['Передать менеджеру', 'danger', 'heroicon-o-user']] as $action => [$label, $color, $icon])
                        <x-filament::button size="sm" :color="$color" :icon="$icon" wire:loading.attr="disabled" wire:target="controlBot" x-on:click="if (window.confirm('Изменить режим бота для этого диалога?')) $wire.controlBot({{ \Illuminate\Support\Js::from($action) }}, {{ \Illuminate\Support\Js::from($botToken) }})">{{ $label }}</x-filament::button>
                    @endforeach
                </div>
            @endif
        </x-filament::section>
        <x-filament::section heading="История сообщений">
            <div role="log" aria-label="История SA-диалога" style="display:grid;gap:14px;max-height:65vh;overflow:auto;">
                @forelse($messages as $message)
                    @php
                        $from = json_decode((string) $message->from_json, true);
                        $sender = $message->direction === 'inbound' ? 'Клиент' : match(strtolower((string) data_get($from, 'type'))) { 'bot' => 'Бот', 'system' => 'Система', default => 'Менеджер' };
                        $attachments = json_decode((string) $message->attachments_json, true);
                    @endphp
                    <article wire:key="inbox-message-{{ $message->id }}" class="viar-sa-message {{ $message->direction === 'inbound' ? 'is-inbound' : 'is-outbound' }}">
                        <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;"><strong>{{ $sender }}</strong><small>{{ optional($message->sent_at ?? $message->created_at)->format('d.m.Y H:i:s') }}</small></div>
                        <div style="white-space:pre-wrap;margin-top:8px;">{{ $message->text ?: '[пустое сообщение]' }}</div>
                        <small style="color:#64748b;">Статус: {{ $message->status ?: 'Не задан' }}</small>
                        @if($message->orders_id)
                            <small> · заказ #{{ $message->orders_id }}</small>
                        @endif
                        @foreach(is_array($attachments) ? $attachments : [] as $attachment)
                            @php($file = \App\Support\Admin\SaChatAttachment::describe($attachment))
                            @if($file['url'])
                                <a href="{{ $file['url'] }}" target="_blank" rel="noopener noreferrer" style="display:inline-block;margin:10px;color:#2563eb;">
                                    @if($file['image'])<img src="{{ $file['url'] }}" alt="{{ $file['name'] }}" loading="lazy" style="max-width:180px;max-height:150px;object-fit:contain;">@endif
                                    <span>{{ $file['name'] }}</span>
                                </a>
                            @else
                                <p>{{ $file['name'] }} — файл недоступен или отклонён.</p>
                            @endif
                        @endforeach
                    </article>
                @empty
                    <p>В диалоге пока нет сообщений.</p>
                @endforelse
            </div>
            @if($this->editable() && $conversation->channel === 'whatsapp')
                <form wire:submit="sendReply" class="viar-sa-reply">
                    <label for="sa-reply-text"><strong>Ответ менеджера</strong></label>
                    <textarea id="sa-reply-text" wire:model="replyText" maxlength="10000" placeholder="Введите сообщение…" required></textarea>
                    @error('replyText')<p role="alert" style="color:#dc2626;">{{ $message }}</p>@enderror
                    <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" wire:model="replyHandoff"> Передать менеджеру после ответа</label>
                    <div class="viar-sa-reply-actions">
                        <x-filament::button type="submit" icon="heroicon-o-paper-airplane" wire:loading.attr="disabled" wire:target="sendReply,newReply">Отправить сообщение</x-filament::button>
                        <x-filament::button type="button" color="gray" wire:click="newReply" wire:loading.attr="disabled" wire:target="sendReply,newReply">Начать новый ответ</x-filament::button>
                    </div>
                    <small style="color:#64748b;">Если отправка не подтверждена, проверьте историю перед новым ответом. Обновление истории сохраняет черновик.</small>
                </form>
            @endif
        </x-filament::section>
        @if($commands->isNotEmpty())
            <x-filament::section heading="Последние команды SA" collapsible collapsed>
                @foreach($commands as $command)
                    <p style="font-size:13px;overflow-wrap:anywhere;">{{ $command->created_at }} · {{ $command->event_type }} · {{ $command->status }} · {{ $command->event_id }}</p>
                @endforeach
            </x-filament::section>
        @endif
        <p style="font-size:13px;color:#64748b;">История обновляется каждые 10 секунд. Открытие страницы не отмечает диалог прочитанным. Ответы и управление ботом доступны для WhatsApp.</p>
        @if(!config('admin_migration.sa_commands_enabled', false))
            <p style="font-size:13px;color:#b45309;">Внешняя отправка отключена. Сообщения и команды сохраняются как тестовые и не отправляются клиенту.</p>
        @endif
    </div>
</x-filament-panels::page>
