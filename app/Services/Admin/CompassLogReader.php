<?php

namespace App\Services\Admin;

use Illuminate\Validation\ValidationException;

class CompassLogReader
{
    public const MAX_FILE_SIZE = 52428800;
    public const LEVELS = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug', 'processed'];

    public function __construct(private ?string $directory = null) {}

    public function files(): array
    {
        $root = realpath($this->directory ?? storage_path('logs'));
        if ($root === false) { return []; }
        $files = [];
        foreach (glob($root.'/*.log') ?: [] as $path) {
            $real = realpath($path);
            if ($real !== false && is_file($real) && dirname($real) === $root) { $files[] = basename($path); }
        }
        rsort($files, SORT_STRING);
        return $files;
    }

    public function checkedPath(string $name): string
    {
        if ($name === '' || str_contains($name, '/') || str_contains($name, '\\') || preg_match('/[\x00-\x1F\x7F]/', $name)
            || ! in_array($name, $this->files(), true)) {
            throw ValidationException::withMessages(['logFile' => 'Файл журнала не найден.']);
        }
        $root = realpath($this->directory ?? storage_path('logs'));
        $path = $root === false ? false : realpath($root.DIRECTORY_SEPARATOR.$name);
        if ($path === false || dirname($path) !== $root || ! is_file($path)) {
            throw ValidationException::withMessages(['logFile' => 'Файл журнала не найден.']);
        }
        return $path;
    }

    public function deleteFiles(array $names): array
    {
        $paths = [];
        foreach ($names as $name) {
            if (! is_string($name)) { throw ValidationException::withMessages(['logFile' => 'Некорректный список журналов.']); }
            $this->checkedPath($name);
            // Unlink the selected directory entry, never a symlink's target.
            $paths[$name] = realpath($this->directory ?? storage_path('logs')).DIRECTORY_SEPARATOR.$name;
        }
        $deleted = [];
        $failed = [];
        foreach ($paths as $name => $path) {
            if ($this->removeFile($path)) { $deleted[] = $name; }
            else { $failed[] = $name; }
        }
        return ['deleted' => $deleted, 'failed' => $failed];
    }

    protected function removeFile(string $path): bool { return @unlink($path); }

    public function page(string $name, int $page = 1, int $perPage = 10, string $search = '', string $level = ''): array
    {
        if ($page < 1 || ! in_array($perPage, [10, 25, 50, 100], true) || ($level !== '' && ! in_array($level, self::LEVELS, true))) {
            throw ValidationException::withMessages(['logFilter' => 'Некорректные параметры просмотра журнала.']);
        }
        $path = $this->checkedPath($name);
        $stream = @fopen($path, 'rb');
        if ($stream === false) { throw ValidationException::withMessages(['logFile' => 'Не удалось открыть журнал.']); }
        try {
            if ((fstat($stream)['size'] ?? 0) > self::MAX_FILE_SIZE) { $this->tooLarge(); }
            $matches = static fn (array $entry): bool => ($level === '' || $entry['level'] === $level)
                && ($search === '' || mb_stripos(implode("\n", [$entry['date'], $entry['context'], $entry['level'], $entry['text'], $entry['stack']]), $search) !== false);
            $total = 0;
            foreach ($this->records($stream) as $entry) { if ($matches($entry)) { $total++; } }
            $lastPage = max(1, (int) ceil($total / $perPage));
            $page = min($page, $lastPage);
            $start = max(0, $total - $page * $perPage);
            $end = max(0, $total - ($page - 1) * $perPage);
            $rows = [];
            $index = 0;
            rewind($stream);
            foreach ($this->records($stream) as $entry) {
                if (! $matches($entry)) { continue; }
                if ($index >= $end) { break; }
                if ($index >= $start) { $rows[] = $entry; }
                $index++;
            }
            return ['entries' => array_reverse($rows), 'total' => $total, 'page' => $page, 'per_page' => $perPage, 'last_page' => $lastPage];
        } finally { fclose($stream); }
    }

    /** @return \Generator<array{date:string,context:string,level:string,text:string,stack:string,line:int}> */
    private function records($stream): \Generator
    {
        $pattern = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+([^\r\n]*?)\.('.implode('|', self::LEVELS).'):\s?(.*)$/i';
        $entry = null;
        $bytes = 0;
        $lineNumber = 0;
        while (($line = fgets($stream)) !== false) {
            $bytes += strlen($line);
            $lineNumber++;
            if ($bytes > self::MAX_FILE_SIZE) { $this->tooLarge(); }
            if (preg_match($pattern, rtrim($line, "\r\n"), $parts)) {
                if ($entry !== null) { $entry['stack'] = rtrim($entry['stack'], "\r\n"); yield $entry; }
                $entry = ['date' => $parts[1], 'context' => $parts[2], 'level' => strtolower($parts[3]), 'text' => $parts[4], 'stack' => '', 'line' => $lineNumber];
            } elseif ($entry !== null) { $entry['stack'] .= $line; }
        }
        if ($entry !== null) { $entry['stack'] = rtrim($entry['stack'], "\r\n"); yield $entry; }
    }

    private function tooLarge(): never
    {
        throw ValidationException::withMessages(['logFile' => 'Размер журнала превышает 50 МиБ.']);
    }
}
