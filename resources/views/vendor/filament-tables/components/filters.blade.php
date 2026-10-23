@php
    use Filament\Tables\Enums\FiltersResetActionPosition;
@endphp

@props([
    'applyAction',
    'form',
    'headingTag' => 'h3',
    'resetActionPosition' => FiltersResetActionPosition::Header,
])

<div {{ $attributes->class(['fi-ta-filters']) }}
    @if ($this instanceof \App\Filament\Resources\Orders\Pages\ListOrders)
        x-data="{ hasFilterValue(value) {
            if (value !== null && typeof value === 'object') return Object.values(value).some(item => this.hasFilterValue(item));
            return value !== null && value !== undefined && String(value).trim() !== '';
        } }"
    @endif
>
    <div class="fi-ta-filters-header">
        <{{ $headingTag }} class="fi-ta-filters-heading">
            {{ __('filament-tables::table.filters.heading') }}
        </{{ $headingTag }}>

        @if ($resetActionPosition === FiltersResetActionPosition::Header)
            <div
                @if ($this instanceof \App\Filament\Resources\Orders\Pages\ListOrders)
                    x-show="hasFilterValue($wire.tableFilters) || hasFilterValue($wire.tableDeferredFilters)"
                    x-cloak
                @endif
            >
                <x-filament::link
                    :attributes="
                        \Filament\Support\prepare_inherited_attributes(
                            new \Filament\Support\View\ComponentAttributeBag([
                                'color' => 'danger',
                                'tag' => 'button',
                                'wire:click' => 'resetTableFiltersForm',
                                'wire:loading.remove.delay.' . config('filament.livewire_loading_delay', 'default') => '',
                                'wire:target' => 'resetTableFiltersForm',
                            ])
                        )
                    "
                >
                    {{ __('filament-tables::table.filters.actions.reset.label') }}
                </x-filament::link>
            </div>
        @endif
    </div>

    {{ $form }}

    @if ($applyAction->isVisible() || $resetActionPosition === FiltersResetActionPosition::Footer)
        <div class="fi-ta-filters-actions-ctn">
            @if ($applyAction->isVisible())
                {{ $applyAction }}
            @endif

            @if ($resetActionPosition === FiltersResetActionPosition::Footer)
                <x-filament::button
                    color="danger"
                    wire:click="resetTableFiltersForm"
                    :x-show="$this instanceof \App\Filament\Resources\Orders\Pages\ListOrders ? 'hasFilterValue($wire.tableFilters) || hasFilterValue($wire.tableDeferredFilters)' : null"
                    :x-cloak="$this instanceof \App\Filament\Resources\Orders\Pages\ListOrders ? '' : null"
                >
                    {{ __('filament-tables::table.filters.actions.reset.label') }}
                </x-filament::button>
            @endif
        </div>
    @endif
</div>
