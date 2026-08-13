@extends('layots.common')

@section('title', $globs['meta_title'])
@section('meta_desc', $globs['meta_desc'])

@section('og_tags')
    <meta property="og:title" content="{{ $globs['meta_title'] }}"/>
    <meta property="og:description" content="{{ $globs['meta_desc'] }}"/>
@endsection

@section('styles')
    <style>
        .modile {
            display: inline-block !important;
        }

    </style>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/gallery-modular.css') }}">

    @php
        $add_class = '';
        $add_class2 = '';
    @endphp

    @if ($type == 'module')
        @php
            $add_class = 'gallery-modular-slider';
            $add_class2 = 'gallery-modular-banner';
        @endphp
    @elseif($type == 'reproduction')
        @php
            $add_class = 'gallery-reproductions-slider';
            $add_class2 = 'gallery-reproductions-banner';
        @endphp
        <link rel="stylesheet" type="text/css" href="{{ asset('css/gallery-reproductions.css') }}">
    @elseif($type == 'photo')
        @php
            $add_class = 'gallery-photo-slider';
            $add_class2 = 'gallery-photo-banner';
        @endphp
        <link rel="stylesheet" type="text/css" href="{{ asset('css/gallery-photo.css') }}">
    @endif

@endsection

@section('content')

    {{ Breadcrumbs::render('gallery_category_add', $page) }}

    <section class="{{ $add_class2 }}">
        <div class="{{ $add_class }} gallery-modular-slider">
            @if ($top_slides)
                @foreach ($top_slides as $slide)
                    <div class="reproductions-slider-item clearfix">
                        <div class="text">
                            <div class="title">
                                <h1 class="p__title__mn">{{ $slide['title'] }}</h1>
                            </div>
                            <p class="text__desc__gal">{!! $slide['text'] !!}</p>
                        </div>
                        <div class="img">
                            <img alt="{{ $slide['title'] }}" title="{{ $slide['title'] }}"
                                 src="{{ Voyager::image($slide['image']) }}">
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </section>
    @if (count($categories) > 0)
        <section class="genres">
            <div class="title">
                <h3>{{ $globs['mod_title'] }}</h3>
            </div>
            <div class="foto-slider">
                @foreach ($categories as $category)
                    <div class="foto-item">
                        <div
                                style="background: url('{{ asset('img/foto-slider-bg.png') }}') no-repeat 50% 50%; background-size: cover;">
                            <a href="{{ App\Models\GalleryCategory::getUrlById($page->url, $category->url) }}"></a>
                            <img src="{{ asset('img/foto-img1.png') }}" alt="" class="foto-item1">
                            <img src="{{ asset('img/foto-img2.png') }}" alt="" class="foto-item2">
                            <h3>{{ App\Models\GalleryCategory::getNameById($category->id)['name'] }}</h3>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <section class="gallery-modular-catalog">
        <div class="gallery-modular-catalog-content">
            <div class="container">
                <div class="popular-content">
                    <div class="title">
                        @if ($page->url == 'module')
                            <h3> {{ $all_globs['pop_modc_title'] }}</h3>
                        @elseif($page->url == 'reproduction')
                            <h3> {{ $all_globs['pop_reprc_title'] }}</h3>
                        @elseif($page->url == 'photo')
                            <h3> {{ $all_globs['pop_photoc_title'] }}</h3>
                        @endif
                        <ul>
                            <li><i class="icon-icon6"></i></li>
                            <li><i class="icon-icon6"></i></li>
                            <li><i class="icon-icon6"></i></li>
                            <li><i class="icon-icon6"></i></li>
                            <li><i class="icon-icon6"></i></li>
                        </ul>
                    </div>
                    <div class="popular-slider">
                        @if ($pop_arts)
                            @foreach ($pop_arts as $item)
                                <div class="popular-item">
                                    <div>
                                        <div class="img">
                                            <img alt="{{ $item->name }}" title="{{ $item->name }}"
                                                 src="{{ '/storage/' . json_decode($item->images)[0] }}">
                                        </div>
                                        <h5>{{ $item->name }}</h5>
                                        <i>{{ $all_globs['size_title'] }}
                                            <span>{{ App\Models\GalleryItem::getSizeByItemId($item->id) }}</span>
                                        </i>
                                        <p>{{ $all_globs['price_text'] }}
                                            <span>{{ $all_globs['price_from_text'] }} {{ $item->price_from }} €</span>
                                        </p>
                                        <a data-id="{{ $item->id }}"
                                           href="{{ App\Models\GalleryItem::getItemSingleUrlById($item->id) }}"><span>{{ $all_globs['order_text'] }}<i></i></span></a>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="popular-content">
                    <div class="title">
                        @if ($page->url == 'module')
                            <h3> {{ $all_globs['rec_modc_title'] }}</h3>
                        @elseif($page->url == 'reproduction')
                            <h3> {{ $all_globs['rec_reprc_title'] }}</h3>
                        @elseif($page->url == 'photo')
                            <h3> {{ $all_globs['rec_photoc_title'] }}</h3>
                        @endif
                        <ul>
                            <li><i class="icon-icon6"></i></li>
                            <li><i class="icon-icon6"></i></li>
                            <li><i class="icon-icon6"></i></li>
                            <li><i class="icon-icon6"></i></li>
                            <li><i class="icon-icon6"></i></li>
                        </ul>
                    </div>
                    <div class="popular-slider">
                        @if ($rec_arts)
                            @foreach ($rec_arts as $art)
                                <div class="popular-item">
                                    <div>
                                        <div class="img">
                                            <img alt="{{ $art->name }}" title="{{ $art->name }}"
                                                 src="{{ '/storage/' . json_decode($art->images)[0] }}">
                                        </div>
                                        <h5>{{ $art->name }}</h5>
                                        <i>{{ $all_globs['size_title'] }}
                                            <span>{{ App\Models\GalleryItem::getSizeByItemId($art->id) }}</span>
                                        </i>
                                        <p>{{ $all_globs['price_text'] }}
                                            <span> {{ $all_globs['price_from_text'] }} {{ $art->price_from }} €</span>
                                        </p>
                                        <a {{-- class="js_add_to_bask" --}}
                                           href="{{ App\Models\GalleryItem::getItemSingleUrlById($art->id) }}">
                                            <span>{{ $all_globs['order_text'] }}<i></i></span>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="popular-content">
                    <div class="title">
                        <h3>{{ $all_globs['all_cat_arts_title'] }}</h3>
                        <ul>
                            <li><i class="icon-icon6"></i></li>
                            <li><i class="icon-icon6"></i></li>
                            <li><i class="icon-icon6"></i></li>
                            <li><i class="icon-icon6"></i></li>
                            <li><i class="icon-icon6"></i></li>
                        </ul>
                    </div>
                    <div class="popular-slider all__arts">
                        @foreach ($all_arts as $art)
                            <div class="popular-item">
                                <div>
                                    <div class="img">
                                        <img alt="{{ $art->name }}" title="{{ $art->name }}"
                                             src="{{ '/storage/' . json_decode($art->images)[0] }}">
                                    </div>
                                    <h5>{{ $art->name }}</h5>
                                    <i>{{ $all_globs['size_title'] }}
                                        <span> {{ App\Models\GalleryItem::getSizeByItemId($art->id) }}</span>
                                    </i>
                                    <p>{{ $all_globs['price_text'] }}
                                        <span> {{ $all_globs['price_from_text'] }} {{ $art->price_from }} €</span>
                                    </p>
                                    <a data-id="{{ $art->id }}"
                                       href="{{ App\Models\GalleryItem::getItemSingleUrlById($art->id) }}">
                                        <span>{{ $all_globs['order_text'] }}<i></i></span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('js/jcf.min.js') }}"></script>
    <script src="{{ asset('js/jcf.checkbox.min.js') }}"></script>
    <script src="{{ asset('js/slick.min.js') }}"></script>
    <script src="{{ asset('js/gallery-modular.min.js') }}"></script>

@endsection
