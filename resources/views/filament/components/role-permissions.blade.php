<section class="viar-role-permissions" x-data="{ search: '' }" aria-label="Разрешения роли">
    <style>
        .viar-role-permissions { margin-top: 8px; }
        .viar-role-permissions h2 { font-size: 18px; font-weight: 600; }
        .viar-role-permissions .role-toolbar { display:flex; flex-wrap:wrap; gap:12px; align-items:center; margin:16px 0; }
        .viar-role-permissions .role-search { border:1px solid #d1d5db; border-radius:8px; padding:8px 12px; width: min(100%, 28rem); }
        .viar-role-permissions .role-groups { display:grid; grid-template-columns:repeat(auto-fit,minmax(min(100%,320px),1fr)); gap:16px; align-items:start; }
        .viar-role-permissions .role-group { border:1px solid #dbe3ee; border-radius:10px; overflow:hidden; }
        .viar-role-permissions .role-group header { padding:12px 16px; background:#f1f5f9; display:flex; flex-wrap:wrap; gap:8px; align-items:center; justify-content:space-between; }
        .viar-role-permissions .role-options { padding:12px 16px; display:grid; gap:10px; }
        .viar-role-permissions .role-option { display:flex; gap:10px; align-items:center; cursor:pointer; }
        .viar-role-permissions input[type=checkbox] { width:16px; height:16px; accent-color:#2563eb; flex-shrink:0; }
        .viar-role-permissions small { display:block; font-size:12px; color:#64748b; overflow-wrap:anywhere; }
        .dark .viar-role-permissions .role-group { border-color:#374151; }
        .dark .viar-role-permissions .role-group header { background:#1f2937; }
        .dark .viar-role-permissions .role-search { background:#111827; border-color:#374151; }
    </style>
    <h2>Разрешения роли</h2>
    <p style="color:#64748b;font-size:14px;margin-top:4px">Права общие для всех языков. Изменения сохраняются вместе с ролью.</p>
    <div class="role-toolbar">
        <input class="role-search" type="search" x-model="search" aria-label="Поиск разрешений" placeholder="Поиск раздела или разрешения">
        <x-filament::button type="button" size="sm" color="gray" wire:click="selectRolePermissions(null, true)">Выбрать все</x-filament::button>
        <x-filament::button type="button" size="sm" color="gray" wire:click="selectRolePermissions(null, false)">Снять все</x-filament::button>
        <span style="font-size:14px;color:#64748b">Выбрано: {{ count($this->rolePermissionIds) }}</span>
    </div>
    @error('rolePermissionIds')<p role="alert" style="color:#dc2626">{{ $message }}</p>@enderror
    @foreach ($errors->get('rolePermissionIds.*') as $messages)
        @foreach ($messages as $message)<p role="alert" style="color:#dc2626">{{ $message }}</p>@endforeach
    @endforeach
    <div class="role-groups">
        @foreach ($this->rolePermissionGroups() as $group)
            @php($haystack = mb_strtolower($group['title'] . ' ' . $group['table'] . ' ' . implode(' ', array_column($group['permissions'], 'key')) . ' ' . implode(' ', array_column($group['permissions'], 'label'))))
            <section class="role-group" wire:key="role-permission-group-{{ $group['table'] ?: 'system' }}" x-show="!search || @js($haystack).includes(search.toLocaleLowerCase())" aria-label="{{ $group['title'] }}">
                <header>
                    <div><h3 style="font-weight:600">{{ $group['title'] }}</h3>@if ($group['table'])<small>{{ $group['table'] }}</small>@endif</div>
                    <div style="display:flex;gap:8px">
                        <x-filament::button type="button" size="xs" color="gray" wire:click="selectRolePermissions(@js($group['table']), true)" aria-label="Выбрать все: {{ $group['title'] }}">Все</x-filament::button>
                        <x-filament::button type="button" size="xs" color="gray" wire:click="selectRolePermissions(@js($group['table']), false)" aria-label="Снять все: {{ $group['title'] }}">Снять</x-filament::button>
                    </div>
                </header>
                <div class="role-options">
                    @foreach ($group['permissions'] as $permission)
                        <label class="role-option" wire:key="role-permission-{{ $permission['id'] }}">
                            <input type="checkbox" wire:model.live="rolePermissionIds" value="{{ $permission['id'] }}">
                            <span>{{ $permission['label'] }}<small>{{ $permission['key'] }}</small></span>
                        </label>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</section>
