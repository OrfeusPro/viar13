<x-filament-panels::page>
    <x-filament::section>
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px">
            @foreach ($this->menus() as $id => $name)
                <x-filament::button size="sm" :color="$menuId === $id ? 'primary' : 'gray'" wire:click="selectMenu({{ $id }})">{{ $name }}</x-filament::button>
            @endforeach
        </div>
        @if ($editing)
            <div style="display:flex;gap:8px;margin-bottom:16px">
                @foreach (app(\App\Filament\Bread\BreadRegistry::class)->locales() as $language)
                    <x-filament::button size="sm" :color="$locale === $language ? 'primary' : 'gray'" wire:click="changeLocale('{{ $language }}')" :disabled="$itemId === null && $language !== config('voyager.multilingual.default', 'en')">{{ strtoupper($language) }}</x-filament::button>
                @endforeach
            </div>
            <form wire:submit="save" style="display:grid;gap:16px">
                {{ $this->form }}
                <div style="display:flex;gap:8px"><x-filament::button type="submit">Сохранить</x-filament::button><x-filament::button color="gray" type="button" wire:click="cancel">Отмена</x-filament::button></div>
            </form>
        @else
            @if (auth('filament')->user()?->hasPermission('add_menus'))
                <div style="margin-bottom:16px"><x-filament::button wire:click="openCreate">Добавить пункт</x-filament::button></div>
            @endif
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse">
                    <thead><tr><th>ID</th><th>Заголовок</th><th>Родитель</th><th>Порядок</th><th>Route / URL</th><th>Действия</th></tr></thead>
                    <tbody>
                    @php($items = $this->items())
                    @php($titles = collect($items)->pluck('title', 'id'))
                    @foreach ($items as $item)
                        <tr style="border-top:1px solid #d1d5db"><td>{{ $item->id }}</td><td>{{ $item->title }}</td><td>{{ $titles->get($item->parent_id) }}</td><td>{{ $item->order }}</td><td>{{ $item->route ?: $item->url }}</td><td>
                            @if (auth('filament')->user()?->hasPermission('edit_menus'))<x-filament::button size="sm" wire:click="openEdit({{ $item->id }})">Изменить</x-filament::button>@endif
                            @if (auth('filament')->user()?->hasPermission('delete_menus'))<x-filament::button size="sm" color="danger" wire:click="deleteItem({{ $item->id }})" wire:confirm="Удалить пункт меню?">Удалить</x-filament::button>@endif
                        </td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
