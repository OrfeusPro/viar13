@foreach($nodes as $node)
    @php($item = $node['item'])
    <li data-menu-id="{{ $item->id }}" style="list-style:none">
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;padding:10px;border:1px solid #d1d5db;border-radius:8px;background:#fff">
            @if($canReorder)
                <button type="button" class="menu-drag-handle" aria-label="Перетащить {{ $item->title }}" style="cursor:grab;touch-action:none;padding:8px">
                    <x-filament::icon icon="heroicon-o-bars-3" style="width:20px;height:20px" />
                </button>
            @endif
            <x-filament::icon :icon="\App\Filament\Bread\BreadNavigationIcon::resolve($item->icon_class ?? null)" style="width:20px;height:20px;flex-shrink:0" />
            <div style="flex:1;min-width:100px;overflow-wrap:anywhere">
                <strong>{{ $item->title }}</strong>
                @if($node['missing_parent'] ?? false)
                    <small style="display:block;color:#b45309">Родитель #{{ $item->parent_id }} отсутствует в этом меню. Связь сохранится при сортировке; выберите нового родителя в редактировании или перетащите в нужный раздел.</small>
                @endif
                <small style="display:block;color:#6b7280">{{ $item->route ?: $item->url ?: '—' }}</small>
            </div>
            <span @class(['viar-management-badge', 'is-enabled' => (bool) $item->status])>{{ $item->status ? 'Активен' : 'Скрыт' }}</span>
            <div class="viar-management-actions">
                @if(auth('filament')->user()?->hasPermission('edit_menus'))<x-filament::icon-button icon="heroicon-o-pencil-square" label="Изменить {{ $item->title }}" wire:click="openEdit({{ $item->id }})" />@endif
                @if(auth('filament')->user()?->hasPermission('delete_menus'))<x-filament::icon-button icon="heroicon-o-trash" label="Удалить {{ $item->title }}" color="danger" wire:click="requestDeleteItem({{ $item->id }})" />@endif
            </div>
        </div>
        <ul data-menu-list style="margin:0 0 0 20px;padding:6px 0;min-height:14px">
            @include('filament.components.menu-editor-branch', ['nodes' => $node['children'], 'canReorder' => $canReorder])
        </ul>
    </li>
@endforeach
