<x-filament-panels::page class="viar-management-page">
    @include('filament.components.management-styles')
    @include('filament.components.database-column-styles')
    <x-filament::section>
        @if($this->structureNotices !== [])
            <aside aria-label="Ограничения структуры таблицы" style="margin-bottom:24px;padding:16px 20px;border:1px solid #fcd34d;border-radius:12px;background:#fffbeb;color:#78350f">
                <h2 style="font-size:15px;font-weight:600;margin-bottom:8px">Особенности этой таблицы</h2>
                <ul style="margin:0;padding-left:20px;display:grid;gap:8px">
                    @foreach($this->structureNotices as $notice)<li>{{ $notice }}</li>@endforeach
                </ul>
            </aside>
        @endif
        {{ $this->form }}
        <div class="viar-management-actions" style="margin-top:24px;justify-content:center;flex-wrap:wrap">
            <x-filament::button color="success" wire:click="addColumn" icon="heroicon-o-plus">Добавить колонку</x-filament::button>
            <x-filament::button color="success" wire:click="addTimestamps" icon="heroicon-o-plus">Добавить timestamps</x-filament::button>
            <x-filament::button color="success" wire:click="addSoftDeletes" icon="heroicon-o-plus">Добавить soft deletes</x-filament::button>
        </div>
        <div class="viar-management-actions" style="margin-top:24px;padding-top:20px;border-top:1px solid #e5e7eb;justify-content:flex-end;flex-wrap:wrap">
            <x-filament::button color="gray" wire:click="preview" icon="heroicon-o-code-bracket">Показать SQL</x-filament::button>
            <x-filament::button tag="a" color="gray" href="{{ \App\Filament\Pages\DatabaseTables::getUrl() }}">Отмена</x-filament::button>
            <x-filament::button wire:click="prepareSaveStructure" icon="heroicon-o-check">Сохранить таблицу</x-filament::button>
        </div>
        <details style="margin-top:16px" class="viar-management-muted"><summary>Параметры и ограничения редактора</summary>
            <p>Not Null — значение обязательно. Длины TEXT и точность TIMESTAMP показаны справочно. Decimal: precision,scale (например, 8,2).</p>
            <p>Изменение структуры не обновляет BREAD автоматически. Порядок существующих колонок в базе не меняется. Изменение связанных колонок и составных индексов ограничено.</p>
        </details>
    </x-filament::section>
    @if($this->sql)
        <x-filament::section>
            <h2>Предварительный SQL</h2>
            <pre style="white-space:pre-wrap;overflow-wrap:anywhere;margin:16px 0">{{ implode(";\n", $this->sql) }};</pre>
            <x-filament::button wire:click="saveStructure" wire:confirm="Применить изменения структуры? Удаление колонок удаляет их данные." icon="heroicon-o-check">Сохранить структуру</x-filament::button>
        </x-filament::section>
    @endif
    <x-filament::modal id="database-save-structure" width="2xl">
        <x-slot name="heading">Сохранить изменения таблицы</x-slot>
        <p>Проверьте изменения. Удаление колонок удаляет их данные.</p>
        <pre style="white-space:pre-wrap;overflow-wrap:anywhere;margin-top:16px">{{ implode(";\n", $this->sql) }};</pre>
        <x-slot name="footerActions">
            <x-filament::button wire:click="saveStructure">Сохранить</x-filament::button>
            <x-filament::button color="gray" x-on:click="$dispatch('close-modal', {id: 'database-save-structure'})">Отмена</x-filament::button>
        </x-slot>
    </x-filament::modal>
    <x-filament::section>
        <details @if($this->showDeletion || $this->dropSql) open @endif><summary id="database-delete">Удаление таблицы</summary>
        <div style="margin-top:16px">
            <x-filament::button color="danger" wire:click="previewDrop" icon="heroicon-o-trash">Показать SQL удаления</x-filament::button>
        </div>
        @if($this->dropSql)
            <pre style="white-space:pre-wrap;overflow-wrap:anywhere;margin:16px 0">{{ implode(";\n", $this->dropSql) }};</pre>
            <label for="database-drop-confirmation">Введите {{ $this->originalTable }} для удаления таблицы и всех её данных</label>
            <x-filament::input.wrapper><x-filament::input id="database-drop-confirmation" wire:model="dropConfirmation" /></x-filament::input.wrapper>
            @error('dropConfirmation')<p>{{ $message }}</p>@enderror
            <div style="margin-top:16px"><x-filament::button color="danger" wire:click="dropTable" wire:confirm="Удалить таблицу и все её данные безвозвратно?">Удалить таблицу</x-filament::button></div>
        @endif
        </details>
    </x-filament::section>
</x-filament-panels::page>
