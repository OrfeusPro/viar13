<style>
    .ellipse {
        margin-top: -100px;
    }
    
    @media screen and (min-width: 1620px){
        .portraits-slider .pmain__screen {
            min-height: 800px;
        }
    }
    @media screen and (min-width: 1920px){
        .portraits-slider .pmain__screen {
            min-height: 1000px;
        }
    }
</style>

@include((config('theme.resource') ?: 'theme.viar.') . 'pages.canvas.breads')
    @include((config('theme.resource') ?: 'theme.viar.') . 'pages.canvas.slider')

    <div>
        @php
            $name=$home_slides[0]['title'];
            $strippedName = strip_tags($name);
            $cleanName = str_replace('"', '', $strippedName);
            $s_items = $item->getMedia('our_works_new');
        @endphp

    <section class="about__screen about__canvas">
        @include((config('theme.resource') ?: 'theme.viar.') . 'pages.canvas.tabs.tabs')
    </section>
    <div class="retouch__screen p-section">
        <div class="section-frame">
            <div class="retouch__inner">
                @include((config('theme.resource') ?: 'theme.viar.') . 'pages.canvas.photo_work')
                @include((config('theme.resource') ?: 'theme.viar.') . 'pages.canvas.form')
            </div>
        </div>
        {{-- <div class="form-bg">
            <img src="{{ asset(env('THEME').'images/canvas/Union.svg') }}" alt="">
        </div> --}}
    </div>
    <div class="ellipse ellipse_black custom-ellipse lgTop">
        <img src="{{ asset(env('THEME').'images/icon/ellipse-black.svg') }}" alt="img" decoding="async" height="99" width="1374">
    </div>
    {{-- @include((config('theme.resource') ?: 'theme.viar.') . 'pages.canvas.sizes') --}}

    <div class="mobile-ell">
        <img src="{{ asset('images/sizes/union.png') }}" alt=""/>
    </div>
    <div style="background: #FBF2EA;height: 60px;margin-top: -60px;"></div>
    @include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.item-card_part-about')


    <div class="ellipse ellipse_black ellipse_top">
        <a href="#formalizaton" class="anchor ellipse-arrow ellipse-arrow_white" aria-label="anchor link">
            <i class="fa-arrow-down"></i>
        </a>
        <img src="{{ asset(env('THEME').'images/icon/ellipse-black.svg') }}" alt="img" decoding="async" height="99" width="1374">
    </div>
        @include((config('theme.resource') ?: 'theme.viar.') . 'pages.canvas.steps')

    <div class="ellipse ellipse_black">
        <img src="{{ asset(env('THEME').'images/icon/ellipse-black.svg') }}" alt="img" loading="lazy">
    </div>
    @include((config('theme.resource') ?: 'theme.viar.') . 'pages.canvas.also_like_examples')
    </div>

    @include(config('theme.resource') . 'partials.google_reviews_section')
    @include(config('theme.resource') . 'pages.index.faq9')
