<x-filament-panels::page class="viar-management-page">
    @include('filament.components.management-styles')
    <x-filament::section>
        <div class="viar-management-toolbar">
        <div class="viar-management-tabs" role="group" aria-label="Выбор меню">
            @foreach ($this->menus() as $id => $name)
                <x-filament::button size="sm" :color="$menuId === $id ? 'primary' : 'gray'" :aria-pressed="$menuId === $id ? 'true' : 'false'" wire:click="selectMenu({{ $id }})">{{ $name }}</x-filament::button>
            @endforeach
        </div>
        @if (! $editing && auth('filament')->user()?->hasPermission('add_menus'))
            <x-filament::button icon="heroicon-o-plus" wire:click="openCreate">Добавить пункт</x-filament::button>
        @endif
        </div>
        @if ($editing)
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:24px">
                @foreach (app(\App\Filament\Bread\BreadRegistry::class)->locales() as $language)
                    <x-filament::button size="sm" :color="$locale === $language ? 'primary' : 'gray'" wire:click="changeLocale('{{ $language }}')" :disabled="$itemId === null && $language !== config('voyager.multilingual.default', 'en')">{{ strtoupper($language) }}</x-filament::button>
                @endforeach
            </div>
            <form wire:submit="save" class="viar-management-form">
                {{ $this->form }}
                <div class="viar-management-form-actions"><x-filament::button type="submit">Сохранить</x-filament::button><x-filament::button color="gray" type="button" wire:click="cancel">Отмена</x-filament::button></div>
            </form>
        @else
            <div class="viar-management-scroll" tabindex="0" role="region" aria-label="Таблица пунктов меню">
                <table class="viar-management-table">
                    <thead><tr><th scope="col">ID</th><th scope="col">Заголовок</th><th scope="col">Родитель</th><th scope="col">Порядок</th><th scope="col">Route / URL</th><th scope="col">Статус</th><th scope="col">Действия</th></tr></thead>
                    <tbody>
                    @php($items = $this->items())
                    @php($titles = collect($items)->pluck('title', 'id'))
                    @foreach ($items as $item)
                        <tr wire:key="menu-item-{{ $item->id }}"><td class="viar-management-muted">{{ $item->id }}</td><td><div class="viar-management-title"><x-filament::icon :icon="\App\Filament\Bread\BreadNavigationIcon::resolve($item->icon_class ?? null)" />{{ $item->title }}</div></td><td class="viar-management-muted">{{ $titles->get($item->parent_id) ?: '—' }}</td><td>{{ $item->order }}</td><td class="viar-management-route"><code class="viar-management-code">{{ $item->route ?: $item->url ?: '—' }}</code></td><td><span @class(['viar-management-badge', 'is-enabled' => (bool) $item->status])>{{ $item->status ? 'Активен' : 'Скрыт' }}</span></td><td><div class="viar-management-actions">
                            @if (auth('filament')->user()?->hasPermission('edit_menus'))<x-filament::icon-button icon="heroicon-o-pencil-square" label="Изменить" wire:click="openEdit({{ $item->id }})" />@endif
                            @if (auth('filament')->user()?->hasPermission('delete_menus'))<x-filament::icon-button icon="heroicon-o-trash" label="Удалить" color="danger" wire:click="deleteItem({{ $item->id }})" wire:confirm="Удалить пункт меню?" />@endif
                        </div>
                        </td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
