@php
    $canEdit = auth('filament')->user()?->can('update', $record) ?? false;
    $imageThreadsOpen = ! in_array($record->status, ['completed', 'sended', 'send_lubanas'], true);
    $allMessages = $record->order_user_comments;
    $images = $record->order_painter_images;
    $grouped = $allMessages->groupBy(fn ($message) => (int) $message->order_painter_image_id);
@endphp

<div x-data x-on:order-chat-updated.window="if ($event.detail.orderId === {{ (int) $record->id }}) $wire.$refresh()" style="max-height: 65vh; overflow-y: auto; padding-right: 8px;">
    <h3 style="font-weight: 700; margin-bottom: 10px;">Общая переписка</h3>
    <div style="max-height: 32vh; overflow-y: auto;" role="region" aria-label="История общего клиентского чата" tabindex="0">
        @include('filament.tables.modals.order-client-messages', ['messages' => $grouped->get(0, collect())])
    </div>
    @if($canEdit)
        @livewire('admin.order-chat-composer', ['orderId' => (int) $record->id, 'stream' => 'client'], key('client-general-'.$record->id))
    @endif

    @foreach(['is_img_painter' => 'Картины', 'is_img_sketch' => 'Наброски'] as $flag => $title)
        @php
            $threadImages = $images->where($flag, 1);
        @endphp
        @if($threadImages->isNotEmpty())
            <h3 style="font-weight: 700; margin: 20px 0 10px; border-top: 1px solid #dbe2ea; padding-top: 12px;">{{ $title }}</h3>
            @foreach($threadImages as $artwork)
                @php
                    $url = \App\Support\Admin\OrderMediaUrl::resolve($artwork->image);
                    $extension = strtolower(pathinfo((string) $artwork->image, PATHINFO_EXTENSION));
                    $preview = match ($extension) {
                        'pdf' => asset('img/pdf.svg'),
                        'psd' => asset('img/psd.svg'),
                        default => \App\Support\Admin\OrderMediaUrl::resolve($artwork->small_image) ?: $url ?: order_image_placeholder(),
                    };
                    $status = $artwork->statusDefinition;
                    $threadType = $flag === 'is_img_sketch' ? 'sketch' : 'painter';
                @endphp
                <section style="margin-bottom: 18px; padding: 12px; border: 1px solid #dbe2ea; border-radius: 7px;">
                    <div style="display: flex; flex-wrap: wrap; align-items: start; gap: 12px; margin-bottom: 10px;">
                        <a @if($url) href="{{ $url }}" target="_blank" rel="noopener noreferrer" @endif>
                            <img src="{{ $preview }}" alt="{{ $title }} #{{ $artwork->id }}" loading="lazy" referrerpolicy="no-referrer" x-on:error.once="$el.src = @js(order_image_placeholder())" style="width: 150px; height: 150px; object-fit: contain;">
                        </a>
                        <div style="overflow-wrap: anywhere; max-width: 360px;">
                            <strong>{{ $flag === 'is_img_sketch' ? 'Набросок' : 'Картина' }} #{{ $artwork->id }}</strong>
                            <div>{{ optional($artwork->created_at)->format('d.m.Y H:i') }}</div>
                            <div>Статус: {{ $status?->getTranslatedAttribute('title') ?: 'Без статуса' }}</div>
                            <div>{{ $artwork->is_show ? 'Показывается клиенту' : 'Скрыто от клиента' }}</div>
                            @if($url)
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" style="color: #2563eb; text-decoration: underline;">Открыть файл</a>
                            @else
                                <div style="color: #b45309; font-size: 12px;">Путь к файлу не указан или некорректен.</div>
                            @endif
                        </div>
                    </div>
                    <div style="max-height: 30vh; overflow-y: auto;" role="region" aria-label="История по изображению #{{ $artwork->id }}" tabindex="0">
                        @include('filament.tables.modals.order-client-messages', ['messages' => $grouped->get((int) $artwork->id, collect())])
                    </div>
                    @if($canEdit && $imageThreadsOpen)
                        @livewire('admin.order-chat-composer', ['orderId' => (int) $record->id, 'stream' => 'client', 'threadType' => $threadType, 'imageId' => (int) $artwork->id], key('client-'.$threadType.'-'.$record->id.'-'.$artwork->id))
                    @elseif(! $imageThreadsOpen)
                        <p style="font-size: 12px; color: #64748b;">Ответы по изображениям закрыты для отправленного или завершённого заказа.</p>
                    @endif
                </section>
            @endforeach
        @endif
    @endforeach

    @php
        // Never discard messages whose artwork was removed or has no supported type.
        $shownIds = $images->filter(fn ($image) => $image->is_img_sketch || $image->is_img_painter)->modelKeys();
        $orphanMessages = $allMessages->filter(fn ($message) => $message->order_painter_image_id && ! in_array((int) $message->order_painter_image_id, $shownIds, true));
    @endphp
    @if($orphanMessages->isNotEmpty())
        <h3 style="font-weight: 700; margin: 15px 0;">Переписка по недоступным изображениям</h3>
        @include('filament.tables.modals.order-client-messages', ['messages' => $orphanMessages])
    @endif
</div>
