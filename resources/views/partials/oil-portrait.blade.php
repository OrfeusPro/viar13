@extends('layots.common')

@section('title', $head['meta_title'])
@section('desc', $head['meta_desc'])

@section('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('css/oil-portrait.css') }}">
@endsection

@section('content')

<section class="stylization-banner"
    style="background: url('{{ asset('img/oil-portrait-bg.png') }}') no-repeat 50% 0; background-size: cover;">
    <div class="bread-crumbs">
        <i class="icon-icon3"></i>

        <ul vocab="https://schema.org/" typeof="BreadcrumbList">
            <li property="itemListElement" typeof="ListItem">
              <a property="item" typeof="WebPage"
                  href="{{ url('/') }}">
                <span property="name">@lang('account.index1')</span></a>
              <meta property="position" content="1">
            </li>
            <li property="itemListElement" typeof="ListItem">
              <span property="name">{{ $head['meta_title'] }}</span>
              <meta property="position" content="2">
            </li>
        </ul>

    </div>
    <div class="stylization-banner-content clearfix">
        <div class="title">
            <h2>{{ $head['meta_title'] }}</h2>
            <h4>{{ trans('gl.create_your_art') }}</h4>
        </div>
        <div class="stylization-tabs">
            <div class="tabs-title">
                <h1 class="h3__title">{{ $head['meta_title'] }}</h1>
            </div>
            <div class="tabs-content">
                <ul class="tabs-item">
                    <li class="active" data-tads="what-canvas"><a
                            href="javascript:void(0)">{{ $collage_header['c_right1'] }}</a></li>
                    <li data-tads="requirements"><a href="javascript:void(0)">{{ $collage_header['c_right2'] }}</a></li>
                    <li data-tads="prices-sizes"><a href="javascript:void(0)">{{ $collage_header['c_right3'] }}</a></li>
                    <li data-tads="tabs-work"><a href="javascript:void(0)">{{ $collage_header['c_right4'] }}</a></li>
                    <li data-tads="service-quality"><a href="javascript:void(0)">{{ $collage_header['c_right5'] }}</a></li>
                    <li data-tads="shipping-time"><a href="javascript:void(0)">{{ $collage_header['c_right6'] }}</a></li>
                </ul>
                <div class="solial">
                    <h6>{{ $collage_header['c_right1_quest_title'] }}:</h6>
                    <ul>
                        <li><a target="_blank" href="{{ setting('sots-seti.telegram_link') }}"><img
                        src="{{ asset('img/solial-icon.png') }}"  @frontendAlt('partials/oil-portrait.blade.php', (asset('img/solial-icon.png')), '', '')></a></li>
                        <li><a target="_blank" href="{{ setting('sots-seti.what_link') }}"><img
                                    src="{{ asset('img/solial-icon2.png') }}"  @frontendAlt('partials/oil-portrait.blade.php', (asset('img/solial-icon2.png')), '', '')></a></li>
                        <li><a target="_blank" href="{{ setting('sots-seti.viber_link') }}"><img
                                    src="{{ asset('img/solial-icon3.png') }}"  @frontendAlt('partials/oil-portrait.blade.php', (asset('img/solial-icon3.png')), '', '')></a></li>
                        <li><a target="_blank" href="{{ setting('sots-seti.gmail_link') }}"><img
                                    src="{{ asset('img/solial-icon4.png') }}"  @frontendAlt('partials/oil-portrait.blade.php', (asset('img/solial-icon4.png')), '', '')></a></li>
                    </ul>
                </div>
            <a class="order" href="{{ route('oil_portrait.buy') }}" onClick="add_to_seo()"><span>{{ $collage_header['c_right_order_title'] }}</span></a>
            </div>
        </div>
    </div>
</section>

@include('partials.graph_portrait_promo.tabs')

<section class="trust">
    <div class="trust-content">
        <div class="title">
        <h2>{{ $head['port_diff_hart_title'] }}</h2>
        </div>
        <p class="title-p">{{ $head['port_diff_hart_text'] }}</p>
        <div class="trust-tabs">
            <div class="tabs-link">
                <ul class="link">
                    @foreach($ph_items as $item)
                        <li data-item="tabs-item{{ $loop->index }}"
                        @if($loop->first) class="active" @endif
                        >{!! $item['name'] !!}
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="tabs-items">
                @foreach($ph_items as $item)
                <div class="tabs-item{{ $loop->index }} tabs-item @if($loop->first) active @endif ">
                <img src="{{ Voyager::image($item['img1']) }}"  class="img-center" @frontendAlt('partials/oil-portrait.blade.php', (Voyager::image($item['img1'])), '', '')>
                    <div class="text">
                        <p>{!! $item['text'] !!}</p>
                        <img src="{{ Voyager::image($item['img2']) }}"  class="img-text" @frontendAlt('partials/oil-portrait.blade.php', (Voyager::image($item['img2'])), '', '')>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<section class="portraits">
    <div class="portraits-content">
        <p>{{ $head['port_prev_title'] }}</p>
        <div class="portraits-slider">
            @foreach($ps_items->chunk(2) as $sub_items)
            <div>
                @foreach($sub_items as $item)
                <div class="portraits-item">
                    <div class="img">
                        <i></i>
                        <img   class="p__img" src="{{ Voyager::image($item['image']) }}"  @frontendAlt('partials/oil-portrait.blade.php', (Voyager::image($item['image'])), ($item['name' ]), ($item['name' ]))>
                    </div>
                    <h4>{{ $item['name' ] }}</h4>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
    </div>
</section>

@include('partials.graph_portrait_promo.steps')
@include('partials.graph_portrait_promo.everyth')
@include('partials.graph_portrait_promo.packs')
@include('partials.graph_portrait_promo.our_work')



<script src="{{ asset('js/BeerSlider.min.js') }}"></script>
<script src="{{ asset('js/slick.min.js') }}"></script>
<script src="{{ asset('js/oil-portrait.min.js') }}"></script>
@endsection
