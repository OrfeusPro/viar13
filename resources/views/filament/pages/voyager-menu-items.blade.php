<x-filament-panels::page class="viar-management-page">
    @include('filament.components.management-styles')
    @assets
    <script src="{{ asset('js/filament/menu-tree-editor.js') }}"></script>
    @endassets
    <x-filament::section>
        <div class="viar-management-toolbar">
        <div class="viar-management-tabs" role="group" aria-label="Выбор меню">
            @foreach ($this->menus() as $id => $name)
                <x-filament::button size="sm" :color="$menuId === $id ? 'primary' : 'gray'" :aria-pressed="$menuId === $id ? 'true' : 'false'" wire:click="selectMenu({{ $id }})">{{ $name }}</x-filament::button>
            @endforeach
        </div>
        <div class="viar-management-actions" style="flex-wrap:wrap;gap:8px">
        @if(auth('filament')->user()?->hasPermission('add_menus'))
            <x-filament::button color="gray" icon="heroicon-o-plus" wire:click="openCreateMenu">Создать меню</x-filament::button>
        @endif
        @if($menuId && auth('filament')->user()?->hasPermission('edit_menus'))
            <x-filament::button color="gray" icon="heroicon-o-pencil-square" wire:click="openRenameMenu">Переименовать меню</x-filament::button>
        @endif
        @if($menuId && auth('filament')->user()?->hasPermission('delete_menus'))
            <x-filament::button color="danger" icon="heroicon-o-trash" x-on:click="$dispatch('open-modal', {id: 'delete-selected-menu'})">Удалить меню</x-filament::button>
            <x-filament::modal id="delete-selected-menu" width="md">
                <x-slot name="heading">Удаление меню</x-slot>
                <p>Удалить меню «{{ $this->menus()[$menuId] ?? '' }}», все его пункты и их переводы? Это действие нельзя отменить.</p>
                <x-slot name="footer">
                    <x-filament::button color="danger" wire:click="deleteMenu({{ $menuId }})" wire:loading.attr="disabled" wire:target="deleteMenu">Удалить меню и пункты</x-filament::button>
                    <x-filament::button color="gray" x-on:click="$dispatch('close-modal', {id: 'delete-selected-menu'})">Отмена удаления</x-filament::button>
                </x-slot>
            </x-filament::modal>
        @endif
        @if ($menuId && ! $editing && ! $menuEditing && auth('filament')->user()?->hasPermission('add_menus'))
            <x-filament::button icon="heroicon-o-plus" wire:click="openCreate">Добавить пункт</x-filament::button>
        @endif
        </div>
        </div>
        @if($menuEditing)
            <form wire:submit="saveMenuName" class="viar-management-form" style="margin-bottom:24px">
                <label for="menu-name">Имя меню</label>
                <input id="menu-name" wire:model="menuName" required maxlength="255" style="display:block;width:100%;border:1px solid #d1d5db;padding:10px;border-radius:8px">
                @error('menuName')<p role="alert" style="color:#dc2626">{{ $message }}</p>@enderror
                <div class="viar-management-form-actions"><x-filament::button type="submit">Сохранить меню</x-filament::button><x-filament::button type="button" color="gray" wire:click="cancelMenu">Отмена</x-filament::button></div>
            </form>
        @elseif(!$menuId)
            <p>Меню ещё не созданы.</p>
        @endif
        @if ($editing)
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:24px">
                @foreach (app(\App\Filament\Bread\BreadRegistry::class)->locales() as $language)
                    <x-filament::button size="sm" :color="$locale === $language ? 'primary' : 'gray'" wire:click="changeLocale('{{ $language }}')">{{ strtoupper($language) }}</x-filament::button>
                @endforeach
            </div>
            <form wire:submit="save" class="viar-management-form">
                {{ $this->form }}
                @foreach($errors->getMessages() as $field => $messages)
                    @if(str_starts_with($field, 'titleTranslations'))
                        @foreach($messages as $message)<p role="alert" style="color:#dc2626">{{ $message }}</p>@endforeach
                    @endif
                @endforeach
                <div class="viar-management-form-actions"><x-filament::button type="submit" wire:loading.attr="disabled">Сохранить</x-filament::button><x-filament::button color="gray" type="button" wire:click="cancel">Отмена</x-filament::button></div>
            </form>
        @else
            @php($canReorder = (bool) auth('filament')->user()?->hasPermission('edit_menus'))
            @error('menuOrder')<p role="alert" style="color:#dc2626;margin-bottom:12px">{{ $message }}</p>@enderror
            <div wire:key="menu-tree-{{ $menuId }}-{{ md5(json_encode($this->items())) }}"
                x-data="menuTreeEditor($wire, {{ $menuId }}, {{ $canReorder ? 'true' : 'false' }})" aria-label="Дерево пунктов меню">
                @if($canReorder)<p style="margin-bottom:12px;color:#6b7280">Перетащите пункт за ручку, чтобы изменить порядок или вложенность. Изменения сохраняются автоматически.</p>@endif
                <p role="status" x-show="saving" x-cloak>Сохранение порядка…</p>
                <p role="alert" x-show="error" x-text="error" x-cloak style="color:#dc2626"></p>
                <ul data-menu-list style="padding:0;margin:0;min-height:32px">
                    @include('filament.components.menu-editor-branch', ['nodes' => $this->menuTree(), 'canReorder' => $canReorder])
                </ul>
            </div>
        @endif
        @if(auth('filament')->user()?->hasPermission('delete_menus'))
            @php($pendingItem = $this->pendingDeleteItem())
            <x-filament::modal id="delete-menu-item" width="md" x-on:modal-closed="$wire.dismissDeleteItem()">
                <x-slot name="heading">Удаление пункта меню</x-slot>
                @if($pendingItem)
                    <p>Удалить пункт «{{ $pendingItem->title }}» и его переводы? Это действие нельзя отменить.</p>
                    @if(\Illuminate\Support\Facades\DB::table('menu_items')->where('menu_id', $menuId)->where('parent_id', $pendingItem->id)->exists())
                        <p style="margin-top:12px">Дочерние пункты сохранятся и будут доступны в дереве на верхнем уровне.</p>
                    @endif
                @endif
                <x-slot name="footer">
                    <x-filament::button color="danger" wire:click="confirmDeleteItem" wire:loading.attr="disabled">Удалить пункт</x-filament::button>
                    <x-filament::button color="gray" wire:click="cancelDeleteItem">Отмена удаления пункта</x-filament::button>
                </x-slot>
            </x-filament::modal>
        @endif
    </x-filament::section>
</x-filament-panels::page>
