<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
	@include(env('THEME_RESOURCES') . 'pages.advertising.sitemap_products_item', ['items' => $photo, 'type' => "photo"])
	@include(env('THEME_RESOURCES') . 'pages.advertising.sitemap_products_item', ['items' => $module, 'type' => "module"])
	@include(env('THEME_RESOURCES') . 'pages.advertising.sitemap_products_item', ['items' => $reproduction, 'type' => "reproduction"])
</urlset>