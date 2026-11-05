<x-filament-panels::page class="viar-management-page">
    @include('filament.components.management-styles')
    <x-filament::section>
        @if(auth('filament')->user()?->hasPermission('add_settings'))
            <x-filament::button wire:click="openCreate" icon="heroicon-o-plus" style="margin-bottom:24px">Добавить настройку</x-filament::button>
        @endif
        @if ($creating || $settingId !== null || $editingAll)
            <form wire:submit="{{ $editingAll ? 'saveAll' : ($creating ? 'createSetting' : 'save') }}" class="viar-management-form">
                {{ $this->form }}
                <div class="viar-management-form-actions"><x-filament::button type="submit" wire:loading.attr="disabled">{{ $editingAll ? 'Сохранить настройки' : 'Сохранить' }}</x-filament::button><x-filament::button type="button" color="gray" wire:click="cancel">Отмена</x-filament::button></div>
            </form>
        @else
            @php($groups = $this->groups())
            @if($groups !== [] && auth('filament')->user()?->hasPermission('edit_settings'))
                <x-filament::button wire:click="openEditAll" icon="heroicon-o-pencil-square" style="margin-bottom:24px">Редактировать все настройки</x-filament::button>
            @endif
            <div x-data="{ group: $wire.entangle('activeGroup') }">
                <div class="viar-management-tabs" role="tablist" aria-label="Группы настроек">
                    @foreach ($groups as $group => $settings)
                        <button type="button" class="viar-management-tab" role="tab" id="settings-tab-{{ $loop->index }}" aria-controls="settings-panel-{{ $loop->index }}" x-bind:aria-selected="group === @js($group)" x-on:click="group = @js($group)">{{ $group ?: 'Общие' }} <span style="margin-left:6px;opacity:.7">{{ count($settings) }}</span></button>
                    @endforeach
                </div>
                @if($groups === [])<p>Настройки ещё не созданы.</p>@endif
                @foreach ($groups as $group => $settings)
                    <div role="tabpanel" id="settings-panel-{{ $loop->index }}" aria-labelledby="settings-tab-{{ $loop->index }}" x-show="group === @js($group)" x-cloak style="margin-top:24px">
                        <div class="viar-management-scroll" tabindex="0" role="region" aria-label="Таблица настроек {{ $group }}">
                            <table class="viar-management-table">
                                <thead><tr><th scope="col">Настройка</th><th scope="col">Ключ</th><th scope="col">Значение</th><th scope="col">Действия</th></tr></thead>
                                <tbody>
                                @foreach ($settings as $setting)
                                    <tr wire:key="setting-row-{{ $setting->id }}">
                                        <td><span class="viar-setting-label">{{ $setting->display_name ?: $setting->key }}</span></td>
                                        <td><code class="viar-management-code">{{ $setting->key }}</code></td>
                                        <td class="viar-management-setting-value">
                                            @if ($setting->type === 'image')
                                                @php($imageUrl = \App\Filament\Bread\BreadImage::urls($setting->value)[0] ?? null)
                                                @if ($imageUrl)<a href="{{ $imageUrl }}" target="_blank" rel="noopener noreferrer"><img class="viar-setting-image" src="{{ $imageUrl }}" alt="{{ $setting->display_name }}" loading="lazy"></a>@else<span class="viar-management-muted">Изображение не задано</span>@endif
                                            @elseif ($setting->type === 'file')
                                                @forelse(\App\Filament\Bread\BreadBrowseFiles::links($setting->value) as $file)
                                                    <a href="{{ $file['url'] }}" target="_blank" rel="noopener noreferrer" style="display:block;color:#2563eb">{{ $file['name'] ?: 'Скачать файл' }}</a>
                                                @empty<span class="viar-management-muted">Файл не задан</span>@endforelse
                                            @elseif ($setting->type === 'checkbox')
                                                <span @class(['viar-management-badge', 'is-enabled' => $setting->value == 1])>{{ $setting->value == 1 ? 'Включено' : 'Выключено' }}</span>
                                            @elseif ($setting->type === 'code_editor')
                                                <code class="viar-management-code">{{ \Illuminate\Support\Str::limit((string) $setting->value, 240) }}</code>
                                            @else
                                                {{ \Illuminate\Support\Str::limit((string) $setting->value, 600) ?: '—' }}
                                            @endif
                                        </td>
                                        <td><div class="viar-management-actions" style="flex-wrap:wrap">
                                            @if(auth('filament')->user()?->hasPermission('edit_settings'))
                                                <x-filament::icon-button icon="heroicon-o-arrow-up" label="Выше {{ $setting->display_name }}" wire:click="moveSetting({{ $setting->id }}, 'up')" />
                                                <x-filament::icon-button icon="heroicon-o-arrow-down" label="Ниже {{ $setting->display_name }}" wire:click="moveSetting({{ $setting->id }}, 'down')" />
                                                <x-filament::icon-button icon="heroicon-o-pencil-square" label="Изменить {{ $setting->display_name }}" wire:click="openEdit({{ $setting->id }})" />
                                            @endif
                                            @if(auth('filament')->user()?->hasPermission('delete_settings'))
                                                @if($setting->value !== null && $setting->value !== '')<x-filament::icon-button icon="heroicon-o-backspace" label="Очистить значение {{ $setting->display_name }}" color="warning" wire:click="requestRemove({{ $setting->id }}, true)" />@endif
                                                <x-filament::icon-button icon="heroicon-o-trash" label="Удалить настройку {{ $setting->display_name }}" color="danger" wire:click="requestRemove({{ $setting->id }})" />
                                            @endif
                                        </div></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
        @if(auth('filament')->user()?->hasPermission('delete_settings'))
            @php($pending = $this->pendingSetting())
            <x-filament::modal id="remove-setting" width="md" x-on:modal-closed="$wire.set('pendingId', null)">
                <x-slot name="heading">{{ $clearingValue ? 'Очистка значения' : 'Удаление настройки' }}</x-slot>
                @if($pending)
                    <p>{{ $clearingValue ? 'Очистить значение настройки' : 'Удалить настройку' }} «{{ $pending->display_name ?: $pending->key }}»? Это действие нельзя отменить.</p>
                    @if($clearingValue && $pending->type === 'image')<p style="margin-top:12px">Файл изображения также будет удалён.</p>@endif
                @endif
                @error('settingDelete')<p role="alert" style="color:#dc2626">{{ $message }}</p>@enderror
                <x-slot name="footer">
                    <x-filament::button color="danger" wire:click="confirmRemove" wire:loading.attr="disabled">{{ $clearingValue ? 'Очистить значение' : 'Удалить настройку' }}</x-filament::button>
                    <x-filament::button color="gray" x-on:click="$dispatch('close-modal', {id: 'remove-setting'})">Отмена удаления</x-filament::button>
                </x-slot>
            </x-filament::modal>
        @endif
    </x-filament::section>
</x-filament-panels::page>
