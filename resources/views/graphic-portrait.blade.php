@extends('layots.common')

@section('title', $data['meta_title'])
@section('meta_desc', $data['meta_desc'])

@section('og_tags')
    <meta property="og:title" content="{{ $data['meta_title'] }}" />
    <meta property="og:description" content="{{ $data['meta_desc'] }}" />
@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/graphic-portrait.css') }}">
@endsection

@section('content')
    <section class="banner">
        <section class="banner-bg"
            style="background: url('{{ asset('img/graphic-portrait-bg.png') }}') no-repeat 34% 0; background-size: cover;">
        </section>
        <div class="banner-content">
            <div class="title">
                <h1 class="g__h1">{!! $data['title'] !!}</h1>
                <h4>{!! $data['sub_title'] !!}</h4>
            </div>
        </div>
    </section>

    <section class="graphic-your">
        <div class="your-content">
            <div class="title">
                <h2>{!! $data['style_title'] !!}</h2>
            </div>
            <div class="style_sub_title">
                {!! $data['style_sub_text'] !!}
            </div>
            <div class="your-items">
                @foreach ($graph_items as $item)
                    <div class="your-item">
                        @if ($item['add_image1'])
                            <div class="img">
                                <img class="" @altAttrs(['type' => \App\Models\GalleryItem::class, 'id' => data_get($item, 'id')], 'add_image1', data_get($item, 'add_image1'), null, data_get($item, 'name'))
                                    src="{{ Voyager::image($item['add_image1']) }}"
                                    data-src="{{ Voyager::image($item['add_image1']) }}">
                            </div>
                        @endif
                        <div class="text">
                            <h4>{{ $item['name'] }}</h4>
                            <p class="g__desc__text">{!! $item['short_desc'] !!}</p>
                            @if ($item['price_from'])
                                <strong>{{ trans('gl.price_text') }}
                                    <span>{{ trans('gl.price_from_text') }}
                                        {{ $item['price_from'] }} €</span></strong>
                            @endif
                            <ul>
                                <li><a href="{{ route('graphic_portrait.page', $item['slug']) }}">
                                        <span>{{ $data['read_more'] }}</span></a></li>
                            </ul>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="gift-card">
        <div class="title">
            <h2>{{ $data['gift_card_title'] }}</h2>
        </div>
        <div class="title-p">
            {!! $data['gift_card_text'] !!}
        </div>
        <div class="gift-card-content">
            <div class="card clearfix">
                <div class="card-item">
                    <img src="{{ asset('img/front_') . app()->getLocale() . '.png' }}" alt="">
                </div>
                <div class="card-item">
                    <img src="{{ asset('img/back_') . app()->getLocale() . '.png' }}" alt="">
                </div>
            </div>
            <a href="{{ route('gift_card') }}"><span>{!! $data['gift_card_order_text'] !!}</span></a>
        </div>
    </section>

    <script src="{{ asset('js/graphic-portrait.min.js') }}"></script>
@endsection
