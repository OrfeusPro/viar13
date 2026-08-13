<?php

namespace App\Services\AltGeneration;

use Illuminate\Database\Eloquent\Model;

/**
 * Writes skip reasons for images that cannot enter the alt-generation flow.
 */
class SkippedAltGenerationLogger
{
    /**
     * @var string
     */
    private $path;

    /**
     * @param string|null $path
     */
    public function __construct(?string $path = null)
    {
        $this->path = $path ?: storage_path('logs/alt-gen-skipped.log');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param string $reason
     * @param array<string, mixed> $context
     * @return void
     */
    public function log(Model $entity, string $reason, array $context = []): void
    {
        $directory = dirname($this->path);

        if (!is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }

        $record = [
            'time' => date('c'),
            'reason' => $reason,
            'imageable_type' => get_class($entity),
            'imageable_id' => $entity->getKey(),
            'context' => $context,
        ];

        $encoded = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($encoded !== false) {
            @file_put_contents($this->path, $encoded . PHP_EOL, FILE_APPEND);
        }
    }
}
