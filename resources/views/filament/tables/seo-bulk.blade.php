@if(\App\Filament\Resources\SeoMetaSuggestions\SeoMetaSuggestionResource::permitted('edit'))
    <div class="viar-seo-bulk">
        <label class="viar-seo-bulk-label" for="seo-bulk-operation">Действие с выбранными записями</label>
        <select id="seo-bulk-operation" wire:model="seoBulkOperation">
            <option value="generate">Сгенерировать выбранное</option>
            <option value="approve">Одобрить выбранное</option>
            <option value="apply">Применить выбранное</option>
            <option value="reject">Отклонить выбранное</option>
        </select>
        <x-filament::button size="sm" icon="heroicon-o-check" x-bind:disabled="! getSelectedRecordsCount()"
            x-on:click="mountAction($wire.seoBulkOperation, {}, { table: true, bulk: true })">Применить</x-filament::button>
        <small x-show="getSelectedRecordsCount()" x-cloak>Выбрано: <span x-text="getSelectedRecordsCount()"></span></small>
    </div>
@endif
