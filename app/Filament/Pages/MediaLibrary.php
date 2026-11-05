<?php

namespace App\Filament\Pages;

use App\Filament\Bread\BreadLibraryFiles;
use App\Filament\Bread\BreadLibraryFolders;
use App\Filament\Bread\BreadRegistry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\WithFileUploads;

class MediaLibrary extends Page
{
    use WithFileUploads;

    public array $uploads = [];
    public array $selectedFiles = [];
    public string $bulkFolder = '';
    public ?string $infoFile = null;
    public ?string $previewFile = null;
    public ?string $movingFolder = null;
    public string $folderDestination = '';

    public function openFolderMove(string $path): void
    {
        abort_unless(config('voyager.media.allow_move', true), 403);
        $this->checkFolder($path);
        abort_if($path === $this->rootFolder(), 422);
        app(BreadLibraryFolders::class)->checkedDirectory($path);
        $this->movingFolder = $path;
        $this->folderDestination = dirname($path) === '.' ? '' : dirname($path);
        $this->resetValidation('folderDestination');
    }

    public function moveFolder(): void
    {
        $this->resetValidation('folderDestination');
        abort_unless(config('voyager.media.allow_move', true), 403);
        abort_unless($this->movingFolder !== null, 409);
        $this->checkFolder($this->movingFolder);
        $this->checkFolder($this->folderDestination);
        abort_if($this->movingFolder === $this->rootFolder(), 422);
        $source = $this->movingFolder;
        $result = app(BreadLibraryFolders::class)->rename($source, basename($source), 'folderDestination',
            app(BreadRegistry::class), $this->folderDestination);
        if ($this->folder === $source || str_starts_with($this->folder, $source.'/')) {
            $this->folder = $result['target'].substr($this->folder, strlen($source));
        }
        $this->movingFolder = null;
        $this->renameFolder = null;
        $this->infoFile = null;
        $this->sourceFile = null;
        $this->cropFile = null;
        $this->selectedFiles = [];
        Notification::make()->title('Папка доступна по новому пути')
            ->body('Обновлено ссылок: '.$result['count'].'. Исходники сохранены для старых URL.')->success()->send();
    }

    public function inspectFile(string $path): void
    {
        $this->checkFolder($this->folder);
        abort_unless((dirname($path) === '.' ? '' : dirname($path)) === $this->folder, 422);
        app(BreadLibraryFiles::class)->checkedFile($path);
        $this->infoFile = $path;
    }

    public function openImage(string $path): void
    {
        $this->inspectFile($path);
        $file = collect($this->listing()['files'])->firstWhere('path', $path);
        abort_unless($file !== null && $file['image'], 422);
        $this->selectCard($path);
        $this->previewFile = $path;
        $this->dispatch('open-modal', id: 'media-image-preview');
    }

    public function renameInspectedFile(string $name): void
    {
        abort_unless(config('voyager.media.allow_rename', true), 403);
        abort_unless($this->infoFile !== null, 409);
        $this->inspectFile($this->infoFile);
        $this->resetValidation('fileName');
        if ($name === basename($this->infoFile)) {
            return;
        }
        $result = app(BreadLibraryFiles::class)->relocate($this->infoFile, $this->folder,
            $name, 'fileName', app(BreadRegistry::class));
        $this->infoFile = null;
        $this->previewFile = null;
        $this->selectedFiles = [];
        Notification::make()->title('Файл доступен по новому имени')
            ->body('Обновлено ссылок: '.$result['count'].'. Исходник сохранён для старых ссылок.')->success()->send();
    }

    public function selectCard(string $path, bool $additive = false, bool $range = false): void
    {
        $listing = $this->listing();
        $paths = array_merge($listing['folders'], array_column($listing['files'], 'path'));
        abort_unless(in_array($path, $paths, true), 422);
        $selection = array_values(array_intersect($this->selectedFiles, $paths));
        if (! $additive && ! $range) {
            $selection = [];
        }
        // Match Voyager's forward range, including its one-selected-item condition.
        if ($range && count($selection) === 1) {
            $start = array_search($selection[0], $paths, true);
            $end = array_search($path, $paths, true);
            if ($start < $end) {
                $selection = array_merge($selection, array_slice($paths, $start, $end - $start));
            }
        }
        $this->selectedFiles = array_slice(array_values(array_unique([...$selection, $path])), 0, 500);
        $this->syncSelectionInformation($listing);
    }

