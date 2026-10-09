<?php

namespace App\Filament\Bread;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/** The additional store behavior of the original AdminLocaleController. */
class BreadLocaleController
{
    public function created(object $type, Model $record): void
    {
        if ($type->name !== 'locales'
            || ltrim((string) ($type->controller ?? ''), '\\') !== 'App\\Http\\Controllers\\Admin\\AdminLocaleController') {
            return;
        }
        $prefix = $record->getAttribute('prefix');
        if (! is_string($prefix) || ! preg_match('/\A[a-zA-Z0-9]+(?:[-_][a-zA-Z0-9]+)*\z/', $prefix) || strlen($prefix) > 80) {
            throw ValidationException::withMessages(['data.prefix' => 'Укажите код языка без разделителей пути.']);
        }
        $root = lang_path();
        $path = $root.DIRECTORY_SEPARATOR.$prefix;
        if (is_link($path) || (file_exists($path) && ! is_dir($path))) {
            throw ValidationException::withMessages(['data.prefix' => 'Каталог языка недоступен.']);
        }
        if (is_dir($path)) { return; }
        if (! is_dir($root) || ! @mkdir($path, 0755)) {
            throw ValidationException::withMessages(['data.prefix' => 'Не удалось создать каталог языка.']);
        }
        $record->getConnection()->afterRollBack(function () use ($path): void {
            // Never remove existing files, or a directory populated by another process.
            if (is_dir($path) && ! is_link($path) && scandir($path) === ['.', '..'] && ! @rmdir($path)) {
                report(new \RuntimeException('Failed to remove empty BREAD language directory after rollback.'));
            }
        });
    }
}
