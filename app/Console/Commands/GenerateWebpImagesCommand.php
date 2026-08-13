<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class GenerateWebpImagesCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'images:generate-webp {--force : Regenerate existing WebP files}';

    /**
     * @var string
     */
    protected $description = 'Generate WebP versions for public storage images.';

    /**
     * @return int
     */
    public function handle(): int
    {
        $root = public_path('storage');

        if (!is_dir($root)) {
            $this->error('public/storage directory was not found.');

            return 1;
        }

        $found = 0;
        $created = 0;
        $skipped = 0;
        $errors = 0;
        $force = (bool) $this->option('force');
        $extensions = function_exists('image_webp_supported_extensions')
            ? image_webp_supported_extensions()
            : ['jpg', 'jpeg', 'png', 'gif', 'bmp'];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo || !$file->isFile()) {
                continue;
            }

            $extension = strtolower($file->getExtension());
            if (!in_array($extension, $extensions, true)) {
                continue;
            }

            $found++;
            $path = $file->getPathname();
            $webpPath = function_exists('image_webp_path')
                ? image_webp_path($path)
                : preg_replace('/\.[^.\/\\\\]+$/', '.webp', $path);

            if (!$force && is_string($webpPath) && file_exists($webpPath) && filesize($webpPath) > 0) {
                $skipped++;
                continue;
            }

            $relative = ltrim(str_replace('\\', '/', substr($path, strlen(public_path()))), '/');

            try {
                $webpUrl = function_exists('image_webp_url') ? image_webp_url($relative, true) : null;
            } catch (\Throwable $exception) {
                $webpUrl = null;
            }

            if ($webpUrl && is_string($webpPath) && file_exists($webpPath) && filesize($webpPath) > 0) {
                $created++;
                continue;
            }

            $errors++;
        }

        $this->table(
            ['found', 'created', 'skipped', 'errors'],
            [[$found, $created, $skipped, $errors]]
        );

        return 0;
    }
}