    public function updatedSelectedFiles(): void
    {
        $listing = $this->listing();
        $this->syncSelectionInformation($listing);
    }

    private function syncSelectionInformation(array $listing): void
    {
        $this->infoFile = count($this->selectedFiles) === 1
            && in_array($this->selectedFiles[0], array_column($listing['files'], 'path'), true)
            ? $this->selectedFiles[0] : null;
    }

    public function refreshFiles(): void
    {
        $this->checkFolder($this->folder);
        $this->selectedFiles = [];
        $this->infoFile = null;
        $this->previewFile = null;
        $this->sourceFile = null;
        $this->renameFolder = null;
        $this->cropFile = null;
        $this->movingFolder = null;
        $this->resetValidation();
        $this->filePage = min($this->filePage, $this->listing()['pages']);
    }
    public ?string $cropFile = null;
    public array $libraryCrop = ['x' => 0, 'y' => 0, 'width' => 1, 'height' => 1];

    public function cropPreview(): array
    {
        abort_unless(config('voyager.media.allow_crop', true), 403);
        abort_unless($this->cropFile !== null, 409);
        $this->checkFolder(dirname($this->cropFile) === '.' ? '' : dirname($this->cropFile));
        app(BreadLibraryFiles::class)->checkedFile($this->cropFile);
        $disk = Storage::disk('public');
        if ($disk->size($this->cropFile) > 10 * 1024 * 1024) {
            throw ValidationException::withMessages(['libraryCrop' => 'Изображение превышает 10 МБ.']);
        }
        $info = @getimagesizefromstring($disk->get($this->cropFile));
        if (! in_array($info['mime'] ?? '', ['image/jpeg', 'image/png', 'image/webp', 'image/bmp', 'image/gif'], true)) {
            throw ValidationException::withMessages(['libraryCrop' => 'Обрезка доступна для JPEG, PNG, WebP, BMP и GIF.']);
        }
        return ['width' => $info[0], 'height' => $info[1], 'mime' => $info['mime'],
            'url' => $disk->url(implode('/', array_map(rawurlencode(...), explode('/', $this->cropFile))))];
    }

    public function openCrop(string $path): void
    {
        $this->cropFile = $path;
        try { $preview = $this->cropPreview(); }
        catch (\Throwable $error) { $this->cropFile = null; throw $error; }
        $width = max(1, (int) round($preview['width'] * .8));
        $height = max(1, (int) round($preview['height'] * .8));
        $this->libraryCrop = ['x' => (int) floor(($preview['width'] - $width) / 2),
            'y' => (int) floor(($preview['height'] - $height) / 2), 'width' => $width, 'height' => $height];
        $this->resetValidation('libraryCrop');
        $this->dispatch('open-modal', id: 'media-crop');
    }

    public function cropImage(bool $createMode): void
    {
        $this->cropPreview();
        $this->validate(['libraryCrop' => 'required|array:x,y,width,height',
            'libraryCrop.x' => 'required|integer|between:-2147483648,2147483647', 'libraryCrop.y' => 'required|integer|between:-2147483648,2147483647',
            'libraryCrop.width' => 'required|integer|min:1', 'libraryCrop.height' => 'required|integer|min:1']);
        $source = $this->cropFile;
        $disk = Storage::disk('public');
        Cache::lock('media-crop:'.sha1($source), 60)->block(5, function () use ($source, $disk, $createMode): void {
            $original = $disk->get($source);
            $result = app(\App\Filament\Bread\BreadImageCrop::class)->crop($original,
                (int) $this->libraryCrop['x'], (int) $this->libraryCrop['y'],
                (int) $this->libraryCrop['width'], (int) $this->libraryCrop['height'], 90, true, true);
            $target = $source;
            if ($createMode) {
                $extension = pathinfo($source, PATHINFO_EXTENSION);
                $stem = substr($source, 0, -strlen($extension) - 1).'.cropped_'.time();
                $target = $stem.'.'.$extension;
                $suffix = 1;
                while ($disk->exists($target)) { $target = $stem.'_'.($suffix++).'.'.$extension; }
            }
            try {
                if (! $disk->put($target, $result['bytes'], 'public')) { throw new \RuntimeException('Не удалось записать обрезанное изображение.'); }
            } catch (\Throwable $error) {
                if ($createMode) { $disk->delete($target); }
                else { $disk->put($source, $original, 'public'); }
                throw $error;
            }
        });
        $this->cropFile = null;
        $this->dispatch('close-modal', id: 'media-crop');
        Notification::make()->title($createMode ? 'Обрезанная копия создана' : 'Изображение обрезано')->success()->send();
    }

