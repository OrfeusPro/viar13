<x-filament-panels::page>
    <x-filament::section>
        @if ($settingId !== null)
            <form wire:submit="save" style="display:grid;gap:16px">
                {{ $this->form }}
                <div style="display:flex;gap:8px"><x-filament::button type="submit">Сохранить</x-filament::button><x-filament::button type="button" color="gray" wire:click="cancel">Отмена</x-filament::button></div>
            </form>
        @else
            @foreach ($this->groups() as $group => $settings)
                <h2 style="font-size:1.2rem;font-weight:600;margin:16px 0 8px">{{ $group }}</h2>
                <div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse">
                    <thead><tr><th>Настройка</th><th>Ключ</th><th>Значение</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($settings as $setting)
                            <tr style="border-top:1px solid #d1d5db"><td style="padding:8px">{{ $setting->display_name }}</td><td style="padding:8px">{{ $setting->key }}</td><td style="padding:8px;max-width:400px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $setting->type === 'image' ? '[изображение]' : $setting->value }}</td><td>@if (auth('filament')->user()?->hasPermission('edit_settings'))<x-filament::button size="sm" wire:click="openEdit({{ $setting->id }})">Изменить</x-filament::button>@endif</td></tr>
                        @endforeach
                    </tbody>
                </table></div>
            @endforeach
        @endif
    </x-filament::section>
</x-filament-panels::page>
