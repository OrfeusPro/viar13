@extends('layots.common')

@section('title', $data['meta_title'])
@section('meta_desc', $data['meta_desc'])

@section('og_tags')
    <meta property="og:title" content="{{ $data['meta_title'] }}" />
    <meta property="og:description" content="{{ $data['meta_desc'] }}">
    <meta property="og:image" content="{{ \URL::to('/') }}/img/logo.png" />
@endsection

@section('styles')
    <script src="{{ asset('js/InteriorGenerator.js') }}"></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/collage-constructor.css') }}"
        href="{{ asset('css/modular-pictures.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/collage-constructor-2.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/PhotoEditor.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/icons_tools1.css') }}">
    <link rel="stylesheet" href="{{ asset('css/family-constructor2.css') }}">

    <script src="{{ asset('js/three.min.js') }}"></script>
    <script src="{{ asset('js/3dcanvas_fam.js') }}"></script>
@endsection

@section('content')
    <div itemtype="https://schema.org/Product" itemscope>
        <meta itemprop="name" content="{{ $data['bread_title'] }}" />
        <div itemprop="offers" itemtype="https://schema.org/Offer" itemscope>
            <link itemprop="url" href="{{ url(Request::url()) }}" />
            <meta itemprop="availability" content="https://schema.org/InStock" />
            <meta itemprop="priceCurrency" content="EUR" />
            <meta itemprop="price" content="0" />
            @php
                $mt = Carbon\Carbon::now();
            @endphp

            <meta itemprop="priceValidUntil" content="{{ $mt->toDateTimeString() }}" />
        </div>
    </div>

    <div class="bread-crumbs">
        <i class="icon-icon3"></i>
        <ul vocab="https://schema.org/" typeof="BreadcrumbList">
            <li property="itemListElement" typeof="ListItem">
                <a property="item" typeof="WebPage" href="{{ url('/') }}">
                    <span property="name">@lang('account.index1')</span></a>
                <meta property="position" content="1">
            </li>
            <li property="itemListElement" typeof="ListItem">
                <span property="name">{{ $data['title'] }}</span>
                <meta property="position" content="2">
            </li>
        </ul>
    </div>

    @include('partials.family.generator')

    @include('partials.family.packs')
    @include('partials.stages_everythin')
    @include('partials.family.comp_pick')

    <script>
        window.onShowInterior = function() {
            return {
                size: {
                    h: size_centimeter_h,
                    w: size_centimeter_w
                },
                src: editor.out(editor.canvas.width, editor.canvas.height, false)
            };
        }

    </script>

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.mask.min.js') }}"></script>
    <script src="{{ asset('js/jquery.matchHeight.min.js') }}"></script>
    <script src="{{ asset('js/jcf.min.js') }}"></script>
    <script src="{{ asset('js/jcf.file.min.js') }}"></script>
    <script src="{{ asset('js/jcf.radio.min.js') }}"></script>
    <script src="{{ asset('js/jcf.checkbox.min.js') }}"></script>
    <script src="{{ asset('js/jcf.range.min.js') }}"></script>
    <script src="{{ asset('js/jquery.collapse_storage.min.js') }}"></script>
    <script src="{{ asset('js/jquery.collapse.min.js') }}"></script>
    <script src="{{ asset('js/slick.min.js') }}"></script>

    <script src="{{ asset('js/modular-pictures_family.min.js') }}"></script>
    <script src="{{ asset('js/templates.js') }}"></script>
    <script src="{{ asset('js/collage-templates.js') }}"></script>

    <link rel="stylesheet" href="{{ asset('css/interior.css') }}">
    <script src="{{ asset('js/interior.js') }}"></script>
    <script src="{{ asset('js/dropzone/dropzone.min.js') }}"></script>

    @include('partials.family.devs')
    @include('partials.family.bot_styles')
    @include(  'partials.schema_reviews')
@endsection
