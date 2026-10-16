<x-filament::button size="sm" color="success" icon="heroicon-o-check"
    wire:click="approveDraft({{ $action->getRecord()->id }})" wire:loading.attr="disabled" wire:target="approveDraft">
    Подтвердить
</x-filament::button>
