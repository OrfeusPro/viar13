@extends('layots.common')

@php
    $pageTitle = data_get($page, 'meta_title', trans('settings.site_name'));
    $pageDescription = data_get($page, 'meta_description', '');
    $pageContent = data_get($page, 'content', '');
    $aboutAreaServed = [
        ['@type' => 'Country', 'name' => trans('countries.lv')],
        ['@type' => 'Country', 'name' => trans('countries.lt')],
        ['@type' => 'Country', 'name' => trans('countries.es')],
        ['@type' => 'Country', 'name' => trans('countries.pl')],
        ['@type' => 'Country', 'name' => trans('countries.ge')],
        ['@type' => 'Country', 'name' => trans('countries.fl')],
        ['@type' => 'Country', 'name' => trans('countries.nl')],
    ];
    $aboutBrandSchema = site_brand_schema([
        'name' => $pageTitle,
        'url' => url('/'),
        'logo' => asset('img/icons/logo.svg'),
        'areaServed' => $aboutAreaServed,
        'contactPoint' => site_brand_contact_points([
            trans('header_footer_new.footer_phone_clean'),
        ], 'orders@viarcanvas.com', $aboutAreaServed),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => trans('header_footer_new.footer_org_streetAddress'),
            'addressLocality' => trans('header_footer_new.footer_org_addressLocality'),
            'addressCountry' => trans('header_footer_new.footer_org_addressCountry'),
            'postalCode' => trans('header_footer_new.footer_org_postalCode'),
        ],
    ]);
@endphp

@section('title', $pageTitle)
@section('meta_desc', $pageDescription)

@section('og_tags')
    <meta property="og:title" content="{{ $pageTitle }}"/>
    <meta property="og:description" content="{{ $pageDescription }}"/>
    <meta property="og:image" content="{{ URL::to('/') }}/img/logo.png"/>
    <script type="application/ld+json">
        {!! json_encode($aboutBrandSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/about.css') }}">
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
                <span property="name">{{ data_get($page, 'title', $pageTitle) }}</span>
                <meta property="position" content="2">
            </li>
        </ul>
    </div>

    <section class="about">
        <div class="about-content clearfix">
            <div class="about-text">
                {!! render_content_images($pageContent) !!}
                <img class="about-img2" src="{{ asset('images/about-img2.png') }}" alt=""/>
            </div>
            <div class="about-logo">
                <div class="link modile"><img class="about-link" src="{{ asset('images/about-link.png') }}"
                                              data-src="{{ asset('images/about-link.png') }}" alt=""/>
                    <a href="{{ route('gallery.index') }}">{{ trans('gl.choose_pic') }}</a>
                </div>
                <img class="logo-about" src="{{ asset('images/logo-about.png') }}" alt=""/>
                <div class="link desc">
                    <img class="about-link" src="{{ asset('images/about-link.png') }}"
                         data-src="{{ asset('images/about-link.png') }}" alt=""/>
                    <a href="{{ route('gallery.index') }}"><span>{{ trans('gl.choose_pic') }}</span></a>
                </div>
            </div>
        </div>
    </section>

    @include(config('theme.resource') . 'pages.index.faq9')

    <script src="{{ asset('js/about.min.js') }}"></script>
@endsection
