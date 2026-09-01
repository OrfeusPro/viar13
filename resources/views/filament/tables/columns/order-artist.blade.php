@php
    $record = $getRecord();
    $painter = $record->painterAssignment?->user;
    $printing = $record->printingAssignment?->user;
    $sketches = $record->order_painter_images->where('is_img_sketch', 1);
    $paintings = $record->order_painter_images->where('is_img_painter', 1);
    $legacySketches = collect(explode(',', trim((string) $record->painter_sketch_images, ',')))->map(fn ($path) => trim($path))->filter();
    $legacyPaintings = collect(explode(',', trim((string) $record->painter_images, ',')))->map(fn ($path) => trim($path))->filter();
    $clientImages = collect(explode(',', trim((string) $record->client_images, ',')))->map(fn ($path) => trim($path))->filter();
    $canEdit = auth('filament')->user()?->can('update', $record) ?? false;
@endphp

<style>
    .adm-fil-order-artist, .adm-fil-order-artist * { box-sizing: border-box; white-space: normal !important; overflow-wrap: anywhere; }
    .adm-fil-order-artist__gallery { display: flex; flex-wrap: wrap; justify-content: center; gap: 6px; margin-top: 5px; }
    .adm-fil-order-artist__image { width: 58px; height: 58px; object-fit: cover; border: 1px solid #d5d9df; border-radius: 3px; }
</style>

<div class="adm-fil-order-artist" style="width: 205px; min-width: 195px; padding: 4px 6px; text-align: center; color: #313942; font-size: 12px; line-height: 1.35;">
    @if($painter)
        <div style="font-weight: 600; color: #2563eb;">{{ trim(($painter->nick ? $painter->nick.' — ' : '').$painter->first_name.' '.$painter->last_name) ?: $painter->email }}</div>
        @if(filled($painter->email))<div style="color: #64748b;">{{ $painter->email }}</div>@endif

        @foreach([['Набросок', $sketches, $legacySketches], ['Картины', $paintings, $legacyPaintings]] as [$title, $images, $legacyImages])
            @if($images->isNotEmpty() || $legacyImages->isNotEmpty())
                <div style="margin-top: 10px; font-weight: 600;">{{ $title }}:</div>
                <div class="adm-fil-order-artist__gallery">
                    @forelse($images as $image)
                        <a href="{{ order_image_url($image->image) }}" target="_blank" rel="noopener" title="{{ $image->statusDefinition?->title ?: 'Статус не указан' }}">
                            <img class="adm-fil-order-artist__image" src="{{ order_image_url($image->small_image ?: $image->image) }}" alt="{{ $title }} заказа №{{ $record->id }}">
                        </a>
                    @empty
                        @foreach($legacyImages as $path)
                            <a href="{{ order_image_url($path) }}" target="_blank" rel="noopener"><img class="adm-fil-order-artist__image" src="{{ order_image_url($path) }}" alt="{{ $title }} заказа №{{ $record->id }}"></a>
                        @endforeach
                    @endforelse
                </div>
                @foreach($images as $image)
                    <div style="margin-top: 3px; color: #64748b;">{{ $image->statusDefinition?->title ?: 'Статус не указан' }} · {{ optional($image->updated_at)->format('d.m.Y') }}</div>
                @endforeach
            @endif
        @endforeach

        @if($clientImages->isNotEmpty())
            <div style="margin-top: 10px; font-weight: 600;">Картины клиента:</div>
            <div class="adm-fil-order-artist__gallery">
                @foreach($clientImages as $path)
                    <a href="{{ order_image_url($path) }}" target="_blank" rel="noopener"><img class="adm-fil-order-artist__image" src="{{ order_image_url($path) }}" alt="Файл клиента заказа №{{ $record->id }}"></a>
                @endforeach
            </div>
        @endif

        <div style="margin-top: 10px;">Время на заказ:</div>
        <strong>{{ filled($record->painter_endtime) ? date('d.m.Y', strtotime((string) $record->painter_endtime)) : 'не задано' }}</strong>
        <div style="margin-top: 7px;">Заказ оплачен/выполнен: <strong>{{ $record->painter_payed ? 'да' : 'нет' }}</strong></div>
        <div style="margin-top: 7px;">Показывать картины клиенту: <strong>{{ $record->is_show_painter_images ? 'да' : 'нет' }}</strong></div>
    @else
        <div style="color: #64748b;">Художник:</div>
        <strong>не назначен</strong>
        <div style="margin-top: 7px;">Показывать картины клиенту: <strong>{{ $record->is_show_painter_images ? 'да' : 'нет' }}</strong></div>
    @endif

    <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid #e5e7eb;">
        <div style="font-weight: 600;">Менеджер печати</div>
        @if($printing)
            <div style="margin-top: 3px; color: #2563eb;">{{ trim(($printing->nick ? $printing->nick.' — ' : '').$printing->first_name.' '.$printing->last_name) ?: $printing->email }}</div>
            @if(filled($printing->email))<div style="color: #64748b;">{{ $printing->email }}</div>@endif
        @else
            <div style="color: #64748b;">не назначен</div>
        @endif
    </div>

    @if($canEdit)
        <button type="button" x-on:click.stop="$wire.mountTableAction('manageArtist', '{{ $record->getKey() }}')" style="display: block; width: 100%; margin-top: 12px; padding: 6px 8px; border: 0; border-radius: 3px; background: #337ab7; color: white; font-size: 12px; cursor: pointer;">Управление</button>
    @endif
</div>
