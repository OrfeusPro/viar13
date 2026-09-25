<?php

namespace App\Services\AltGeneration;

use RuntimeException;

/** Disk-backed inventory: merge all usages without holding every context in PHP memory. */
class FrontendImageInventory
{
    private $file;
    private $offsets = [];

    public function __construct()
    {
        $this->file = tmpfile();
        if (!$this->file) {
            throw new RuntimeException('Cannot create temporary frontend image inventory.');
        }
    }

    public function put(array $row, FrontendImageRegistry $registry): void
    {
        if (isset($this->offsets[$row['id']])) {
            $previous = $this->read($this->offsets[$row['id']]);
            if ($previous['image_path'] !== $row['image_path']) {
                throw new RuntimeException('Frontend image identity collision.');
            }
            $row['context'] = $registry->mergeContexts($previous['context'], $row['context']);
            $row['placement'] = min($previous['placement'], $row['placement']);
        }
        fseek($this->file, 0, SEEK_END);
        $offset = ftell($this->file);
        $line = json_encode($row, JSON_THROW_ON_ERROR) . "\n";
        if (fwrite($this->file, $line) !== strlen($line)) {
            throw new RuntimeException('Cannot write temporary frontend image inventory.');
        }
        $this->offsets[$row['id']] = $offset;
    }

    public function chunks(): iterable
    {
        foreach (array_chunk($this->offsets, 100, true) as $offsets) {
            $rows = [];
            foreach ($offsets as $id => $offset) {
                $rows[$id] = $this->read($offset);
            }
            yield $rows;
        }
    }

    private function read(int $offset): array
    {
        if (fseek($this->file, $offset) !== 0) {
            throw new RuntimeException('Cannot seek temporary frontend image inventory.');
        }
        $line = fgets($this->file);
        if ($line === false) {
            throw new RuntimeException('Cannot read temporary frontend image inventory.');
        }
        return json_decode($line, true, 512, JSON_THROW_ON_ERROR);
    }

    public function close(): void
    {
        if (is_resource($this->file)) {
            fclose($this->file);
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}
