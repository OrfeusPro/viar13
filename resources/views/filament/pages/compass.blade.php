<x-filament-panels::page class="viar-management-page">
    @include('filament.components.management-styles')
    @assets
        <link rel="stylesheet" href="{{ asset('vendor/voyager-icons/icons.css') }}">
    @endassets
    <div class="viar-management-tabs" role="tablist" aria-label="Вкладки Compass">
        <button type="button" role="tab" class="viar-management-tab" aria-selected="{{ $tab === 'resources' ? 'true' : 'false' }}" wire:click="selectTab('resources')">Ресурсы</button>
        <button type="button" role="tab" class="viar-management-tab" aria-selected="{{ $tab === 'commands' ? 'true' : 'false' }}" wire:click="selectTab('commands')">Команды</button>
        <button type="button" role="tab" class="viar-management-tab" aria-selected="{{ $tab === 'logs' ? 'true' : 'false' }}" wire:click="selectTab('logs')">Логи</button>
    </div>
    @if($tab === 'resources')
    <x-filament::section heading="Ресурсы">
        <div style="display:flex;flex-wrap:wrap;gap:16px">
            <a href="https://voyager-docs.devdojo.com/" target="_blank" rel="noopener noreferrer" style="color:#2563eb">Документация Voyager</a>
            <a href="https://voyager.devdojo.com/" target="_blank" rel="noopener noreferrer" style="color:#2563eb">Сайт Voyager</a>
        </div>
    </x-filament::section>
    <x-filament::section id="fonts" heading="Иконки Voyager">
        <label for="compass-icon-search">Поиск класса иконки</label>
        <input id="compass-icon-search" type="search" wire:model.live.debounce.250ms="iconSearch" maxlength="255"
            style="display:block;width:100%;max-width:420px;margin-top:8px;padding:10px;border:1px solid #d1d5db;border-radius:8px" placeholder="Например, voyager-home">
        @php($icons = $this->icons())
        <p role="status" style="margin:16px 0">Найдено: {{ count($icons) }}</p>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,190px),1fr));gap:12px">
            @forelse($icons as $icon)
                <div wire:key="compass-{{ $icon }}" style="display:flex;align-items:center;gap:12px;padding:12px;border:1px solid #e5e7eb;border-radius:8px;min-width:0">
                    <span class="{{ $icon }}" aria-hidden="true" style="font-size:26px;flex-shrink:0"></span>
                    <div style="min-width:0;flex:1">
                        <input readonly aria-label="Класс {{ $icon }}" value="{{ $icon }}" style="width:100%;min-width:0;border:0;padding:4px;background:transparent;font-size:12px">
                        <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:#6b7280">Символ
                            <input readonly aria-label="Символ {{ $icon }}" value="{{ $this->iconCharacter($icon) }}" style="width:48px;border:0;padding:4px;background:transparent;font-family:voyager">
                        </label>
                    </div>
                </div>
            @empty<p>Иконки не найдены.</p>@endforelse
        </div>
    </x-filament::section>
    @elseif($tab === 'commands')
        @include('filament.components.compass-commands')
    @else
        @include('filament.components.compass-logs')
    @endif
</x-filament-panels::page>
