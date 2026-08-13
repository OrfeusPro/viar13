@extends('layots.common')

@section('title', trans('gl.sitemap_title'))

@section('og_tags')
    <meta property="og:title" content="{{ trans('gl.sitemap_title') }}" />
@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/about.css') }}" />
    <style>
        header {
            position: static;
        }

        .sitemap-lnner {
            max-width: 1170px;
            margin: 100px auto 100px auto;
            padding: 0 20px;
            font-family: Arial, sans-serif;
        }

        /* .sitemap-lnner a {
            display: inline-block;
            color: #f2aa55;
            font-size: 0.88em;
            margin-bottom: 5px;
        } */

        .sitemap-lnner h1 {
            font-size: 2.2rem;
            margin-bottom: 40px;
            color: #222;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }

        .sitemap-section {
            margin-bottom: 50px;
        }

        .sitemap-section h2 {
            font-size: 1.6rem;
            margin-bottom: 25px;
            color: #444;
        }

        .sitemap-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .sitemap-category {
            background: #fafafa;
            border: 1px solid #e5e5e5;
            border-radius: 10px;
            padding: 20px;
            transition: box-shadow 0.3s ease;
        }

        .sitemap-category:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.07);
        }

        .sitemap-category h3 {
            font-size: 1.2rem;
            margin-bottom: 12px;
            color: #f2aa55;
        }

        .sitemap-category h3 a {
            color: #f2aa55;
        }

        .sitemap-category ul {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .sitemap-category li {
            margin-bottom: 8px;
        }

        .sitemap-category a {
            text-decoration: none;
            color: #333;
            transition: color 0.2s ease;
        }

        .sitemap-category a:hover {
            color: #f2aa55;
        }

        .sitemap-menu {
            margin: 40px 0;
            padding: 0;
            list-style: none;
            display: flex;
            flex-wrap: wrap;
            gap: 15px 25px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .sitemap-menu li {
            margin: 0;
        }

        .sitemap-menu a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.2s ease, border-bottom 0.2s ease;
            padding-bottom: 2px;
        }

        .sitemap-menu a:hover {
            color: #f2aa55;
        }
    </style>
@endsection

@section('content')
    <div class="sitemap-lnner">
        <h1>{{ trans('gl.sitemap_title') }}</h1>

        <div class="sitemap-section">
            <h2>
                <a href="{{ route('hb.gallery.index') }}">{{ trans('gl.gallery') }}</a>
            </h2>
            <div class="sitemap-grid">
                @foreach ($g_types as $type)
                    <div class="sitemap-category">
                        @php
                            $type_url = '';

                            switch($type->url) {
                                case ($type->url == 'graphical-portrait'):
                                    $type_url = route('graphic_portrait.index');
                                    break;
                                case ($type->url == 'stylization-paintings'):
                                    $type_url = route('stylization_paintings.index');
                                    break;
                                case ($type->url == 'oil-pictures'):
                                    $type_url = route('graphic_portrait.oil');
                                    break;
                                case ($type->url == 'Sharj'):
                                    $type_url = route('caricature');
                                    break;
                                default:
                                    $type_url = route('hb.gallery.module', ['type' => $type->url]);
                            }
                        @endphp
                        <h3>
                            <a href="{{ $type_url }}">
                                {{ trans('gl.type') }}: {{ $type->getTranslatedAttribute('name') }}
                            </a>
                        </h3>
                        @isset($categories[$type->url])
                        <ul>
                            @foreach ($categories[$type->url] as $category)
                                <li>
                                    <a href="{{ route('hb.gallery.category', ['type' => $type->url, 'category' => $category->url]) }}">
                                        {{ $category->getTranslatedAttribute('name') }}
                                    </a>
                                </li>
                            @endforeach
                            {{-- @foreach ($type->items as $item)
                                @if($item->active === 1)
                                <li>
                                    @php
                                        $gi_url = '';

                                        switch($type->url) {
                                            case ($type->url == 'graphical-portrait'):
                                                $gi_url = route('graphic_portrait.new_page', ['slug' => $item->slug]);
                                                break;
                                            case ($type->url == 'stylization-paintings'):
                                                $gi_url = route('stylization_paintings.page', ['slug' => $item->slug]);
                                                break;
                                            case ($type->url == 'oil-pictures'):
                                                $gi_url =  route('graphic_portrait.new_page', ['slug' => 'kartiny']);
                                                break;
                                            case ($type->url == 'Sharj'):
                                                $gi_url = route('sub_caricature', ['pageslug' => $item->slug]);
                                                break;
                                            default:
                                                $gi_url = App\Models\GalleryItem::getItemSingleUrlById($item->id);
                                        }
                                    @endphp
                                    <a href="{{ $gi_url }}">
                                        {{ $item->getTranslatedAttribute('name') }}
                                    </a>
                                </li>
                                @endif
                            @endforeach --}}
                        </ul>
                        @endisset
                        @if(isset($subcategories[$type->url]))
                        <ul>
                            @foreach ($subcategories[$type->url] as $subcategory)
                                <li>
                                    <a href="{{ route('sub_caricature', ['pageslug' => $subcategory->slug]) }}">
                                        {!! $subcategory->name !!}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                @endforeach
                @foreach ($static_types as $stype)
                <div class="sitemap-category">
                    <h3>
                        <a href="{{ $stype['url'] }}">
                            {{ trans('gl.type') }}: {{ $stype['name'] }}
                        </a>
                    </h3>
                </div>
                @endforeach
            </div>
        </div>

        <div class="sitemap-section">
            <h2>
                <a href="{{ route('blog') }}">{{ App\Models\Blog::first()->translate(App::getLocale())->title }}</a>
            </h2>
            <div class="sitemap-grid">
                @foreach ($blog_categories as $category)
                    <div class="sitemap-category">
                        <h3>{{ trans('gl.category') }}: {{ $category->getTranslatedAttribute('title') }}</h3>
                        <ul>
                            @foreach ($category->posts as $post)
                                <li>
                                    <a href="{{ route('blog_inner', $post['slug']) }}">
                                        {{ $post->getTranslatedAttribute('title') }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- @php
            $replace = '/' . app()->getLocale() . '/';
            if (app()->getLocale() == 'ru') {
                $replace = '/';
            }
            $top_menu = preg_replace('^/en/^', $replace, menu('header', 'layots.menu.default'));
        @endphp
        <ul class="sitemap-menu">
            {!! $top_menu !!}
        </ul>

        <br>
        @php
            $foot_menu = preg_replace('^/en/^', $replace, menu('footer', 'layots.menu.default'));
        @endphp
        <ul class="sitemap-menu">
            {!! $foot_menu !!}
        </ul> --}}

    </div>
@endsection
