@extends('layots.common', ['is_noscripts' => 1])

@section('title', $item['name'])

@section('og_tags')
    <meta property="og:title" content="{{ $item['name'] }}" />
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/graphic-interior.css') }}">
@endsection

@section('content')

    @include('partials.graph_portrait_promo.header')
    @include('partials.graph_portrait_promo.tabs')

    @if ($is_granj != 1)
        @include('partials.graph_portrait_promo.before_after')
    @else
        @include('partials.graph_portrait_promo.granj')
    @endif

    @include('partials.graph_portrait_promo.steps')
    @include('partials.graph_portrait_promo.everyth')
    @include('partials.graph_portrait_promo.etc_styles')
    @include('partials.graph_portrait_promo.packs')
    @include('partials.graph_portrait_promo.our_work')

    {{-- <script src="{{ asset('js/jquery.min.js') }}"></script> --}}
    <script src="{{ asset('spinner/jm.spinner.js') }}"></script>
    <script src="{{ asset('js/jquery.mask.min.js') }}"></script>
    <script src="{{ asset('js/jquery.matchHeight.min.js') }}"></script>
    <script src="{{ asset('js/BeerSlider.min.js') }}"></script>
    <script src="{{ asset('js/BeerSlider.min.js') }}"></script>
    <script src="{{ asset('js/slick.min.js') }}"></script>
    <script src="{{ ver_asset('js/script.min.js') }}"></script>
    <script src="{{ asset('js/graphic-interior.min.js') }}"></script>

    <script>
        $(document).on('click', '.js_change_active_sl', function(e) {
            var bef = $(this).data('before');
            var aft = $(this).data('after')

            $('#slider').children('img').attr('src', bef);
            $('#slider').find('.beer-reveal').find('img').attr('src', aft);
        });

    </script>

@endsection
