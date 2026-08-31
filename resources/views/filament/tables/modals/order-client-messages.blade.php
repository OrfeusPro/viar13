@forelse($messages as $message)
    @php
        $author = $message->author;
        $sender = match (true) {
            (int) $message->user_id === (int) auth('filament')->id() => 'Вы',
            (int) $author?->role_id === 3 => 'Художник',
            (bool) $message->is_admin => 'Администратор',
            default => 'Клиент',
        };
        $unread = ! $message->is_admin && ! $message->admin_is_read;
    @endphp
    <article data-client-message-id="{{ $message->id }}" class="adm-chat-message {{ $unread ? 'is-unread' : '' }}">
        <div class="adm-chat-message-head">
            <strong>{{ $sender }}</strong>
            <span class="adm-chat-message-date">{{ optional($message->created_at)->format('Y-m-d H:i:s') }}</span>
        </div>
        <div class="adm-chat-message-text"
            @if($unread && $canEdit)
                role="button" tabindex="0" aria-label="Прочитать сообщение №{{ $message->id }}"
                x-on:click="if (!$event.target.closest('a, button')) $wire.mountTableAction('readClientChatMessage', '{{ $record->getKey() }}', { message_id: {{ (int) $message->id }} })"
                x-on:keydown.enter.self.prevent="$wire.mountTableAction('readClientChatMessage', '{{ $record->getKey() }}', { message_id: {{ (int) $message->id }} })"
                x-on:keydown.space.self.prevent="$wire.mountTableAction('readClientChatMessage', '{{ $record->getKey() }}', { message_id: {{ (int) $message->id }} })"
            @endif
        >{{ \App\Support\Admin\ChatMessageText::render($message->comment) }}</div>
        @if($message->is_admin)
            <div style="margin-top: 5px; font-size: 12px;">{{ $message->is_read ? 'Прочитано клиентом' : 'Ещё не прочитано клиентом' }}</div>
        @elseif($unread)
            @if($canEdit)
                <x-filament::button size="xs" color="warning" style="margin-top: 6px;" x-on:click="$wire.mountTableAction('readClientChatMessage', '{{ $record->getKey() }}', { message_id: {{ (int) $message->id }} })">Отметить прочитанным</x-filament::button>
            @else
                <span style="color: #b45309;">Не прочитано администратором</span>
            @endif
        @else
            <div style="margin-top: 5px; font-size: 12px; color: #64748b;">Прочитано администратором</div>
        @endif
    </article>
@empty
    <p style="padding: 10px 0; color: #64748b;">Сообщений нет</p>
@endforelse
