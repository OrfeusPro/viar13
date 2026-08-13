@php echo "<?xml version='1.0' encoding='utf-8' ?>"; @endphp

<root>
	@include(env('THEME_RESOURCES') . 'pages.advertising.item_kurpirkt', ['items' => $photo, 'type' => "photo"])
	@include(env('THEME_RESOURCES') . 'pages.advertising.item_kurpirkt', ['items' => $module, 'type' => "module"])
	@include(env('THEME_RESOURCES') . 'pages.advertising.item_kurpirkt', ['items' => $reproduction, 'type' => "reproduction"])
</root>
