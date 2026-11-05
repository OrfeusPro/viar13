<?php

return [
    'custom_path_generators' => [
        \App\Models\GalleryItem::class => \App\Support\Media\GalleryItemPathGenerator::class,
    ],
];
