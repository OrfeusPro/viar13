@extends('layots.common')
@section('title', $page->meta_title)

@section('og_tags')
    <meta property="og:title" content="{{ $page->meta_title }}" />
@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/partnership.css') }}" />
    <style>
        h3:after {
            content: '';
            z-index: -1 !important;
        }
    </style>
@endsection

@section('content')
    <div class="bread-crumbs">
        <i class="icon-icon3"></i>
        <ul vocab="https://schema.org/" typeof="BreadcrumbList">
            <li property="itemListElement" typeof="ListItem" class="breadcrumb-item">
                <a property="itemListElement" typeof="WebPage" href="{{ url('/') }}">
                    <span property="name">@lang('account.index1')</span>
                </a>
                <meta property="position" content="1" />
            </li>

            <li property="itemListElement" typeof="ListItem" class="breadcrumb-item active">
                <span property="name">{{ $page->title }}</span>
            </li>
        </ul>
    </div>

    <div class="title">
        <h5>ViarStudia</h5>
        <h2><span>{{ $page->meta_title }}</span></h2>
        {!! $page->top_text !!}
    </div>

    <div class="partnership-content clearfix">
        {!! $page->partner_item_1 !!}
        {!! $page->partner_item_2 !!}

        <div class="partnership-items3">
            {!! $page->partner_item_3 !!}
            <div class="sale">
                <div>
                    <span>{!! $page->sale_from !!} </span> <img src="{{ asset('img/sale-img.png') }}" alt="" />
                    <h6>{!! $page->sale_after !!}</h6>
                </div>
                {!! $page->sale_bot_text !!}
            </div>
            <a href="{!! $page->sale_link !!}"><span> {!! $page->sale_link_title !!}</span></a>
        </div>
    </div>

@endsection
