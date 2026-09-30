<x-filament-panels::page class="viar-management-page">
    @include('filament.components.management-styles')
    <x-filament::section>
        @if ($settingId !== null)
            <form wire:submit="save" class="viar-management-form">
                {{ $this->form }}
                <div class="viar-management-form-actions"><x-filament::button type="submit">Сохранить</x-filament::button><x-filament::button type="button" color="gray" wire:click="cancel">Отмена</x-filament::button></div>
            </form>
        @else
            @php($groups = $this->groups())
            @php($initialGroup = array_key_exists('Site', $groups) ? 'Site' : array_key_first($groups))
            <div x-data="{ group: @js($initialGroup) }">
                <div class="viar-management-tabs" role="tablist" aria-label="Группы настроек">
                    @foreach ($groups as $group => $settings)
                        <button type="button" class="viar-management-tab" role="tab" id="settings-tab-{{ $loop->index }}" aria-controls="settings-panel-{{ $loop->index }}" x-bind:aria-selected="group === @js($group)" x-on:click="group = @js($group)">{{ $group ?: 'Общие' }} <span style="margin-left:6px;opacity:.7">{{ count($settings) }}</span></button>
                    @endforeach
                </div>
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
                                            @elseif ($setting->type === 'checkbox')
                                                <span @class(['viar-management-badge', 'is-enabled' => (bool) $setting->value])>{{ $setting->value ? 'Включено' : 'Выключено' }}</span>
                                            @elseif ($setting->type === 'code_editor')
                                                <code class="viar-management-code">{{ \Illuminate\Support\Str::limit((string) $setting->value, 240) }}</code>
                                            @else
                                                {{ \Illuminate\Support\Str::limit((string) $setting->value, 600) ?: '—' }}
                                            @endif
                                        </td>
                                        <td>@if (auth('filament')->user()?->hasPermission('edit_settings'))<x-filament::icon-button icon="heroicon-o-pencil-square" label="Изменить" wire:click="openEdit({{ $setting->id }})" />@endif</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
