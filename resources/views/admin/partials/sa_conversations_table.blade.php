<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Conversation</th>
                <th>Клиент</th>
                <th>Канал</th>
                <th>Последнее сообщение</th>
                <th>Заказ</th>
                <th>Сообщений</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            @forelse($conversations as $conversation)
                <tr>
                    <td>
                        <div><strong>{{ $conversation->conversation_id }}</strong></div>
                        <small class="text-muted">
                            {{ optional($conversation->last_message_at)->format('Y-m-d H:i:s') ?: $conversation->updated_at }}
                        </small>
                        @if(isset($conversation->unread_for_manager) && (int) $conversation->unread_for_manager === 1)
                            <div style="margin-top:6px;">
                                <span class="label label-danger">Непрочитано</span>
                            </div>
                        @endif
                    </td>
                    <td>
                        <div>{{ $conversation->client_name ?: 'Без имени' }}</div>
                        <small class="text-muted">{{ $conversation->client_phone ?: 'Без телефона' }}</small>
                    </td>
                    <td>{{ $conversation->channel ?: '-' }}</td>
                    <td style="max-width:380px;">
                        <div>
                            @if($conversation->last_message_direction === 'outbound')
                                <span class="label label-success">OUT</span>
                            @else
                                <span class="label label-info">IN</span>
                            @endif
                            @if(!$conversation->order_id)
                                <span class="label label-warning">Без заказа</span>
                            @endif
                        </div>
                        <div style="margin-top:6px; white-space:normal;">
                            {{ \Illuminate\Support\Str::limit((string) $conversation->last_message_text, 140) ?: 'Нет сообщений' }}
                        </div>
                    </td>
                    <td>
                        @if($conversation->order_id)
                            <a href="{{ route('edit_admin_order', ['id' => $conversation->order_id]) }}" target="_blank">
                                #{{ $conversation->order_id }}
                            </a>
                            <div><small class="text-muted">{{ $conversation->order_status ?: '-' }}</small></div>
                        @else
                            <span class="label label-warning">Без заказа</span>
                        @endif
                    </td>
                    <td>{{ (int) $conversation->messages_count }}</td>
                    <td>
                        <a href="{{ route('admin.sa.conversations.show', ['conversation' => $conversation->conversation_id]) }}" class="btn btn-sm btn-primary">Открыть</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">Диалоги не найдены.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div>
    {{ $conversations->links() }}
</div>
