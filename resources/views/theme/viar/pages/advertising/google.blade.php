<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">
	<channel>
		<title>ViarCanvas</title>
		<link>{{Request::getHost()}}</link>
		<description></description>	

		@include(env('THEME_RESOURCES') . 'pages.advertising.item', ['items' => $photo, 'type' => "photo"])
		@include(env('THEME_RESOURCES') . 'pages.advertising.item', ['items' => $module, 'type' => "module"])
		@include(env('THEME_RESOURCES') . 'pages.advertising.item', ['items' => $reproduction, 'type' => "reproduction"])
	</channel>
</rss>