@php
    $canEdit = auth('filament')->user()?->can('update', $record) ?? false;
@endphp
<div>
    <div style="max-height: 55vh; overflow-y: auto; padding-right: 8px;">
        @forelse($record->orders_chats as $message)
            @php($unread = ! $message->is_admin && ! $message->admin_is_read)
            <article style="margin-bottom: 10px; padding: 10px 12px; border: 1px solid {{ $unread ? '#f59e0b' : '#dbe2ea' }}; border-radius: 6px;">
                <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 8px;">
                    <strong>{{ $message->is_admin ? 'Администратор' : 'Художник' }}@if($message->is_img_sketch) (набросок)@elseif($message->is_img_painter) (картина)@endif</strong>
                    <span style="font-size: 12px; color: #64748b;">{{ optional($message->created_at)->format('Y-m-d H:i:s') }}</span>
                </div>
                <div style="white-space: pre-wrap; overflow-wrap: anywhere; margin: 6px 0;">{{ $message->comment }}</div>
                @if($unread)
                    <span style="font-size: 12px; color: #b45309;">Не прочитано администратором</span>
                    @if($canEdit)
                        <x-filament::button size="xs" color="gray" x-on:click="$wire.mountTableAction('readPainterChatMessage', '{{ $record->getKey() }}', { message_id: {{ (int) $message->id }} })">Отметить прочитанным</x-filament::button>
                    @endif
                @elseif($message->is_admin)
                    <span style="font-size: 12px; color: #64748b;">{{ $message->is_read ? 'Прочитано художником' : 'Ещё не прочитано художником' }}</span>
                @endif
            </article>
        @empty
            <p style="margin-bottom: 12px; color: #64748b;">Сообщений пока нет.</p>
        @endforelse
    </div>
    @if($canEdit)
        <x-filament::button size="sm" x-on:click="$wire.mountTableAction('sendPainterChatMessage', '{{ $record->getKey() }}')">Ответить художнику</x-filament::button>
    @endif
</div>
