@extends('voyager::master')

@section('page_title', 'SA Conversation')

@section('content')
<div class="page-content container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-bordered">
                <div class="panel-heading" style="display:flex; justify-content:space-between; align-items:center;">
                    <h3 class="panel-title">
                        <i class="voyager-chat"></i> Диалог {{ $conversation->conversation_id }}
                    </h3>
                    <div>
                        <a href="{{ route('admin.sa.simulator') }}" class="btn btn-default btn-sm">SA Simulator</a>
                        <a href="{{ route('admin.sa.conversations.index') }}" class="btn btn-default btn-sm">Назад к списку</a>
                        @if($order)
                            <a href="{{ route('edit_admin_order', ['id' => $order->id]) }}" class="btn btn-primary btn-sm" target="_blank">Открыть заказ #{{ $order->id }}</a>
                        @endif
                    </div>
                </div>
                <div class="panel-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="row" style="margin-bottom:20px;">
                        <div class="col-md-4">
                            <div><strong>Клиент:</strong> {{ $conversation->client_name ?: 'Без имени' }}</div>
                            <div><strong>Телефон:</strong> {{ $conversation->client_phone ?: '-' }}</div>
                            <div><strong>Канал:</strong> {{ $conversation->channel ?: '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div><strong>Последнее сообщение:</strong> {{ optional($conversation->last_message_at)->format('Y-m-d H:i:s') ?: '-' }}</div>
                            <div>
                                <strong>Режим бота:</strong>
                                <span id="sa_conversation_bot_mode_label">{{ $conversation->bot_mode ?: '-' }}</span>
                            </div>
                            <div><strong>Статус:</strong>
                                @if(isset($conversation->unread_for_manager) && (int) $conversation->unread_for_manager === 1)
                                    <span class="label label-danger">Непрочитано</span>
                                @else
                                    <span class="label label-success">Прочитано</span>
                                @endif
                            </div>
                            <div><strong>Заказ:</strong>
                                @if($order)
                                    <a href="{{ route('edit_admin_order', ['id' => $order->id]) }}" target="_blank">#{{ $order->id }}</a>
                                @else
                                    <span class="label label-warning">Еще не создан</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <div class="btn-group" style="display:inline-flex; gap:6px; flex-wrap:wrap; justify-content:flex-end;">
                                <button type="button" class="btn btn-success js-sa-conversation-bot-control" data-action="resume_bot">Включить бота</button>
                                <button type="button" class="btn btn-warning js-sa-conversation-bot-control" data-action="pause_bot">Пауза</button>
                                <button type="button" class="btn btn-danger js-sa-conversation-bot-control" data-action="handoff_to_manager">Передать менеджеру</button>
                            </div>
                            @if(!$order)
                                <form method="POST" action="{{ route('admin.sa.conversations.create_order', ['conversation' => $conversation->conversation_id]) }}" style="display:inline-block; margin-top:8px;">
                                    @csrf
                                    <button type="submit" class="btn btn-success">Создать заказ из диалога</button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div class="row" style="margin-bottom:20px;">
                        <div class="col-md-12">
                            <div class="panel panel-default" style="margin-bottom:0;">
                                <div class="panel-heading"><strong>Привязать к существующему заказу</strong></div>
                                <div class="panel-body">
                                    <form method="POST" action="{{ route('admin.sa.conversations.bind_order', ['conversation' => $conversation->conversation_id]) }}" class="row" style="display:flex; flex-wrap:wrap; align-items:flex-end;">
                                        @csrf
                                        <div class="col-md-4">
                                            <label for="bind_order_id" style="display:block; margin-bottom:6px;">ID заказа</label>
                                            <input
                                                id="bind_order_id"
                                                type="number"
                                                min="1"
                                                name="order_id"
                                                class="form-control"
                                                value="{{ old('order_id', $order ? $order->id : '') }}"
                                                placeholder="Введите ID заказа"
                                                required
                                            >
                                        </div>
                                        <div class="col-md-3" style="margin-top:24px;">
                                            <button type="submit" class="btn btn-warning">Привязать к заказу</button>
                                        </div>
                                        <div class="col-md-12" style="margin-top:12px;">
                                            <div class="alert alert-info" style="margin-bottom:0; padding:10px 12px;">
                                                Подтягивает всю накопленную цепочку сообщений в заказ и связывает dialog -> order.
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="background:#fafafa; border:1px solid #ddd; border-radius:6px; padding:15px; max-height:520px; overflow:auto;">
                        @forelse($messages as $message)
                            @php
                                $attachments = $message->attachments_json ? (json_decode($message->attachments_json, true) ?: []) : [];
                                $isOutbound = $message->direction === 'outbound';
                                $from = $message->from_json ? (json_decode($message->from_json, true) ?: []) : [];
                                $fromType = strtolower((string) data_get($from, 'type', ''));
                                if ($message->direction === 'inbound') {
                                    $senderLabel = 'Клиент';
                                } elseif ($fromType === 'bot') {
                                    $senderLabel = 'Бот';
                                } elseif ($fromType === 'system') {
                                    $senderLabel = 'Система';
                                } else {
                                    $senderLabel = 'Менеджер';
                                }
                            @endphp
                            <div style="margin-bottom:15px; display:flex; justify-content:{{ $isOutbound ? 'flex-end' : 'flex-start' }};">
                                <div style="max-width:75%; background:{{ $isOutbound ? '#dcf8c6' : '#fff' }}; border:1px solid #ddd; border-radius:8px; padding:12px 14px;">
                                    <div style="font-weight:600; margin-bottom:4px;">
                                        {{ $senderLabel }}
                                    </div>
                                    <div style="white-space:pre-wrap;">{{ $message->text ?: '[пустое сообщение]' }}</div>
                                    @if(!empty($attachments))
                                        <div style="margin-top:10px;">
                                            @foreach($attachments as $attachment)
                                                @if(!empty($attachment['path']))
                                                    @php
                                                        $attachmentUrl = \Illuminate\Support\Facades\Storage::url($attachment['path']);
                                                    @endphp
                                                    <div style="margin-bottom:8px;">
                                                        <a href="{{ $attachmentUrl }}" target="_blank">{{ $attachment['original_name'] ?? basename($attachment['path']) }}</a>
                                                        <div>
                                                            <img src="{{ $attachmentUrl }}" alt="" style="max-width:180px; max-height:180px; border:1px solid #ddd; border-radius:4px; margin-top:6px;">
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                    <div style="margin-top:8px; color:#666; font-size:12px;">
                                        {{ optional($message->sent_at)->format('Y-m-d H:i:s') ?: $message->created_at }}
                                        @if($message->status)
                                            | {{ $message->status }}
                                        @endif
                                        @if($message->orders_id)
                                            | order #{{ $message->orders_id }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted">Сообщений пока нет.</div>
                        @endforelse
                    </div>

                    <hr>

                    <form method="POST" action="{{ route('admin.sa.conversations.send_message', ['conversation' => $conversation->conversation_id]) }}">
                        @csrf
                        <div class="form-group">
                            <label for="text">Ответ менеджера</label>
                            <textarea name="text" id="text" rows="4" class="form-control" placeholder="Введите сообщение..." required>{{ old('text') }}</textarea>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="handoff" value="1"> Отключить бота после отправки (handoff)
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary">Отправить сообщение</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
@parent
<script>
    function setConversationBotMode(action) {
        if (!confirm('Изменить режим бота для этого диалога?')) {
            return;
        }

        var orderId = '{{ $order ? (int) $order->id : '' }}';
        var conversationId = @json((string) $conversation->conversation_id);
        var requestData = {
            _token: '{{ csrf_token() }}',
            action: action
        };

        if (orderId !== '') {
            requestData.order_id = orderId;
        }
        if (conversationId !== '') {
            requestData.conversation_id = conversationId;
        }

        $.ajax({
            type: 'POST',
            url: '{{ route("admin.sa.bot_control") }}',
            data: requestData,
            success: function(response) {
                if (response.status !== 'ok') {
                    alert('Ошибка: ' + (response.message || 'Неизвестная ошибка'));
                    return;
                }

                var mode = response && response.data && response.data.data && response.data.data.bot_mode
                    ? response.data.data.bot_mode
                    : '-';
                if (action === 'resume_bot') {
                    mode = 'active';
                } else if (action === 'pause_bot') {
                    mode = 'paused';
                } else if (action === 'handoff_to_manager') {
                    mode = 'handoff_to_manager';
                }

                $('#sa_conversation_bot_mode_label').text(mode);

                if (typeof toastr !== 'undefined') {
                    toastr.success('Режим бота изменен: ' + mode);
                }
            },
            error: function(xhr) {
                alert('Ошибка при изменении режима бота: ' + xhr.statusText);
            }
        });
    }

    $('.js-sa-conversation-bot-control').on('click', function() {
        setConversationBotMode($(this).data('action'));
    });
</script>
@endsection
