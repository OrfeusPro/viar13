<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:xhtml="http://www.w3.org/1999/xhtml">
    @if(Route::currentRouteName() !== 'sitemap.images')
	<url>
		<loc>{{ str_replace('https:/','https://',trim(str_replace('//','/',asset('https://viarcanvas.com/'.$loc.'/')), "/")) }}</loc>
		<changefreq>daily</changefreq>
		<priority>1.0</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/about') }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/all_styles') }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/blog') }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/collage') }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/condition') }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/new/gift-card') }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/modular-generator') }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/new/canvas') }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/new/caricature') }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/new/gallery') }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/new/gallery/module') }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/new/gallery/photo') }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/new/gallery/reproduction') }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/page/contacts') }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/page/delivery') }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/faq') }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/photo_portrait') }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/review') }}</loc>
		<changefreq>weekly</changefreq>
		<priority>0.8</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/simpsons') }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/sizesprices') }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/stocks') }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/gallery/modern_handmade_paintings') }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/gallery/modern_painters') }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/gallery/painters') }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc>{{ asset($loc.'/gallery/paintings-top') }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>


	@if(isset($blog_categories) && $blog_categories)
	@foreach($blog_categories as $item)
    @php
        $categorySlug = data_get($item, 'slug');
        $categoryUpdatedAt = data_get($item, 'updated_at');
    @endphp
    @continue(empty($categorySlug))

	<url>
		<loc>{{ route('blog_category', ['slug' => $categorySlug]) }}</loc>
		@if($categoryUpdatedAt)<lastmod>{{ str_replace(" ", "T", $categoryUpdatedAt) . "+00:00" }}</lastmod>@endif
		<changefreq>daily</changefreq>
		<priority>0.8</priority>
	</url>

	@endforeach
	@endif

    @endif

	@if(isset($blog_posts) && $blog_posts)
	@foreach($blog_posts as $item)
    @php
        $postSlug = data_get($item, 'slug');
        $postUpdatedAt = data_get($item, 'updated_at');
    @endphp
    @continue(empty($postSlug))

	<url>
		<loc>{{ route('blog_inner', ['slug' => $postSlug]) }}</loc>
		@if($postUpdatedAt)<lastmod>{{ str_replace(" ", "T", $postUpdatedAt) . "+00:00" }}</lastmod>@endif
		<changefreq>daily</changefreq>
		<priority>0.7</priority>
        @if(Route::currentRouteName() == 'sitemap.images')
        @if(!empty($item['image']))
        <image:image>
            <image:loc>{{ Voyager::image($item['image']) }}</image:loc>
        </image:image>
        @endif
        @if(!empty($item['image_user']))
        <image:image>
            <image:loc>{{ Voyager::image($item['image_user']) }}</image:loc>
        </image:image>
        @endif
        @endif

	</url>

	@endforeach
	@endif

    @include(env('THEME_RESOURCES') . 'pages.advertising.sitemap_categories_item', ['items' => $categories, 'type' => "categories"])
	@include(env('THEME_RESOURCES') . 'pages.advertising.sitemap_products_item', ['items' => $photo, 'type' => "photo"])
	@include(env('THEME_RESOURCES') . 'pages.advertising.sitemap_products_item', ['items' => $module, 'type' => "module"])
	@include(env('THEME_RESOURCES') . 'pages.advertising.sitemap_products_item', ['items' => $reproduction, 'type' => "reproduction"])
	@include(env('THEME_RESOURCES') . 'pages.advertising.sitemap_products_item', ['items' => $graphicportrait, 'type' => "graphic-portrait", 'route' => "graphic_portrait.new_page"])
	@include(env('THEME_RESOURCES') . 'pages.advertising.sitemap_products_item', ['items' => $gallery_genres, 'route' => "hb.gallery.genre"])
	@include(env('THEME_RESOURCES') . 'pages.advertising.sitemap_products_item', ['items' => $gallery_styles, 'route' => "hb.gallery.style"])
	@include(env('THEME_RESOURCES') . 'pages.advertising.sitemap_products_item', ['items' => $gallery_nationality, 'route' => "hb.gallery.nationality"])
	@include(env('THEME_RESOURCES') . 'pages.advertising.sitemap_products_item', ['items' => $gallery_age, 'route' => "hb.gallery.age"])
	{{-- @include(env('THEME_RESOURCES') . 'pages.advertising.sitemap_products_item', ['items' => $gallery_painters, 'route' => "hb.gallery.painters_letter"]) --}}
</urlset>