    public function moveSelectedFiles(): void
    {
        $this->checkFolder($this->folder);
        abort_unless(config('voyager.media.allow_move', true), 403);
        $this->resetValidation('bulkFolder');
        $this->checkFolder($this->bulkFolder);
        $this->validate(['selectedFiles' => 'required|array|min:1|max:500', 'selectedFiles.*' => 'required|string|distinct', 'bulkFolder' => 'string|max:1000']);
        foreach ($this->selectedFiles as $path) {
            $parent = dirname($path) === '.' ? '' : dirname($path);
            abort_unless($parent === $this->folder, 422);
        }
        $references = app(BreadLibraryFiles::class)->relocateMany($this->selectedFiles, $this->bulkFolder, app(BreadRegistry::class));
        $count = count($this->selectedFiles);
        $this->selectedFiles = [];
        Notification::make()->title('Перемещено объектов: '.$count)
            ->body('Обновлено ссылок: '.$references.'. Исходники сохранены для старых URL.')->success()->send();
    }

    public function selectPageFiles(): void
    {
        $listing = $this->listing();
        $this->selectedFiles = array_slice(array_merge($listing['folders'], array_column($listing['files'], 'path')), 0, 500);
        $this->syncSelectionInformation($listing);
    }

    public function clearFileSelection(): void
    {
        abort_unless(static::canAccess(), 403);
        $this->selectedFiles = [];
        $this->infoFile = null;
    }

    public function deleteSelectedFiles(): void
    {
        $this->checkFolder($this->folder);
        abort_unless(config('voyager.media.allow_delete', true), 403);
        $this->resetValidation('selectedFiles');
        $this->validate(['selectedFiles' => 'required|array|min:1|max:500', 'selectedFiles.*' => 'required|string|distinct']);
        $checker = app(BreadLibraryFiles::class);
        $disk = Storage::disk('public');
        $directories = [];
        foreach ($this->selectedFiles as $path) {
            $parent = dirname($path) === '.' ? '' : dirname($path);
            abort_unless($parent === $this->folder, 422);
            $directories[$path] = $disk->directoryExists($path);
            if ($directories[$path]) {
                app(BreadLibraryFolders::class)->checkedDirectory($path);
                if ($disk->files($path) !== [] || $disk->directories($path) !== []) {
                    throw ValidationException::withMessages(['selectedFiles' => 'Удаление возможно только для пустых папок: '.basename($path)]);
                }
            } else { $checker->checkedFile($path); }
        }
        $deleted = 0;
        $failed = [];
        foreach ($this->selectedFiles as $path) {
            if ($directories[$path]) {
                try { app(BreadLibraryFolders::class)->deleteEmpty($path, 'selectedFiles'); $deleted++; }
                catch (ValidationException) { $failed[] = $path; }
            } elseif ($disk->delete($path)) { $deleted++; }
            else { $failed[] = $path; }
        }
        $this->selectedFiles = $failed;
        $this->sourceFile = null;
        $this->cropFile = null;
        Notification::make()->title('Удалено объектов: '.$deleted)
            ->body($failed === [] ? '' : 'Не удалось удалить: '.count($failed))
            ->color($failed === [] ? 'success' : 'warning')->send();
    }

