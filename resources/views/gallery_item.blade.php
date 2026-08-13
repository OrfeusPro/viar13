@extends('layots.common')

    @if ($item->meta_title != '')
        @section('title', $item->meta_title)
    @else
        @section('title', $item->name)
    @endif

    @section('meta_desc', $item->meta_desc)

    @section('meta_robots', $item->meta_robots)

    @section('og_tags')
        <meta property="og:title" content="{{ $item->name }}" />
        <meta property="og:description" content="{{ $item->meta_desc }}" />
        <meta property="og:image" content="{{ \URL::to('/') }}/img/logo.png" />
    @endsection

    @section('styles')
        <link rel="stylesheet" type="text/css" href="{{ asset('css/gallery-modular-one.css') }}?v=0.01">
    @endsection

    @section('content')

        {{ Breadcrumbs::render('gallery_item_add', $page, $item) }}
        <div itemtype="https://schema.org/Product" itemscope>
            <meta itemprop="name" content="{{ $item->name }}" />
            @php
                $images = json_decode($item->images, true);
            @endphp
            @if ($images)
                @foreach ($images as $image)
                    @php $src = '/storage/' . $image; @endphp
                    <link itemprop="image" href="{{ $src }}" />
                @endforeach
            @endif
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

        @include('partials.gallery_item.header')
        @include('partials.gallery_item.see_to_order')
        @include('partials.gallery_item.compositions')
        @include('partials.gallery_item.create_pic')
        @include('partials.gallery_item.comp_pics')
        @include('partials.gallery_item.our_works')

        <script>
            window.is_image_canvas = 1;
        </script>

        <script src="{{ ver_asset('js/jcf.min.js') }}"></script>
        <script src="{{ ver_asset('js/jcf.file.min.js') }}"></script>
        <script src="{{ ver_asset('js/jcf.radio.min.js') }}"></script>
        <script src="{{ ver_asset('js/jcf.checkbox.min.js') }}"></script>
        <script src="{{ ver_asset('js/jcf.range.min.js') }}"></script>
        <script src="{{ ver_asset('js/jquery.collapse_storage.min.js') }}"></script>
        <script src="{{ ver_asset('js/jquery.collapse.min.js') }}"></script>
        <script src="{{ ver_asset('js/slick.min.js') }}"></script>
        <script src="{{ ver_asset('js/gallery-modular-one.min.js') }}"></script>
        <script src="{{ ver_asset('js/interior.js') }}?v=99"></script>

        <link rel="stylesheet" href="{{ ver_asset('css/icons_tools1.css') }}">
        <link rel="stylesheet" href="{{ ver_asset('css/interior.css') }}">
        <link rel="stylesheet" href="{{ ver_asset('css/portrait-name.css') }}?v=0.01">

        @include('partials.gallery_item.devs')
        @include(  'partials.schema_reviews')
    @endsection
