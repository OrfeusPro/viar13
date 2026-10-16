<?php

namespace Tests\Unit;

use App\Services\Admin\CompassLogReader;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CompassLogReaderTest extends TestCase
{
    private function reader(): CompassLogReader
    {
        return new CompassLogReader(Storage::disk('compass-logs')->path(''));
    }

    public function test_lists_only_top_level_logs_and_pages_newest_records_without_changing_file(): void
    {
        Storage::fake('compass-logs');
        $disk = Storage::disk('compass-logs');
        $content = '';
        for ($index = 1; $index <= 25; $index++) { $content .= '[2026-10-16 10:00:00] local.INFO: Entry '.$index."\n"; }
        $disk->put('laravel-2026-10-16.log', $content);
        $disk->put('laravel-2026-10-15.log', '');
        $disk->put('secret.txt', 'Not a log');
        $disk->put('nested/hidden.log', 'Not top level');
        $reader = $this->reader();
        $this->assertSame(['laravel-2026-10-16.log', 'laravel-2026-10-15.log'], $reader->files());
        $first = $reader->page('laravel-2026-10-16.log');
        $this->assertSame(25, $first['total']);
        $this->assertSame(3, $first['last_page']);
        $this->assertSame('Entry 25', $first['entries'][0]['text']);
        $this->assertSame('Entry 16', $first['entries'][9]['text']);
        $second = $reader->page('laravel-2026-10-16.log', 2);
        $this->assertSame('Entry 15', $second['entries'][0]['text']);
        $this->assertSame('Entry 6', $second['entries'][9]['text']);
        $last = $reader->page('laravel-2026-10-16.log', 99);
        $this->assertSame(3, $last['page']);
        $this->assertCount(5, $last['entries']);
        $this->assertSame('Entry 1', $last['entries'][4]['text']);
        $this->assertSame($content, $disk->get('laravel-2026-10-16.log'));
    }

    public function test_multiline_stacks_context_levels_and_unicode_search_are_preserved(): void
    {
        Storage::fake('compass-logs');
        $content = "Unstructured prefix\r\n";
        foreach (CompassLogReader::LEVELS as $level) {
            $content .= '[2026-10-16 10:00:01] production.'.strtoupper($level).': Пример '.$level."\r\n";
            $content .= "#0 /synthetic/file.php(17): call()\r\nStack detail {$level}\r\n";
        }
        Storage::disk('compass-logs')->put('laravel.log', $content);
        $result = $this->reader()->page('laravel.log', search: 'ПРИМЕР', level: 'error');
        $this->assertSame(1, $result['total']);
        $entry = $result['entries'][0];
        $this->assertSame('production', $entry['context']);
        $this->assertSame('error', $entry['level']);
        $this->assertSame('Пример error', $entry['text']);
        $this->assertSame("#0 /synthetic/file.php(17): call()\r\nStack detail error", $entry['stack']);
        $this->assertGreaterThan(1, $entry['line']);
        $this->assertSame(9, $this->reader()->page('laravel.log', search: '/synthetic/file.php')['total']);
        $this->assertSame(0, $this->reader()->page('laravel.log', search: 'missing')['total']);
    }

    public function test_rejects_arbitrary_paths_and_non_log_files(): void
    {
        Storage::fake('compass-logs');
        Storage::disk('compass-logs')->put('laravel.log', '');
        Storage::disk('compass-logs')->put('.env', 'Synthetic secret');
        foreach (['../laravel.log', '..\\laravel.log', 'C:\\logs\\laravel.log', '/tmp/laravel.log', '.env', 'missing.log', "laravel.log\0"] as $name) {
            try {
                $this->reader()->page($name);
                $this->fail('Unsafe/unlisted filename must fail.');
            } catch (ValidationException $error) { $this->assertArrayHasKey('logFile', $error->errors()); }
        }
    }

    public function test_empty_log_and_invalid_filters(): void
    {
        Storage::fake('compass-logs');
        $this->assertSame([], $this->reader()->files());
        Storage::disk('compass-logs')->put('empty.log', '');
        $page = $this->reader()->page('empty.log');
        $this->assertSame([], $page['entries']);
        $this->assertSame(0, $page['total']);
        $this->assertSame(1, $page['last_page']);
        foreach ([[0, 10, ''], [1, 11, ''], [1, 10, 'invalid']] as [$number, $size, $level]) {
            try {
                $this->reader()->page('empty.log', $number, $size, level: $level);
                $this->fail('Invalid filter must fail.');
            } catch (ValidationException $error) { $this->assertArrayHasKey('logFilter', $error->errors()); }
        }
    }

    public function test_oversized_file_uses_the_original_fifty_mebibyte_limit(): void
    {
        Storage::fake('compass-logs');
        $path = Storage::disk('compass-logs')->path('large.log');
        $stream = fopen($path, 'wb');
        try { ftruncate($stream, CompassLogReader::MAX_FILE_SIZE + 1); } finally { fclose($stream); }
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Размер журнала превышает 50 МиБ.');
        $this->reader()->page('large.log');
    }

    public function test_delete_prevalidates_all_names_and_leaves_unselected_files_intact(): void
    {
        Storage::fake('compass-logs');
        $disk = Storage::disk('compass-logs');
        $disk->put('first.log', 'First');
        $disk->put('second.log', 'Second');
        $disk->put('other.txt', 'Keep');
        try {
            $this->reader()->deleteFiles(['first.log', '../outside.log']);
            $this->fail('Entire list must validate before deletion.');
        } catch (ValidationException) { $this->assertSame('First', $disk->get('first.log')); }
        $this->assertSame(['deleted' => ['first.log'], 'failed' => []], $this->reader()->deleteFiles(['first.log', 'first.log']));
        $this->assertSame('Second', $disk->get('second.log'));
        $this->assertSame('Keep', $disk->get('other.txt'));
    }

    public function test_delete_reports_partial_filesystem_failure(): void
    {
        Storage::fake('compass-logs');
        $disk = Storage::disk('compass-logs');
        $disk->put('remove.log', 'Remove');
        $disk->put('locked.log', 'Keep');
        $reader = new class($disk->path('')) extends CompassLogReader {
            protected function removeFile(string $path): bool { return basename($path) === 'locked.log' ? false : parent::removeFile($path); }
        };
        $this->assertSame(['deleted' => ['remove.log'], 'failed' => ['locked.log']], $reader->deleteFiles(['remove.log', 'locked.log']));
        $this->assertSame('Keep', $disk->get('locked.log'));
        $disk->assertMissing('remove.log');
    }

    public function test_symlink_cannot_escape_log_directory(): void
    {
        Storage::fake('compass-logs');
        Storage::fake('compass-outside');
        Storage::disk('compass-outside')->put('outside.log', 'Synthetic external data');
        $link = Storage::disk('compass-logs')->path('escape.log');
        if (! @symlink(Storage::disk('compass-outside')->path('outside.log'), $link)) {
            $this->markTestSkipped('This Windows session cannot create symlinks.');
        }
        $this->assertSame([], $this->reader()->files());
        $this->expectException(ValidationException::class);
        $this->reader()->page('escape.log');
    }
}
