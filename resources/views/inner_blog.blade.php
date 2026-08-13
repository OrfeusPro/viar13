@extends('layots.common')

@section('title', $data['meta_title'])

@section('og_tags')
    <meta property="og:title" content="{{ $data['meta_title'] }}" />
    <meta property="og:description" content="{{ $fb_short_text }}">
@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/blog.css') }}">
@endsection

@section('content')
    <div class="bread-crumbs">
        <i class="icon-icon3"></i>
        <ul vocab="https://schema.org/" typeof="BreadcrumbList">
            <li property="itemListElement" typeof="ListItem">
                <a property="item" typeof="WebPage" href="{{ url('/') }}">
                    <span property="name">@lang('account.index1')</span></a>
                <meta property="position" content="1">
            </li>
            <li property="itemListElement" typeof="ListItem">
                <a property="item" typeof="WebPage" href="{{ route('blog') }}">
                    <span property="name">{{ $b_data['meta_title'] }}</span></a>
                <meta property="position" content="2">
            </li>
            <li property="itemListElement" typeof="ListItem">
                <span property="name">{{ $data['title'] }}</span>
                <meta property="position" content="3">
            </li>
        </ul>
    </div>

    <section class="blog-lnner">
        <div class="title">
            <h2>{{ $b_data['title'] }}</h2>
        </div>
        <div class="blog-content">
            <i>{{ Carbon\Carbon::parse($b_data->created_at)->format('d.m.Y ') }}</i>
            <h3>{{ $data['title'] }}</h3>
            <div class="blog-article">
                <article>
                    <div class="text--inner">
                        {!! render_content_images($data['text']) !!}
                    </div>
                </article>
            </div>
            <div class="soc__icons" style="display:flex;align-items:center;">
                <ul class="social">
                    <li>
                        <a class="js_fb" href="{{ $b_data['fb_link'] }}" target="_blank">
                            <img src="{{ asset('img/social-blog1.png') }}" alt=""></a>
                    </li>
                </ul>
                <span style="margin-left:15px;font-size:15px;">{{ trans('gl.share_text') }}</span>
            </div>
        </div>
    </section>

    <script src="{{ asset('js/jquery.mCustomScrollbar.min.js') }}"></script>
    <script src="{{ asset('js/slick.min.js') }}"></script>
    <script src="{{ asset('js/blog.min.js') }}"></script>

    <div class="share42init" style="display:none;"></div>
    <script src="{{ asset('/share42/share42.js') }}"></script>
@endsection
