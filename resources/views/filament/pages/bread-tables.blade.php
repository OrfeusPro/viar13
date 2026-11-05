<x-filament-panels::page class="viar-management-page">
    @include('filament.components.management-styles')
    <style>.viar-bread-catalog th:last-child,.viar-bread-catalog td:last-child{width:320px;min-width:320px}.viar-bread-catalog .viar-management-actions{flex-wrap:wrap}</style>
    <x-filament::section>
        <div class="viar-management-toolbar">
            <div style="width:420px;max-width:100%">
                <label for="bread-table-search" style="display:block;margin-bottom:8px">Поиск таблицы или раздела</label>
                <x-filament::input.wrapper prefix-icon="heroicon-o-magnifying-glass"><x-filament::input id="bread-table-search" wire:model.live.debounce.300ms="search" /></x-filament::input.wrapper>
            </div>
            <span class="viar-management-muted">Физические таблицы текущей базы данных</span>
        </div>
        <div class="viar-management-scroll" role="region" aria-label="Все таблицы BREAD" tabindex="0">
            <table class="viar-management-table viar-bread-catalog">
                <thead><tr><th>Таблица</th><th>Раздел BREAD</th><th>Состояние</th><th>Действия</th></tr></thead>
                <tbody>
                    @forelse($this->tables() as $table)
                        <tr wire:key="bread-table-{{ $table['name'] }}">
                            <td><code>{{ $table['name'] }}</code></td>
                            <td>{{ $table['title'] ?: '—' }} @if($table['slug'])<div class="viar-management-muted">{{ $table['slug'] }}</div>@endif</td>
                            <td><span class="viar-management-badge {{ $table['configured'] && $table['available'] ? 'is-enabled' : '' }}">{{ ! $table['configured'] ? 'Без BREAD' : ($table['available'] ? 'BREAD настроен' : 'Модель недоступна') }}</span></td>
                            <td><div class="viar-management-actions">
                                @if($table['browse_url'])<x-filament::button tag="a" href="{{ $table['browse_url'] }}" size="sm" color="gray" icon="heroicon-o-eye">Открыть</x-filament::button>@endif
                                @if($table['edit_url'])<x-filament::button tag="a" href="{{ $table['edit_url'] }}" size="sm" icon="heroicon-o-pencil-square">Редактировать BREAD</x-filament::button>@endif
                                @if($table['create_url'])<x-filament::button tag="a" href="{{ $table['create_url'] }}" size="sm" color="success" icon="heroicon-o-plus">Добавить BREAD</x-filament::button>@endif
                            </div></td>
                        </tr>
                    @empty<tr><td colspan="4">Таблицы не найдены.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
