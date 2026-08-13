@extends('layots.common')

@section('title', $canvas_head['meta_title'])
@section('meta_desc', $canvas_head['meta_desc'])


@section('og_tags')
    <meta property="og:title" content="{{ $canvas_head['meta_title'] }}" />
    <meta property="og:description" content="{{ $canvas_head['meta_desc'] }}" />
    <meta property="og:image" content="{{ \URL::to('/') }}/img/logo.png" />
@endsection

@section('styles')
    <script src="{{ asset('js/InteriorGenerator.js') }}"></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/combine_canvas.css') }}">

@endsection

@section('content')
    @include('partials.canvas.header')
    @include('partials.canvas.tabs')
    @include('partials.canvas.generator')
    @include('partials.canvas.after_gen')

    <script src="{{ asset('js/three.min.js') }}"></script>
    <script src="{{ asset('js/3dcanvas.js') }}"></script>

    <script>
        window.onShowInterior = function() {
            return {
                size: {
                    h: size_centimeter_h,
                    w: size_centimeter_w
                },
                src: editor.out(editor.canvas.width, editor.canvas.height, false)
            };
        }

    </script>

    <script src="{{ asset('js/jcf.min.js') }}"></script>
    <script src="{{ asset('js/jcf.file.min.js') }}"></script>
    <script src="{{ asset('js/jcf.radio.min.js') }}"></script>
    <script src="{{ asset('js/jcf.checkbox.min.js') }}"></script>
    <script src="{{ asset('js/jcf.range.min.js') }}"></script>
    <script src="{{ asset('js/jquery.collapse_storage.min.js') }}"></script>
    <script src="{{ asset('js/jquery.collapse.min.js') }}"></script>
    <script src="{{ asset('js/slick.min.js') }}"></script>
    <script src="{{ asset('js/templates.js') }}"></script>
    <script src="{{ asset('js/canvas.min.js') }}"></script>
    <script src="{{ asset('js/interior.js') }}"></script>

    <style>
        .tabs-container .tabs-items .prices-sizes .prices-sizes-content .prices-cintant a::after,
        .canvas-banner .canvas-banner-content .canvas-tabs .tabs-content .order::after {
            content: '';
            background-size: 100% 100%;
        }

    </style>

    @include('partials.canvas.top_scripts')
    @include('partials.canvas.bot_scripts')

@endsection
