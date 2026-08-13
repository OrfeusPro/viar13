<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Base64TxtToImage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'base64:convert
        {--source= : Source directory with .txt files}
        {--dest= : Destination directory for images}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert base64 strings from txt files into image files';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $source = $this->option('source') ?: storage_path('app/public/a1/txt');
        $dest = $this->option('dest') ?: storage_path('app/public/a1/img');

        if (!is_dir($source)) {
            $this->error('Source directory not found: ' . $source);
            return 1;
        }

        if (!is_dir($dest) && !mkdir($dest, 0755, true) && !is_dir($dest)) {
            $this->error('Unable to create destination directory: ' . $dest);
            return 1;
        }

        $files = glob(rtrim($source, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.txt');
        if (!$files) {
            $this->info('No .txt files found in: ' . $source);
            return 0;
        }

        $saved = 0;
        $errors = 0;

        foreach ($files as $file) {
            $raw = trim((string)file_get_contents($file));
            if ($raw === '') {
                $this->warn('Empty file: ' . $file);
                $errors++;
                continue;
            }

            [$decoded, $extension] = $this->decodeBase64Image($raw);
            if ($decoded === null) {
                $this->error('Invalid base64 image in: ' . $file);
                $errors++;
                continue;
            }

            $baseName = pathinfo($file, PATHINFO_FILENAME);
            $target = rtrim($dest, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $baseName . '.' . $extension;

            if (file_put_contents($target, $decoded) === false) {
                $this->error('Failed to write: ' . $target);
                $errors++;
                continue;
            }

            $saved++;
        }

        $this->info('Saved images: ' . $saved);
        if ($errors > 0) {
            $this->warn('Errors: ' . $errors);
            return 1;
        }

        return 0;
    }

    private function decodeBase64Image($raw)
    {
        $extension = null;

        if (preg_match('/^data:image\/([a-zA-Z0-9+.-]+);base64,/', $raw, $matches)) {
            $extension = $this->mimeToExtension('image/' . strtolower($matches[1])) ?: strtolower($matches[1]);
            $raw = substr($raw, strpos($raw, ',') + 1);
        }

        $raw = preg_replace('/\s+/', '', $raw);
        $decoded = base64_decode($raw, true);
        if ($decoded === false) {
            return [null, null];
        }

        if ($extension === null) {
            $info = @getimagesizefromstring($decoded);
            if ($info && !empty($info['mime'])) {
                $extension = $this->mimeToExtension($info['mime']);
            }
        }

        if ($extension === null) {
            $extension = 'png';
        }

        return [$decoded, $extension];
    }

    private function mimeToExtension($mime)
    {
        switch (strtolower($mime)) {
            case 'image/jpeg':
                return 'jpg';
            case 'image/png':
                return 'png';
            case 'image/gif':
                return 'gif';
            case 'image/webp':
                return 'webp';
            case 'image/bmp':
                return 'bmp';
            case 'image/svg+xml':
                return 'svg';
            default:
                return null;
        }
    }
}
