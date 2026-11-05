<div>
    <x-filament::button type="button" size="sm" color="gray" wire:click="openFileLibrary('{{ $field }}')">Выбрать из библиотеки</x-filament::button>
    @if ($this->libraryField === $field)
        @php($library = $this->fileLibrary())
        <div style="border:1px solid #d1d5db;border-radius:8px;padding:12px;margin-top:12px;">
            <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-bottom:12px;">
                <strong>{{ $library['folder'] }}</strong>
                <x-filament::button type="button" size="sm" color="gray" wire:click="$set('libraryField', null)">Закрыть библиотеку</x-filament::button>
                @if ($library['parent'])
                    <x-filament::button type="button" size="sm" color="gray" wire:click="browseFileLibrary({{ \Illuminate\Support\Js::from($library['parent']) }})">Папка выше</x-filament::button>
                    @if ($library['canRenameFolder'])
                        <x-filament::button type="button" size="sm" color="gray" wire:click="openLibraryFolderRename({{ \Illuminate\Support\Js::from($library['folder']) }})">Переименовать папку</x-filament::button>
                    @endif
                    @if ($library['canDeleteFolder'])
                        <x-filament::button type="button" size="sm" color="danger" wire:click="deleteEmptyLibraryFolder({{ \Illuminate\Support\Js::from($library['folder']) }})" wire:confirm="Удалить текущую папку? Удаление разрешено только для пустой папки." wire:loading.attr="disabled">Удалить пустую папку</x-filament::button>
                    @endif
                @endif
            </div>
            @if ($this->libraryRenameFolder)
                <div style="border:1px solid #d1d5db;border-radius:8px;padding:12px;margin-bottom:12px;">
                    <p style="overflow-wrap:anywhere;">Папка: {{ $this->libraryRenameFolder }}</p>
                    <p style="margin:8px 0;">Ссылки в файловых полях будут обновлены. Исходная папка останется для старых ссылок в контенте.</p>
                    <x-filament::input.wrapper><x-filament::input type="text" aria-label="Новое имя папки" wire:model="libraryFolderName" maxlength="100" /></x-filament::input.wrapper>
                    <div style="display:flex;gap:8px;margin-top:12px;">
                        <x-filament::button type="button" size="sm" wire:click="renameLibraryFolder" wire:confirm="Применить новое имя папки и обновить связанные файловые поля?" wire:loading.attr="disabled">Применить имя папки</x-filament::button>
                        <x-filament::button type="button" size="sm" color="gray" wire:click="$set('libraryRenameFolder', null)">Отменить переименование папки</x-filament::button>
                    </div>
                </div>
            @endif
            @error('libraryFolderName')<p role="alert" style="color:#dc2626;margin-bottom:12px;">{{ $message }}</p>@enderror
            @if ($library['canCreateFolder'])
                <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-bottom:12px;">
                    <x-filament::input.wrapper>
                        <x-filament::input type="text" aria-label="Название новой папки" placeholder="Название новой папки" wire:model="libraryNewFolder" maxlength="100" />
                    </x-filament::input.wrapper>
                    <x-filament::button type="button" size="sm" color="gray" wire:click="createLibraryFolder" wire:loading.attr="disabled">Создать папку</x-filament::button>
                </div>
                @error('libraryNewFolder')
                    <p role="alert" style="color:#dc2626;margin-bottom:12px;">{{ $message }}</p>
                @enderror
            @endif
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
                @foreach ($library['folders'] as $folder)
                    <x-filament::button type="button" size="sm" color="gray" wire:click="browseFileLibrary({{ \Illuminate\Support\Js::from($folder) }})">📁 {{ basename($folder) }}</x-filament::button>
                @endforeach
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;">
                @forelse ($library['files'] as $file)
                    <div style="border:1px solid #d1d5db;border-radius:8px;padding:8px;">
                    <button type="button" wire:click="chooseLibraryFile('{{ $field }}', {{ \Illuminate\Support\Js::from($file['path']) }})" style="text-align:left;width:100%;">
                        <img src="{{ $file['url'] }}" alt="" loading="lazy" style="width:100%;height:96px;object-fit:contain;">
                        <span style="overflow-wrap:anywhere;">{{ basename($file['path']) }}</span>
                    </button>
                    @if ($library['canRelocate'])
                        <x-filament::button type="button" size="sm" color="gray" style="margin-top:8px;" wire:click="openLibraryRelocation({{ \Illuminate\Support\Js::from($file['path']) }})">Изменить путь</x-filament::button>
                    @endif
                    @if ($library['canCrop'])
                        <x-filament::button type="button" size="sm" color="gray" style="margin-top:8px;" wire:click="openLibraryCrop({{ \Illuminate\Support\Js::from($file['path']) }})">Обрезать</x-filament::button>
                    @endif
                    </div>
                @empty
                    <p>В этой папке нет изображений.</p>
                @endforelse
            </div>
            @if ($this->librarySourceFile)
                <div style="border:1px solid #d1d5db;border-radius:8px;padding:12px;margin-top:12px;">
                    <p style="overflow-wrap:anywhere;">Файл: {{ $this->librarySourceFile }}</p>
                    <p style="margin:8px 0;">Ссылки в файловых полях будут обновлены. Исходник сохранится для старых ссылок в контенте.</p>
                    <label>Новое имя
                        <x-filament::input.wrapper><x-filament::input type="text" aria-label="Новое имя файла" wire:model="libraryTargetName" /></x-filament::input.wrapper>
                    </label>
                    <label>Папка назначения (существующий путь)
                        <x-filament::input.wrapper><x-filament::input type="text" aria-label="Папка назначения" wire:model="libraryTargetFolder" /></x-filament::input.wrapper>
                    </label>
                    @error('libraryTargetName')<p role="alert" style="color:#dc2626;">{{ $message }}</p>@enderror
                    <div style="display:flex;gap:8px;margin-top:12px;">
                        <x-filament::button type="button" size="sm" wire:click="relocateLibraryFile" wire:loading.attr="disabled" wire:confirm="Применить новый путь и обновить связанные файловые поля?">Применить путь</x-filament::button>
                        <x-filament::button type="button" size="sm" color="gray" wire:click="$set('librarySourceFile', null)">Отменить смену пути</x-filament::button>
                    </div>
                </div>
            @endif
            @if ($this->libraryCropFile)
                @php($preview = $this->libraryCropPreview())
                <div wire:key="crop-{{ $this->libraryCropFile }}" x-data="{ crop: $wire.entangle('libraryCrop') }" style="border:1px solid #d1d5db;border-radius:8px;padding:12px;margin-top:12px;">
                    <strong>Обрезка: {{ basename($this->libraryCropFile) }}</strong>
                    <p>Размер: {{ $preview['width'] }} × {{ $preview['height'] }} px. Координаты отсчитываются от левого верхнего угла.</p>
                    <div style="position:relative;display:inline-block;max-width:100%;margin:12px 0;">
                        <img src="{{ $preview['url'] }}" alt="Предпросмотр области обрезки" style="display:block;max-width:100%;max-height:400px;image-orientation:none;">
                        <div aria-hidden="true" style="position:absolute;border:2px solid #2563eb;background:rgba(37,99,235,.15);pointer-events:none;"
                            x-bind:style="{ left: (crop.x / {{ $preview['width'] }} * 100) + '%', top: (crop.y / {{ $preview['height'] }} * 100) + '%', width: (crop.width / {{ $preview['width'] }} * 100) + '%', height: (crop.height / {{ $preview['height'] }} * 100) + '%' }"></div>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:12px;">
                        @foreach (['x' => 'Слева (px)', 'y' => 'Сверху (px)', 'width' => 'Ширина (px)', 'height' => 'Высота (px)'] as $key => $label)
                            <label>{{ $label }}
                                <x-filament::input.wrapper><x-filament::input type="number" aria-label="{{ $label }}" x-model.number="crop.{{ $key }}" min="{{ in_array($key, ['x', 'y']) ? 0 : 1 }}" step="1" /></x-filament::input.wrapper>
                            </label>
                        @endforeach
                    </div>
                    @foreach (['libraryCrop', 'libraryCrop.x', 'libraryCrop.y', 'libraryCrop.width', 'libraryCrop.height'] as $errorKey)
                        @error($errorKey)<p role="alert" style="color:#dc2626;">{{ $message }}</p>@enderror
                    @endforeach
                    <p style="margin-top:12px;">Будет создана новая копия и выбрана в этой форме. Оригинал и другие записи останутся прежними.</p>
                    <div style="display:flex;gap:8px;margin-top:12px;">
                        <x-filament::button type="button" size="sm" wire:click="cropLibraryFile" wire:loading.attr="disabled">Создать обрезанную копию</x-filament::button>
                        <x-filament::button type="button" size="sm" color="gray" wire:click="$set('libraryCropFile', null)">Отменить обрезку</x-filament::button>
                    </div>
                </div>
            @endif
            <div style="display:flex;gap:8px;margin-top:12px;">
                @if ($this->libraryPage > 1)
                    <x-filament::button type="button" size="sm" color="gray" wire:click="browseFileLibrary({{ \Illuminate\Support\Js::from($library['folder']) }}, {{ $this->libraryPage - 1 }})">Назад</x-filament::button>
                @endif
                @if ($library['hasNext'])
                    <x-filament::button type="button" size="sm" color="gray" wire:click="browseFileLibrary({{ \Illuminate\Support\Js::from($library['folder']) }}, {{ $this->libraryPage + 1 }})">Далее</x-filament::button>
                @endif
            </div>
        </div>
    @endif
</div>
