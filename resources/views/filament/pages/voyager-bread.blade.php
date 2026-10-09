<x-filament-panels::page class="viar-bread-page">
    <style>
        .viar-bread-media-preview { width: 96px; height: 96px; object-fit: cover; display: block; }
        .viar-bread-media-row { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px; }
        .viar-bread-media-card { border: 1px solid #d1d5db; border-radius: 8px; padding: 12px; margin-bottom: 16px; }
        .viar-bread-media-fields { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px; }
        .viar-bread-media-actions { display: flex; gap: 8px; margin-top: 12px; }
        .viar-bread-page .fi-page-main, .viar-bread-page .fi-section { min-width: 0; }
        .viar-bread-search { max-width: 28rem; margin-bottom: 24px; }
        .viar-bread-table-scroll { width: 100%; max-width: 100%; overflow-x: auto; position: relative; border: 1px solid #dbe3ee; border-radius: 10px; }
        .viar-bread-table { width: max-content; min-width: 100%; border-collapse: separate; border-spacing: 0; }
        .viar-bread-table th, .viar-bread-table td { min-width: 150px; max-width: 240px; padding: 9px 12px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        .viar-bread-table thead th { padding: 13px 12px; text-align: left; vertical-align: middle; white-space: nowrap; font-weight: 600; color: #334155; background: #eef2f8; border-bottom: 1px solid #dbe3ee; }
        .viar-bread-table th:first-child, .viar-bread-table td:first-child { min-width: 75px; }
        .viar-bread-table th:last-child, .viar-bread-table td:last-child { position: sticky; right: 0; width: 160px; min-width: 160px; max-width: 160px; z-index: 1; background: white; border-left: 1px solid #e5e7eb; box-shadow: -4px 0 6px -4px #9ca3af; }
        .dark .viar-bread-table th:last-child, .dark .viar-bread-table td:last-child { background: #18181b; }
        .viar-bread-table thead th:last-child { background: #eef2f8; }
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
        @if ($canAddRecord)
            <div><x-filament::button wire:click="openCreate">Создать запись</x-filament::button></div>
        @endif
        <x-filament::section>
            @if ($bread->name !== 'users')
            <div class="mb-4 flex flex-wrap gap-2" style="margin-bottom: 24px;" role="group" aria-label="Язык записей">
                @foreach (app(\App\Filament\Bread\BreadRegistry::class)->locales() as $language)
                    <x-filament::button size="sm" :color="$locale === $language ? 'primary' : 'gray'" :aria-pressed="$locale === $language ? 'true' : 'false'" wire:click="changeLocale('{{ $language }}')">{{ strtoupper($language) }}</x-filament::button>
                @endforeach
            </div>
            @endif
            <div class="viar-bread-search"><x-filament::input.wrapper><x-filament::input type="search" wire:model.live.debounce.350ms="search" aria-label="Поиск по текстовым полям" placeholder="Поиск по текстовым полям" /></x-filament::input.wrapper></div>
            <div class="viar-bread-table-scroll" tabindex="0" role="region" aria-label="Таблица записей с горизонтальной прокруткой">
                <table class="viar-bread-table text-sm">
                    <thead><tr class="border-b text-left">
                        @foreach ($records['columns'] ?? [] as $column)<th class="p-2">{{ $column['label'] }}</th>@endforeach
                        <th class="p-2">Действия</th>
                    </tr></thead>
                    <tbody>
                    @foreach ($records['rows'] ?? [] as $record)
                        <tr class="border-b">
                            @foreach ($records['columns'] as $column)
                                @php($value = $record->{$column['field']} ?? null)
                                <td class="max-w-64 truncate p-2" title="{{ is_scalar($value) ? \Illuminate\Support\Str::limit(strip_tags((string) $value), 300) : '' }}">
                                    @if (in_array($column['type'], ['image', 'multiple_images', 'media_picker', 'adv_media_files'], true) || ($column['type'] === 'file' && \App\Filament\Bread\BreadImage::urls($value) !== []))
                                        @php($images = $column['type'] === 'adv_media_files' ? (is_array($value) ? $value : []) : \App\Filament\Bread\BreadImage::urls($value))
                                        <div style="display:flex;align-items:center;gap:6px;max-width:220px;overflow:hidden;">
                                            @foreach (array_slice($images, 0, 3) as $url)
                                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"><img src="{{ $url }}" alt="{{ $column['label'] }}" loading="lazy" style="width:52px;height:52px;object-fit:cover;border-radius:6px;"></a>
                                            @endforeach
                                            @if (count($images) > 3)<span>+{{ count($images) - 3 }}</span>@endif
                                        </div>
                                    @elseif ($column['type'] === 'checkbox')
                                        {{ app(\App\Filament\Bread\BreadCheckbox::class)->caption($value, $column['details'] ?? []) }}
                                    @else
                                        {{ is_scalar($value) ? \Illuminate\Support\Str::limit(strip_tags((string) $value), 80) : '' }}
                                    @endif
                                </td>
                            @endforeach
                            <td class="whitespace-nowrap p-2"><div style="display:flex;align-items:center;gap:8px">
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
                    @endforeach
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
