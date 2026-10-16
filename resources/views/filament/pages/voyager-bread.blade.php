<x-filament-panels::page class="viar-bread-page">
    <style>
        .viar-bread-media-preview { width: 96px; height: 96px; object-fit: cover; display: block; }
        .viar-bread-media-row { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px; }
        .viar-bread-media-card { border: 1px solid #d1d5db; border-radius: 8px; padding: 12px; margin-bottom: 16px; }
        .viar-bread-media-fields { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px; }
        .viar-bread-media-actions { display: flex; gap: 8px; margin-top: 12px; }
        .viar-bread-page .fi-page-main, .viar-bread-page .fi-section { min-width: 0; }
        .viar-bread-search { display: grid; grid-template-columns: minmax(150px, 1fr) minmax(140px, .7fr) minmax(200px, 2fr) minmax(130px, .7fr); align-items: end; gap: 12px; margin-bottom: 24px; }
        .viar-bread-search label { display: grid; gap: 6px; font-size: 14px; }
        .viar-bread-sort { display: inline-flex; align-items: center; gap: 8px; max-width: 100%; text-align: left; white-space: normal; overflow-wrap: anywhere; cursor: pointer; }
        .viar-bread-sort:focus-visible { outline: 2px solid #2563eb; outline-offset: 4px; border-radius: 3px; }
        @media (max-width: 900px) { .viar-bread-search { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 480px) { .viar-bread-search { grid-template-columns: minmax(0, 1fr); } }
        .viar-bread-table-scroll { width: 100%; max-width: 100%; overflow-x: auto; position: relative; border: 1px solid #dbe3ee; border-radius: 10px; }
        .viar-bread-table { width: max-content; min-width: 100%; border-collapse: separate; border-spacing: 0; }
        .viar-bread-table th, .viar-bread-table td { min-width: 150px; max-width: 240px; padding: 9px 12px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        .viar-bread-table thead th { padding: 13px 12px; text-align: left; vertical-align: middle; white-space: normal; overflow-wrap: anywhere; font-weight: 600; color: #334155; background: #eef2f8; border-bottom: 1px solid #dbe3ee; }
        .viar-bread-table .viar-bread-value { white-space: normal; overflow-wrap: anywhere; }
        .viar-bread-table th:first-child, .viar-bread-table td:first-child { min-width: 75px; }
        .viar-bread-table .viar-bread-selection { min-width: 44px; width: 44px; max-width: 44px; text-align: center; }
        .viar-bread-table th:last-child, .viar-bread-table td:last-child { position: sticky; right: 0; width: 160px; min-width: 160px; max-width: 160px; z-index: 1; background: white; border-left: 1px solid #e5e7eb; box-shadow: -4px 0 6px -4px #9ca3af; }
        .dark .viar-bread-table th:last-child, .dark .viar-bread-table td:last-child { background: #18181b; }
        .viar-bread-table thead th:last-child { background: #eef2f8; }
        .viar-bread-row-actions { display: flex; align-items: center; gap: 8px; }
        @media (max-width: 640px) {
            .viar-bread-table th:last-child, .viar-bread-table td:last-child { width: 84px; min-width: 84px; max-width: 84px; }
            .viar-bread-table td:last-child > .viar-bread-row-actions { display: grid; grid-template-columns: repeat(2, 24px); }
            .viar-bread-table th:last-child { font-size: 12px; }
        }
        .dark .viar-bread-table-scroll { border-color: #334155; }
        .dark .viar-bread-table thead th { color: #e2e8f0; background: #243047; border-color: #334155; }
        .viar-bread-page .viar-bread-image-single { width: 100%; max-width: 280px; }
        .viar-bread-page .viar-bread-image-grid { width: 100%; max-width: 720px; }
        .viar-bread-page .viar-bread-image-grid .filepond--root[data-style-panel-layout="grid"] .filepond--item { width: calc(33.333% - 0.5rem); }
        @media (max-width: 640px) {
            .viar-bread-page .viar-bread-image-grid .filepond--root[data-style-panel-layout="grid"] .filepond--item { width: calc(50% - 0.5rem); }
        }
        .viar-bread-tabs { min-width: 0; max-width: 100%; }
        .viar-bread-tabs > .fi-tabs { display: flex; flex-wrap: nowrap; max-width: 100%; overflow-x: auto; overscroll-behavior-x: contain; touch-action: pan-x pan-y; scrollbar-width: thin; cursor: grab; gap: 8px; padding: 12px; background: #eef2f8; border-bottom: 1px solid #d9e2ef; border-radius: 12px 12px 0 0; }
        .viar-bread-tabs > .fi-tabs .fi-tabs-item { flex-shrink: 0; white-space: nowrap; }
        .viar-bread-tabs > .fi-tabs.viar-tabs-dragging, .viar-bread-tabs > .fi-tabs.viar-tabs-dragging * { cursor: grabbing; user-select: none; }
        .viar-bread-tabs > .fi-tabs .fi-tabs-item { padding: 10px 14px; border: 1px solid #d4deeb; border-radius: 8px; background: #fff; color: #475569; box-shadow: 0 1px 2px rgb(15 23 42 / 4%); transition: background-color 150ms, border-color 150ms, box-shadow 150ms; }
        .viar-bread-tabs > .fi-tabs .fi-tabs-item-label { color: inherit; font-weight: 600; }
        .viar-bread-tabs > .fi-tabs .fi-tabs-item:hover { background: #e0eaff; border-color: #a5bff7; color: #1d4ed8; }
        .viar-bread-tabs > .fi-tabs .fi-tabs-item[aria-selected="true"] { background: #2563eb; border-color: #2563eb; color: #fff; box-shadow: 0 3px 8px rgb(37 99 235 / 22%); }
        .viar-bread-tabs > .fi-tabs .fi-tabs-item:focus-visible { outline: 2px solid #2563eb; outline-offset: 3px; }
        .dark .viar-bread-tabs > .fi-tabs { background: #172033; border-color: #334155; }
        .dark .viar-bread-tabs > .fi-tabs .fi-tabs-item { background: #243047; border-color: #475569; color: #e2e8f0; }
        .dark .viar-bread-tabs > .fi-tabs .fi-tabs-item:hover { background: #334155; border-color: #60a5fa; }
        .dark .viar-bread-tabs > .fi-tabs .fi-tabs-item[aria-selected="true"] { background: #2563eb; border-color: #60a5fa; color: #fff; }
    </style>
    @php($bread = $this->bread())
    @php($breadRegistry = app(\App\Filament\Bread\BreadRegistry::class))
    @php($canAddRecord = $breadRegistry->permitted($bread, 'add') && $breadRegistry->hasFormFields($bread, 'add'))
    @php($canEditRecord = $breadRegistry->permitted($bread, 'edit') && $breadRegistry->hasFormFields($bread, 'edit'))
    @php($canDeleteRecord = $breadRegistry->permitted($bread, 'delete'))
    @php($records = ! $editing && $viewId === null ? $this->records() : [])
    @if (! app(\App\Filament\Bread\BreadRegistry::class)->model($bread))
        <x-filament::section>
            Модель или таблица этого раздела недоступна. Редактирование заблокировано до исправления конфигурации Voyager.
        </x-filament::section>
    @elseif ($viewId !== null)
        <x-filament::section>
            @if ($bread->name !== 'users')
            <div class="mb-4 flex flex-wrap gap-2" style="margin-bottom: 24px;">
                @foreach (app(\App\Filament\Bread\BreadRegistry::class)->locales() as $language)
                    <x-filament::button size="sm" :color="$locale === $language ? 'primary' : 'gray'" wire:click="changeLocale('{{ $language }}')">{{ strtoupper($language) }}</x-filament::button>
                @endforeach
            </div>
            @endif
            <dl class="space-y-4">
                @foreach ($this->viewedFields() as $field)
                    <div><dt class="text-sm font-semibold">{{ $field['label'] }}</dt><dd class="whitespace-pre-wrap">
                        @if (in_array($field['type'] ?? '', ['image', 'multiple_images', 'media_picker', 'adv_media_files'], true) || (($field['type'] ?? '') === 'file' && \App\Filament\Bread\BreadImage::urls($field['value']) !== []))
                            @php($images = ($field['type'] ?? '') === 'adv_media_files' ? $field['value'] : \App\Filament\Bread\BreadImage::urls($field['value']))
                            @foreach ($images as $url)<a href="{{ $url }}" target="_blank" rel="noopener noreferrer"><img src="{{ $url }}" alt="{{ $field['label'] }}" loading="lazy" class="viar-bread-media-preview"></a>@endforeach
                        @else
                            {{ is_scalar($field['value']) ? \Illuminate\Support\Str::limit(strip_tags((string) $field['value']), 5000) : '' }}
                        @endif
                    </dd></div>
                @endforeach
            </dl>
            <div class="mt-4"><x-filament::button color="gray" wire:click="cancel">Назад</x-filament::button></div>
        </x-filament::section>
    @elseif ($editing)
        <x-filament::section>
            @if ($bread->name !== 'users')
            <div class="mb-4 flex flex-wrap gap-2" style="margin-bottom: 24px;">
                @foreach (app(\App\Filament\Bread\BreadRegistry::class)->locales() as $language)
                    <x-filament::button size="sm" :color="$locale === $language ? 'primary' : 'gray'" :aria-pressed="$locale === $language ? 'true' : 'false'" wire:click="changeLocale('{{ $language }}')" wire:loading.attr="disabled" wire:target="changeLocale" :disabled="$recordId === null && $language !== config('voyager.multilingual.default', 'en')">
                        {{ strtoupper($language) }}
                    </x-filament::button>
                @endforeach
            </div>
            @endif
            <form wire:submit="save" class="space-y-5">
                @if ($bread->name === 'users' && $recordId !== null)
                    <div style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:24px">
                        {{ $this->requestClientReviewAction }}
                        @if ($this->painterAssignmentsVisible()) {{ $this->painterAssignmentsAction }} @endif
                    </div>
                @endif
                @if ($recordId !== null && $this->seoMetaTarget())
                    <div style="margin-bottom:24px;">
                        <x-filament::button type="button" color="gray" wire:click="generateSeoMeta" wire:loading.attr="disabled" wire:target="generateSeoMeta" wire:confirm="Сгенерировать и сохранить SEO-метаданные для всех языков?">Сгенерировать Meta Title / Description</x-filament::button>
                    </div>
                @endif
                <x-bread-scrollable-tabs>
                    {{ $this->form }}
                </x-bread-scrollable-tabs>
                @include('filament.components.bread-alt-panel')
                <div class="flex gap-2" style="margin-top: 24px;">
                    <x-filament::button type="submit">Сохранить</x-filament::button>
                    <x-filament::button color="gray" wire:click="cancel" type="button">Отмена</x-filament::button>
                </div>
            </form>
        </x-filament::section>
    @else
        <div class="flex flex-wrap gap-2">
            @if ($canAddRecord)<x-filament::button wire:click="openCreate">Создать запись</x-filament::button>@endif
            @if ($orderUrl = $this->orderUrl())<x-filament::button tag="a" :href="$orderUrl" color="gray" icon="heroicon-o-bars-3">Порядок записей</x-filament::button>@endif
        </div>
        <x-filament::section>
            @if ($bread->name !== 'users')
            <div class="mb-4 flex flex-wrap gap-2" style="margin-bottom: 24px;" role="group" aria-label="Язык записей">
                @foreach (app(\App\Filament\Bread\BreadRegistry::class)->locales() as $language)
                    <x-filament::button size="sm" :color="$locale === $language ? 'primary' : 'gray'" :aria-pressed="$locale === $language ? 'true' : 'false'" wire:click="changeLocale('{{ $language }}')">{{ strtoupper($language) }}</x-filament::button>
                @endforeach
            </div>
            @endif
            @php($browseFields = $this->browseFields())
            <div class="viar-bread-search">
                <label>Поле поиска<x-filament::input.wrapper><x-filament::input.select wire:model.live="searchKey" aria-label="Поле поиска">
                    <option value="">{{ ($bread->server_side ?? true) ? 'Все текстовые поля' : 'Все поля списка' }}</option>
                    @foreach ($this->searchFields() as $field => $label)<option value="{{ $field }}">{{ $label }}</option>@endforeach
                </x-filament::input.select></x-filament::input.wrapper></label>
                <label>Условие<x-filament::input.wrapper><x-filament::input.select wire:model.live="searchFilter" aria-label="Условие поиска">
                    <option value="contains">Содержит</option><option value="equals">Равно</option>
                </x-filament::input.select></x-filament::input.wrapper></label>
                <label>Поиск<x-filament::input.wrapper><x-filament::input type="search" wire:model.live.debounce.350ms="search" aria-label="Поиск записей" placeholder="Поиск записей" /></x-filament::input.wrapper></label>
                <label>На странице<x-filament::input.wrapper><x-filament::input.select wire:model.live="perPage" aria-label="Записей на странице">
                    @foreach ([10, 25, 50, 100] as $size)<option value="{{ $size }}">{{ $size }}</option>@endforeach
                </x-filament::input.select></x-filament::input.wrapper></label>
            </div>
            @if ($canDeleteRecord)
                @if ($selectedRecords !== [])
                <div style="display:flex;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
                    <span role="status">Выбрано: {{ count($selectedRecords) }}</span>
                    <x-filament::button size="sm" color="danger" wire:click="deleteSelectedRecords" :disabled="count($selectedRecords) === 0" wire:confirm="Удалить выбранные записи ({{ count($selectedRecords) }})?">Удалить выбранные</x-filament::button>
                    <x-filament::button size="sm" color="gray" wire:click="clearRecordSelection">Снять выбор</x-filament::button>
                    <span style="font-size:13px;color:#71717a;">Выбор действует на текущей странице</span>
                </div>
                @endif
                @error('selectedRecords')<p role="alert" style="color:#dc2626;margin-bottom:16px;">{{ $message }}</p>@enderror
                @error('selectedRecords.*')<p role="alert" style="color:#dc2626;margin-bottom:16px;">{{ $message }}</p>@enderror
            @endif
            <div class="viar-bread-table-scroll" tabindex="0" role="region" aria-label="Таблица записей с горизонтальной прокруткой">
                <table class="viar-bread-table text-sm">
                    <thead><tr class="border-b text-left">
                        @if ($canDeleteRecord)
                            @php($pageIds = $records['rows']->pluck('id')->map(fn ($id) => (string) $id)->all())
                            <th class="viar-bread-selection"><input type="checkbox" aria-label="Выбрать все записи текущей страницы" wire:click="togglePageSelection" @checked($pageIds !== [] && count(array_intersect($pageIds, $selectedRecords)) === count($pageIds)) @disabled($pageIds === []) /></th>
                        @endif
                        @foreach ($records['columns'] ?? [] as $column)
                            <th class="p-2" @if (isset($browseFields[$column['field']])) aria-sort="{{ $sortField === $column['field'] ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}" @endif>
                                @if (isset($browseFields[$column['field']]))
                                    <button type="button" class="viar-bread-sort" wire:click="sortBy(@js($column['field']))" aria-label="Сортировать: {{ $column['label'] }}">
                                        {{ $column['label'] }}<span aria-hidden="true">{{ $sortField === $column['field'] ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                    </button>
                                @else {{ $column['label'] }} @endif
                            </th>
                        @endforeach
                        <th class="p-2">Действия</th>
                    </tr></thead>
                    <tbody>
                    @forelse ($records['rows'] ?? [] as $record)
                        <tr class="border-b">
                            @if ($canDeleteRecord)<td class="viar-bread-selection"><input type="checkbox" value="{{ $record->id }}" wire:model.live="selectedRecords" aria-label="Выбрать запись {{ $record->id }}" /></td>@endif
                            @foreach ($records['columns'] as $column)
                                @php($value = $record->{$column['field']} ?? null)
                                <td class="viar-bread-value max-w-64 p-2" title="{{ $column['type'] === 'adv_fields_group' || isset($column['details']['view']) ? '' : (is_scalar($value) ? \Illuminate\Support\Str::limit(in_array($column['type'], ['adv_json', 'adv_page_layout'], true) ? (string) $value : strip_tags((string) $value), 300) : '') }}">
                                    @if (isset($records['customCells'][$record->id][$column['field']]))
                                        {{ $records['customCells'][$record->id][$column['field']] }}
                                    @elseif ($column['type'] === 'file')
                                        @foreach (\App\Filament\Bread\BreadBrowseFiles::links($value) as $file)
                                            <a href="{{ $file['url'] }}" target="_blank" rel="noopener noreferrer" style="display:block;color:#2563eb;">{{ $file['name'] }}</a>
                                        @endforeach
                                    @elseif ($column['type'] === 'media_picker')
                                        @php($media = \App\Filament\Bread\BreadBrowseFiles::media($value, $column['details'] ?? []))
                                        @if ($media['empty'])
                                            {{ trans_choice('admin_bread.files', 0) }}
                                        @elseif ($media['images'])
                                            <div style="display:flex;align-items:center;gap:6px;max-width:220px;overflow:hidden;">
                                                @foreach ($media['items'] as $path)
                                                    @if ($url = \App\Filament\Bread\BreadBrowseFiles::url($path))
                                                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"><img src="{{ $url }}" alt="{{ $column['label'] }}" loading="lazy" style="width:52px;height:52px;object-fit:cover;border-radius:6px;"></a>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            <ul style="list-style:disc;padding-left:18px;">
                                                @foreach ($media['items'] as $path)<li>{{ is_scalar($path) ? $path : '' }}</li>@endforeach
                                            </ul>
                                        @endif
                                        @if ($media['more'])<span>{{ __('admin_bread.files_more', ['count' => $media['more']]) }}</span>@endif
                                    @elseif (in_array($column['type'], ['image', 'multiple_images', 'adv_media_files'], true))
                                        @php($images = $column['type'] === 'adv_media_files' ? (is_array($value) ? $value : []) : \App\Filament\Bread\BreadImage::urls($value))
                                        <div style="display:flex;align-items:center;gap:6px;max-width:220px;overflow:hidden;">
                                            @foreach (array_slice($images, 0, 3) as $url)
                                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"><img src="{{ $url }}" alt="{{ $column['label'] }}" loading="lazy" style="width:52px;height:52px;object-fit:cover;border-radius:6px;"></a>
                                            @endforeach
                                            @if (count($images) > 3)<span>+{{ count($images) - 3 }}</span>@endif
                                        </div>
                                    @elseif ($column['type'] === 'checkbox')
                                        @if (array_key_exists($column['field'], $records['inlineValues'][$record->id] ?? []))
                                            @php($inlineValue = $records['inlineValues'][$record->id][$column['field']])
                                            <button type="button" role="switch" aria-checked="{{ $inlineValue ? 'true' : 'false' }}" aria-label="{{ $column['label'] }}: {{ app(\App\Filament\Bread\BreadCheckbox::class)->caption($inlineValue, $column['details'] ?? []) }}"
                                                wire:click="saveInlineField({{ (int) $record->id }}, @js($column['field']), {{ $inlineValue ? 0 : 1 }})" wire:loading.attr="disabled" wire:target="saveInlineField"
                                                style="display:inline-flex;align-items:center;width:38px;height:22px;padding:3px;border-radius:999px;background:{{ $inlineValue ? '#2563eb' : '#94a3b8' }};cursor:pointer;">
                                                <span aria-hidden="true" style="width:16px;height:16px;border-radius:50%;background:white;transform:translateX({{ $inlineValue ? 16 : 0 }}px);"></span>
                                            </button>
                                            @error('inline.'.$record->id.'.'.$column['field'])<span role="alert" style="display:block;color:#dc2626;white-space:normal;">{{ $message }}</span>@enderror
                                        @else
                                            @if (\App\Filament\Bread\BreadInlineEdit::enabled('checkbox', $column['details'] ?? []))
                                                <button type="button" role="switch" disabled aria-checked="{{ $value ? 'true' : 'false' }}" aria-label="{{ $column['label'] }}: {{ app(\App\Filament\Bread\BreadCheckbox::class)->caption($value, $column['details'] ?? []) }}"
                                                    style="display:inline-flex;align-items:center;width:38px;height:22px;padding:3px;border-radius:999px;background:{{ $value ? '#2563eb' : '#94a3b8' }};cursor:default;">
                                                    <span aria-hidden="true" style="width:16px;height:16px;border-radius:50%;background:white;transform:translateX({{ $value ? 16 : 0 }}px);"></span>
                                                </button>
                                            @elseif (is_string($column['details']['on'] ?? null) && is_string($column['details']['off'] ?? null))
                                                {{ app(\App\Filament\Bread\BreadCheckbox::class)->caption($value, $column['details'] ?? []) }}
                                            @else
                                                {{ is_scalar($value) ? $value : '' }}
                                            @endif
                                        @endif
                                    @elseif (in_array($column['type'], ['text', 'number'], true) && array_key_exists($column['field'], $records['inlineValues'][$record->id] ?? []))
                                        @php($inlineValue = $records['inlineValues'][$record->id][$column['field']])
                                        <div wire:key="inline-{{ $record->id }}-{{ $column['field'] }}-{{ $locale }}-{{ md5(json_encode($inlineValue)) }}"
                                            x-data="{ editing: false, busy: false, value: @js($inlineValue), async save() { if (this.busy) return; this.busy = true; try { if (await $wire.saveInlineField({{ (int) $record->id }}, @js($column['field']), this.value)) this.editing = false; } finally { this.busy = false; } } }">
                                            <div x-show="!editing" style="display:flex;align-items:center;gap:8px;">
                                                <span>{{ \App\Filament\Bread\BreadCaption::preview($column['type'], $value, $column['translated'] ?? false) }}</span>
                                                <x-filament::icon-button icon="heroicon-o-pencil-square" label="Редактировать поле" x-on:click="editing = true; $nextTick(() => { $refs.inlineInput.focus(); $refs.inlineInput.select(); })" />
                                            </div>
                                            <div x-show="editing" x-cloak style="display:flex;align-items:center;gap:6px;">
                                                <input x-ref="inlineInput" x-model="value" type="{{ $column['type'] === 'number' ? 'number' : 'text' }}" step="any" aria-label="{{ $column['label'] }}" :disabled="busy"
                                                    x-on:keydown.enter.prevent="save()" x-on:keydown.escape.prevent="value = @js($inlineValue); editing = false" style="width:160px;padding:6px;border:1px solid #cbd5e1;border-radius:6px;" />
                                                <x-filament::icon-button icon="heroicon-o-check" label="Сохранить поле" x-on:click="save()" x-bind:disabled="busy" />
                                                <x-filament::icon-button icon="heroicon-o-x-mark" label="Отменить изменение поля" x-on:click="value = {{ \Illuminate\Support\Js::from($inlineValue) }}; editing = false" x-bind:disabled="busy" />
                                            </div>
                                            @error('inline.'.$record->id.'.'.$column['field'])<span role="alert" style="display:block;color:#dc2626;white-space:normal;">{{ $message }}</span>@enderror
                                        </div>
                                    @elseif (in_array($column['type'], ['text', 'number', 'text_area', 'rich_text_box'], true))
                                        <div>{{ \App\Filament\Bread\BreadCaption::preview($column['type'], $value, $column['translated'] ?? false) }}</div>
                                    @elseif ($column['type'] === 'adv_fields_group')
                                        <div class="viar-bread-group-indicators" style="display:flex;align-items:center;gap:8px;">
                                            @foreach (app(\App\Filament\Bread\BreadFieldsGroup::class)->browseIndicators($value, $column['details'] ?? []) as $filled)
                                                <span data-filled="{{ $filled ? '1' : '0' }}" role="img" aria-label="Поле {{ $loop->iteration }}: {{ $filled ? 'заполнено' : 'пусто' }}" style="display:inline-flex;align-items:center;justify-content:center;width:16px;height:16px;">
                                                    @if ($filled)<x-filament::icon :icon="\Filament\Support\Icons\Heroicon::OutlinedCheck" style="width:16px;height:16px;" />
                                                    @else<span aria-hidden="true" style="width:4px;height:4px;background:currentColor;border-radius:50%;"></span>@endif
                                                </span>
                                            @endforeach
                                        </div>
                                        @if (($column['editable'] ?? false) && array_key_exists('browse_inline_editor', $column['details'] ?? []) && ! isset($column['details']['view']) && app(\App\Filament\Bread\BreadRegistry::class)->permitted($bread, 'edit', (int) $record->id))
                                            <x-filament::icon-button icon="heroicon-o-pencil-square" label="Редактировать группу" wire:click="mountAction('editInlineGroup', { id: {{ (int) $record->id }}, field: {{ \Illuminate\Support\Js::from($column['field']) }} })" />
                                        @endif
                                    @elseif (in_array($column['type'], ['adv_json', 'adv_page_layout', 'multiple_checkbox', 'select_multiple'], true))
                                        {{ is_scalar($value) ? \Illuminate\Support\Str::limit((string) $value, 80) : '' }}
                                    @elseif (in_array($column['type'], ['select_dropdown', 'radio_btn'], true) && is_array($column['details']['options'] ?? null) && ! isset($column['details']['relationship']))
                                        {{ is_scalar($value) ? \Illuminate\Support\Str::limit((string) $value, 80) : '' }}
                                    @else
                                        {{ is_scalar($value) ? \Illuminate\Support\Str::limit(strip_tags((string) $value), 80) : '' }}
                                    @endif
                                </td>
                            @endforeach
                            <td class="whitespace-nowrap p-2"><div class="viar-bread-row-actions">
                                @if (app(\App\Filament\Bread\BreadRegistry::class)->permitted($bread, 'read'))
                                    <x-filament::icon-button size="sm" color="gray" :icon="\Filament\Support\Icons\Heroicon::OutlinedEye" label="Просмотр" tooltip="Просмотр" wire:click="openView({{ (int) $record->id }})" />
                                @endif
                                @if ($canEditRecord)
                                    <x-filament::icon-button size="sm" :icon="\Filament\Support\Icons\Heroicon::OutlinedPencilSquare" label="Изменить" tooltip="Изменить" :href="$this->editUrl((int) $record->id)" tag="a" />
                                @endif
                                @if ($bread->name === 'gallery_items' && $canAddRecord && $canEditRecord)
                                    <x-filament::icon-button size="sm" color="gray" :icon="\Filament\Support\Icons\Heroicon::OutlinedDocumentDuplicate" label="Клонировать" tooltip="Клонировать" wire:click="cloneRecord({{ (int) $record->id }})" wire:confirm="Создать копию товара?" />
                                @endif
                                @if (app(\App\Filament\Bread\BreadRegistry::class)->permitted($bread, 'delete'))
                                    <x-filament::icon-button size="sm" color="danger" :icon="\Filament\Support\Icons\Heroicon::OutlinedTrash" label="Удалить" tooltip="Удалить" wire:click="deleteRecord({{ (int) $record->id }})" wire:confirm="Удалить запись?" />
                                @endif
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($records['columns'] ?? []) + 1 + ($canDeleteRecord ? 1 : 0) }}" style="padding:24px;text-align:center;">Записи не найдены</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @php($paginator = $records['rows'])
            @if ($paginator->total() > 0)
                <nav aria-label="Страницы списка" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-top:20px;">
                    <span style="font-size:14px;color:#71717a;">Показаны {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} из {{ $paginator->total() }}</span>
                    @if ($paginator->hasPages())
                        @php($firstPage = max(1, min($paginator->currentPage() - 2, $paginator->lastPage() - 4)))
                        @php($lastPage = min($paginator->lastPage(), $firstPage + 4))
                        <div style="display:flex;align-items:center;flex-wrap:wrap;gap:6px;">
                            <x-filament::button size="sm" color="gray" wire:click="previousPage" :disabled="$paginator->onFirstPage()">Назад</x-filament::button>
                            @if ($firstPage > 1)
                                <x-filament::button size="sm" color="gray" wire:click="gotoPage(1)" aria-label="Страница 1">1</x-filament::button>
                                @if ($firstPage > 2)<span aria-hidden="true">…</span>@endif
                            @endif
                            @for ($number = $firstPage; $number <= $lastPage; $number++)
                                <x-filament::button size="sm" :color="$number === $paginator->currentPage() ? 'primary' : 'gray'" :disabled="$number === $paginator->currentPage()" wire:click="gotoPage({{ $number }})" aria-label="Страница {{ $number }}" :aria-current="$number === $paginator->currentPage() ? 'page' : null">{{ $number }}</x-filament::button>
                            @endfor
                            @if ($lastPage < $paginator->lastPage())
                                @if ($lastPage < $paginator->lastPage() - 1)<span aria-hidden="true">…</span>@endif
                                <x-filament::button size="sm" color="gray" wire:click="gotoPage({{ $paginator->lastPage() }})" aria-label="Страница {{ $paginator->lastPage() }}">{{ $paginator->lastPage() }}</x-filament::button>
                            @endif
                            <x-filament::button size="sm" color="gray" wire:click="nextPage" :disabled="! $paginator->hasMorePages()">Далее</x-filament::button>
                        </div>
                    @endif
                </nav>
            @endif
        </x-filament::section>
    @endif
</x-filament-panels::page>