    public function uploadFiles(?string $folderAtStart = null): bool
    {
        $this->checkFolder($this->folder);
        abort_unless(config('voyager.media.allow_upload', true), 403);
        abort_unless($folderAtStart === null || $folderAtStart === $this->folder, 409);
        $this->resetValidation('uploads');
        $this->validate(['uploads' => 'required|array|min:1|max:20', 'uploads.*' => 'required|file|max:10240']);
        $disk = Storage::disk('public');
        if ($this->folder !== '') {
            app(BreadLibraryFolders::class)->checkedDirectory($this->folder);
        }
        foreach ($this->uploads as $index => $upload) {
            $name = $upload->getClientOriginalName();
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $allowed = config('voyager.media.allowed_mimetypes', '*');
            if ($name !== basename($name) || preg_match('/[\\\\\/\x00-\x1F\x7F:]/', $name)
                || str_starts_with($name, '.') || str_ends_with($name, '.') || strlen($name) > 200
                || preg_match('/\A(?:CON|PRN|AUX|NUL|COM[0-9]|LPT[0-9])(?:\.|\z)/i', $name)
                || in_array($extension, ['php', 'phtml', 'phar', 'cgi', 'pl', 'asp', 'aspx', 'jsp', 'exe', 'bat', 'cmd', 'com', 'ps1', 'htaccess'], true)
                || (is_array($allowed) && ! in_array($upload->getMimeType(), $allowed, true))) {
                throw ValidationException::withMessages(['uploads.'.$index => 'Недопустимое имя или тип файла.']);
            }
        }
        $stored = [];
        try {
            Cache::lock('media-upload:'.sha1($this->folder), 120)->block(5, function () use ($disk, &$stored): void {
                foreach ($this->uploads as $upload) {
                    $name = $upload->getClientOriginalName();
                    $extension = pathinfo($name, PATHINFO_EXTENSION);
                    $base = $extension === '' ? $name : substr($name, 0, -strlen($extension) - 1);
                    $candidate = $name;
                    $number = 1;
                    $prefix = $this->folder === '' ? '' : $this->folder.'/';
                    $variants = app(\App\Filament\Bread\BreadImageUpload::class)->mediaVariants($upload, 'uploads', [
                        'thumbnails' => config('voyager.media.thumbnails', []),
                        'watermark' => config('voyager.media.watermark', []),
                    ]);
                    $variantPaths = static function (string $candidate) use ($prefix, $extension, $variants): array {
                        $stem = $extension === '' ? $candidate : substr($candidate, 0, -strlen($extension) - 1);
                        return array_map(fn ($suffix) => $prefix.$stem.$suffix.($extension === '' ? '' : '.'.$extension), array_keys($variants));
                    };
                    while (collect($variantPaths($candidate))->contains(fn ($path) => $disk->exists($path))) {
                        $candidate = $base.'_'.($number++).($extension === '' ? '' : '.'.$extension);
                    }
                    foreach (array_combine($variantPaths($candidate), array_values($variants)) as $path => $content) {
                        $stored[] = $path;
                        if (! $disk->put($path, $content, 'public')) {
                            throw new \RuntimeException('Не удалось сохранить файл.');
                        }
                    }
                }
            });
        } catch (\Throwable $exception) {
            foreach ($stored as $path) {
                $disk->delete($path);
            }
            throw $exception;
        }
        $this->uploads = [];
        $this->filePage = 1;
        Notification::make()->title('Загружено файлов: '.count($stored))->success()->send();
        return true;
    }

    protected static ?string $slug = 'media';

    protected static ?string $navigationLabel = 'Медиатека';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected string $view = 'filament.pages.media-library';

    #[Url]
    public string $folder = '';

    public int $filePage = 1;

    public string $newFolder = '';

    public ?string $renameFolder = null;

    public string $folderName = '';

    public ?string $sourceFile = null;

    public string $fileName = '';

    public string $targetFolder = '';

    public function editFile(string $path): void
    {
        abort_unless(static::canAccess(), 403);
        abort_unless(config('voyager.media.allow_rename', true) || config('voyager.media.allow_move', true), 403);
        $this->checkFolder(dirname($path) === '.' ? '' : dirname($path));
        app(BreadLibraryFiles::class)->checkedFile($path);
        $this->sourceFile = $path;
        $this->fileName = basename($path);
        $this->targetFolder = dirname($path) === '.' ? '' : dirname($path);
        $this->resetValidation('fileName');
    }

