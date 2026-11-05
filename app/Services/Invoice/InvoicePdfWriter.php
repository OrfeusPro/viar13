<?php

namespace App\Services\Invoice;

use Illuminate\Container\Attributes\Singleton;
use Illuminate\Database\DatabaseTransactionRecord;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

#[Singleton]
class InvoicePdfWriter
{
    private array $transactions = [];

    public function __construct(private readonly Filesystem $files) {}

    public function write(string $path, string $contents): void
    {
        $this->change($path, $contents);
    }

    public function remove(string $path): void
    {
        $this->change($path, null);
    }

    private function change(string $path, ?string $contents): void
    {
        $connection = DB::connection();
        $transaction = $connection->transactionLevel() > 0
            ? app('db.transactions')->getPendingTransactions()->last(fn ($record) => $record->connection === $connection->getName())
            : null;
        if (! $transaction) {
            $this->publish($path, $contents);

            return;
        }

        $this->files->ensureDirectoryExists(dirname($path));
        $path = $this->files->exists($path)
            ? realpath($path)
            : realpath(dirname($path)).DIRECTORY_SEPARATOR.basename($path);
        $root = $transaction;
        while ($root->parent) {
            $root = $root->parent;
        }
        $rootId = spl_object_id($root);
        if (! isset($this->transactions[$rootId])) {
            $this->transactions[$rootId] = ['paths' => [], 'frames' => []];
            $root->addCallback(fn () => $this->forget($rootId));
            $root->addCallbackForRollback(fn () => $this->restoreRoot($rootId));
        }
        $journal = &$this->transactions[$rootId];
        if (! array_key_exists($path, $journal['paths'])) {
            $original = $this->files->exists($path) ? $this->files->get($path) : null;
            $journal['paths'][$path] = ['original' => $original, 'current' => $original, 'versions' => []];
        }
        $entry = &$journal['paths'][$path];
        if (! $this->matches($path, $entry['current'])) {
            throw new RuntimeException('PDF-счёт изменён другим процессом. Обновите заказ.');
        }
        $this->publish($path, $contents);
        $entry['versions'][] = ['frame' => $transaction, 'contents' => $contents, 'active' => true];
        $entry['current'] = $contents;
        $frameId = spl_object_id($transaction);
        if ($transaction !== $root && ! isset($journal['frames'][$frameId])) {
            $journal['frames'][$frameId] = true;
            $transaction->addCallbackForRollback(fn () => $this->restoreFrame($rootId, $transaction));
        }
    }

    private function forget(int $rootId): void
    {
        unset($this->transactions[$rootId]);
    }

    private function restoreRoot(int $rootId): void
    {
        try {
            foreach ($this->transactions[$rootId]['paths'] ?? [] as $path => $entry) {
                $this->restore($path, $entry['current'], $entry['original']);
            }
        } finally {
            $this->forget($rootId);
        }
    }

    private function restoreFrame(int $rootId, DatabaseTransactionRecord $frame): void
    {
        if (! isset($this->transactions[$rootId])) {
            return;
        }
        foreach ($this->transactions[$rootId]['paths'] as $path => &$entry) {
            $desired = $entry['original'];
            foreach ($entry['versions'] as &$version) {
                if ($version['frame'] === $frame) {
                    $version['active'] = false;
                }
                if ($version['active']) {
                    $desired = $version['contents'];
                }
            }
            unset($version);
            if ($this->restore($path, $entry['current'], $desired)) {
                $entry['current'] = $desired;
            }
        }
    }

    private function matches(string $path, ?string $contents): bool
    {
        return $contents === null
            ? ! $this->files->exists($path)
            : $this->files->exists($path) && hash_equals(hash('sha256', $contents), hash_file('sha256', $path));
    }

    private function restore(string $path, ?string $current, ?string $desired): bool
    {
        if (! $this->matches($path, $current)) {
            Log::warning('Invoice PDF rollback skipped: file changed outside transaction.', ['file' => basename($path)]);

            return false;
        }
        if ($desired !== $current) {
            $this->publish($path, $desired);
        }

        return true;
    }

    private function publish(string $path, ?string $contents): void
    {
        if ($contents === null) {
            if ($this->files->exists($path) && ! $this->files->delete($path)) {
                throw new RuntimeException('Не удалось удалить PDF отменённого счёта.');
            }
        } else {
            $this->replace($path, $contents);
        }
    }

    private function replace(string $path, string $contents): void
    {
        $directory = dirname($path);
        $this->files->ensureDirectoryExists($directory);
        $temporary = tempnam($directory, '.invoice-');
        if ($temporary === false) {
            throw new RuntimeException('Не удалось создать временный файл счёта.');
        }

        try {
            if (realpath(dirname($temporary)) !== realpath($directory)) {
                throw new RuntimeException('Временный файл счёта создан вне целевого каталога.');
            }
            if ($this->files->put($temporary, $contents) !== strlen($contents)) {
                throw new RuntimeException('Не удалось полностью записать PDF-счёт.');
            }
            $this->files->chmod($temporary, $this->files->exists($path) ? (fileperms($path) & 0777) : 0644);
            if (! $this->files->move($temporary, $path)) {
                throw new RuntimeException('Не удалось заменить PDF-счёт.');
            }
        } finally {
            if ($this->files->exists($temporary)) {
                $this->files->delete($temporary);
            }
        }
    }
}
