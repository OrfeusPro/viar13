@php
    $record = $action->getRecord();
    $generate = $action->getName() === 'generate';
@endphp
<div class="viar-seo-row-action">
    <x-filament::button size="sm" :color="$generate ? 'primary' : 'success'" :icon="$generate ? 'heroicon-o-sparkles' : 'heroicon-o-check'" :title="$generate ? 'Сгенерировать' : 'Одобрить'"
        wire:click="submitSeoDraft({{ $record->id }}, '{{ $action->getName() }}')" wire:loading.attr="disabled" wire:target="submitSeoDraft">
        {{ $generate ? ($record->suggested_meta_title || $record->suggested_meta_description ? 'Сгенерировать заново' : 'Сгенерировать') : 'Одобрить' }}
    </x-filament::button>
    @if($generate && ! in_array($record->status, ['pending', 'generated', 'approved'], true))
        <small>Доступна только генерация.</small>
    @endif
</div>
