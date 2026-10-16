@php
    $record = $getRecord();
    $draft = $getLivewire()->draftFor($record);
    $editor = \App\Filament\Resources\ImageAltSuggestions\ImageAltSuggestionResource::permitted('edit');
@endphp
<div style="width:280px;display:grid;gap:12px" wire:key="alt-draft-{{ $record->id }}">
    @foreach(['alt' => ['ALT', config('alt_generation.limits.alt_max', 125)], 'title' => ['TITLE', config('alt_generation.limits.title_max', 70)]] as $field => [$label, $max])
        <div>
            <label style="display:block;margin-bottom:6px;font-weight:600" for="alt-{{ $record->id }}-{{ $field }}">{{ $label }}</label>
            @if($editor)
                @if($field === 'alt')
                    <textarea style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px;resize:vertical" id="alt-{{ $record->id }}-{{ $field }}" wire:model="altDrafts.{{ $record->id }}.{{ $field }}" rows="3" maxlength="{{ $max }}">{{ $draft[$field] }}</textarea>
                @else
                    <input style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px" id="alt-{{ $record->id }}-{{ $field }}" wire:model="altDrafts.{{ $record->id }}.{{ $field }}" value="{{ $draft[$field] }}" maxlength="{{ $max }}">
                @endif
            @else
                <p>{{ $draft[$field] ?: '—' }}</p>
            @endif
            @error('altDrafts.'.$record->id.'.'.$field)<p role="alert" class="is-too-long">{{ $message }}</p>@enderror
        </div>
    @endforeach
</div>
