@php($logData = $this->logData())
<x-filament::section heading="Журналы приложения">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,200px),1fr));gap:16px;margin-bottom:24px">
        <label>Файл журнала
            <select wire:model.live="logFile" style="display:block;width:100%;margin-top:8px;border-radius:8px">
                @forelse($this->logFiles() as $name)<option value="{{ $name }}">{{ $name }}</option>
                @empty<option value="">Журналов нет</option>@endforelse
            </select>
        </label>
        <label>Поиск в журнале<input type="search" wire:model.live.debounce.500ms="logSearch" maxlength="255" style="display:block;width:100%;margin-top:8px;border-radius:8px"></label>
        <label>Уровень
            <select wire:model.live="logLevel" style="display:block;width:100%;margin-top:8px;border-radius:8px">
                <option value="">Все</option>
                @foreach(\App\Services\Admin\CompassLogReader::LEVELS as $level)<option value="{{ $level }}">{{ $level }}</option>@endforeach
            </select>
        </label>
        <label>Записей на странице
            <select wire:model.live="logPerPage" style="display:block;width:100%;margin-top:8px;border-radius:8px">
                @foreach([10,25,50,100] as $size)<option value="{{ $size }}">{{ $size }}</option>@endforeach
            </select>
        </label>
    </div>
    @if(isset($logData['error']))<p role="alert">{{ $logData['error'] }}</p>@endif
    <div class="viar-management-scroll" role="region" aria-label="Записи журнала" tabindex="0">
        <table class="viar-management-table">
            <thead><tr><th>Уровень</th><th>Контекст</th><th>Дата</th><th>Содержание</th></tr></thead>
            <tbody>
                @forelse($logData['entries'] as $entry)
                    <tr wire:key="log-{{ md5((string) $logFile) }}-{{ $entry['line'] }}">
                        <td>{{ $entry['level'] }}</td><td>{{ $entry['context'] }}</td><td style="white-space:nowrap">{{ $entry['date'] }}</td>
                        <td style="max-width:800px;overflow-wrap:anywhere;white-space:normal">
                            {{ $entry['text'] }}
                            @if($entry['stack'] !== '')<details style="margin-top:8px"><summary>Подробности</summary><pre style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $entry['stack'] }}</pre></details>@endif
                        </td>
                    </tr>
                @empty<tr><td colspan="4">Записи не найдены.</td></tr>@endforelse
            </tbody>
        </table>
    </div>
    <div class="viar-management-actions" style="flex-wrap:wrap;margin-top:20px;gap:16px">
        <span role="status">Найдено: {{ $logData['total'] }}. Страница {{ $logData['page'] }} из {{ $logData['last_page'] }}.</span>
        <x-filament::button color="gray" wire:click="changeLogPage({{ $logData['page'] - 1 }})" :disabled="$logData['page'] <= 1">Предыдущая</x-filament::button>
        <x-filament::button color="gray" wire:click="changeLogPage({{ $logData['page'] + 1 }})" :disabled="$logData['page'] >= $logData['last_page']">Следующая</x-filament::button>
    </div>
    @if($logFile !== null && in_array($logFile, $this->logFiles(), true))
        <div class="viar-management-actions" style="flex-wrap:wrap;gap:12px;margin-top:24px">
            <x-filament::button tag="a" :href="route('filament.admin.pages.compass.download', ['file' => $logFile])" color="gray" icon="heroicon-o-arrow-down-tray">Скачать журнал</x-filament::button>
            <x-filament::button color="danger" icon="heroicon-o-trash" wire:click="requestLogDeletion">Удалить журнал</x-filament::button>
            @if(count($this->logFiles()) > 1)<x-filament::button color="danger" wire:click="requestLogDeletion(true)">Удалить все журналы</x-filament::button>@endif
        </div>
    @endif
</x-filament::section>
<x-filament::modal id="delete-compass-logs" width="md" x-on:modal-closed="$wire.dismissLogDeletion()">
    <x-slot name="heading">Удаление журналов</x-slot>
    <p>Удалить выбранные файлы? Это действие нельзя отменить.</p>
    <ul style="margin-top:12px;overflow-wrap:anywhere">
        @foreach($logsToDelete as $name)<li>{{ $name }}</li>@endforeach
    </ul>
    @error('logDelete')<p role="alert" style="color:#dc2626;margin-top:12px">{{ $message }}</p>@enderror
    <x-slot name="footer">
        <x-filament::button color="danger" wire:click="confirmLogDeletion" wire:loading.attr="disabled">Подтвердить удаление журналов</x-filament::button>
        <x-filament::button color="gray" wire:click="cancelLogDeletion">Отмена удаления журналов</x-filament::button>
    </x-slot>
</x-filament::modal>