    public function relocateFile(): void
    {
        abort_unless(static::canAccess(), 403);
        abort_unless($this->sourceFile !== null, 409);
        $sourceParent = dirname($this->sourceFile) === '.' ? '' : dirname($this->sourceFile);
        $this->checkFolder($sourceParent);
        $this->checkFolder($this->targetFolder);
        abort_unless($this->fileName === basename($this->sourceFile) || config('voyager.media.allow_rename', true), 403);
        abort_unless($this->targetFolder === $sourceParent || config('voyager.media.allow_move', true), 403);
        $this->resetValidation('fileName');
        $result = app(BreadLibraryFiles::class)->relocate($this->sourceFile,
            $this->targetFolder, $this->fileName, 'fileName', app(BreadRegistry::class));
        $this->sourceFile = null;
        Notification::make()->title('Файл доступен по новому пути')
            ->body('Обновлено ссылок: '.$result['count'].'. Исходник сохранён для старых ссылок.')->success()->send();
    }

    public function editFolder(string $path): void
    {
        abort_unless(config('voyager.media.allow_rename', true), 403);
        abort_if($path === $this->rootFolder(), 422);
        $this->checkFolder($path);
        app(BreadLibraryFolders::class)->checkedDirectory($path);
        $this->renameFolder = $path;
        $this->folderName = basename($path);
        $this->resetValidation('folderName');
    }

    public function renameDirectory(): void
    {
        abort_unless(static::canAccess(), 403);
        abort_unless($this->renameFolder !== null, 409);
        abort_unless(config('voyager.media.allow_rename', true), 403);
        abort_if($this->renameFolder === $this->rootFolder(), 422);
        $this->checkFolder($this->renameFolder);
        $this->resetValidation('folderName');
        $result = app(BreadLibraryFolders::class)->rename($this->renameFolder, $this->folderName,
            'folderName', app(BreadRegistry::class));
        if ($this->folder === $this->renameFolder || str_starts_with($this->folder, $this->renameFolder.'/')) {
            $this->folder = $result['target'].substr($this->folder, strlen($this->renameFolder));
        }
        $this->renameFolder = null;
        $this->movingFolder = null;
        Notification::make()->title('Новое имя папки применено')
            ->body('Обновлено ссылок: '.$result['count'].'. Исходная папка сохранена для старых ссылок в контенте.')->success()->send();
    }

    public function deleteFolder(string $path): void
    {
        abort_unless(config('voyager.media.allow_delete', true), 403);
        abort_if($path === $this->rootFolder(), 422);
        $this->checkFolder($path);
        $this->resetValidation('folderName');
        app(BreadLibraryFolders::class)->deleteEmpty($path, 'folderName');
        if ($this->folder === $path) {
            $this->folder = dirname($path) === '.' ? '' : dirname($path);
        }
        if ($this->renameFolder === $path) {
            $this->renameFolder = null;
        }
        Notification::make()->title('Пустая папка удалена')->success()->send();
    }

    public function createFolder(): void
    {
        abort_unless(config('voyager.media.allow_create_folder', true) && config('voyager.media.show_folders', true), 403);
        $this->checkFolder($this->folder);
        $this->resetValidation('newFolder');
        app(BreadLibraryFolders::class)->create($this->folder, $this->newFolder, 'newFolder');
        $this->newFolder = '';
        Notification::make()->title('Папка создана')->success()->send();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && method_exists($user, 'hasPermission') && $user->hasPermission('browse_media');
    }

    public function mount(): void
    {
        if ($this->folder === '') { $this->folder = $this->rootFolder(); }
        $this->bulkFolder = $this->rootFolder();
        $this->checkFolder($this->folder);
    }

