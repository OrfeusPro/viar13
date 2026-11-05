<?php

return [
    'custom_path_generators' => [
        \App\Models\CanvasNew::class => \App\Support\Media\CanvasNewPathGenerator::class,
        \App\Models\GalleryItem::class => \App\Support\Media\GalleryItemPathGenerator::class,
    ],
];
