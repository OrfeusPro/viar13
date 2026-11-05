<x-filament-panels::page class="viar-management-page">
    @include('filament.components.management-styles')
    @include('filament.components.database-column-styles')
    <x-filament::section>
        {{ $this->form }}
        <p class="viar-management-muted" style="margin-top:16px">Not Null — значение обязательно. Длина строк и binary по умолчанию 255; decimal: precision,scale (например, 8,2).</p>
        <div class="viar-management-actions" style="margin-top:16px;justify-content:center;flex-wrap:wrap">
            <x-filament::button color="success" wire:click="addColumn" icon="heroicon-o-plus">Добавить колонку</x-filament::button>
            <x-filament::button color="gray" wire:click="addTimestamps" icon="heroicon-o-clock">Добавить timestamps</x-filament::button>
            <x-filament::button color="gray" wire:click="addSoftDeletes" icon="heroicon-o-trash">Добавить soft deletes</x-filament::button>
        </div>
        <div class="viar-management-actions" style="margin-top:24px">
            <x-filament::button wire:click="preview" icon="heroicon-o-code-bracket">Показать SQL</x-filament::button>
            <x-filament::button tag="a" color="gray" href="{{ \App\Filament\Pages\DatabaseTables::getUrl() }}">Отмена</x-filament::button>
        </div>
    </x-filament::section>
    @if($this->sql)
        <x-filament::section>
            <h2 style="margin-bottom:16px">Предварительный SQL</h2>
            <pre style="white-space:pre-wrap;overflow-wrap:anywhere;margin-bottom:20px">{{ implode(";\n", $this->sql) }};</pre>
            @if($this->modelPreview)
                <h3>Модель: {{ $this->modelPreview['class'] }}</h3>
                <pre style="white-space:pre-wrap;overflow-wrap:anywhere;margin:16px 0">{{ $this->modelPreview['source'] }}</pre>
            @endif
            <x-filament::button color="success" wire:click="createTable" icon="heroicon-o-plus">Создать таблицу</x-filament::button>
        </x-filament::section>
    @endif
</x-filament-panels::page>
