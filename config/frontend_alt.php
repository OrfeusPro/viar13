<?php

return [
    'svg_converter' => env('ALT_SVG_CONVERTER', 'rsvg-convert'),
    'binding_cache_bytes' => 16 * 1024 * 1024,
    'hosts' => ['viarcanvas.com', 'www.viarcanvas.com'],
    'excluded_path_prefixes' => ['orders/', 'uploads/'],
    // Only fixed, verified page routes. Shared and parameterized views keep a null URL.
    'page_routes' => [
        'theme/viar/pages/index/' => 'home',
        'partials/index_new/' => 'home',
        'theme/viar/pages/canvas/' => 'canvas',
        'partials/canvas_new/' => 'canvas',
        'theme/viar/pages/collage/' => 'collage',
        'partials/collage_new/' => 'collage',
        'theme/viar/pages/about/' => 'about',
        'theme/viar/pages/simpsons/' => 'simpsons',
        'theme/viar/pages/sizesprices/' => 'sizesprices',
        'theme/viar/pages/stocks/' => 'new_stocks',
        'partials/blog/scripts.blade.php' => 'blog',
        'faq.blade.php' => 'faq',
    ],
];
