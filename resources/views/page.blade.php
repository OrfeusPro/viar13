`@extends('layots.common')

@if ($page->meta_title != '')
    @section('title', $page->meta_title)
    @else
    @section('title', $page->title)
    @endif

    @section('meta_robots', $page->meta_robots)
    @section('meta_desc', $page->meta_description)

    @section('og_tags')
        @if ($page->meta_title != '')
            <meta property="og:title" content="{{ $page->meta_title }}" />
        @else
            <meta property="og:title" content="{{ $page->title }}" />
        @endif
        <meta property="og:description" content="{{ $page->meta_description }}" />
    @endsection

    @section('styles')

        @if (isset($template))

            @if ($template == 'contacts')
                <link rel="stylesheet" type="text/css" href="{{ ver_asset('css/contacts.css') }}" />
            @endif

            @if ($template == 'about')
                <link rel="stylesheet" type="text/css" href="{{ ver_asset('css/about.css') }}" />
            @endif

            @if ($template == 'terms-sale')
                <link rel="stylesheet" type="text/css" href="{{ ver_asset('css/terms-sale.css') }}" />
            @endif

            @if ($template == 'shipping-payment')
                <link rel="stylesheet" type="text/css" href="{{ ver_asset('css/shipping-payment.css') }}" />
            @endif

            @if ($template == 'partnership')
                <link rel="stylesheet" type="text/css" href="{{ ver_asset('css/partnership.css') }}" />
            @endif

        @endif

    @endsection

    @section('content')

        {{ Breadcrumbs::render('pages_add', $page) }}

        @if ($template == 'about')
            <section class="about">
                <div class="about-content clearfix">
                    <div class="about-text">
        @endif
        {!! render_content_images($page->content) !!}
        @if ($template == 'about')
            <img class="about-img2" src="{{ asset('images/about-img2.png') }}" alt="" /></div>
            <div class="about-logo">
                <div class="link modile"><img class="about-link" src="{{ asset('images/about-link.png') }}"
                        data-src="{{ asset('images/about-link.png') }}" alt="" />
                    <a href="{{ route('gallery.index') }}">{{ trans('gl.choose_pic') }}</a>
                </div>
                <img class="logo-about" src="{{ asset('images/logo-about.png') }}" alt="" />
                <div class="link desc">
                    <img class="about-link" src="{{ asset('images/about-link.png') }}"
                        data-src="{{ asset('images/about-link.png') }}" alt="" />
                    <a href="{{ route('gallery.index') }}"><span>{{ trans('gl.choose_pic') }}</span></a>
                </div>
            </div>
            </div>
            </section>
        @endif

        @if (isset($template))
            @if ($template == 'contacts')
                <div class="contacts-content" style="margin-bottom:150px;">
                    <div class="maps-content">
                        <div class="maps">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d8708.10140426991!2d24.1756302!3d56.9312323!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0xf7001249d1a2a994!2sViarCanvas!5e0!3m2!1sru!2sua!4v1601969320809!5m2!1sru!2sua"
                                width="600" height="450" frameborder="0" style="border:0;" allowfullscreen=""
                                aria-hidden="false" tabindex="0"></iframe>
                        </div>
                    </div>
                </div>
            @endif
        @endif

    @endsection
