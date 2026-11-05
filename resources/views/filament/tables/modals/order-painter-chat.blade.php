@php
    $canEdit = auth('filament')->user()?->can('update', $record) ?? false;
@endphp
<div x-data x-on:order-chat-updated.window="if ($event.detail.orderId === {{ (int) $record->id }}) $wire.$refresh()">
    <div class="adm-chat-compact-history">
        @forelse($record->orders_chats as $message)
            @php($unread = ! $message->is_admin && ! $message->admin_is_read)
            <article class="adm-chat-message {{ $unread ? 'is-unread' : '' }}">
                <div class="adm-chat-message-head">
                    <strong>{{ $message->is_admin ? 'Администратор' : 'Художник' }}@if($message->is_img_sketch) (набросок)@elseif($message->is_img_painter) (картина)@endif</strong>
                    <span style="font-size: 12px; color: #64748b;">{{ optional($message->created_at)->format('Y-m-d H:i:s') }}</span>
                </div>
                <div class="adm-chat-message-text"
                    @if($unread && $canEdit)
                        role="button" tabindex="0" aria-label="Прочитать сообщение художника №{{ $message->id }}"
                        x-on:click="$wire.mountTableAction('readPainterChatMessage', '{{ $record->getKey() }}', { message_id: {{ (int) $message->id }} })"
                        x-on:keydown.enter.prevent="$wire.mountTableAction('readPainterChatMessage', '{{ $record->getKey() }}', { message_id: {{ (int) $message->id }} })"
                        x-on:keydown.space.prevent="$wire.mountTableAction('readPainterChatMessage', '{{ $record->getKey() }}', { message_id: {{ (int) $message->id }} })"
                    @endif
                >{{ $message->comment }}</div>
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
        @livewire('admin.order-chat-composer', ['orderId' => (int) $record->id, 'stream' => 'painter'], key('painter-general-'.$record->id))
    @endif
</div>
