<x-filament-panels::page>
    <style>
        .viar-order-list { display: grid; gap: 8px; margin-top: 20px; }
        .viar-order-item { display: flex; align-items: center; gap: 12px; padding: 12px; border: 1px solid #dbe3ee; border-radius: 8px; background: #f8fafc; }
        .viar-order-label { flex: 1; min-width: 0; overflow-wrap: anywhere; }
        .viar-order-image { width: 96px; height: 96px; object-fit: contain; border-radius: 6px; }
        .viar-order-handle { cursor: grab; padding: 8px; touch-action: none; }
        .viar-order-actions { display: flex; gap: 4px; }
        .dark .viar-order-item { background: #18181b; border-color: #334155; }
    </style>
    <x-filament::section>
        <x-filament::button tag="a" :href="$this->listUrl()" color="gray" icon="heroicon-o-arrow-left">Вернуться к списку</x-filament::button>
        <p style="margin-top: 16px;">Перетаскивайте записи за значок или используйте стрелки. Порядок сохраняется после каждого перемещения.</p>
        @error('ordering') <p role="alert" style="color: #dc2626; margin-top: 12px;">{{ $message }}</p> @enderror
        @php($configuration = $this->orderConfiguration())
        @php($ordered = $this->orderedRecords())
        <ol class="viar-order-list" wire:sort="moveRecord" wire:loading.attr="inert" wire:target="moveRecord" aria-label="Порядок записей">
            @foreach ($ordered as $record)
                <li class="viar-order-item" wire:key="order-{{ $record->getKey() }}" wire:sort:item="{{ $record->getKey() }}">
                    <span class="viar-order-handle" wire:sort:handle title="Перетащить"><x-filament::icon icon="heroicon-o-bars-3" style="width: 20px; height: 20px;" /></span>
                    <div class="viar-order-label">
                        @if ($configuration['image'] && filled($record->{$configuration['label']}))
                            @php($image = $record->{$configuration['label']})
                            <img class="viar-order-image" src="{{ filter_var($image, FILTER_VALIDATE_URL) && in_array(parse_url($image, PHP_URL_SCHEME), ['http', 'https'], true) ? $image : \Illuminate\Support\Facades\Storage::disk('public')->url($image) }}" alt="Запись #{{ $record->getKey() }}" loading="lazy" />
                        @else
                            {{ $record->{$configuration['label']} }}
                        @endif
                    </div>
                    <span style="color: #64748b;">#{{ $record->getKey() }}</span>
                    <div class="viar-order-actions" wire:sort:ignore>
                        <x-filament::icon-button color="gray" icon="heroicon-o-chevron-up" label="Переместить выше" :disabled="$loop->first" wire:click="moveRecord({{ $record->getKey() }}, {{ max(0, $loop->index - 1) }})" />
                        <x-filament::icon-button color="gray" icon="heroicon-o-chevron-down" label="Переместить ниже" :disabled="$loop->last" wire:click="moveRecord({{ $record->getKey() }}, {{ $loop->index + 1 }})" />
                    </div>
                </li>
            @endforeach
        </ol>
        @if ($ordered->isEmpty()) <p style="margin-top: 20px;">Нет записей для упорядочивания.</p> @endif
    </x-filament::section>
</x-filament-panels::page>
