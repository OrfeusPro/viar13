<x-filament-panels::page>
    @php
        ['conversation' => $conversation, 'messages' => $messages, 'commands' => $commands] = $this->history();
        $snapshot = app(\App\Services\Admin\SaInboxService::class)->readToken($conversation);
    @endphp
    <div wire:poll.10s style="display:grid;gap:20px;min-width:0;">
        <x-filament::section>
            <div style="display:flex;flex-wrap:wrap;gap:20px;align-items:center;overflow-wrap:anywhere;">
                <div><strong>{{ $conversation->client_name ?: 'Без имени' }}</strong><br>{{ $conversation->client_phone }}</div>
                <div>Канал: {{ $conversation->channel }}<br>Бот: {{ ['active' => 'Активен', 'paused' => 'Пауза', 'handoff_to_manager' => 'Менеджер'][$conversation->bot_mode] ?? ($conversation->bot_mode ?: 'Не задан') }}</div>
                <div>Диалог: {{ $conversation->conversation_id }}<br>Заказ: {{ $conversation->orders_id ?: 'Не создан' }}</div>
                <div>
                    <x-filament::badge :color="$conversation->unread_for_manager ? 'warning' : 'success'">{{ $conversation->unread_for_manager ? 'Непрочитано' : 'Прочитано' }}</x-filament::badge>
                    @if($conversation->unread_for_manager && $this->editable())
                        <x-filament::button size="sm" color="gray" style="margin-top:12px;" wire:loading.attr="disabled" x-on:click="$wire.acknowledge({{ \Illuminate\Support\Js::from($snapshot) }})">Отметить прочитанным</x-filament::button>
                    @endif
                </div>
            </div>
        </x-filament::section>
        <x-filament::section heading="История сообщений">
            <div role="log" aria-label="История SA-диалога" style="display:grid;gap:14px;max-height:65vh;overflow:auto;">
                @forelse($messages as $message)
                    @php
                        $from = json_decode((string) $message->from_json, true);
                        $sender = $message->direction === 'inbound' ? 'Клиент' : match(strtolower((string) data_get($from, 'type'))) { 'bot' => 'Бот', 'system' => 'Система', default => 'Менеджер' };
                        $attachments = json_decode((string) $message->attachments_json, true);
                    @endphp
                    <article wire:key="inbox-message-{{ $message->id }}" style="padding:14px;border:1px solid #e2e8f0;border-radius:12px;background:{{ $message->direction === 'inbound' ? '#f8fafc' : '#eff6ff' }};color:#0f172a;overflow-wrap:anywhere;">
                        <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;"><strong>{{ $sender }}</strong><small>{{ optional($message->sent_at ?? $message->created_at)->format('d.m.Y H:i:s') }}</small></div>
                        <div style="white-space:pre-wrap;margin-top:8px;">{{ $message->text }}</div>
                        <small style="color:#64748b;">Статус: {{ $message->status ?: 'Не задан' }}</small>
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
