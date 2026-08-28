@php
    $messages = match ($stream) {
        'client' => $record->order_user_comments,
        'admin' => $record->adminChats,
        'sa' => $record->saMessages,
        'painter' => $record->orders_chats,
        default => collect(),
    };
@endphp

<div style="max-height: 55vh; overflow-y: auto; padding-right: 6px;">
    @if($stream === 'sa')
        <div style="margin-bottom: 12px; padding: 9px 12px; border-radius: 6px; background: #f3f4f6;">
            Режим бота SA: <strong>{{ strtoupper((string) ($record->sa_bot_mode ?: 'n/a')) }}</strong>
        </div>
    @endif

    @forelse($messages as $message)
        @php
            $sender = match ($stream) {
                'client' => $message->is_admin ? 'Администратор' : 'Клиент',
                'admin' => trim((string) ($message->user?->first_name.' '.$message->user?->last_name)) ?: ($message->user?->email ?: 'Администратор'),
                'painter' => $message->is_admin ? 'Администратор' : 'Художник',
                'sa' => $message->direction === 'inbound'
                    ? 'Клиент'
                    : match (strtolower((string) data_get(json_decode((string) $message->from_json, true) ?: [], 'type'))) {
                        'bot' => 'Бот',
                        'system' => 'Система',
                        default => 'Менеджер',
                    },
                default => 'Сообщение',
            };
            $text = $stream === 'sa' ? $message->text : $message->comment;
            $kind = ! empty($message->is_img_sketch) ? 'набросок' : (! empty($message->is_img_painter) ? 'картина' : null);
        @endphp
        <div style="margin-bottom: 10px; padding: 10px 12px; border: 1px solid #dbe2ea; border-radius: 7px; background: {{ ($stream !== 'sa' && ! empty($message->is_admin)) ? '#eff6ff' : '#fff' }};">
            <div style="display: flex; justify-content: space-between; gap: 12px; margin-bottom: 5px; color: #596579; font-size: 12px;">
                <strong>{{ $sender }}@if($kind) ({{ $kind }})@endif</strong>
                <span>{{ optional($message->sent_at ?? $message->created_at)->format('d.m.Y H:i') }}</span>
            </div>
            <div style="white-space: pre-wrap; overflow-wrap: anywhere; color: #273142;">{{ $text }}</div>
            @if($stream === 'sa' && filled($message->status))
                <div style="margin-top: 5px; color: #64748b; font-size: 11px;">Статус: {{ $message->status }}</div>
            @endif
            @if($stream === 'sa' && filled($message->attachments_json))
                @foreach(json_decode((string) $message->attachments_json, true) ?: [] as $attachment)
                    @if(filled($attachment['path'] ?? null))
                        <a target="_blank" rel="noopener" href="{{ Storage::url($attachment['path']) }}" style="display: inline-block; margin-top: 6px; color: #2563eb; text-decoration: underline;">Вложение {{ $loop->iteration }}</a>
                    @endif
                @endforeach
            @endif
        </div>
    @empty
        <div style="padding: 18px; text-align: center; color: #6b7280;">Сообщений нет</div>
    @endforelse

    @if($stream !== 'admin')
        <div style="margin-top: 10px; color: #6b7280; font-size: 12px;">
            На этом этапе поток доступен только для просмотра. Отправка будет подключена отдельным проверяемым действием без legacy AJAX.
        </div>
    @endif
</div>
