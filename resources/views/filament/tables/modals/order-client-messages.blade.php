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
    <article data-client-message-id="{{ $message->id }}" style="margin-bottom: 10px; padding: 10px 12px; border: 1px solid {{ $unread ? '#f59e0b' : '#dbe2ea' }}; border-radius: 7px;">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 8px; margin-bottom: 5px; font-size: 12px;">
            <strong>{{ $sender }}</strong>
            <span>{{ optional($message->created_at)->format('d.m.Y H:i') }}</span>
        </div>
        <div style="white-space: pre-wrap; overflow-wrap: anywhere;">{{ $message->comment }}</div>
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
