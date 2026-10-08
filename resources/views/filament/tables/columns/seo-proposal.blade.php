@php
    $record = $getRecord();
    $editor = \App\Filament\Resources\SeoMetaSuggestions\SeoMetaSuggestionResource::permitted('edit');
    $draft = $getLivewire()->draftFor($record);
@endphp
<div class="viar-seo-editor" wire:key="seo-editor-{{ $record->id }}">
    @foreach(['seo_keywords' => ['Ключевые слова', 1000], 'meta_title' => ['Meta Title', config('seo_meta_generation.limits.meta_title_max', 60)], 'meta_description' => ['Meta Description', config('seo_meta_generation.limits.meta_description_max', 155)]] as $field => [$label, $max])
        <div class="viar-seo-field" x-data="{ value: $wire.entangle('seoDrafts.{{ $record->id }}.{{ $field }}') }">
            <label for="seo-{{ $record->id }}-{{ $field }}">{{ $label }}</label>
            @if($editor)
                <textarea id="seo-{{ $record->id }}-{{ $field }}" x-model="value" wire:model="seoDrafts.{{ $record->id }}.{{ $field }}" rows="2" maxlength="{{ $max }}" @if($field === 'seo_keywords') placeholder="Например: canvas print, custom portrait, gift" @endif></textarea>
            @else
                <p>{{ $draft[$field] ?: '—' }}</p>
            @endif
            @if($field === 'seo_keywords')
                <small>Будут учтены при следующей генерации.</small>
            @else
                <small :class="Array.from(value || '').length > {{ $max }} ? 'is-too-long' : ''"><span x-text="Array.from(value || '').length">{{ mb_strlen($draft[$field]) }}</span> / {{ $max }}</small>
            @endif
            @error('seoDrafts.'.$record->id.'.'.$field)<p role="alert" class="is-too-long">{{ $message }}</p>@enderror
        </div>
    @endforeach
    @if($record->error)<p role="alert" class="viar-seo-error">{{ $record->error }}</p>@endif
</div>
