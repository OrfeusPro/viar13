<script>
    var add_to_seo = function(){
        var cur_item__title = $('.h3__title').text();

        window.dataLayer = window.dataLayer || [];
        dataLayer.push({
            'ecommerce': {
                    'currencyCode': 'EUR',
                    'detail': {
                    'actionField': {'list': 'Sezonas preces'},
                        'products': [{
                            'name': cur_item__title,
                            'id': '0',
                            'categoryId': '0',
                            'category': '',
                        }]
                    }
            },
            'event': 'EE-event',
            'EE-event-category': 'Enhanced Ecommerce',
            'EE-event-action': 'ProductDetail',
            'EE-event-non-interaction': 'False'

        });
     }
</script>

<section class="graphic-banner"
    style="background: url('{{ Voyager::image($item['add_image_bg']) }}') no-repeat 50% 0; background-size: cover;">
    <div class="bread-crumbs">
        <i class="icon-icon3"></i>
        <ul vocab="https://schema.org/" typeof="BreadcrumbList">
            <li property="itemListElement" typeof="ListItem">
                <a property="item" typeof="WebPage" href="{{ route('home') }}">
                    <span property="name">@lang('account.index1')</span></a>
                <meta property="position" content="1">
            </li>
            @if(!isset($is_style_painting))
            <li property="itemListElement" typeof="ListItem">
                <a property="item" typeof="WebPage" href="{{ route('graphic_portrait.index') }}">
                    <span property="name">{{ trans('gl.graph_portrait') }}</span></a>
                <meta property="position" content="2">
            </li>
            @else
            <li property="itemListElement" typeof="ListItem">
                <a property="item" typeof="WebPage" href="{{ route('stylization_paintings.index') }}">
                    <span property="name">{{ trans('gl.styl_paint_title') }}</span></a>
                <meta property="position" content="2">
            </li>
            @endif
            <li property="itemListElement" typeof="ListItem">
                <span property="name">{!! $item['name'] !!}</span>
                <meta property="position" content="3">
            </li>
        </ul>

    </div>
    @if($item['add_image2_inner'])
        @php
            $graphicBannerImageSources = image_picture_sources($item['add_image2_inner'], true);
        @endphp
        <picture>
            @if(!empty($graphicBannerImageSources['src_webp']))
                <source srcset="{{ $graphicBannerImageSources['src_webp'] }}" type="image/webp">
            @endif
            @if(!empty($graphicBannerImageSources['src']) && !empty($graphicBannerImageSources['type']))
                <source srcset="{{ $graphicBannerImageSources['src'] }}" type="{{ $graphicBannerImageSources['type'] }}">
            @endif
            <img src="{{ $graphicBannerImageSources['src'] }}" @altAttrs(['type' => \App\Models\GalleryItem::class, 'id' => data_get($item, 'id')], 'add_image2_inner', data_get($item, 'add_image2_inner'), null, data_get($item, 'name')) class="graphic-banner-img">
        </picture>
    @endif
    <div class="graphic-banner-content def__tabs clearfix">
        <div class="graphic-tabs">
            <div class="tabs-title">
                @php
                $has_h3 = 0;
                @endphp
                @foreach(explode(' ', $item['name']) as $name_part)
                @if($loop->first)
                <h5>{{ $name_part}}</h5>
                @else
                @if($has_h3 == 0)
                <h1 class="h3__title">
                    @php
                    $has_h3 = 1;
                    @endphp
                    @endif
                    {!! $name_part !!}
                    @endif
                    @endforeach
                </h1>
                {{-- <h3>{{ $item['name'] }}</h3> --}}
            </div>
            <div class="tabs-content">
                <ul class="tabs-item">
                    <li class="active" data-tads="what-canvas"><a
                            href="javascript:void(0)">{{ $collage_header['c_right1'] }}</a></li>
                    <li data-tads="requirements"><a href="javascript:void(0)">{{ $collage_header['c_right2'] }}</a></li>
                    <li data-tads="prices-sizes"><a href="javascript:void(0)">{{ $collage_header['c_right3'] }}</a></li>
                    <li data-tads="tabs-work"><a href="javascript:void(0)">{{ $collage_header['c_right4'] }}</a></li>
                    <li data-tads="service-quality"><a href="javascript:void(0)">{{ $collage_header['c_right5'] }}</a>
                    </li>
                    <li data-tads="shipping-time"><a href="javascript:void(0)">{{ $collage_header['c_right6'] }}</a>
                    </li>
                </ul>
                <div class="solial">
                    <h6>{{ $collage_header['c_right1_quest_title'] }}:</h6>
                    <ul>
                        <li><a target="_blank" href="{{ setting('sots-seti.telegram_link') }}"><img
                                    src="{{ asset('img/solial-icon.png') }}" alt=""></a></li>
                        <li><a target="_blank" href="{{ setting('sots-seti.what_link') }}"><img
                                    src="{{ asset('img/solial-icon2.png') }}" alt=""></a></li>
                        <li><a target="_blank" href="{{ setting('sots-seti.viber_link') }}"><img
                                    src="{{ asset('img/solial-icon3.png') }}" alt=""></a></li>
                        <li><a target="_blank" href="{{ setting('sots-seti.gmail_link') }}"><img
                                    src="{{ asset('img/solial-icon4.png') }}" alt=""></a></li>
                    </ul>
                </div>
                @if($data_id == 5)
                <a onClick="add_to_seo()" class="order"
                    href="{{ route('graphic_portrait.buy', ['slug' => $item['slug'] ]) }}">
                    <span>{{ $collage_header['c_right_order_title'] }}</span></a>
                @endif

                @if($data_id == 6)
                <a onClick="add_to_seo()" class="order"
                    href="{{ route('styl_portrait.buy', ['slug' => $item['slug'] ]) }}">
                    <span>{{ $collage_header['c_right_order_title'] }}</span></a>
                @endif

            </div>
        </div>
        <div class="lt__cover">
            <div class="title">
                <h2>{!! $item['name'] !!}</h2>
                <h4>{{ trans('gl.create_your_art') }}</h4>
            </div>
        </div>
    </div>
</section>
