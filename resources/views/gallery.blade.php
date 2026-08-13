@extends('layots.common')

@section('title', $gallery['meta_title'])
@section('meta_desc', $gallery['meta_description'])

@section('og_tags')
    <meta property="og:title" content="{{ $gallery['meta_title'] }}" />
    <meta property="og:description" content="{{ $gallery['meta_description'] }}" />
    <meta property="og:image" content="{{ \URL::to('/') }}/img/logo.png" />
@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/gallery.css') }}">
@endsection

@section('content')
    <section class="gallery-banner">
        {{ Breadcrumbs::render('gallery_add') }}
        <img src="{{ asset('img/gallery-banner-img.png') }}" alt="" class="gallery-banner-img">
        <div class="gallery-banner-content clearfix">
            <div class="title">
                <h1 class="gal__h1">{{ $gallery['left_title'] }}</h1>
                <h4>{{ $gallery['left_sub_title'] }}</h4>
            </div>
            <div class="gallery-tabs">
                <div class="tabs-title">
                    <h3 class="h3__title gal__h3">{{ $gallery['right_title'] }}</h3>
                    <ul>
                        <li><a href="#modular">{{ $gallery['right_t1'] }}</a><img
                                src="{{ asset('img/gallery-icon1.png') }}" alt="">
                        </li>
                        <li><a href="#photo">{{ $gallery['right_t2'] }}</a><img
                                src="{{ asset('img/gallery-icon2.png') }}" alt=""></li>
                        <li><a href="#reproductions">{{ $gallery['right_t3'] }}</a><img
                                src="{{ asset('img/gallery-icon3.png') }}" alt="">
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="gb__text">
            {!! $gallery['gal__desc'] !!}
        </div>
    </section>

    <section id="modular" class="modular">
        <div class="container">
            <div class="modular-content clearfix">
                <div class="img">
                    <img class="" alt="{{ $gallery['modc_title'] }}" title="{{ $gallery['modc_title'] }}"
                        src="{{ asset('img/moudlar_v2.png') }}" data-src="{{ asset('img/moudlar_v2.png') }}">
                </div>
                <div class="text">
                    <div class="modular-title">
                        <h3>{{ $gallery['modc_title'] }}</h3>
                        <h5>{{ $gallery['modc_sub'] }}</h5>
                    </div>
                    <div class="modular__gal__text">
                        {!! $gallery['modc_text'] !!}
                    </div>
                    <a
                        href="{{ route('gallery_module', 'module') }}"><span>{{ $gallery['modc_link_text'] }}</span></a>
                </div>
            </div>
        </div>
    </section>

    <section id="photo" class="photo">
        <div class="container">
            <div class="photo-content clearfix">
                <div class="img">
                    <img class="" alt="{{ $gallery['fotoc_title'] }}" title="{{ $gallery['fotoc_title'] }}"
                        src="{{ asset('img/photo-img.png') }}" data-src="{{ asset('img/photo-img.png') }}">
                </div>
                <div class="text">
                    <div class="photo-title">
                        <h3>{{ $gallery['fotoc_title'] }}</h3>
                        <h5>{{ $gallery['fotoc_sub'] }}</h5>
                    </div>
                    <div class="modular__gal__text">
                        {!! $gallery['fotoc_text'] !!}
                    </div>
                    <a href="{{ route('gallery_module', 'photo') }}">
                        <span>{{ $gallery['fotoc_link_text'] }}</span></a>
                </div>
            </div>
        </div>
    </section>

    <section id="reproductions" class="reproductions">
        <div class="container">
            <div class="reproductions-content clearfix">
                <div class="img">
                    <img class="" alt="{{ $gallery['repr_title'] }}" title="{{ $gallery['repr_title'] }}"
                        src="{{ asset('img/reproductions-img.png') }}"
                        data-src="{{ asset('img/reproductions-img.png') }}">
                </div>
                <div class="text">
                    <div class="reproductions-title">
                        <h3>{{ $gallery['repr_title'] }}</h3>
                        <h5>{{ $gallery['repr_sub_title'] }}</h5>
                    </div>
                    <div class="modular__gal__text">
                        {!! $gallery['repr_text'] !!}
                    </div>
                    <a href="{{ route('gallery_module', 'reproduction') }}"><span>
                            {{ $gallery['repr_link_text'] }}</span></a>
                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('js/gallery.min.js') }}"></script>
@endsection
