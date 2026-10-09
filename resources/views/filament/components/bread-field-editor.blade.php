@php($sqlColumns = $this->sqlColumns())
<style>
    .viar-bread-fields{margin-top:32px}.viar-bread-fields table{min-width:1100px}
    .viar-bread-fields th:last-child,.viar-bread-fields td:last-child{position:static;min-width:280px;width:30%}
    .viar-bread-fields th:first-child,.viar-bread-fields td:first-child{min-width:180px;width:20%}
    .viar-bread-fields td{vertical-align:top!important}.viar-bread-visibility{display:grid;gap:8px;min-width:120px}
    .viar-bread-visibility label{display:flex;gap:8px;align-items:center;font-size:13px}
    .viar-bread-fields input[type=checkbox]{width:16px;height:16px;accent-color:#2563eb}
    .viar-bread-fields textarea{font-family:monospace;font-size:13px;min-height:96px}
    .viar-bread-field-info{display:grid;gap:6px;font-size:12px;margin:10px 0;color:#6b7280}
</style>
<div class="viar-bread-fields">
    <h2 style="font-size:18px;font-weight:600;margin-bottom:16px">Поля таблицы {{ $this->getSettingsContext()?->name }}</h2>
    @if($deletedRelationships !== [])<p style="margin-bottom:16px;color:#b91c1c">Связей к удалению из BREAD: {{ count($deletedRelationships) }}. Изменения применятся после сохранения; записи связанных таблиц сохранятся.</p>@endif
    <div style="margin-bottom:20px"><x-filament::button type="button" color="gray" icon="heroicon-o-link" wire:click="openRelationship">Создать связь</x-filament::button></div>
    @if($relationshipEditor)
        <div style="padding:20px;border:1px solid #dbe3ef;border-radius:12px;margin-bottom:24px">
            <h3 style="font-weight:600;margin-bottom:16px">Настройки связи</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px">
                @php($childrenRelation = in_array($relationship['type'] ?? '', ['hasOne','hasMany'], true))
                @foreach(['type'=>'Тип связи','table'=>'Связанная таблица','model'=>'Модель связи','column'=>$childrenRelation ? 'Внешняя колонка связанной таблицы' : 'Колонка текущей таблицы','key'=>$childrenRelation ? 'Ключ текущей таблицы' : 'Ключ связанной таблицы','label'=>'Подпись связанной записи','pivot_table'=>'Промежуточная таблица'] as $key=>$label)
                    @php($options = match($key) {
                        'type' => ['belongsTo'=>'belongsTo', 'belongsToMany'=>'belongsToMany', 'hasOne'=>'hasOne', 'hasMany'=>'hasMany'],
                        'table','pivot_table' => array_combine($this->relationshipTables(), $this->relationshipTables()),
                        'model' => app(\App\Services\Admin\BreadCreationService::class)->models($relationship['table'] ?? ''),
                        'column' => $childrenRelation ? array_combine($this->relationshipColumns($relationship['table'] ?? ''), $this->relationshipColumns($relationship['table'] ?? '')) : array_combine($this->columns(), $this->columns()),
                        'key' => $childrenRelation ? array_combine($this->columns(), $this->columns()) : array_combine($this->relationshipColumns($relationship['table'] ?? ''), $this->relationshipColumns($relationship['table'] ?? '')),
                        default => array_combine($this->relationshipColumns($relationship['table'] ?? ''), $this->relationshipColumns($relationship['table'] ?? '')),
                    })
                    <label style="display:grid;gap:8px">{{ $label }}<x-filament::input.wrapper><x-filament::input.select wire:model.live="relationship.{{ $key }}" aria-label="{{ $label }}">
                        <option value="">Выберите</option>@foreach($options as $value=>$title)<option value="{{ $value }}">{{ $title }}</option>@endforeach
                    </x-filament::input.select></x-filament::input.wrapper></label>
                @endforeach
                @if(($relationship['type'] ?? '') === 'belongsToMany')
                    <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" wire:model="relationship.taggable" aria-label="Создание связанных записей">Создание связанных записей (taggable)</label>
                @endif
            </div>
            <p class="viar-management-muted" style="margin-top:16px">Связь будет сохранена вместе с BREAD. hasOne/hasMany показывают дочерние записи. taggable создаёт запись сразу; привязка сохраняется кнопкой «Сохранить». Требуется право создания в связанном разделе.</p>
            <div style="display:flex;gap:12px;margin-top:16px"><x-filament::button type="button" wire:click="stageRelationship">Применить связь к форме</x-filament::button><x-filament::button type="button" color="gray" wire:click="$set('relationshipEditor', false)">Закрыть</x-filament::button></div>
        </div>
    @endif
    <div class="viar-management-scroll" tabindex="0" role="region" aria-label="Редактирование полей BREAD">
        <table class="viar-management-table">
            <thead><tr><th>Поле</th><th>Видимость</th><th>Тип поля</th><th>Подпись</th><th>Параметры (JSON)</th></tr></thead>
            <tbody>
                @foreach($this->editorRows() as $row)
                    @php($column = $sqlColumns[$row->field] ?? null)
                    <tr wire:key="bread-edit-field-{{ $row->id }}" x-data="{}" draggable="true"
                        x-on:dragstart="if ($event.target.closest('input,textarea,select')) { $event.preventDefault(); return; } $event.dataTransfer.setData('text/plain', '{{ $row->id }}')"
                        x-on:dragover.prevent x-on:drop.prevent="$wire.moveField($event.dataTransfer.getData('text/plain'), '{{ $row->id }}')">
                        <td>
                            <strong>{{ $row->field }}</strong>
                            <span title="Перетащите строку для изменения порядка" style="cursor:grab;margin-left:8px">⠿</span>
                            @if($row->type === 'relationship')<div style="margin-top:8px;display:grid;gap:8px"><x-filament::button type="button" color="gray" size="sm" wire:click="openRelationship('{{ $row->id }}')">Настроить связь</x-filament::button><x-filament::button type="button" color="danger" size="sm" wire:click="removeRelationship('{{ $row->id }}')">Удалить связь из BREAD</x-filament::button></div>@endif
                            @if(isset($newFields[$row->id]))<div class="viar-management-muted">Ещё не настроено в BREAD</div>@endif
                            <div class="viar-bread-field-info">
                                <span>Тип в БД: {{ $column['type_name'] ?? 'Связь / виртуальное поле' }}</span>
                                <span>NULL: {{ $column ? ($column['nullable'] ? 'Да' : 'Нет') : '—' }}</span>
                                <span>Ключ: {{ ($column['key'] ?? '') ?: '—' }}</span>
                            </div>
                            <label for="bread-order-{{ $row->id }}" style="display:block;margin-bottom:6px">Порядок {{ $row->field }}</label>
                            <x-filament::input.wrapper><x-filament::input id="bread-order-{{ $row->id }}" type="number" min="0" wire:model="fieldRows.{{ $row->id }}.order" /></x-filament::input.wrapper>
                        </td>
                        <td><div class="viar-bread-visibility">
                            @foreach(['browse'=>'Список','read'=>'Просмотр','edit'=>'Редактирование','add'=>'Создание','delete'=>'Удаление','required'=>'Обязательное'] as $flag=>$label)
                                <label><input type="checkbox" wire:model="fieldRows.{{ $row->id }}.{{ $flag }}" aria-label="{{ $label }} {{ $row->field }}">{{ $label }}</label>
                            @endforeach
                        </div></td>
                        <td><x-filament::input.wrapper><x-filament::input.select wire:model="fieldRows.{{ $row->id }}.type" aria-label="Тип поля {{ $row->field }}">
                            @foreach(array_unique([...\App\Services\Admin\BreadMetadataService::TYPES, $row->type]) as $inputType)<option value="{{ $inputType }}">{{ $inputType }}</option>@endforeach
                        </x-filament::input.select></x-filament::input.wrapper></td>
                        <td><x-filament::input.wrapper><x-filament::input wire:model="fieldRows.{{ $row->id }}.display_name" aria-label="Подпись {{ $row->field }}" /></x-filament::input.wrapper></td>
                        <td><x-filament::input.wrapper><textarea class="fi-input" wire:model="fieldRows.{{ $row->id }}.details" aria-label="Параметры {{ $row->field }}" rows="4" style="display:block;width:100%;padding:8px 12px;border:0;background:transparent"></textarea></x-filament::input.wrapper></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
