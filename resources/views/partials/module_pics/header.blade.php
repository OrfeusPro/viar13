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

<section class="modular-banner">
    <div class="bread-crumbs">
        <i class="icon-icon3"></i>

        <ul vocab="https://schema.org/" typeof="BreadcrumbList">
            <li property="itemListElement" typeof="ListItem">
                <a property="item" typeof="WebPage" href="{{ url('/') }}">
                    <span property="name">@lang('header.gallery')</span></a>
                <meta property="position" content="1">
            </li>
            <li property="itemListElement" typeof="ListItem">
                <span property="name">{!! $mod_head['meta_title'] !!}</span>
                <meta property="position" content="2">
            </li>
        </ul>

    </div>
    <img src="{{ asset('img/modular-banner-img.png') }}"  class="modular-banner-img" @frontendAlt('partials/module_pics/header.blade.php', (asset('img/modular-banner-img.png')), '', '')>
    <img src="{{ asset('img/modular-banner-img2.png') }}"  class="modular-banner-img2" @frontendAlt('partials/module_pics/header.blade.php', (asset('img/modular-banner-img2.png')), '', '')>
    <div class="modular-banner-content def__tabs clearfix">
        <div class="modular-tabs">
            <div class="tabs-title">
                <h1 class="h3__title">{!! $mod_head['right2_title'] !!}</h1>
            </div>
            <div class="tabs-content">
                <ul class="tabs-item">
                    <li class="active" data-tads="what-canvas"><a href="javascript:void(0)">{{ $tab['c_right1'] }}</a>
                    </li>
                    <li data-tads="requirements"><a href="javascript:void(0)">{{ $tab['c_right2'] }}</a></li>
                    <li data-tads="prices-sizes"><a href="javascript:void(0)">{{ $tab['c_right3'] }}</a></li>
                    <li data-tads="tabs-work"><a href="javascript:void(0)">{{ $tab['c_right4'] }}</a></li>
                    <li data-tads="service-quality"><a href="javascript:void(0)">{{ $tab['c_right5'] }}</a></li>
                    <li data-tads="shipping-time"><a href="javascript:void(0)">{{ $tab['c_right6'] }}</a></li>
                </ul>
                <div class="solial">
                    <h6>{{ $tab['c_right1_quest_title'] }}</h6>
                    <ul>
                        <li><a target="_blank" href="{{ setting('sots-seti.telegram_link') }}">
                                <img src="{{ asset('img/solial-icon.png') }}"  @frontendAlt('partials/module_pics/header.blade.php', (asset('img/solial-icon.png')), '', '')></a></li>
                        <li><a target="_blank" href="{{ setting('sots-seti.what_link') }}">
                                <img src="{{ asset('img/solial-icon2.png') }}"  @frontendAlt('partials/module_pics/header.blade.php', (asset('img/solial-icon2.png')), '', '')></a></li>
                        <li><a target="_blank" href="{{ setting('sots-seti.viber_link') }}">
                                <img src="{{ asset('img/solial-icon3.png') }}"  @frontendAlt('partials/module_pics/header.blade.php', (asset('img/solial-icon3.png')), '', '')></a></li>
                        <li><a target="_blank" href="{{ setting('sots-seti.gmail_link') }}">
                                <img src="{{ asset('img/solial-icon4.png') }}"  @frontendAlt('partials/module_pics/header.blade.php', (asset('img/solial-icon4.png')), '', '')></a></li>
                    </ul>
                </div>
                <a class="order js_scroll_calc" href="#generator"
                    onClick="add_to_seo()"><span>{{ $tab['c_right_order_title'] }}</span></a>
            </div>
        </div>
        <div class="lt__cover">
            <div class="title">
                {!! $mod_head['right1_title'] !!}
            </div>
        </div>
    </div>
</section>
