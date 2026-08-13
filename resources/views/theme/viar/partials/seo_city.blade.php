@php
$cities = \App\Models\SeoCity::where('local', app()->getLocale())->get();
$chunks = $cities->chunk(ceil($cities->count() / 4));
$areaServedCities = site_brand_area_served_cities();
$brandSchema = site_brand_schema([
    'areaServed' => $areaServedCities,
    'contactPoint' => site_brand_contact_points([
        trans('header_footer_new.footer_phone_clean'),
        trans('header_footer_new.footer_phone_clean2'),
    ], 'orders@viarcanvas.com', $areaServedCities),
]);
@endphp
<script type="application/ld+json">
    {!! json_encode($brandSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
<section class="seo-cities">
    <div class="section-frame">
        <div class="top-title">
            <div class="page-title h2_old">{{ $seo_city_title }}</div>
            <p>{{ $seo_city_desc }}</p>
        </div>
        <div class="seo-cities-list">
            @foreach($chunks as $chunk)
            <ul>
                @foreach($chunk as $city)
                <li>{{ $city->city }}</li>
                @endforeach
            </ul>
            @endforeach
        </div>
    </div>
</section>
