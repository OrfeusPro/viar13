@extends('layots.common')

@section('title', $meta['meta_title'])
@section('desc', $meta['meta_desc'])

@section('og_tags')
    <meta property="og:title" content="{{ $meta['meta_title'] }}" />
    <meta property="og:description" content="{{ $meta['meta_desc'] }}" />
@endsection

@section('styles')

    <link rel="stylesheet" type="text/css" href="{{ asset('css/stylization-paintings.css') }}">

@endsection

@section('content')

    <section class="banner">
        <section class="banner-bg"
            style="
                background: url('{{ asset('img/stylization-paintings-bg.png') }}') no-repeat 68% 0; background-size: cover;">
        </section>
        <div class="banner-content">
            <div class="title">
                <h1 class="g__h1">{{ $data['title'] }}</h1>
                <h4>{{ $data['subtitle'] }}</h4>
            </div>
        </div>
    </section>

    <section class="stylization-your">
        <div class="your-content">
            <div class="title">
                <h2>{!! $data['style_title'] !!}</h2>
            </div>
            <div class="style_sub_title">
                {!! $data['style_sub_text'] !!}
            </div>
            <div class="your-items">
                @foreach ($items as $item)
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
                            <p>{!! $item['short_desc'] !!}</p>
                            @if ($item['price_from'])
                                <strong>{{ trans('gl.price_text') }}
                                    <span>{{ trans('gl.price_from_text') }}
                                        {{ $item['price_from'] }} €</span></strong>
                            @endif
                            <ul>
                                <li><a href="{{ route('stylization_paintings.page', $item['slug']) }}">
                                        <span>{{ trans('gl.read_more') }}</span></a></li>
                            </ul>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="gift-card">
        <div class="title">
            <h2>{{ $data['gift_title'] }}</h2>
        </div>
        <div class="title-p">
            {!! $data['gift_sub'] !!}
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
            <a href="{{ route('gift_card') }}"><span>{!! $data['gift_order'] !!}</span></a>
        </div>
    </section>


    <script src="{{ asset('js/stylization-paintings.min.js') }}"></script>
@endsection
