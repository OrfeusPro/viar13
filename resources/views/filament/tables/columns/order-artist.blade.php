@php
    $record = $getRecord();
    $isPrinting = auth('filament')->user()?->role?->name === 'printing';
    $painter = $record->painterAssignment?->user;
    $printing = $record->printingAssignment?->user;
    $sketches = $record->order_painter_images->where('is_img_sketch', 1);
    $paintings = $record->order_painter_images->where('is_img_painter', 1);
    $legacySketches = collect(explode(',', trim((string) $record->painter_sketch_images, ',')))->map(fn ($path) => trim($path))->filter();
    $legacyPaintings = collect(explode(',', trim((string) $record->painter_images, ',')))->map(fn ($path) => trim($path))->filter();
    $clientImages = collect(explode(',', trim((string) $record->client_images, ',')))->map(fn ($path) => trim($path))->filter();
    $canEdit = auth('filament')->user()?->can('update', $record) ?? false;
    $mediaStatuses = $sketches->isNotEmpty() || $paintings->isNotEmpty()
        ? \App\Models\APainterImagesStatus::query()->orderBy('id')->pluck('title', 'id') : collect();
    $galleries = $isPrinting
        ? [['Картины', $paintings, $legacyPaintings]]
        : [['Набросок', $sketches, $legacySketches], ['Картины', $paintings, $legacyPaintings]];
@endphp

