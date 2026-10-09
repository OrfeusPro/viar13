@php($section = $this->singleImageSection($field))
@if($section)
<div style="display:grid;gap:16px">
    <h3 style="font-weight:600">{{ $section['label'] }}</h3>
    @if($media = $section['media'])
        <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap">
            <a href="{{ $media->getUrl() }}" target="_blank" rel="noopener"><img src="{{ $media->getUrl() }}" alt="{{ $media->getCustomProperty('alt') }}" class="viar-bread-media-preview"></a>
            <span>{{ $media->file_name }} · {{ $media->human_readable_size }}</span>
            <x-filament::icon-button type="button" icon="heroicon-o-trash" label="Удалить изображение" wire:click="deleteSingleImage('{{ $field }}', {{ $media->id }})" wire:confirm="Удалить изображение?" />
        </div>
    @endif
    <label>Изображение <input type="file" wire:model="singleImageUploads.{{ $field }}" accept="image/jpeg,image/png,image/webp,image/gif"></label>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px">
        <label>TITLE <x-filament::input.wrapper><x-filament::input wire:model="singleImageProperties.{{ $field }}.title" /></x-filament::input.wrapper></label>
        <label>ALT <x-filament::input.wrapper><x-filament::input wire:model="singleImageProperties.{{ $field }}.alt" /></x-filament::input.wrapper></label>
    </div>
    <p class="viar-management-muted">Файл и подписи применяются при сохранении формы.</p>
    @foreach(['singleImageProperties.'.$field.'.title','singleImageProperties.'.$field.'.alt','singleImageUploads.'.$field] as $errorKey)
        @error($errorKey)<p class="text-danger-600">{{ $message }}</p>@enderror
    @endforeach
</div>
@endif
