@php($section = collect($this->mediaSections())->firstWhere('field', $field))
@if ($section)
    <div>
        <h3 style="font-weight:600;margin-bottom:12px;">{{ $section['label'] }}</h3>
        @if ($section['files']->isNotEmpty())
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px;">
                <x-filament::button type="button" size="sm" color="gray" wire:click="selectAllMedia('{{ $field }}')">Выбрать все</x-filament::button>
                <x-filament::button type="button" size="sm" color="gray" wire:click="clearMediaSelection('{{ $field }}')">Снять выделение</x-filament::button>
                <x-filament::button type="button" size="sm" color="danger" :disabled="empty($this->selectedMedia[$field])" wire:click="deleteSelectedMedia('{{ $field }}')" wire:confirm="Удалить выбранные файлы без возможности восстановления?">Удалить выбранные ({{ count($this->selectedMedia[$field] ?? []) }})</x-filament::button>
            </div>
        @endif
        <div style="display:flex;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
            <input type="file" multiple wire:model="mediaUploads.{{ $section['field'] }}" accept="image/jpeg,image/png,image/webp,image/gif" aria-label="{{ $section['label'] }}">
            <x-filament::button type="button" wire:click="uploadMedia('{{ $section['field'] }}')" wire:loading.attr="disabled">Добавить изображения</x-filament::button>
        </div>
        @error('mediaUpload') <p class="text-sm text-danger-600">{{ $message }}</p> @enderror
        @error('files') <p>{{ $message }}</p> @enderror
        @foreach ($errors->get('files.*') as $messages)
            @foreach ($messages as $message) <p>{{ $message }}</p> @endforeach
        @endforeach
        @foreach ($section['files'] as $media)
            <div class="viar-bread-media-card" wire:key="bread-media-{{ $media->id }}">
                <div class="viar-bread-media-row">
                    <input type="checkbox" wire:model.live="selectedMedia.{{ $field }}" value="{{ $media->id }}" aria-label="Выбрать {{ $media->file_name }}">
                    <img src="{{ $media->getUrl() }}" alt="" class="viar-bread-media-preview">
                    <div><p class="font-semibold">{{ $media->file_name }}</p><p class="text-sm">{{ $loop->iteration }} · {{ $media->human_readable_size }} · ID {{ $media->id }}</p></div>
                    <x-filament::icon-button type="button" :icon="\Filament\Support\Icons\Heroicon::OutlinedArrowUp" label="Переместить выше" :disabled="$loop->first" wire:click="moveMedia('{{ $section['field'] }}', {{ $media->id }}, -1)" />
                    <x-filament::icon-button type="button" :icon="\Filament\Support\Icons\Heroicon::OutlinedArrowDown" label="Переместить ниже" :disabled="$loop->last" wire:click="moveMedia('{{ $section['field'] }}', {{ $media->id }}, 1)" />
                </div>
                <div class="viar-bread-media-fields">
                    <label>Title <x-filament::input.wrapper><x-filament::input wire:model="mediaProperties.{{ $media->id }}.title" /></x-filament::input.wrapper></label>
                    <label>ALT <x-filament::input.wrapper><x-filament::input wire:model="mediaProperties.{{ $media->id }}.alt" /></x-filament::input.wrapper></label>
                    @foreach ($section['extra'] as $key => $definition)
                        <label>{{ $definition['title'] ?? $key }}
                            @if (($definition['type'] ?? '') === 'dropdown')
                                <select wire:model="mediaProperties.{{ $media->id }}.{{ $key }}" style="width:100%;border:1px solid #d1d5db;border-radius:8px;padding:8px;">
                                    <option value="">—</option>
                                    @foreach ($definition['options'] ?? [] as $value => $label)<option value="{{ is_int($value) ? $label : $value }}">{{ $label }}</option>@endforeach
                                </select>
                            @elseif (($definition['type'] ?? '') === 'textarea')
                                <textarea wire:model="mediaProperties.{{ $media->id }}.{{ $key }}" style="width:100%;border:1px solid #d1d5db;border-radius:8px;padding:8px;"></textarea>
                            @else
                                <x-filament::input.wrapper><x-filament::input wire:model="mediaProperties.{{ $media->id }}.{{ $key }}" /></x-filament::input.wrapper>
                            @endif
                        </label>
                        @error('mediaProperties.' . $media->id . '.' . $key) <p>{{ $message }}</p> @enderror
                    @endforeach
                </div>
                <div style="display:flex;align-items:center;flex-wrap:wrap;gap:12px;margin-top:12px;">
                    <input type="file" wire:model="mediaReplacements.{{ $media->id }}" accept="image/jpeg,image/png,image/webp,image/gif" aria-label="Новый файл для {{ $media->file_name }}">
                    <x-filament::button type="button" size="sm" color="gray" wire:click="replaceMedia('{{ $section['field'] }}', {{ $media->id }})" wire:loading.attr="disabled" wire:confirm="Заменить файл, сохранив подписи и порядок?">Заменить файл</x-filament::button>
                </div>
                @error('mediaReplacements.' . $media->id) <p>{{ $message }}</p> @enderror
                <div class="viar-bread-media-actions">
                    <x-filament::button type="button" size="sm" wire:click="saveMediaProperties('{{ $section['field'] }}', {{ $media->id }})">Сохранить подписи</x-filament::button>
                    <x-filament::button type="button" size="sm" color="danger" wire:click="deleteMedia('{{ $section['field'] }}', {{ $media->id }})" wire:confirm="Удалить файл?">Удалить файл</x-filament::button>
                </div>
            </div>
        @endforeach
    </div>
@endif
