<section class="about__screen">
    <div class="section-frame section-m-frame">
        <div class="about__screen--inner">
            <div class="page-title h2_old">{{ trans('canvas.tabs_title') }}</div>
        </div>
        <div class="about__screen--wrapper">
            <div class="about-tabs">
                <div class="about-tab active">
                    <a href="#">{{ trans('portrait.tab5_title') }}</a>
                </div>
                <div class="about-tab">
                    <a href="#">{{ trans('portrait.tab1_title') }}</a>
                </div>
                <div class="about-tab">
                    <a href="#">{{ trans('portrait.tab2_title') }}</a>
                </div>
                <div class="about-tab ">
                    <a href="#">{{ trans('portrait.tab4_title') }}</a>
                </div>
            </div>
        </div>
        <div class="about__screen--blocks">
            @include('partials.canvas_new.tabs.fifth')
            @include('partials.canvas_new.tabs.first')
            @include('partials.canvas_new.tabs.second')
            @include('partials.canvas_new.tabs.fourth')

        </div>
    </div>
</section>
