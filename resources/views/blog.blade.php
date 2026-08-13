@extends('layots.common')

@section('title', $data['meta_title'])

@section('og_tags')
    <meta property="og:title" content="{{ $data['meta_title'] }}" />
@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/blog.css') }}">
    <style>
        .blog-article img {
            max-height: 100%;
            width: 100%;
            height: 100% !important;
        }
    </style>
@endsection

@section('content')
    <div class="bread-crumbs">
        <i class="icon-icon3"></i>
        <ul vocab="https://schema.org/" typeof="BreadcrumbList">
            <li property="itemListElement" typeof="ListItem">
                <a property="item" typeof="WebPage" href="{{ url('/') }}">
                    <span property="name">@lang('breadcrumbs.home')</span></a>
                <meta property="position" content="1">
            </li>
            <li property="itemListElement" typeof="ListItem">
                <span property="name">{{ $data['title'] }}</span>
                <meta property="position" content="2">
            </li>
        </ul>
    </div>

    <section class="blog">
        <div class="title">
            <h1>{{ $data['title'] }}</h1>
            <h5>ViarStudia</h5>
            <p>{!! $data['desc'] !!}</p>
        </div>
        <div class="blog-content clearfix">
            <div class="blog-ideas">
                <h2>{!! $data['int_ideas'] !!}</h2>
                <div class="slider-ideas">
                    @foreach ($idea_posts as $post)
                        <div class="ideas-item">
                            <div class="img">
                                @php
                                    $postImage = image_picture_sources(Voyager::image($post['image']));
                                @endphp
                                <picture>
                                    @if($postImage['src_webp'])
                                        <source srcset="{{ $postImage['src_webp'] }}" type="image/webp">
                                    @endif
                                    <source srcset="{{ $postImage['src'] }}" type="{{ $postImage['type'] ?? 'image/jpeg' }}">
                                <img @altAttrs(['type' => \App\Models\BlogPost::class, 'id' => $post['id'] ?? null], 'image', $post['image'] ?? null, null, $post['title'] ?? null) class=""
                                    src="{{ $postImage['src'] }}"
                                    data-src="{{ $postImage['src'] }}">
                                </picture>
                            </div>
                            <h3 class="b_post__title">{{ $post['title'] }}</h3>
                            <a
                                href="{{ route('blog_inner', $post['slug']) }}"><span>{{ $data['read_more'] }}</span></a>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="blog-article">
                <div class="blog-items clearfix js_blog_arts" data-url="{{ Request::url() }}"
                    data-more="{{ $data['read_more'] }}">
                    @foreach ($blog_posts as $blog_item)
                        <div class="blog-item">
                            <div class="img">
                                @php
                                    $blogItemImage = image_picture_sources(Voyager::image($blog_item['image']));
                                @endphp
                                <picture>
                                    @if($blogItemImage['src_webp'])
                                        <source srcset="{{ $blogItemImage['src_webp'] }}" type="image/webp">
                                    @endif
                                    <source srcset="{{ $blogItemImage['src'] }}" type="{{ $blogItemImage['type'] ?? 'image/jpeg' }}">
                                <img @altAttrs(['type' => \App\Models\BlogPost::class, 'id' => $blog_item['id'] ?? null], 'image', $blog_item['image'] ?? null, null, $blog_item['title'] ?? null) class="img"
                                    src="{{ $blogItemImage['src'] }}"
                                    data-src="{{ $blogItemImage['src'] }}">
                                </picture>
                            </div>
                            <h3 class="b_post__title">{{ $blog_item['title'] }}</h3>
                            <p>{!! strip_tags(Str::limit($blog_item['text'], 120)) !!}</p>
                            <a href="{{ route('blog_inner', $blog_item['slug']) }}"><span>{{ $data['read_more'] }}</span></a>
                        </div>
                    @endforeach
                </div>
                <div class="clearfix">
                    <a class="download js_load_more" href="javascript:void(0)">{{ $data['load_more'] }}</a>
                </div>
            </div>
        </div>
    </section>

    @include('partials.blog.scripts')
@endsection
