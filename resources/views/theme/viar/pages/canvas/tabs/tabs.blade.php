<section class="about__screen">
    <div class="section-frame section-m-frame">
        <div class="about__screen--inner">
            @if (Route::currentRouteName() == 'canvas')
                <h2 class="page-title">{{ trans('canvas.tabs_title') }}</h2>
            @else
                <h2 class="page-title">{{ trans('canvas.tabs_title') }}</h2>
            @endif
        </div>
        <div class="about__screen--wrapper">
            <div class="about-tabs">
                <div class="about-tab active">
                    <a href="#">{{ trans('portrait.tab1_title') }}</a>
                </div>
                <div class="about-tab ">
                    <a href="#">{{ trans('portrait.tab3_title') }}</a>
                </div>
                <div class="about-tab ">
                    <a href="#">{{ trans('portrait.tab4_title') }}</a>
                </div>
                <div class="about-tab">
                    <a href="#">{{ trans('portrait.tab5_title') }}</a>
                </div>
                <div class="about-tab">
                    <a href="#">{{ trans('portrait.tab2_title') }}</a>
                </div>
            </div>
        </div>
        <div class="about__screen--blocks">
            @include(env('THEME_RESOURCES') . 'pages.canvas.tabs.first')
            @include(env('THEME_RESOURCES') . 'pages.canvas.tabs.third')
            @include(env('THEME_RESOURCES') . 'pages.canvas.tabs.fourth')
            @include(env('THEME_RESOURCES') . 'pages.canvas.tabs.fifth')
            @include(env('THEME_RESOURCES') . 'pages.canvas.tabs.second')

        </div>
    </div>
</section>
