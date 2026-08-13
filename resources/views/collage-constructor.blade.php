@extends('layots.common')

@section('title', $data['meta_title'])

@section('og_tags')
    <meta property="og:title" content="{{ $data['meta_title'] }}" />
    <meta property="og:image" content="{{ \URL::to('/') }}/img/logo.png" />
@endsection

@section('styles')
    <script src="{{ asset('js/InteriorGenerator.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/collage_combine.css') }}">
    @include('partials.collage-constructor.top_styles')
@endsection

@section('content')
    <div itemtype="https://schema.org/Product" itemscope>
        <meta itemprop="name" content="{{ $data['bread_title'] }}" />
        <div itemprop="offers" itemtype="https://schema.org/Offer" itemscope>
            <link itemprop="url" href="{{ url(Request::url()) }}" />
            <meta itemprop="availability" content="https://schema.org/InStock" />
            <meta itemprop="priceCurrency" content="EUR" />
            <meta itemprop="price" content="0" />
            @php $mt = Carbon\Carbon::now(); @endphp
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
                <span property="name">{{ $data['bread_title'] }}</span>
                <meta property="position" content="2">
            </li>
        </ul>
    </div>

    @include('partials.collage-constructor.generate')
    @include('partials.collage-constructor.packing')
    @include('partials.stages_everythin')
    @include('partials.collage-constructor.comp_pic')

    @include('partials.collage-constructor.bot_scripts')
    @include('partials.collage-constructor.devs')
    @include(  'partials.schema_reviews')
@endsection

