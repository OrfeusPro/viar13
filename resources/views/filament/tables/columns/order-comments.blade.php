@php
    $record = $getRecord();
    $clientPreviews = $record->order_user_comments->take(-3);
    $painterPreviews = $record->order_painter_comments->take(-3);
    $adminPreviews = $record->adminChats->take(-3);
    $saUnread = $record->saMessages->filter(function ($message): bool {
        $from = json_decode((string) $message->from_json, true) ?: [];
        $fromType = strtolower((string) data_get($from, 'type', ''));

        return ($message->direction === 'inbound' || ($message->direction === 'outbound' && $fromType === 'bot'))
            && $message->status !== 'read';
    })->count();
@endphp

<style>
    .adm-fil-order-comments,
    .adm-fil-order-comments * {
        box-sizing: border-box;
        white-space: normal !important;
        overflow-wrap: anywhere;
    }
</style>
<div class="adm-fil-order-comments" style="width: 245px; min-width: 225px; max-width: 245px; padding: 5px 7px; text-align: center; font-size: 13px; line-height: 1.35; color: #313942; overflow: hidden;">
    @if(filled($record->comment))
        <div>Комментарий заказа:</div>
        <div style="margin-bottom: 7px;">{{ $record->comment }}</div>
    @endif
    @if(filled($record->admin_comment))
        <div style="color: #d33;">Комментарий администратора:</div>
        <div style="margin-bottom: 7px;">{{ $record->admin_comment }}</div>
    @endif
    @if(filled($record->painter_comment))
        <div>Комментарий художника:</div>
        <div style="margin-bottom: 7px;">{{ $record->painter_comment }}</div>
    @endif

    <div style="margin-top: 8px;">Комментарии<br>клиента к картинам:</div>
    @if(filled($record->client_comment))<div>{{ $record->client_comment }}</div>@endif
    @foreach($clientPreviews as $message)
        <div style="margin-top: 4px; font-size: 12px;"><span style="color: #64748b;">{{ optional($message->created_at)->format('d.m.Y') }} —</span> {{ $message->comment }}</div>
    @endforeach

    <div style="margin-top: 8px;">Комментарии<br>художника:</div>
    @foreach($painterPreviews as $message)
        <div style="margin-top: 4px; font-size: 12px;"><span style="color: #64748b;">{{ optional($message->created_at)->format('d.m.Y') }} —</span> {{ $message->comment }}</div>
    @endforeach

    <button type="button" x-on:click.stop="$wire.mountTableAction('viewClientChat', '{{ $record->getKey() }}')" style="display: block; width: 122px; margin: 12px auto 0; padding: 7px 9px; border: 0; border-radius: 3px; background: #22a7e8; color: white; cursor: pointer;">
        Показать чат<br>с клиентом @if($record->client_messages_count)<strong>({{ $record->client_messages_count }})</strong>@endif
        @if($record->unread_client_messages_count)<span style="display: inline-block; margin-left: 3px; padding: 1px 5px; border-radius: 9px; background: #fff; color: #dc2626;">{{ $record->unread_client_messages_count }}</span>@endif
    </button>

    <button type="button" x-on:click.stop="$wire.mountTableAction('manageAdminChat', '{{ $record->getKey() }}')" style="display: block; width: 122px; margin: 12px auto 0; padding: 7px 9px; border: 0; border-radius: 3px; background: #22a7e8; color: white; cursor: pointer;">
        Показать чат<br>для админов @if($record->admin_messages_count)<strong>({{ $record->admin_messages_count }})</strong>@endif
    </button>

    @if($adminPreviews->isNotEmpty())
        <div style="margin-top: 8px; text-align: left;">
            @foreach($adminPreviews as $message)
                <div style="margin-bottom: 5px; padding: 5px 7px; border-radius: 4px; background: #f3f4f6;">
                    <strong style="font-size: 11px;">{{ trim((string) ($message->user?->first_name.' '.$message->user?->last_name)) ?: ($message->user?->email ?: 'Администратор') }}</strong>
                    <div>{{ $message->comment }}</div>
                </div>
            @endforeach
        </div>
    @endif

    <button type="button" x-on:click.stop="$wire.mountTableAction('viewSaChat', '{{ $record->getKey() }}')" style="display: block; min-width: 145px; margin: 12px auto 0; padding: 7px 9px; border: 0; border-radius: 3px; background: #25d366; color: white; cursor: pointer;">
        WhatsApp Чат (SA)
        @if($saUnread)<span style="display: inline-block; margin-left: 4px; padding: 1px 5px; border-radius: 9px; background: #fff; color: #111827;">{{ $saUnread }}</span>@endif
    </button>

    <button type="button" x-on:click.stop="$wire.mountTableAction('viewPainterChat', '{{ $record->getKey() }}')" style="display: block; width: 122px; margin: 12px auto 0; padding: 7px 9px; border: 0; border-radius: 3px; background: #22a7e8; color: white; cursor: pointer;">
        Показать чат<br>с художником @if($record->painter_messages_count)<strong>({{ $record->painter_messages_count }})</strong>@endif
    </button>
</div>
