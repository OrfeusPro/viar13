@extends('layots.common')

@section('title', $head['meta_title'])

@section('styles')

    <link rel="stylesheet" type="text/css" href="{{ asset('css/stocks.css') }}">

@endsection

@section('content')

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

    <section class="stocks">
        <div class="title">
        <h2>{{ $head['poss_sales'] }}</h2>
            <h5>ViarStudia</h5>
        </div>
            @include('partials.stocks.header')
       </section>

    <section class="other-offers">
        <div class="title">
            <h2>{{ $head['etc_actual_subm'] }}</h2>
        </div>
        <div class="other-offers-content">
            <div class="container">
                @if($module_big_sale)
                    @php
                        $items_big_sale = $module_big_sale;
                        $cat_name = $head['modc_title'];
                    @endphp
                    @include('partials.stocks.all_big')
                @endif

                {{-- module sale --}}
                @if($mod_sale)
                <div class="popular-content">
                    <div class="title-offers">
                        <h3>{{ $head['modc_sale'] }}</h3>
                        <a href="javascript:void(0)" class="discount">
                            <img src="{{ asset('img/akcii_').app()->getLocale().'.png' }}" alt="">
                            <span><i>%</i></span>
                        </a>
                    </div>
                    <div class="popular-slider">
                        @foreach($mod_sale as $mod_sale_item)
                        <div class="popular-item">
                            <div>
                                <div class="img">
                                    @php
                                        $images = json_decode($mod_sale_item['images'], true);
                                        $image = '/storage/' . $images[0];
                                    @endphp
                                    <img alt="{{ $mod_sale_item['name'] }}"
                                    title="{{ $mod_sale_item['name'] }}" src="{{ $image }}" >
                                </div>
                                <h5>{{ $mod_sale_item['name'] }}</h5>
                                <div class="custom_sale__container">
                                    @php
                                    $custom_sizes = explode(',', $mod_sale_item['custom_size_prices']);
                                        $custom_sizes_sale = explode(',', $mod_sale_item['custom_size_prices_sale']);
                                        if(is_array($custom_sizes_sale) && !empty($custom_sizes_sale) && $custom_sizes_sale[0] != ""){
                                            $custom_sizes_saved = $custom_sizes_sale;
                                        }
                                    @endphp
                                    @include('partials.sale_sizes_list')
                                </div>

                                <p>{{ trans('gl.price_text') }} <span> {{ trans('gl.price_from_text') }} {{ $mod_sale_item['price_from'] }} €</span></p>
                                <a href="{{ App\Models\GalleryItem::getItemSingleUrlById($mod_sale_item['id']) }}"><span>{{ trans('gl.order_btn') }} <i></i></span></a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($foto_big_sale)
                    @php
                        $items_big_sale = $foto_big_sale;
                        $cat_name = $head['fotoc'];
                    @endphp
                    @include('partials.stocks.all_big')
                @endif

                <div class="popular-content">
                    <div class="title-offers">
                    <h3>{{ $head['fotoc_sale'] }}</h3>
                    <a href="javascript:void(0)" class="discount">
                        <img src="{{ asset('img/akcii_').app()->getLocale().'.png' }}" alt="">
                        <span><i>%</i></span>
                    </a>
                </div>

                {{-- foto  sale --}}
                @if($foto_sale)
                <div class="popular-slider">
                    @foreach($foto_sale as $photo_sale_item)
                    <div class="popular-item">
                        <div>
                            <div class="img">
                                @php
                                    $images = json_decode($photo_sale_item['images'], true);
                                    $image = '/storage/' . $images[0];
                                @endphp
                                <img alt="{{ $photo_sale_item['name'] }}" title="{{ $photo_sale_item['name'] }}" src="{{  $image }}">
                            </div>
                            <h5>{{ $photo_sale_item['name'] }}</h5>
                            <div class="custom_sale__container">
                                @php
                                $custom_sizes = explode(',', $photo_sale_item['custom_size_prices']);
                                    $custom_sizes_sale = explode(',', $photo_sale_item['custom_size_prices_sale']);
                                    if(is_array($custom_sizes_sale) && !empty($custom_sizes_sale) && $custom_sizes_sale[0] != ""){
                                        $custom_sizes_saved = $custom_sizes_sale;
                                    }
                                @endphp
                                @include('partials.sale_sizes_list')
                            </div>

                            <p>{{ trans('gl.price_text') }} <span> {{ trans('gl.price_from_text') }} {{ $photo_sale_item['price_from'] }} €</span></p>
                            <a href="{{ App\Models\GalleryItem::getItemSingleUrlById($photo_sale_item['id']) }}"><span>
                                {{ trans('gl.order_btn') }} <i></i></span></a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
                </div>


                @if($repr_big_sale)
                    @php
                        $items_big_sale = $repr_big_sale;
                        $cat_name = $head['repr'];
                    @endphp
                    @include('partials.stocks.all_big')
                @endif

                <div class="popular-content">
                    <div class="title-offers">
                    <h3>{{ $head['repr_sale'] }}</h3>
                    <a href="javascript:void(0)" class="discount">
                        <img src="{{ asset('img/akcii_').app()->getLocale().'.png' }}" alt="">
                        <span><i>%</i></span>
                    </a>
                </div>

                @if($repr_sale)
                <div class="popular-slider">
                    @foreach($repr_sale as $repr_sale_item)
                    <div class="popular-item">
                        <div>
                            <div class="img">
                                @php
                                    $images = json_decode($repr_sale_item['images'], true);
                                    $image = '/storage/' . $images[0];
                                @endphp
                                <img alt="{{ $photo_sale_item['name'] }}" title="{{ $photo_sale_item['name'] }}" src="{{  $image }}">
                            </div>
                            <h5>{{ $repr_sale_item['name'] }}</h5>
                            <div class="custom_sale__container">
                                @php
                                $custom_sizes = explode(',', $repr_sale_item['custom_size_prices']);
                                    $custom_sizes_sale = explode(',', $repr_sale_item['custom_size_prices_sale']);
                                    if(is_array($custom_sizes_sale) && !empty($custom_sizes_sale) && $custom_sizes_sale[0] != ""){
                                        $custom_sizes_saved = $custom_sizes_sale;
                                    }
                                @endphp
                                @include('partials.sale_sizes_list')
                            </div>

                            <p>{{ trans('gl.price_text') }} <span> {{ trans('gl.price_from_text') }} {{ $photo_sale_item['price_from'] }} €</span></p>
                            <a href="{{ App\Models\GalleryItem::getItemSingleUrlById($photo_sale_item['id']) }}"><span>
                                {{ trans('gl.order_btn') }} <i></i></span></a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
                </div>
                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('js/jquery.mask.min.js') }}"></script>
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('js/jcf.min.js') }}"></script>
    <script src="{{ asset('js/jcf.file.min.js') }}"></script>
    <script src="{{ asset('js/slick.min.js') }}"></script>
    <script src="{{ asset('js/stocks.min.js') }}"></script>

    @auth
    <script>
        var hash = window.location.hash;
        if(hash == '#print'){
            $(".popup-print-screen").addClass("active");
        }
    </script>
    @else
    <script>
        var hash = window.location.hash;
        if(hash == '#print'){
            $(".popup-login-registration ").addClass("active");
        }
    </script>
    @endauth
@endsection
