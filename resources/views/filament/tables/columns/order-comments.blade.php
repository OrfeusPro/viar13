@php
    $record = $getRecord();
    $delivery = is_array($record->delivery) ? $record->delivery : (json_decode((string) $record->delivery, true) ?: []);
    $comments = collect([
        filled($delivery['comment'] ?? null) ? 'Доставка: ' . $delivery['comment'] : null,
        filled($record->comment) ? 'Заказ: ' . $record->comment : null,
        filled($record->admin_comment) ? 'Админ: ' . $record->admin_comment : null,
        filled($record->painter_comment) ? 'Художник: ' . $record->painter_comment : null,
    ])->filter();
@endphp

<div class="min-w-48 space-y-2 text-xs">
    <div class="flex flex-wrap gap-1">
        <span class="rounded bg-gray-100 px-2 py-1 dark:bg-gray-800">Клиент: {{ $record->client_messages_count }}</span>
        @if($record->unread_client_messages_count)
            <span class="rounded bg-danger-600 px-2 py-1 font-semibold text-white">Новых: {{ $record->unread_client_messages_count }}</span>
        @endif
        <span class="rounded bg-gray-100 px-2 py-1 dark:bg-gray-800">Админ: {{ $record->admin_messages_count }}</span>
        <span class="rounded bg-gray-100 px-2 py-1 dark:bg-gray-800">Художник: {{ $record->painter_messages_count }}</span>
    </div>
    @foreach($comments->take(3) as $comment)
        <div class="break-words text-gray-600 dark:text-gray-300">{{ $comment }}</div>
    @endforeach
</div>
