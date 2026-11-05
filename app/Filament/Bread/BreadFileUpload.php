<?php

namespace App\Filament\Bread;

use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Str;

class BreadFileUpload extends FileUpload
{
    public function compactImagePreviews(bool $multiple = false): static
    {
        $this->imagePreviewHeight('160')->openable()->extraAttributes([
            'class' => $multiple ? 'viar-bread-image-grid' : 'viar-bread-image-single',
        ]);
        if ($multiple) {
            $this->panelLayout('grid')->itemPanelAspectRatio(0.8);
        }

        return $this;
    }

    public function toEmbeddedHtml(): string
    {
        $labels = [];
        foreach ($this->getRawState() ?? [] as $file) {
            if (is_string($file)) {
                $labels[rawurlencode(basename($file))] = basename($file);
            }
        }

        return view('filament.components.bread-file-labels', [
            'labels' => $labels,
            'content' => parent::toEmbeddedHtml(),
        ])->render();
    }

    public function getUploadedFile(string $file, string|array|null $storedFileNames): ?array
    {
        $uploadedFile = parent::getUploadedFile($file, $storedFileNames);

        if ($uploadedFile === null || $this->getVisibility() !== 'public') {
            return $uploadedFile;
        }

        // Voyager stores literal filenames, including spaces and Cyrillic characters.
        // Encode path segments before Filament sanitizes the public URL.
        $path = implode('/', array_map(rawurlencode(...), explode('/', $file)));
        $uploadedFile['url'] = Str::sanitizeUrl($this->getDisk()->url($path));

        return $uploadedFile;
    }
}
