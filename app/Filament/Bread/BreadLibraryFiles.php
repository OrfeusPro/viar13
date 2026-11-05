<?php

namespace App\Filament\Bread;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BreadLibraryFiles
{
    public function relocateMany(array $sources, string $folder, BreadRegistry $registry): int
    {
        if ($folder !== '') { app(BreadLibraryFolders::class)->checkedDirectory($folder); }
        $disk = Storage::disk('public');
        $directories = [];
        foreach ($sources as $source) {
            $directories[$source] = $disk->directoryExists($source);
            if ($directories[$source]) {
                app(BreadLibraryFolders::class)->checkedDirectory($source);
            } else { $this->checkedFile($source); }
            $target = ($folder === '' ? '' : $folder.'/').basename($source);
            $compareSource = PHP_OS_FAMILY === 'Windows' ? strtolower($source) : $source;
            $compareTarget = PHP_OS_FAMILY === 'Windows' ? strtolower($target) : $target;
            if ($compareTarget === $compareSource || ($directories[$source] && str_starts_with($compareTarget, $compareSource.'/')) || $disk->exists($target)) {
                throw ValidationException::withMessages(['bulkFolder' => 'Папка назначения содержит файл с таким именем: '.basename($source)]);
            }
        }
        $created = [];
        try {
            return DB::transaction(function () use ($sources, $folder, $registry, $directories, &$created): int {
                $references = 0;
                foreach ($sources as $source) {
                    $result = $directories[$source]
                        ? app(BreadLibraryFolders::class)->rename($source, basename($source), 'bulkFolder', $registry, $folder)
                        : $this->relocate($source, $folder, basename($source), 'bulkFolder', $registry);
                    $created[] = ['path' => $result['target'], 'directory' => $directories[$source]];
                    $references += $result['count'];
                }
                return $references;
            });
        } catch (\Throwable $exception) {
            foreach (array_reverse($created) as $item) {
                if ($item['directory']) { $disk->deleteDirectory($item['path']); }
                else { $disk->delete($item['path']); }
            }
            throw $exception;
        }
    }

    public function checkedFile(string $path): void
    {
        abort_if($path === '' || str_starts_with($path, '/') || str_contains($path, '..') || str_contains($path, '\\') || str_contains($path, ':') || preg_match('/[\x00-\x1F\x7F]/', $path), 422);
        $disk = Storage::disk('public');
        abort_unless($disk->exists($path) && is_file($disk->path($path)), 404);
        $root = realpath($disk->path(''));
        $resolved = realpath($disk->path($path));
        $normalize = static fn (string $value): string => PHP_OS_FAMILY === 'Windows' ? strtolower(rtrim(str_replace('\\', '/', $value), '/')) : rtrim(str_replace('\\', '/', $value), '/');
        abort_unless($root && $resolved && ! is_link($disk->path($path)) && str_starts_with($normalize($resolved), $normalize($root).'/'), 422);
    }

    public function relocate(string $source, string $folder, string $name, string $errorField, BreadRegistry $registry): array
    {
        $this->checkedFile($source);
        if ($folder !== '') {
            app(BreadLibraryFolders::class)->checkedDirectory($folder);
        }
        $name = trim($name);
        validator([$errorField => $name], [$errorField => ['required', 'string', 'max:200', 'regex:/\A[\pL\pN][\pL\pN _().-]*\z/u']])->validate();
        if (str_contains($name, '..') || preg_match('/\A(?:CON|PRN|AUX|NUL|COM[0-9]|LPT[0-9])(?:\.|\z)/i', $name) || str_ends_with($name, '.') || strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== strtolower(pathinfo($source, PATHINFO_EXTENSION))) {
            throw ValidationException::withMessages([$errorField => 'Некорректное имя; расширение файла менять нельзя.']);
        }
        $target = ($folder === '' ? '' : $folder.'/').$name;
        $disk = Storage::disk('public');
        abort_unless($folder === '' || $disk->directoryExists($folder), 404);
        if ($source === $target || $disk->exists($target)) {
            throw ValidationException::withMessages([$errorField => 'Укажите новый путь, который ещё не занят.']);
        }
        $count = Cache::lock('bread-library:'.sha1($target), 60)->block(5, function () use ($disk, $source, $target, $errorField, $registry): int {
            if ($disk->exists($target)) {
                throw ValidationException::withMessages([$errorField => 'Файл с таким именем уже существует.']);
            }
            if (! $disk->copy($source, $target)) {
                throw ValidationException::withMessages([$errorField => 'Не удалось скопировать файл.']);
            }
            try {
                return DB::transaction(fn () => app(BreadLibraryReferences::class)->replace($source, $target, $registry));
            } catch (\Throwable $exception) {
                $disk->delete($target);
                throw $exception;
            }
        });

        return ['target' => $target, 'count' => $count];
    }
}
