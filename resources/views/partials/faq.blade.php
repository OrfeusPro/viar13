@extends('layots.common')

@section('title', $faq_desc['title'])

@section('styles')

    <link rel="stylesheet" type="text/css" href="{{ asset('css/faq.css') }}">  
    
@endsection

@section('content')

    <div class="bread-crumbs">
        <i class="icon-icon3"></i>

        <ul vocab="https://schema.org/" typeof="BreadcrumbList">
            <li property="itemListElement" typeof="ListItem">
              <a property="item" typeof="WebPage"
                  href="{{ url('/') }}">
                <span property="name">@lang('account.index1')</span></a>
              <meta property="position" content="1">
            </li>
            <li property="itemListElement" typeof="ListItem">
              <span property="name">{{ $faq_desc['title'] }}</span>
              <meta property="position" content="2">
            </li>
        </ul>

    </div>

    <section class="faq">
        <div class="title">
            <h2>{{ $faq_desc['title'] }}</h2>
            <h5>ViarStudia</h5>
            <p>{!! $faq_desc['desc'] !!}</p>
        </div>
        <div class="faq-content clearfix">
            <img src="{{ asset('img/faq-img1.png') }}" alt="" class="faq-img1">
            <div class="accordion" id="accordion">
                @foreach($faqs as $faq)
                <h3>{{ $loop->index + 1 }}. {!! $faq->question !!}</h3>
                <div>
                    <p>{!! $faq->answer !!}.</p>
                </div>
                @endforeach
            </div>
            <p style="text-align:center;">
                {!! $faq_desc['bot_text'] !!}
            </p>
            <img src="{{ asset('img/faq-img2.png') }}" alt="" class="faq-img2">
        </div>
    </section>

    <script src="{{ asset('js/jquery.mask.min.js') }}"></script>
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('js/faq.min.js') }}"></script>
@endsection
