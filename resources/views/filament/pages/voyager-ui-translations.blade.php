<x-filament-panels::page>
    <x-filament::section>
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px">
            <select wire:change="selectGroup($event.target.value)" aria-label="Группа переводов" class="fi-input" style="max-width:280px">
                @foreach ($this->groups() as $name)
                    <option value="{{ $name }}" @selected($group === $name)>{{ $name }}</option>
                @endforeach
            </select>
            @foreach ($this->locales() as $language)
                <x-filament::button size="sm" :color="$locale === $language ? 'primary' : 'gray'" wire:click="selectLocale('{{ $language }}')">{{ strtoupper($language) }}</x-filament::button>
            @endforeach
        </div>
        @if ($key !== null)
            <form wire:submit="save" style="display:grid;gap:16px">
                {{ $this->form }}
                <div style="display:flex;gap:8px"><x-filament::button type="submit">Сохранить и опубликовать</x-filament::button><x-filament::button type="button" color="gray" wire:click="cancel">Отмена</x-filament::button></div>
            </form>
        @else
            <input wire:model.live.debounce.400ms="search" type="search" aria-label="Поиск ключа" placeholder="Поиск ключа" class="fi-input" style="max-width:400px;margin-bottom:16px">
            <div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse">
                <thead><tr><th>Ключ</th><th>Значение {{ strtoupper($locale) }}</th><th></th></tr></thead>
                <tbody>
                    @foreach ($this->entries() as $entry)
                        <tr style="border-top:1px solid #d1d5db"><td style="padding:8px">{{ $entry->key }}</td><td style="padding:8px;max-width:550px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $entry->value }}</td><td><x-filament::button size="sm" wire:click="openEdit({{ \Illuminate\Support\Js::from($entry->key) }})">Изменить</x-filament::button></td></tr>
                    @endforeach
                </tbody>
            </table></div>
            {{ $this->entries()->links() }}
        @endif
    </x-filament::section>
</x-filament-panels::page>
