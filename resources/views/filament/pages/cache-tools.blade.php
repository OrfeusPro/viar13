<x-filament-panels::page class="viar-management-page">
    @include('filament.components.management-styles')
    <x-filament::section heading="Очистка кеша приложения">
        <p>Удаляет сохранённые значения кеша приложения.</p>
        <x-filament::button wire:click="run('clear')" wire:loading.attr="disabled" icon="heroicon-o-arrow-path" style="margin-top:20px">Очистить кеш приложения</x-filament::button>
    </x-filament::section>
    <x-filament::section heading="Кеш конфигурации и маршрутов">
        <p>Собирает кеш текущей конфигурации, затем кеш маршрутов.</p>
        <x-filament::button wire:click="run('build')" wire:loading.attr="disabled" icon="heroicon-o-archive-box" style="margin-top:20px">Собрать кеш конфигурации и маршрутов</x-filament::button>
    </x-filament::section>
    @error('operation')<p role="alert" style="color:#dc2626">{{ $message }}</p>@enderror
</x-filament-panels::page>
