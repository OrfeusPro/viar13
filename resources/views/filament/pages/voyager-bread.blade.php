<x-filament-panels::page>
    <style>
        .viar-bread-media-preview { width: 96px; height: 96px; object-fit: cover; display: block; }
        .viar-bread-media-row { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px; }
        .viar-bread-media-card { border: 1px solid #d1d5db; border-radius: 8px; padding: 12px; margin-bottom: 16px; }
        .viar-bread-media-fields { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px; }
        .viar-bread-media-actions { display: flex; gap: 8px; margin-top: 12px; }
        .viar-bread-table { width: max-content; min-width: 100%; border-collapse: separate; border-spacing: 0; }
        .viar-bread-table th, .viar-bread-table td { min-width: 150px; max-width: 240px; padding: 9px 12px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        .viar-bread-table th { white-space: nowrap; font-weight: 600; }
        .viar-bread-table th:first-child, .viar-bread-table td:first-child { min-width: 75px; }
        .viar-bread-table th:last-child, .viar-bread-table td:last-child { position: sticky; right: 0; min-width: 120px; background: white; border-left: 1px solid #e5e7eb; }
        .dark .viar-bread-table th:last-child, .dark .viar-bread-table td:last-child { background: #18181b; }
    </style>
    @php($bread = $this->bread())
    @php($records = ! $editing && $viewId === null ? $this->records() : [])
    @if (! app(\App\Filament\Bread\BreadRegistry::class)->model($bread))
        <x-filament::section>
            Модель или таблица этого раздела недоступна. Редактирование заблокировано до исправления конфигурации Voyager.
        </x-filament::section>
    @elseif ($viewId !== null)
        <x-filament::section>
            <div class="mb-4 flex flex-wrap gap-2" style="margin-bottom: 24px;">
                @foreach (app(\App\Filament\Bread\BreadRegistry::class)->locales() as $language)
                    <x-filament::button size="sm" :color="$locale === $language ? 'primary' : 'gray'" wire:click="changeLocale('{{ $language }}')">{{ strtoupper($language) }}</x-filament::button>
                @endforeach
            </div>
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
            <div class="mb-4 flex flex-wrap gap-2" style="margin-bottom: 24px;">
                @foreach (app(\App\Filament\Bread\BreadRegistry::class)->locales() as $language)
                    <x-filament::button size="sm" :color="$locale === $language ? 'primary' : 'gray'" wire:click="changeLocale('{{ $language }}')" :disabled="$recordId === null && $language !== config('voyager.multilingual.default', 'en')">
                        {{ strtoupper($language) }}
                    </x-filament::button>
                @endforeach
            </div>
            <form wire:submit="save" class="space-y-5">
                {{ $this->form }}
                <div class="flex gap-2" style="margin-top: 24px;">
                    <x-filament::button type="submit">Сохранить</x-filament::button>
                    <x-filament::button color="gray" wire:click="cancel" type="button">Отмена</x-filament::button>
                </div>
            </form>
        </x-filament::section>
        @foreach ($this->mediaSections() as $section)
            <x-filament::section :heading="$section['label']">
                <div class="mb-4 flex items-center gap-3">
                    <input type="file" wire:model="mediaUpload" accept="image/jpeg,image/png,image/webp,image/gif">
                    <x-filament::button wire:click="uploadMedia('{{ $section['field'] }}')">Добавить изображение</x-filament::button>
                </div>
                @error('mediaUpload') <p class="text-sm text-danger-600">{{ $message }}</p> @enderror
                <div class="space-y-5">
                    @foreach ($section['files'] as $media)
                        <div class="viar-bread-media-card" wire:key="bread-media-{{ $media->id }}">
                            <div class="viar-bread-media-row">
                                <img src="{{ $media->getUrl() }}" alt="" class="viar-bread-media-preview">
                                <div><p class="font-semibold">{{ $media->file_name }}</p><p class="text-sm">ID {{ $media->id }}</p></div>
                            </div>
                            <div class="viar-bread-media-fields">
                                <label>Title <x-filament::input.wrapper><x-filament::input wire:model="mediaProperties.{{ $media->id }}.title" /></x-filament::input.wrapper></label>
                                <label>ALT <x-filament::input.wrapper><x-filament::input wire:model="mediaProperties.{{ $media->id }}.alt" /></x-filament::input.wrapper></label>
                                @foreach ($section['extra'] as $key => $definition)
                                    <label>{{ $definition['title'] ?? $key }}<x-filament::input.wrapper><x-filament::input wire:model="mediaProperties.{{ $media->id }}.{{ $key }}" /></x-filament::input.wrapper></label>
                                @endforeach
                            </div>
                            <div class="viar-bread-media-actions">
                                <x-filament::button size="sm" wire:click="saveMediaProperties('{{ $section['field'] }}', {{ $media->id }})">Сохранить подписи</x-filament::button>
                                <x-filament::button size="sm" color="danger" wire:click="deleteMedia('{{ $section['field'] }}', {{ $media->id }})" wire:confirm="Удалить файл?">Удалить файл</x-filament::button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endforeach
    @else
        @if (app(\App\Filament\Bread\BreadRegistry::class)->permitted($bread, 'add'))
            <div><x-filament::button wire:click="openCreate">Создать запись</x-filament::button></div>
        @endif
        <x-filament::section>
            <div class="mb-4 flex flex-wrap gap-2" style="margin-bottom: 24px;" role="group" aria-label="Язык записей">
                @foreach (app(\App\Filament\Bread\BreadRegistry::class)->locales() as $language)
                    <x-filament::button size="sm" :color="$locale === $language ? 'primary' : 'gray'" :aria-pressed="$locale === $language ? 'true' : 'false'" wire:click="changeLocale('{{ $language }}')">{{ strtoupper($language) }}</x-filament::button>
                @endforeach
            </div>
            <div class="mb-4 max-w-md"><x-filament::input.wrapper><x-filament::input type="search" wire:model.live.debounce.350ms="search" aria-label="Поиск по текстовым полям" placeholder="Поиск по текстовым полям" /></x-filament::input.wrapper></div>
            <div class="overflow-x-auto">
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
                                        {{ $value ? 'Да' : 'Нет' }}
                                    @else
                                        {{ is_scalar($value) ? \Illuminate\Support\Str::limit(strip_tags((string) $value), 80) : '' }}
                                    @endif
                                </td>
                            @endforeach
                            <td class="whitespace-nowrap p-2"><div style="display:flex;align-items:center;gap:8px">
                                @if (app(\App\Filament\Bread\BreadRegistry::class)->permitted($bread, 'read'))
                                    <x-filament::icon-button size="sm" color="gray" :icon="\Filament\Support\Icons\Heroicon::OutlinedEye" label="Просмотр" tooltip="Просмотр" wire:click="openView({{ (int) $record->id }})" />
                                @endif
                                @if (app(\App\Filament\Bread\BreadRegistry::class)->permitted($bread, 'edit'))
                                    <x-filament::icon-button size="sm" :icon="\Filament\Support\Icons\Heroicon::OutlinedPencilSquare" label="Изменить" tooltip="Изменить" wire:click="openEdit({{ (int) $record->id }})" />
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
