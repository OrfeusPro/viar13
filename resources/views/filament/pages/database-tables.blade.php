<x-filament-panels::page class="viar-management-page">
    @include('filament.components.management-styles')
    <style>
        .viar-database-catalog td { padding: 8px 16px; }
        .viar-database-catalog th:last-child,.viar-database-catalog td:last-child { width: 230px; min-width: 230px; }
        .viar-database-catalog td:first-child { width: 30%; }
        .viar-database-catalog .viar-management-actions { white-space: nowrap; }
        .viar-database-catalog .fi-btn { white-space: nowrap; }
        .viar-database-catalog .database-bread-actions { min-width: 375px; }
        .viar-database-catalog .database-table-link { color: #2563eb; text-decoration: underline; text-underline-offset: 3px; }
        .viar-database-catalog tbody tr:nth-child(even) > td { background: #f8fafc; }
        .dark .viar-database-catalog tbody tr:nth-child(even) > td { background: #202630; }
    </style>
    <x-filament::section>
        <div class="viar-management-toolbar">
            <x-filament::button tag="a" href="{{ \App\Filament\Pages\DatabaseCreate::getUrl() }}" icon="heroicon-o-plus">Создать таблицу</x-filament::button>
            <div style="width:420px;max-width:100%">
                <label for="database-search" style="display:block;margin-bottom:8px">Поиск таблицы или раздела</label>
                <x-filament::input.wrapper prefix-icon="heroicon-o-magnifying-glass"><x-filament::input id="database-search" wire:model.live.debounce.300ms="search" /></x-filament::input.wrapper>
            </div>
            <span class="viar-management-muted">Просмотр структуры текущей базы данных</span>
        </div>
        <div class="viar-management-scroll" role="region" aria-label="Таблицы базы данных" tabindex="0">
            <table class="viar-management-table viar-database-catalog">
                <thead><tr><th>Таблица</th><th>BREAD</th><th>Действия с таблицей</th></tr></thead>
                <tbody>
                @forelse($this->tables() as $table)
                    <tr wire:key="database-table-{{ $table['name'] }}">
                        <td><a class="database-table-link" href="{{ \App\Filament\Pages\DatabaseTables::getUrl().'?table='.rawurlencode($table['name']) }}">{{ $table['name'] }}</a></td>
                        <td class="database-bread-actions"><div class="viar-management-actions">
                            @if($table['browse_url'])<x-filament::button tag="a" size="sm" color="warning" icon="heroicon-o-eye" href="{{ $table['browse_url'] }}">Открыть BREAD</x-filament::button>@endif
                            @if(\App\Filament\Pages\BreadMetadata::canAccess())
                                <x-filament::button tag="a" size="sm" color="{{ $table['bread_id'] ? 'gray' : 'success' }}" icon="heroicon-o-adjustments-horizontal" href="{{ \App\Filament\Pages\BreadMetadata::getUrl().($table['bread_id'] ? '?section='.$table['bread_id'] : '?create='.rawurlencode($table['name'])) }}">{{ $table['bread_id'] ? 'Редактировать BREAD' : 'Добавить BREAD' }}</x-filament::button>
                                @if($table['bread_id'])<x-filament::button size="sm" color="danger" icon="heroicon-o-trash" wire:click="prepareBreadDeletion({{ \Illuminate\Support\Js::from($table['name']) }})">Удалить BREAD</x-filament::button>@endif
                            @endif
                            @if(! $table['bread_id'] && ! \App\Filament\Pages\BreadMetadata::canAccess())<span class="viar-management-muted">Без BREAD</span>@endif
                        </div></td>
                        <td><div class="viar-management-actions">
                            <x-filament::button tag="a" size="sm" color="gray" icon="heroicon-o-eye" href="{{ \App\Filament\Pages\DatabaseTables::getUrl().'?table='.rawurlencode($table['name']) }}">Структура</x-filament::button>
                            <x-filament::button tag="a" size="sm" color="gray" icon="heroicon-o-pencil-square" href="{{ \App\Filament\Pages\DatabaseEdit::getUrl().'?table='.rawurlencode($table['name']) }}">Изменить</x-filament::button>
                            <x-filament::button tag="a" size="sm" color="danger" icon="heroicon-o-trash" href="{{ \App\Filament\Pages\DatabaseEdit::getUrl().'?table='.rawurlencode($table['name']).'&action=delete#database-delete' }}">Удалить</x-filament::button>
                        </div></td>
                    </tr>
                @empty<tr><td colspan="3">Таблицы не найдены.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
    <x-filament::modal id="delete-database-bread" width="lg">
        <x-slot name="heading">Удаление BREAD: {{ $this->breadDeletionTable }}</x-slot>
        <p>Будут удалены настройки BREAD, определения полей, их переводы и права раздела. Физическая таблица, записи и изображения сохранятся.</p>
        <div style="margin-top:16px"><label for="bread-delete-confirmation">Введите точное имя таблицы</label>
            <x-filament::input.wrapper><x-filament::input id="bread-delete-confirmation" wire:model="breadConfirmation" /></x-filament::input.wrapper>
            @error('breadConfirmation')<p class="text-danger-600">{{ $message }}</p>@enderror
        </div>
        <x-slot name="footerActions">
            <x-filament::button color="danger" wire:click="deleteBread" wire:confirm="Удалить настройки BREAD и права этого раздела?">Удалить BREAD</x-filament::button>
            <x-filament::button color="gray" x-on:click="$dispatch('close-modal', {id: 'delete-database-bread'})">Отмена</x-filament::button>
        </x-slot>
    </x-filament::modal>
    @if($structure = $this->structure())
        <x-filament::section>
            <div class="viar-management-toolbar"><h2>Структура: {{ $this->selectedTable }}</h2><x-filament::button tag="a" href="{{ \App\Filament\Pages\DatabaseTables::getUrl() }}" color="gray" icon="heroicon-o-x-mark">Закрыть</x-filament::button></div>
            <div class="viar-management-scroll" role="region" aria-label="Колонки таблицы" tabindex="0">
                <table class="viar-management-table"><thead><tr><th>Поле</th><th>Тип</th><th>NULL</th><th>По умолчанию</th><th>Дополнительно</th></tr></thead><tbody>
                @foreach($structure['columns'] as $column)
                    <tr><td>{{ $column['name'] }}</td><td>{{ $column['type'] }}</td><td>{{ $column['nullable'] ? 'Да' : 'Нет' }}</td><td>{{ $column['default'] ?? '—' }}</td><td>{{ ($column['auto_increment'] ?? false) ? 'AUTO INCREMENT' : '' }} @if($column['generation'] ?? null){{ $column['generation']['expression'] }}@endif {{ $column['comment'] ?? '' }}</td></tr>
                @endforeach
                </tbody></table>
            </div>
            <h3 style="margin:24px 0 12px">Индексы</h3>
            <div class="viar-management-scroll" role="region" aria-label="Индексы таблицы" tabindex="0"><table class="viar-management-table"><thead><tr><th>Имя</th><th>Колонки</th><th>Тип</th></tr></thead><tbody>
                @forelse($structure['indexes'] as $index)<tr><td>{{ $index['name'] }}</td><td>{{ implode(', ', $index['columns']) }}</td><td>{{ $index['primary'] ? 'PRIMARY' : ($index['unique'] ? 'UNIQUE' : ($index['type'] ?? 'INDEX')) }}</td></tr>@empty<tr><td colspan="3">Индексов нет.</td></tr>@endforelse
            </tbody></table></div>
            <h3 style="margin:24px 0 12px">Внешние ключи</h3>
            <div class="viar-management-scroll" role="region" aria-label="Внешние ключи таблицы" tabindex="0"><table class="viar-management-table"><thead><tr><th>Имя</th><th>Колонки</th><th>Ссылается на</th><th>ON UPDATE</th><th>ON DELETE</th></tr></thead><tbody>
                @forelse($structure['foreign_keys'] as $key)<tr><td>{{ $key['name'] ?? '—' }}</td><td>{{ implode(', ', $key['columns']) }}</td><td>{{ $key['foreign_table'] }} ({{ implode(', ', $key['foreign_columns']) }})</td><td>{{ $key['on_update'] }}</td><td>{{ $key['on_delete'] }}</td></tr>@empty<tr><td colspan="5">Внешних ключей нет.</td></tr>@endforelse
            </tbody></table></div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