<style>
    .adm-fil-order-artist, .adm-fil-order-artist * { box-sizing: border-box; white-space: normal !important; overflow-wrap: anywhere; }
    .adm-fil-order-artist__gallery { display: flex; flex-wrap: wrap; justify-content: center; gap: 6px; margin-top: 5px; }
    .adm-fil-order-artist__image { display: block; width: 58px; height: 58px; margin: 0 auto; object-fit: contain; border: 1px solid #d5d9df; border-radius: 3px; }
    .adm-fil-order-artist select, .adm-fil-order-artist input { width:100%;min-width:0;margin-top:4px;padding:5px;border:1px solid #9ca3af;border-radius:3px;background:#fff;color:#374151;font-size:12px; }
    .adm-fil-order-artist__remove { margin-left:6px;border:0;background:none;color:#dc2626;cursor:pointer;font-size:15px; }
</style>

<div class="adm-fil-order-artist" x-data="{ save(field, value) { $wire.mountTableAction('updateOrderArtistField', '{{ $record->getKey() }}', { field, value }) }, saveMedia(type, image_id, status) { $wire.mountTableAction('updateOrderMediaStatus', '{{ $record->getKey() }}', { type, image_id, status }) } }" style="width: 205px; min-width: 195px; padding: 4px 6px; text-align: center; color: #313942; font-size: 12px; line-height: 1.35;">
    @if($painter)
        <div style="font-weight: 600; color: #2563eb;">{{ trim(($painter->nick ? $painter->nick.' — ' : '').$painter->first_name.' '.$painter->last_name) ?: $painter->email }}</div>
        @if(filled($painter->email))<div style="color: #64748b;">{{ $painter->email }}</div>@endif
        @if($canEdit && ! $isPrinting)
            <button type="button" class="adm-fil-order-artist__remove" aria-label="Снять художника заказа №{{ $record->id }}" x-on:click.stop="save('painter_id', null)" wire:loading.attr="disabled">×</button>
        @endif

        @foreach($galleries as [$title, $images, $legacyImages])
            @if($images->isNotEmpty() || $legacyImages->isNotEmpty())
                @php
                    $mediaType = $title === 'Набросок' ? 'sketch' : 'painting';
                    $mediaLabel = $mediaType === 'sketch' ? 'наброска' : 'картины';
                @endphp
                <div style="margin-top: 10px; font-weight: 600;">{{ $title }}:</div>
                @forelse($images as $image)
                    <div style="margin-top: 6px; padding-bottom: 12px; border-bottom: 1px solid #eee;" wire:key="order-media-status-{{ $record->id }}-{{ $mediaType }}-{{ $image->id }}">
                        <a href="{{ order_image_url($image->image) }}" target="_blank" rel="noopener" title="{{ $image->statusDefinition?->title ?: 'Статус не указан' }}">
                            <img class="adm-fil-order-artist__image" src="{{ order_image_url($image->small_image ?: $image->image) }}" alt="{{ $title }} заказа №{{ $record->id }}">
                        </a>
                        @if($canEdit)
                            <select aria-label="Статус {{ $mediaLabel }} №{{ $image->id }} заказа №{{ $record->id }}" x-on:change.stop="saveMedia('{{ $mediaType }}', {{ $image->id }}, $el.value)" wire:loading.attr="disabled" style="display: block; margin: 4px auto 0; width: auto; max-width: 100%; padding: 1px 3px;">
                                <option value="" disabled @selected($image->status === null)>Статус не указан</option>
                                @foreach($mediaStatuses as $id => $label)
                                    <option value="{{ $id }}" @selected((string) $image->status === (string) $id)>{{ $label }}</option>
                                @endforeach
                            </select>
                        @else
                            <div>{{ $image->statusDefinition?->title ?: 'Статус не указан' }}</div>
                        @endif
                        <div style="color: #94a3b8;">{{ optional($image->updated_at)->format('Y-m-d H:i:s') }}</div>
                    </div>
                @empty
                    <div class="adm-fil-order-artist__gallery">
                        @foreach($legacyImages as $path)
                            <a href="{{ order_image_url($path) }}" target="_blank" rel="noopener"><img class="adm-fil-order-artist__image" src="{{ order_image_url($path) }}" alt="{{ $title }} заказа №{{ $record->id }}"></a>
                        @endforeach
                    </div>
                @endforelse
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

        @if(! $isPrinting)
        <div style="margin-top: 10px;">Время на заказ:</div>
        @if($canEdit)
            <input type="date" aria-label="Время на заказ №{{ $record->id }}" value="{{ filled($record->painter_endtime) ? substr((string) $record->painter_endtime, 0, 10) : '' }}" x-ref="painterDeadline">
            <button type="button" x-on:click.stop="save('painter_endtime', $refs.painterDeadline.value || null)" wire:loading.attr="disabled" style="margin-top:5px;padding:4px 8px;border:0;border-radius:3px;background:#337ab7;color:#fff;cursor:pointer;">Обновить</button>
            <div style="margin-top: 7px;">Заказ оплачен/выполнен:</div>
            <select aria-label="Заказ художника №{{ $record->id }} оплачен или выполнен" x-on:change.stop="save('painter_payed', $el.value)" wire:loading.attr="disabled">
                <option value="0" @selected(! $record->painter_payed)>нет</option>
                <option value="1" @selected($record->painter_payed)>да</option>
            </select>
        @else
        <strong>{{ filled($record->painter_endtime) ? date('d.m.Y', strtotime((string) $record->painter_endtime)) : 'не задано' }}</strong>
        <div style="margin-top: 7px;">Заказ оплачен/выполнен: <strong>{{ $record->painter_payed ? 'да' : 'нет' }}</strong></div>
        @endif
        @endif
    @else
        <div style="color: #64748b;">Художник:</div>
        <strong>не назначен</strong>
        @if($canEdit && ! $isPrinting)
            <select aria-label="Назначить художника заказа №{{ $record->id }}" x-on:change.stop="if ($el.value) save('painter_id', $el.value)" wire:loading.attr="disabled">
                <option value="">нет</option>
                @foreach(\App\Filament\Resources\Orders\Tables\OrderArtistActions::userOptions(3) as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                @endforeach
            </select>
        @endif
    @endif

    @if(! $isPrinting)
        <div style="margin-top: 7px;">Показывать картины клиенту:</div>
        @if($canEdit)
            <select aria-label="Показывать картины заказа №{{ $record->id }} клиенту" x-on:change.stop="save('is_show_painter_images', $el.value)" wire:loading.attr="disabled">
                <option value="1" @selected($record->is_show_painter_images)>да</option>
                <option value="0" @selected(! $record->is_show_painter_images)>нет</option>
            </select>
        @else
            <strong>{{ $record->is_show_painter_images ? 'да' : 'нет' }}</strong>
        @endif
    @endif

    <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid #e5e7eb;">
        <div style="font-weight: 600;">Менеджер печати</div>
        @if($printing)
            <div style="margin-top: 3px; color: #2563eb;">{{ trim(($printing->nick ? $printing->nick.' — ' : '').$printing->first_name.' '.$printing->last_name) ?: $printing->email }}</div>
            @if(filled($printing->email))<div style="color: #64748b;">{{ $printing->email }}</div>@endif
            @if($canEdit && ! $isPrinting)
                <button type="button" class="adm-fil-order-artist__remove" aria-label="Снять печатника заказа №{{ $record->id }}" x-on:click.stop="save('printing_id', null)" wire:loading.attr="disabled">×</button>
            @endif
        @else
            <div style="color: #64748b;">не назначен</div>
            @if($canEdit && ! $isPrinting)
                <select aria-label="Назначить печатника заказа №{{ $record->id }}" x-on:change.stop="if ($el.value) save('printing_id', $el.value)" wire:loading.attr="disabled">
                    <option value="">нет</option>
                    @foreach(\App\Filament\Resources\Orders\Tables\OrderArtistActions::userOptions(6) as $id => $label)
                        <option value="{{ $id }}">{{ $label }}</option>
                    @endforeach
                </select>
            @endif
        @endif
    </div>

</div>
