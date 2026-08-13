@extends('layots.common')

@section('title', $data['meta_title'])

@section('styles')

<link rel="stylesheet" type="text/css" href="{{ asset('css/collage-constructor.css') }}"
    href="{{ asset('css/modular-pictures.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('css/collage-constructor-2.css') }}" />
<link rel="stylesheet" href="{{ asset('css/PhotoEditor.css') }}" />
<link rel="stylesheet" href="{{ asset('css/icons_tools1.css') }}">
<link rel="stylesheet" href="{{ asset('css/family-constructor2.css') }}">


<script src="{{ asset('js/three.min.js') }}"></script>
<script src="{{ asset('js/3dcanvas_fam.js') }}"></script>


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
          <span property="name">{{ $data['title'] }}</span>
          <meta property="position" content="2">
        </li>
    </ul>

</div>

{{-- main --}}
@include('partials.family.generator')

@include('partials.family.packs')
@include('partials.stages_everythin')
@include('partials.family.comp_pick')

<script>
    window.onShowInterior = function()
    {
        return{size:{h:size_centimeter_h,w:size_centimeter_w},src:editor.out(editor.canvas.width,editor.canvas.height,false)};
    }
    
    
</script>

{{-- <script src="{{ mix('js/family_combine.js') }}"></script> --}}

<script src="{{ asset('js/jquery3.min.js') }}"></script>
<script src="{{ asset('js/jquery.mask.min.js') }}"></script>
<script src="{{ asset('js/jquery.matchHeight.min.js') }}"></script>
<script src="{{ asset('js/jcf.min.js') }}"></script>
<script src="{{ asset('js/jcf.file.min.js') }}"></script>
<script src="{{ asset('js/jcf.radio.min.js') }}"></script>
<script src="{{ asset('js/jcf.checkbox.min.js') }}"></script>
<script src="{{ asset('js/jcf.range.min.js') }}"></script>
<script src="{{ asset('js/jquery.collapse_storage.min.js') }}"></script>
<script src="{{ asset('js/jquery.collapse.min.js') }}"></script>
<script src="{{ asset('js/slick.min.js') }}"></script>
<script src="{{ asset('js/modular-pictures.min.js') }}"></script>
<script src="{{ asset('js/templates.js') }}"></script>
<script src="{{ asset('js/collage-templates.js') }}"></script>
<script src="{{ asset('js/interior.js') }}"></script>


<script>
    $(document).on('click', '.delete', function(){
        $(this).prev().attr('src', '').removeAttr('style');
    });
    function previewFile() {
        files = document.getElementById('user_image').files;
        $('#user_image').removeAttr('style');   
        

        for (let index = 0; index < files.length; index++) {
            const el_f = files[index];
            var reader = new FileReader();

            var res = reader.readAsDataURL(el_f);

            reader.onload = function (e) {
                var res = e.target.result;
                $('.pd-graph').append(`
                    <div class="download">
                        <div class="img img__down" style="background-image: url();background-size: cover;">
                            <img src="${res}" alt="" class="pd__fix">
                        </div>
                    </div>
                `);
                if(index == 0){
                    $('#tab1__generator').children().remove();
                    $('#tab1__generator').append("<img src='"+res+"'>");
                }
            }
        }

    }

</script>

@include('partials.family.devs')

<style>
    h2.js_page_name {
        position: relative;
        z-index: 2;
    }
        .generate .generate-content .title:after {
        content: '';
        top: 50%;
        left: 50%;
        z-index: 0;
        width: 300px;
        height: 50px;
        position: absolute;
        background: url(../img/generate-title.png) no-repeat 50% 50%;
        background-size: cover;
        -webkit-transform: translate(-50%, -50%);
        transform: translate(-50%, -50%);
    }
    @media screen and (min-width: 768px){
        .generate .generate-content .title::after {
            width: 400px;
            height: 68px;
        }
    }
    @media screen and (min-width: 1280px){
    .generate .generate-content .title::after {
            width: 636px;
            height: 109px;
        }
    }
    .generate .generate-content .title{
        position: relative;
    }
    .size-item input {
        width: 85px;
        height: 25px;
        padding: 0 5px;
        color: #313131;
        font-size: 15px;
        font-weight: 500;
        line-height: 20px;
        text-align: center;
        border-radius: 5px;
        border: 1px solid #8c535b;
        background-color: #ffffff;
    }

    .tab2-item-child {
        width: 100%;
        height: 400px;
    }

    .range__box {
        display: none;
    }

    @media screen and (min-width: 1400px) {
        .generate .generate-content .gallery-photo .photo-content .slider-tabs .tabs-content {
            padding: 40px 20px;
        }
    }

    .int-draggable img {
        max-width: 178px !important;
        max-height: 356px !important;
    }

    .product-download {
        max-height: 192px;
    }
</style>



@endsection