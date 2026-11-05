<x-filament-panels::page>
    @assets
    <link rel="stylesheet" href="{{ asset('vendor/cropperjs/cropper.min.css') }}">
    <script src="{{ asset('vendor/cropperjs/cropper.min.js') }}"></script>
    <script src="{{ asset('js/filament/media-library-crop.js') }}"></script>
    <script src="{{ asset('js/filament/media-library-upload.js') }}"></script>
    @endassets
    @php($listing = $this->listing())
    <x-filament::section>
        <div x-data="{ tool: null }">
        <div role="group" aria-label="Инструменты медиатеки" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-bottom:16px;padding:12px;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:8px">
            @if(config('voyager.media.allow_upload', true))
                <x-filament::button type="button" size="sm" icon="heroicon-o-arrow-up-tray" x-on:click="tool = tool === 'upload' ? null : 'upload'" x-bind:aria-expanded="tool === 'upload'" aria-controls="media-upload-form">Загрузить файлы</x-filament::button>
            @endif
            @if(config('voyager.media.allow_create_folder', true) && config('voyager.media.show_folders', true))
                <x-filament::button type="button" size="sm" icon="heroicon-o-folder-plus" x-on:click="tool = tool === 'folder' ? null : 'folder'" x-bind:aria-expanded="tool === 'folder'" aria-controls="media-create-folder-form">Новая папка</x-filament::button>
            @endif
            <x-filament::button size="sm" wire:click="refreshFiles" x-on:click="tool = null" color="gray" icon="heroicon-o-arrow-path" wire:loading.attr="disabled">Обновить</x-filament::button>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:16px;align-items:center;margin-bottom:24px">
            <x-filament::button wire:click="browse({{ \Illuminate\Support\Js::from($this->rootFolder()) }})" color="gray" icon="heroicon-o-home">Корневая папка</x-filament::button>
            @if($folder !== $this->rootFolder())
                <x-filament::button wire:click="browse({{ \Illuminate\Support\Js::from(dirname($folder) === '.' ? '' : dirname($folder)) }})" color="gray">На уровень выше</x-filament::button>
            @endif
            <nav aria-label="Путь папки" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;overflow-wrap:anywhere">
                @foreach($this->folderTrail() as $part)
                    @if(!$loop->first)<span aria-hidden="true">›</span>@endif
                    @if($loop->last)
                        <strong aria-current="page">{{ $part['label'] }}</strong>
                    @else
                        <button type="button" style="color:#2563eb;text-decoration:underline" wire:click="browse({{ \Illuminate\Support\Js::from($part['path']) }})">{{ $part['label'] }}</button>
                    @endif
                @endforeach
            </nav>
            <span>Файлов: {{ $listing['total'] }}</span>
        </div>
        @if(config('voyager.media.allow_upload', true))
            <div id="media-upload-form" x-data="mediaLibraryUpload($wire)" x-show="tool === 'upload'" x-cloak
                style="display:grid;min-width:0;gap:12px;margin-bottom:24px">
                <div x-on:dragover.prevent="dragging = true" x-on:dragleave.prevent="dragging = false" x-on:drop.prevent="drop($event)"
                    x-bind:style="{background: dragging ? '#eff6ff' : 'transparent'}"
                    style="min-width:0;padding:20px;border:2px dashed #9ca3af;border-radius:8px">
                    <label for="media-uploads" style="display:block;margin-bottom:12px">Перетащите файлы сюда или выберите их</label>
                    <input id="media-uploads" type="file" multiple x-bind:disabled="busy" style="display:block;min-width:0;width:100%;max-width:100%"
                        x-on:change="receive($event.target.files); $event.target.value = ''">
                    <small style="display:block;margin-top:8px">До 20 файлов, до 10 МБ каждый. Загрузка начинается автоматически.</small>
                </div>
                <ul aria-label="Файлы для загрузки" x-show="names.length" style="overflow-wrap:anywhere">
                    <template x-for="(name, index) in names" :key="index"><li x-text="name"></li></template>
                </ul>
                <div x-show="busy" role="status" aria-live="polite">
                    <label for="media-upload-progress" x-text="saving ? 'Сохранение файлов…' : 'Передача файлов: ' + progress + '%'"></label>
                    <progress id="media-upload-progress" max="100" x-bind:value="progress" style="display:block;width:100%"></progress>
                </div>
                <p role="alert" x-show="error" x-text="error" style="color:#dc2626"></p>
                <p role="status" x-show="!busy && status" x-text="status"></p>
                <div style="display:flex;gap:12px">
                    <x-filament::button type="button" size="sm" color="gray" x-show="busy && !saving" x-on:click="cancel()">Отменить передачу</x-filament::button>
                    <x-filament::button type="button" size="sm" color="gray" x-bind:disabled="busy" x-on:click="tool = null">Закрыть загрузку</x-filament::button>
                </div>
            </div>
            @error('uploads')<p role="alert">{{ $message }}</p>@enderror
            @foreach($errors->get('uploads.*') as $messages)
                @foreach($messages as $message)<p role="alert">{{ $message }}</p>@endforeach
            @endforeach
        @endif
        @if(config('voyager.media.allow_create_folder', true) && config('voyager.media.show_folders', true))
        <form id="media-create-folder-form" x-show="tool === 'folder'" x-cloak wire:submit="createFolder" style="display:flex;flex-wrap:wrap;align-items:end;gap:12px;margin-bottom:24px">
            <div>
                <label for="media-new-folder" style="display:block;margin-bottom:6px">Имя новой папки</label>
                <input id="media-new-folder" wire:model="newFolder" maxlength="100" style="border:1px solid #d1d5db;padding:8px;border-radius:6px">
                @error('newFolder')<p role="alert">{{ $message }}</p>@enderror
            </div>
            <x-filament::button type="submit" icon="heroicon-o-folder-plus" wire:loading.attr="disabled" wire:target="createFolder">Создать папку</x-filament::button>
            <x-filament::button type="button" color="gray" x-on:click="tool = null">Отмена</x-filament::button>
        </form>
        @endif
        @if($renameFolder !== null)
            <form wire:submit="renameDirectory" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;margin-bottom:24px">
                <label for="media-folder-name">Новое имя: {{ $renameFolder }}</label>
                <input id="media-folder-name" wire:model="folderName" maxlength="100" style="border:1px solid #d1d5db;padding:8px;border-radius:6px">
                <x-filament::button type="submit">Переименовать</x-filament::button>
                <x-filament::button type="button" wire:click="$set('renameFolder', null)" color="gray">Отмена</x-filament::button>
            </form>
        @endif
        @error('folderName')<p role="alert" style="margin-bottom:16px;color:#dc2626">{{ $message }}</p>@enderror
        @if($movingFolder !== null)
            <form wire:submit="moveFolder" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;margin-bottom:24px">
                <strong style="overflow-wrap:anywhere">{{ $movingFolder }}</strong>
                <label>Папка назначения <input wire:model="folderDestination" style="border:1px solid #d1d5db;padding:8px;border-radius:6px"></label>
                <x-filament::button type="submit" icon="heroicon-o-folder-arrow-down" wire:confirm="Переместить папку и обновить ссылки на её файлы?">Переместить папку</x-filament::button>
                <x-filament::button type="button" color="gray" wire:click="$set('movingFolder', null)">Отменить перемещение папки</x-filament::button>
                @error('folderDestination')<p role="alert" style="color:#dc2626">{{ $message }}</p>@enderror
            </form>
        @endif
        @if($sourceFile !== null)
            <form wire:submit="relocateFile" style="display:grid;gap:12px;margin-bottom:24px">
                <strong>{{ $sourceFile }}</strong>
                <label>Имя файла <input wire:model="fileName" maxlength="200" style="border:1px solid #d1d5db;padding:8px;border-radius:6px"></label>
                <label>Папка назначения <input wire:model="targetFolder" style="border:1px solid #d1d5db;padding:8px;border-radius:6px"></label>
                <div><x-filament::button type="submit">Применить</x-filament::button>
                <x-filament::button type="button" wire:click="$set('sourceFile', null)" color="gray">Отмена</x-filament::button></div>
                @error('fileName')<p role="alert">{{ $message }}</p>@enderror
            </form>
        @endif
        @if($listing['files'] !== [] || $listing['folders'] !== [])
            <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;margin-bottom:20px">
                <x-filament::button wire:click="selectPageFiles" color="gray">Выбрать объекты страницы</x-filament::button>
                @if($selectedFiles !== [])
                    <span>Выбрано: {{ count($selectedFiles) }}</span>
                    <x-filament::button wire:click="clearFileSelection" color="gray">Снять выделение</x-filament::button>
                    @if(config('voyager.media.allow_move', true))
                        <label>Папка назначения <input wire:model="bulkFolder" placeholder="{{ $this->rootFolder() === '' ? 'Пусто — корневая папка' : $this->rootFolder() }}" style="border:1px solid #d1d5db;padding:8px;border-radius:6px"></label>
                        <x-filament::button wire:click="moveSelectedFiles" wire:confirm="Переместить выбранные файлы в указанную папку?">Переместить выбранные</x-filament::button>
                    @endif
                    @if(config('voyager.media.allow_delete', true))
                        <x-filament::button wire:click="deleteSelectedFiles" color="danger" icon="heroicon-o-trash"
                            wire:confirm="Удалить выбранные файлы без возможности восстановления? Ссылки на них перестанут работать.">Удалить выбранные</x-filament::button>
                    @endif
                @endif
            </div>
            @error('bulkFolder')<p role="alert" style="margin-bottom:16px;color:#dc2626">{{ $message }}</p>@enderror
            @error('selectedFiles')<p role="alert" style="margin-bottom:16px;color:#dc2626">{{ $message }}</p>@enderror
        @endif
        <x-filament::modal id="media-crop" width="2xl" x-on:modal-closed="$wire.set('cropFile', null)">
            <x-slot name="heading">Обрезка изображения</x-slot>
        @if($cropFile !== null)
            @php($preview = $this->cropPreview())
            <div wire:key="media-crop-{{ $cropFile }}"
                x-data="mediaLibraryCrop($wire)"
                x-on:modal-opened.window="if ($event.detail.id === 'media-crop') scheduleEditor()"
                style="border:1px solid #d1d5db;border-radius:8px;padding:16px;margin-bottom:24px">
                <h2 style="font-weight:600">Обрезка: {{ basename($cropFile) }}</h2>
                <p>Размер: {{ $preview['width'] }} × {{ $preview['height'] }} px. Выделите область мышью или пальцем.</p>
                @if($preview['mime'] === 'image/gif')<p>Обрезка GIF сохраняет первый кадр без анимации.</p>@endif
                <div wire:ignore style="margin:12px 0;height:360px;max-width:100%;overflow:hidden">
                    <img x-ref="image" src="{{ $preview['url'] }}" alt="Предпросмотр области обрезки" draggable="false"
                        style="display:block;max-width:100%;max-height:360px">
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:16px">
                    @foreach(['x' => 'Слева (px)', 'y' => 'Сверху (px)', 'width' => 'Ширина (px)', 'height' => 'Высота (px)'] as $key => $label)
                        <label>{{ $label }}<input type="number" x-model.number="crop.{{ $key }}" x-on:change="$nextTick(() => setRegion())" @if(in_array($key, ['width', 'height'])) min="1" @endif step="1" style="display:block;width:130px;border:1px solid #d1d5db;padding:8px;border-radius:6px"></label>
                    @endforeach
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:12px">
                    <x-filament::button wire:click="cropImage(false)" wire:confirm="Заменить исходное изображение обрезанным?" wire:loading.attr="disabled">Обрезать</x-filament::button>
                    <x-filament::button wire:click="cropImage(true)" wire:loading.attr="disabled">Обрезать и создать</x-filament::button>
                    <x-filament::button x-on:click="$dispatch('close-modal', {id: 'media-crop'})" color="gray">Отмена обрезки</x-filament::button>
                </div>
            </div>
        @endif
        @foreach(['libraryCrop', 'libraryCrop.x', 'libraryCrop.y', 'libraryCrop.width', 'libraryCrop.height'] as $errorKey)
            @error($errorKey)<p role="alert" style="color:#dc2626;margin-bottom:16px">{{ $message }}</p>@enderror
        @endforeach
        </x-filament::modal>
        @php($imagePreview = collect($listing['files'])->firstWhere('path', $previewFile))
        <x-filament::modal id="media-image-preview" width="5xl" x-on:modal-closed="$wire.set('previewFile', null)">
            <x-slot name="heading">Просмотр изображения</x-slot>
            @if($imagePreview && $imagePreview['image'])
                <figure wire:key="media-preview-{{ $imagePreview['path'] }}">
                    <img src="{{ $imagePreview['url'] }}" alt="{{ $imagePreview['name'] }}"
                        style="display:block;max-width:100%;max-height:70vh;margin:0 auto;object-fit:contain">
                    <figcaption style="margin-top:12px;overflow-wrap:anywhere">{{ $imagePreview['name'] }}</figcaption>
                </figure>
            @endif
            <x-slot name="footer"><x-filament::button color="gray" x-on:click="$dispatch('close-modal', {id: 'media-image-preview'})">Закрыть просмотр</x-filament::button></x-slot>
        </x-filament::modal>
        @php($inspected = collect($listing['files'])->firstWhere('path', $infoFile))
        <style>
            .media-content-layout { display:flex; flex-wrap:wrap; align-items:flex-start; gap:24px; }
            .media-files-grid { flex:1 1 480px; min-width:0; }
            .media-file-details { flex:0 1 340px; min-width:0; }
            .media-file-details dt { color:#6b7280; margin-top:8px; }
            .media-file-details a { color:#2563eb; text-decoration:underline; }
            @media(max-width:900px) { .media-file-details { flex-basis:100%; } .media-files-grid { flex-basis:100%; } }
        </style>
        <div class="media-content-layout">
        <div class="media-files-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px">
            @foreach($listing['folders'] as $path)
                <div>
                <label style="display:flex;gap:8px;margin-bottom:8px"><input type="checkbox" aria-label="Выбрать папку {{ basename($path) }}" wire:model.live="selectedFiles" value="{{ $path }}">Выбрать</label>
                <button type="button" x-on:click="$wire.selectCard({{ \Illuminate\Support\Js::from($path) }}, $event.ctrlKey || $event.metaKey, $event.shiftKey)"
                    x-on:dblclick="$wire.browse({{ \Illuminate\Support\Js::from($path) }})"
                    aria-pressed="{{ in_array($path, $selectedFiles, true) ? 'true' : 'false' }}"
                    style="background:{{ in_array($path, $selectedFiles, true) ? '#eff6ff' : 'transparent' }};display:block;width:100%;padding:20px;border:1px solid #d1d5db;border-radius:8px;overflow-wrap:anywhere;text-align:left">
                    <x-filament::icon icon="heroicon-o-folder" style="width:28px;height:28px;margin-bottom:8px" />
                    {{ basename($path) }}
                </button>
                <div style="display:flex;gap:6px;margin-top:8px">
                    <x-filament::icon-button type="button" color="gray" icon="heroicon-o-folder-open" label="Открыть папку {{ basename($path) }}" style="margin:0;width:36px;height:36px;border:1px solid #d1d5db;border-radius:6px;background:#fff" wire:click="browse({{ \Illuminate\Support\Js::from($path) }})" />
                    @if(config('voyager.media.allow_move', true))<x-filament::icon-button type="button" color="gray" icon="heroicon-o-folder-arrow-down" label="Переместить папку {{ basename($path) }}" style="margin:0;width:36px;height:36px;border:1px solid #d1d5db;border-radius:6px;background:#fff" wire:click="openFolderMove({{ \Illuminate\Support\Js::from($path) }})" />@endif
                    @if(config('voyager.media.allow_rename', true))<x-filament::icon-button type="button" color="gray" icon="heroicon-o-pencil-square" label="Переименовать папку {{ basename($path) }}" style="margin:0;width:36px;height:36px;border:1px solid #d1d5db;border-radius:6px;background:#fff" wire:click="editFolder({{ \Illuminate\Support\Js::from($path) }})" />@endif
                    @if(config('voyager.media.allow_delete', true))<x-filament::icon-button type="button" color="danger" icon="heroicon-o-trash" label="Удалить пустую папку {{ basename($path) }}" style="margin:0;width:36px;height:36px;border:1px solid #d1d5db;border-radius:6px;background:#fff" wire:click="deleteFolder({{ \Illuminate\Support\Js::from($path) }})" wire:confirm="Удалить пустую папку?" />@endif
                </div>
                </div>
            @endforeach
            @foreach($listing['files'] as $file)
                <div>
                <label style="display:flex;gap:8px;margin-bottom:8px"><input type="checkbox" aria-label="Выбрать {{ $file['name'] }}" wire:model.live="selectedFiles" value="{{ $file['path'] }}">Выбрать</label>
                <button type="button" x-on:click="$wire.selectCard({{ \Illuminate\Support\Js::from($file['path']) }}, $event.ctrlKey || $event.metaKey, $event.shiftKey)"
                    @if($file['image']) x-on:dblclick="$wire.openImage({{ \Illuminate\Support\Js::from($file['path']) }})" @endif
                    aria-pressed="{{ in_array($file['path'], $selectedFiles, true) ? 'true' : 'false' }}"
                    style="width:100%;text-align:left;background:{{ in_array($file['path'], $selectedFiles, true) ? '#eff6ff' : 'transparent' }};display:block;padding:12px;border:1px solid #d1d5db;border-radius:8px;overflow-wrap:anywhere">
                    @if($file['image'])
                        <img src="{{ $file['url'] }}" alt="" loading="lazy" style="width:100%;height:120px;object-fit:contain;margin-bottom:12px">
                    @else
                        <x-filament::icon icon="heroicon-o-document" style="width:36px;height:36px;margin-bottom:12px" />
                    @endif
                    <p>{{ $file['name'] }}</p>
                    <small>{{ number_format($file['size'] / 1024, 1) }} KB · {{ $file['mime'] }}</small>
                </button>
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px">
                @if($file['image'])<x-filament::icon-button type="button" color="gray" style="margin:0;width:36px;height:36px;border:1px solid #d1d5db;border-radius:6px;background:#fff" icon="heroicon-o-magnifying-glass-plus" label="Просмотреть {{ $file['name'] }}" wire:click="openImage({{ \Illuminate\Support\Js::from($file['path']) }})" />@endif
                <x-filament::icon-button type="button" color="gray" style="margin:0;width:36px;height:36px;border:1px solid #d1d5db;border-radius:6px;background:#fff" icon="heroicon-o-information-circle" label="Сведения" wire:click="inspectFile({{ \Illuminate\Support\Js::from($file['path']) }})" />
                @foreach($file['thumbnails'] as $thumbnail)
                    <a href="{{ $thumbnail['url'] }}" target="_blank" rel="noopener noreferrer" style="display:block;margin-top:8px;overflow-wrap:anywhere">{{ $thumbnail['name'] }}</a>
                @endforeach
                @if(config('voyager.media.allow_rename', true) || config('voyager.media.allow_move', true))<x-filament::icon-button type="button" color="gray" style="margin:0;width:36px;height:36px;border:1px solid #d1d5db;border-radius:6px;background:#fff" icon="heroicon-o-pencil-square" label="Переименовать / переместить" wire:click="editFile({{ \Illuminate\Support\Js::from($file['path']) }})" />@endif
                @if(config('voyager.media.allow_crop', true) && in_array($file['mime'], ['image/jpeg', 'image/png', 'image/webp', 'image/bmp', 'image/gif'], true))
                    <x-filament::icon-button type="button" color="gray" style="margin:0;width:36px;height:36px;border:1px solid #d1d5db;border-radius:6px;background:#fff" icon="heroicon-o-scissors" label="Обрезать" wire:click="openCrop({{ \Illuminate\Support\Js::from($file['path']) }})" />
                @endif
                </div>
                </div>
            @endforeach
        </div>
        @if($inspected)
            <aside aria-label="Сведения о файле" class="media-file-details" style="border:1px solid #d1d5db;border-radius:8px;padding:16px;overflow-wrap:anywhere">
                @if($inspected['image'])
                    <img src="{{ $inspected['url'] }}" alt="Предпросмотр выбранного файла" style="display:block;width:100%;max-height:280px;object-fit:contain;margin-bottom:16px">
                @elseif(str_starts_with($inspected['mime'], 'video/'))
                    <video wire:key="media-video-{{ $inspected['path'] }}" aria-label="Предпросмотр видео" controls preload="metadata"
                        style="display:block;width:100%;max-height:280px;margin-bottom:16px">
                        <source src="{{ $inspected['url'] }}" type="{{ $inspected['mime'] }}">
                        Ваш браузер не поддерживает воспроизведение видео.
                    </video>
                @elseif(str_starts_with($inspected['mime'], 'audio/'))
                    <x-filament::icon icon="heroicon-o-musical-note" style="width:48px;height:48px;margin-bottom:12px" />
                    <audio wire:key="media-audio-{{ $inspected['path'] }}" aria-label="Предпросмотр аудио" controls preload="metadata"
                        style="display:block;width:100%;margin-bottom:16px">
                        <source src="{{ $inspected['url'] }}" type="{{ $inspected['mime'] }}">
                        Ваш браузер не поддерживает воспроизведение аудио.
                    </audio>
                @else
                    <x-filament::icon icon="heroicon-o-document" style="width:48px;height:48px;margin-bottom:16px" />
                @endif
                <h2 style="font-weight:600;margin-bottom:12px">{{ $inspected['name'] }}</h2>
                @if(config('voyager.media.allow_rename', true))
                    <form wire:key="media-detail-name-{{ $inspected['path'] }}" x-data="{}"
                        x-on:submit.prevent="$wire.renameInspectedFile($refs.name.value)" style="margin-bottom:16px">
                        <label for="media-detail-name" style="display:block;margin-bottom:6px">Имя файла</label>
                        <div style="display:flex;gap:6px;align-items:center">
                            <input id="media-detail-name" x-ref="name" value="{{ $inspected['name'] }}" maxlength="200"
                                style="min-width:0;width:100%;border:1px solid #d1d5db;padding:8px;border-radius:6px">
                            <x-filament::icon-button type="submit" icon="heroicon-o-check" label="Сохранить имя файла" style="margin:0;width:36px;height:36px;flex-shrink:0" wire:loading.attr="disabled" wire:target="renameInspectedFile" />
                        </div>
                        <small>Enter — переименовать.</small>
                        @error('fileName')<p role="alert" style="color:#dc2626">{{ $message }}</p>@enderror
                    </form>
                @endif
                <dl style="display:grid;grid-template-columns:1fr;gap:6px">
                    <dt>Тип</dt><dd>{{ $inspected['mime'] }}</dd>
                    <dt>Размер</dt><dd>{{ number_format($inspected['size'] / 1024, 1) }} KB</dd>
                    <dt>Публичная ссылка</dt><dd><a href="{{ $inspected['url'] }}" target="_blank" rel="noopener noreferrer">{{ $inspected['url'] }}</a></dd>
                    <dt>Дата изменения</dt><dd>{{ \Illuminate\Support\Carbon::createFromTimestamp($inspected['modified'], config('app.timezone'))->format('Y-m-d H:i:s') }}</dd>
                </dl>
                <x-filament::button wire:click="$set('infoFile', null)" color="gray" style="margin-top:16px">Закрыть сведения</x-filament::button>
            </aside>
        @endif
        </div>
        @if($listing['pages'] > 1)
            <div style="display:flex;gap:16px;margin-top:24px;align-items:center">
                <x-filament::button wire:click="setFilePage({{ $listing['page'] - 1 }})" :disabled="$listing['page'] === 1" color="gray">Назад</x-filament::button>
                <span>{{ $listing['page'] }} / {{ $listing['pages'] }}</span>
                <x-filament::button wire:click="setFilePage({{ $listing['page'] + 1 }})" :disabled="$listing['page'] === $listing['pages']" color="gray">Далее</x-filament::button>
            </div>
        @endif
        </div>
    </x-filament::section>
</x-filament-panels::page>
