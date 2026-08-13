{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	@foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties) @php $cur_url = LaravelLocalization::getLocalizedURL($localeCode, null, [], true); $cur_url_mod = strtok($cur_url, '?'); @endphp
	<sitemap>
		<loc>https://viarcanvas.com/{{ $localeCode }}/sitemap/sitemap.xml</loc>
		<lastmod>{{ Carbon::now()->toAtomString() }}</lastmod>
	</sitemap>
	@endforeach
</sitemapindex>