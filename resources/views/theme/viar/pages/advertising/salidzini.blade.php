@php echo "<?xml version='1.0' encoding='utf-8' ?>"; @endphp

<root>
	@include(env('THEME_RESOURCES') . 'pages.advertising.item_salidzini', ['items' => $photo, 'type' => "photo"])
	@include(env('THEME_RESOURCES') . 'pages.advertising.item_salidzini', ['items' => $module, 'type' => "module"])
	@include(env('THEME_RESOURCES') . 'pages.advertising.item_salidzini', ['items' => $reproduction, 'type' => "reproduction"])
</root>
