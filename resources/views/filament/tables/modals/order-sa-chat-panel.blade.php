<div x-data x-on:order-sa-read.window="if ($event.detail.orderId === {{ (int) $record->id }}) $wire.$refresh()">
    @livewire('admin.order-sa-chat-history', ['orderId' => (int) $record->id], key('sa-history-'.$record->id))
</div>
