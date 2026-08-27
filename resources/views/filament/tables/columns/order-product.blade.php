@php
    $record = $getRecord();
    $items = is_array($record->items) ? $record->items : (json_decode((string) $record->items, true) ?: []);
    $products = collect($items)->filter(fn ($item, $key) => is_int($key) && is_array($item));
@endphp

<div class="min-w-52 space-y-2 text-xs">
    @forelse($products as $item)
        @php
            $name = $item['name'] ?? $item['type'] ?? 'Товар';
            $size = $item['size_name'] ?? data_get($item, 'show.size');
            $count = (int) ($item['count'] ?? 1);
            $image = $item['image'] ?? $item['img'] ?? data_get($item, 'show.image');
        @endphp
        <div class="flex gap-2 border-b border-gray-100 pb-2 last:border-0 dark:border-gray-800">
            @if(filled($image))
                <img src="{{ str_starts_with($image, 'http') ? $image : asset(ltrim($image, '/')) }}" alt="" class="h-12 w-12 rounded object-cover">
            @endif
            <div>
                <div class="font-semibold text-gray-950 dark:text-white">{{ $name }}</div>
                @if($size)<div>Размер: {{ $size }}</div>@endif
                @if($count > 1)<div>Количество: {{ $count }}</div>@endif
                @if(isset($item['price']))<div>{{ number_format((float) $item['price'], 2) }} €</div>@endif
            </div>
        </div>
    @empty
        <span class="text-gray-500">—</span>
    @endforelse
</div>
