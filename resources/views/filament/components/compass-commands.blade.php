@if($lastCommandName !== null)
    <x-filament::section heading="Результат выполнения">
        <p><code>{{ $lastCommandName }}</code> · Код завершения: {{ $commandExitCode }}</p>
        <pre style="margin-top:12px;white-space:pre-wrap;overflow-wrap:anywhere">{{ $commandOutput }}</pre>
        <x-filament::button color="gray" wire:click="clearCommandOutput" style="margin-top:16px">Скрыть результат</x-filament::button>
    </x-filament::section>
@endif
@if($commandName !== null)
    <x-filament::section heading="Выполнение команды">
        <form wire:submit="runCommand">
            <p><code>php artisan {{ $commandName }}</code></p>
            <label for="compass-command-args" style="display:block;margin-top:16px">Дополнительные аргументы</label>
            <input id="compass-command-args" wire:model="commandArgs" maxlength="65535" type="text"
                style="display:block;width:100%;margin-top:8px;border:1px solid #d1d5db;border-radius:8px;padding:10px">
            @error('commandArgs')<p role="alert" style="color:#dc2626">{{ $message }}</p>@enderror
            <x-filament::button type="submit" icon="heroicon-o-command-line" wire:loading.attr="disabled" style="margin-top:20px">Выполнить команду</x-filament::button>
        </form>
    </x-filament::section>
@endif
<x-filament::section heading="Команды Artisan">
    <label for="compass-command-search">Поиск команды</label>
    <input id="compass-command-search" type="search" maxlength="255" wire:model.live.debounce.300ms="commandSearch"
        style="display:block;width:100%;max-width:480px;margin:8px 0 24px;border:1px solid #d1d5db;border-radius:8px;padding:10px">
    @error('commandName')<p role="alert" style="color:#dc2626">{{ $message }}</p>@enderror
    <div class="viar-management-scroll" role="region" aria-label="Каталог команд" tabindex="0">
        <table class="viar-management-table">
            <thead><tr><th>Команда</th><th>Описание</th><th>Действия</th></tr></thead>
            <tbody>
                @forelse($this->commands() as $name => $description)
                    <tr wire:key="command-{{ $name }}">
                        <td><code>{{ $name }}</code></td><td style="white-space:normal;overflow-wrap:anywhere">{{ $description }}</td>
                        <td><x-filament::button color="gray" wire:click="selectCommand({{ \Illuminate\Support\Js::from($name) }})">Выбрать {{ $name }}</x-filament::button></td>
                    </tr>
                @empty<tr><td colspan="3">Команды не найдены.</td></tr>@endforelse
            </tbody>
        </table>
    </div>
</x-filament::section>
