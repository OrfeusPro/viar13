<section class="canvas-banner">
    <div class="bread-crumbs">
        <i class="icon-icon3"></i>

        <ul vocab="https://schema.org/" typeof="BreadcrumbList">
            <li property="itemListElement" typeof="ListItem">
                <a property="item" typeof="WebPage" href="{{ url('/') }}">
                    <span property="name">@lang('account.index1')</span></a>
                <meta property="position" content="1">
            </li>
            <li property="itemListElement" typeof="ListItem">
                @php
                if(isset($canvas_head['c_right_top'])){
                $title = $canvas_head['c_right_top'];
                }else{
                $title = $canvas_head['meta_title'];
                }
                @endphp
                <span property="name">{{ $title }}</span>
                <meta property="position" content="2">
            </li>
        </ul>

    </div>
    <img
        src="{{ $og_image ?? url('/images_single/canvas.png') }}"
        alt=""
        class="canvas-banner-img"
        width="{{ $og_image_width ?? 1356 }}"
        height="{{ $og_image_height ?? 848 }}"
    >
    <div class="canvas-banner-content clearfix def__tabs">
        <div class="canvas-tabs">
            <div class="tabs-title">
                <h1 class="h3__title">{{ $canvas_head['c_right_top'] }}</h1>
            </div>
            <div class="tabs-content">
                <ul class="tabs-item">
                    <li class="active" data-tads="what-canvas"><a
                            href="javascript:void(0)">{{ $canvas_head['c_right1'] }}</a></li>
                    <li data-tads="requirements"><a href="javascript:void(0)">{{ $canvas_head['c_right2'] }}</a></li>
                    <li data-tads="prices-sizes"><a href="javascript:void(0)">{{ $canvas_head['c_right3'] }}</a></li>
                    <li data-tads="tabs-work"><a href="javascript:void(0)">{{ $canvas_head['c_right4'] }}</a></li>
                    <li data-tads="service-quality"><a href="javascript:void(0)">{{ $canvas_head['c_right5'] }}</a></li>
                    <li data-tads="shipping-time"><a href="javascript:void(0)">{{ $canvas_head['c_right6'] }}</a></li>
                </ul>
                <div class="solial">
                    <h6>{{ $canvas_head['c_right1_quest_title'] }}:</h6>
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
                <a class="order js_scroll_calc" href="#generator"
                    onClick="add_to_seo()"><span>{{ $canvas_head['c_right_order_title'] }} </span></a>
            </div>
        </div>
        <div class="lt__cover">
            <div class="title">
                <h2>{{ $canvas_head['c_left1'] }}</h2>
                <h4>{{ $canvas_head['c_left2'] }}</h4>
            </div>
        </div>
    </div>
</section>

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