    public function hydrate(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    public function getTitle(): string
    {
        return 'Медиатека';
    }

    private function checkFolder(string $folder): void
    {
        abort_unless(static::canAccess(), 403);
        abort_if(str_starts_with($folder, '/') || str_contains($folder, '..')
            || str_contains($folder, '\\') || str_contains($folder, ':')
            || preg_match('/[\x00-\x1F\x7F]/', $folder), 422);
        $root = $this->rootFolder();
        abort_unless($root === '' || $folder === $root || str_starts_with($folder, $root.'/'), 422);
        abort_unless(config('voyager.media.show_folders', true) || $folder === $root, 403);
        abort_unless($folder === '' || Storage::disk('public')->directoryExists($folder), 404);
    }

    public function rootFolder(): string
    {
        $root = trim((string) config('voyager.media.path', '/'), '/');
        abort_if(str_contains($root, '..') || str_contains($root, '\\') || str_contains($root, ':')
            || preg_match('/[\x00-\x1F\x7F]/', $root), 422);
        return $root;
    }

    public function folderTrail(): array
    {
        $this->checkFolder($this->folder);
        $root = $this->rootFolder();
        $trail = [['label' => 'Медиатека', 'path' => $root]];
        $relative = $root === '' ? $this->folder : ltrim(substr($this->folder, strlen($root)), '/');
        $path = $root;
        foreach (array_filter(explode('/', $relative), fn ($segment) => $segment !== '') as $segment) {
            $path = ($path === '' ? '' : $path.'/').$segment;
            $trail[] = ['label' => $segment, 'path' => $path];
        }
        return $trail;
    }

    public function browse(string $folder): void
    {
        $this->checkFolder($folder);
        $this->folder = $folder;
        $this->movingFolder = null;
        $this->filePage = 1;
        $this->selectedFiles = [];
        $this->cropFile = null;
        $this->infoFile = null;
        $this->previewFile = null;
        $this->sourceFile = null;
        $this->renameFolder = null;
    }

    public function setFilePage(int $page): void
    {
        $this->checkFolder($this->folder);
        $this->filePage = max(1, min($page, $this->listing()['pages']));
        $this->selectedFiles = [];
        $this->infoFile = null;
        $this->previewFile = null;
        $this->cropFile = null;
        $this->sourceFile = null;
    }

    public function listing(): array
    {
        $this->checkFolder($this->folder);
        $disk = Storage::disk('public');
        $folders = config('voyager.media.show_folders', true) ? $disk->directories($this->folder) : [];
        $files = array_values(array_filter($disk->files($this->folder), fn ($path) => config('voyager.hidden_files', false)
            || pathinfo($path, PATHINFO_FILENAME) !== ''));
        $thumbNames = array_filter(array_column((array) config('voyager.media.thumbnails', []), 'name'), 'is_string');
        $thumbnails = array_values(array_filter($files, fn ($path) => \Illuminate\Support\Str::endsWith(pathinfo($path, PATHINFO_FILENAME), $thumbNames)));
        $files = array_values(array_diff($files, $thumbnails));
        natcasesort($folders);
        natcasesort($files);
        $files = array_values($files);
        $pages = max(1, (int) ceil(count($files) / 48));
        $page = max(1, min($this->filePage, $pages));

        return ['folders' => $folders, 'total' => count($files), 'pages' => $pages, 'page' => $page,
            'files' => array_map(function ($path) use ($disk, $thumbnails): array {
                $mime = $disk->mimeType($path) ?: 'application/octet-stream';

                return ['path' => $path, 'name' => basename($path), 'mime' => $mime,
                    'image' => str_starts_with($mime, 'image/') && $mime !== 'image/svg+xml',
                    'size' => $disk->size($path), 'modified' => $disk->lastModified($path),
                    'url' => $disk->url(implode('/', array_map(rawurlencode(...), explode('/', $path)))),
                    'thumbnails' => array_values(array_map(fn ($thumb) => ['name' => basename($thumb),
                        'url' => $disk->url(implode('/', array_map(rawurlencode(...), explode('/', $thumb))))],
                        array_filter($thumbnails, fn ($thumb) => str_starts_with(pathinfo($thumb, PATHINFO_FILENAME), pathinfo($path, PATHINFO_FILENAME).'-'))))];
            }, array_slice($files, ($page - 1) * 48, 48))];
    }
}
