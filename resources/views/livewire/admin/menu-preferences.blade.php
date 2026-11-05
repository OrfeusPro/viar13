<div class="viar-personal-menu" x-bind:class="{ 'is-compact': ! $store.sidebar.isOpen }">
    <nav class="fi-sidebar-nav" aria-label="Основное меню">
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIDEBAR_NAV_START) }}
        @include('filament.components.menu-tree', ['tree' => $tree])
        @if (! $tree)
            <p class="viar-menu-empty" x-show="$store.sidebar.isOpen">Все пункты скрыты. Включите режим «Все» или настройте меню.</p>
        @endif
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIDEBAR_NAV_END) }}
    </nav>
    <div class="viar-menu-preferences-bar">
        <label x-show="$store.sidebar.isOpen" class="viar-menu-show-all">
            <span>Основное</span>
            <span class="viar-menu-mode-switch">
                <input type="checkbox" role="switch" aria-label="Показать все разделы" wire:model.live="showAll" wire:loading.attr="disabled" />
                <span class="viar-menu-mode-track" aria-hidden="true"></span>
            </span>
            <span>Все</span>
        </label>
        <button type="button" wire:click="openSettings" class="viar-menu-settings-button" title="Настроить меню" aria-label="Настроить меню" wire:loading.attr="disabled">
            <x-filament::icon icon="heroicon-o-cog-6-tooth" />
        </button>
    </div>
    <x-filament::modal id="admin-menu-preferences" width="3xl" teleport="body" sticky-header sticky-footer>
        <x-slot name="heading">Настроить основное меню</x-slot>
        <x-slot name="description">Выберите разделы для режима «Основное». Режим «Все» показывает все доступные вам разделы. Настройка сохраняется только для вас.</x-slot>
        <form wire:submit="save" id="admin-menu-preferences-form" class="viar-menu-preferences-form">
            @foreach ($groups as $index => $group)
                <fieldset wire:key="preferences-group-{{ $index }}">
                    <legend>{{ $group['label'] }}</legend>
                    <div class="viar-menu-preferences-options">
                        @foreach ($group['items'] as $id => $label)
                            <label wire:key="preference-{{ $id }}">
                                <input type="checkbox" wire:model="selected" value="{{ $id }}" />
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach
            @error('selected') <p role="alert">Не удалось сохранить выбор. Откройте настройки заново.</p> @enderror
            @error('selected.*') <p role="alert">Один из разделов больше недоступен. Откройте настройки заново.</p> @enderror
        </form>
        <x-slot name="footer">
            <div class="viar-menu-preferences-actions">
                <x-filament::button color="gray" wire:click="resetSelection" wire:loading.attr="disabled">Сбросить</x-filament::button>
                <x-filament::button wire:click="save" wire:loading.attr="disabled">Сохранить</x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</div>
