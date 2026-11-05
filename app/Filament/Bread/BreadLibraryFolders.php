<?php

namespace App\Filament\Bread;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BreadLibraryFolders
{
    public function create(string $parent, string $name, string $errorField): string
    {
        abort_if(str_starts_with($parent, '/') || str_contains($parent, '..') || str_contains($parent, '\\')
            || str_contains($parent, ':') || preg_match('/[\x00-\x1F\x7F]/', $parent), 422);
        $name = trim($name);
        validator([$errorField => $name], [$errorField => [
            'required', 'string', 'max:100', 'regex:/\A[\pL\pN][\pL\pN _-]*\z/u',
        ]], [$errorField.'.regex' => 'Используйте буквы, цифры, пробелы, дефис или подчёркивание.'])->validate();
        if (preg_match('/\A(?:CON|PRN|AUX|NUL|COM[0-9]|LPT[0-9])\z/i', $name)) {
            throw ValidationException::withMessages([$errorField => 'Это имя зарезервировано системой.']);
        }
        $disk = Storage::disk('public');
        $resolvedRoot = realpath($disk->path(''));
        $ancestor = $disk->path($parent);
        while (! file_exists($ancestor) && dirname($ancestor) !== $ancestor) {
            $ancestor = dirname($ancestor);
        }
        $resolvedParent = realpath($ancestor);
        $normalize = static fn (string $path): string => PHP_OS_FAMILY === 'Windows'
            ? strtolower(rtrim(str_replace('\\', '/', $path), '/')) : rtrim(str_replace('\\', '/', $path), '/');
        abort_unless($resolvedRoot && $resolvedParent && ($normalize($resolvedParent) === $normalize($resolvedRoot)
            || str_starts_with($normalize($resolvedParent), $normalize($resolvedRoot).'/')), 422);
        $path = ($parent === '' ? '' : rtrim($parent, '/').'/').$name;
        if ($disk->exists($path)) {
            throw ValidationException::withMessages([$errorField => 'Папка или файл с таким именем уже существует.']);
        }
        if (! $disk->makeDirectory($path)) {
            throw ValidationException::withMessages([$errorField => 'Не удалось создать папку.']);
        }

        return $path;
    }

    public function checkedDirectory(string $path): void
    {
        abort_if($path === '' || str_starts_with($path, '/') || str_contains($path, '..') || str_contains($path, '\\') || str_contains($path, ':') || preg_match('/[\x00-\x1F\x7F]/', $path), 422);
        $disk = Storage::disk('public');
        abort_unless($disk->directoryExists($path), 404);
        $root = realpath($disk->path(''));
        $resolved = realpath($disk->path($path));
        $normalize = static fn (string $value): string => PHP_OS_FAMILY === 'Windows' ? strtolower(rtrim(str_replace('\\', '/', $value), '/')) : rtrim(str_replace('\\', '/', $value), '/');
        abort_unless($root && $resolved && ! is_link($disk->path($path)) && str_starts_with($normalize($resolved), $normalize($root).'/'), 422);
    }

    public function rename(string $source, string $name, string $errorField, BreadRegistry $registry, ?string $destinationParent = null): array
    {
        $this->checkedDirectory($source);
        $name = trim($name);
        if ($destinationParent === null || $name !== basename($source)) {
            validator([$errorField => $name], [$errorField => ['required', 'string', 'max:100', 'regex:/\A[\pL\pN][\pL\pN _-]*\z/u']])->validate();
        }
        if (preg_match('/\A(?:CON|PRN|AUX|NUL|COM[0-9]|LPT[0-9])\z/i', $name)) {
            throw ValidationException::withMessages([$errorField => 'Это имя зарезервировано системой.']);
        }
        if ($destinationParent !== null && $destinationParent !== '') { $this->checkedDirectory($destinationParent); }
        $parent = $destinationParent ?? (dirname($source) === '.' ? '' : dirname($source));
        $target = ($parent === '' ? '' : $parent.'/').$name;
        $comparisonSource = PHP_OS_FAMILY === 'Windows' ? strtolower($source) : $source;
        $comparisonTarget = PHP_OS_FAMILY === 'Windows' ? strtolower($target) : $target;
        if ($comparisonTarget === $comparisonSource || str_starts_with($comparisonTarget, $comparisonSource.'/')) {
            throw ValidationException::withMessages([$errorField => 'Нельзя переместить папку в неё саму или в её вложенную папку.']);
        }
        $disk = Storage::disk('public');
        $count = Cache::lock('bread-library:'.sha1($target), 120)->block(5, function () use ($disk, $source, $target, $registry, $errorField): int {
            if ($disk->exists($target)) {
                throw ValidationException::withMessages([$errorField => 'Укажите новое имя, которое ещё не занято.']);
            }
            $files = $disk->allFiles($source);
            $folders = $disk->allDirectories($source);
            foreach ($folders as $folder) { $this->checkedDirectory($folder); }
            foreach ($files as $file) { app(BreadLibraryFiles::class)->checkedFile($file); }
            if (count($files) + count($folders) > 500) {
                throw ValidationException::withMessages([$errorField => 'В папке больше 500 объектов. Переименуйте вложенные папки отдельно.']);
            }
            $createdFiles = [];
            $createdFolders = [];
            try {
                $destinations = array_merge([$target], array_map(fn ($path) => $target.substr($path, strlen($source)), $folders));
                usort($destinations, fn ($a, $b) => strlen($a) <=> strlen($b));
                foreach ($destinations as $folder) {
                    if (! $disk->makeDirectory($folder)) {
                        throw new \RuntimeException('Unable to create destination directory.');
                    }
                    $createdFolders[] = $folder;
                }
                foreach ($files as $file) {
                    $destination = $target.substr($file, strlen($source));
                    $createdFiles[] = $destination;
                    if (! $disk->copy($file, $destination)) {
                        throw new \RuntimeException('Unable to copy library file.');
                    }
                }

                return DB::transaction(fn () => app(BreadLibraryReferences::class)->replace($source, $target, $registry, true));
            } catch (\Throwable $exception) {
                foreach ($createdFiles as $file) {
                    $disk->delete($file);
                }
                foreach (array_reverse($createdFolders) as $folder) {
                    @rmdir($disk->path($folder));
                }
                throw $exception;
            }
        });

        return ['target' => $target, 'count' => $count];
    }

    public function deleteEmpty(string $path, string $errorField): void
    {
        $this->checkedDirectory($path);
        $disk = Storage::disk('public');
        Cache::lock('bread-library:'.sha1($path), 30)->block(5, function () use ($disk, $path, $errorField): void {
            if ($disk->files($path) !== [] || $disk->directories($path) !== [] || ! @rmdir($disk->path($path))) {
                throw ValidationException::withMessages([$errorField => 'Удаление возможно только для пустой папки без вложенных папок.']);
            }
        });
    }
}
