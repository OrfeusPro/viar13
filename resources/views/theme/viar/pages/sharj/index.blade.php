@if(Route::getCurrentRoute()->getName() == "sub_caricature")
    @include(env('THEME_RESOURCES') . 'pages.sharj.breads')
@else
    @include(env('THEME_RESOURCES') . 'pages.portrait.breads')
@endif

<style>
    .kviz-radio span {
        /*display: flex;*/
        /*cursor: pointer;*/
        /*margin-bottom: 13px;*/
    }
    .divider-container .close-btn{
        display: none;
    }
    .js-simps-calc
    {
        cursor: pointer;
    }
    .sharjCategories-slider .page-title
    {
        margin-bottom: 40px;
    }
    .h-auto
    {
     height: auto!important;

    }
    .portrait-list__item img
    {
        height: 451px!important;
    }
</style>

@include(env('THEME_RESOURCES') . 'pages.sharj.slider')
@include(env('THEME_RESOURCES') . 'pages.sharj.tabs')


<section class="black-section">
    <div class="section-frame">
        <div class="section-inner">
            @include(env('THEME_RESOURCES') . 'pages.sharj.examples')
            @include(env('THEME_RESOURCES') . 'pages.sharj.form')
        </div>
    </div>
</section>

@include(env('THEME_RESOURCES') . 'pages.sharj.gift')
<section class="steps-order divider-container section-block">
    <div class="section-frame">
        <div class="section-inner">
            @include(env('THEME_RESOURCES') . 'pages.sharj.steps-order')
            @include(env('THEME_RESOURCES') . 'pages.sharj.popup-order', ['template_id' => 'sharj','main_page' => true])
        </div>
    </div>
    <div class="custom-shape-divider-bottom-1685740923">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M600,112.77C268.63,112.77,0,65.52,0,7.23V120H1200V7.23C1200,65.52,931.37,112.77,600,112.77Z" class="shape-fill shape-fill-white"></path>
        </svg>
    </div>
</section>
@include(env('THEME_RESOURCES') . 'pages.sharj.other-categories')
@include(env('THEME_RESOURCES') . 'pages.sharj.service-info')
{{--@include(env('THEME_RESOURCES') . 'pages.portrait.gift')--}}
{{--@include(env('THEME_RESOURCES') . 'pages.portrait.order_steps--small')--}}
{{--@include(env('THEME_RESOURCES') . 'pages.portrait.base_and_extra_services')--}}

@include(env('THEME_RESOURCES') . 'pages.gallery.item-card_part-about', ['info_block' => "hidden",'first_block' => "hidden"])

@include(env('THEME_RESOURCES') . 'pages.index.faq9')
        <div class="popup-wrapper">
            <div class="popup-item calculator-block image-calculator" style="display: block;">
                @include(env('THEME_RESOURCES') . 'pages.sharj.popup-order', ['template_id' => 'sharj'])
            </div>
            <div class="popup-layout"></div>
        </div>
<div class="popup-wrapper-template">
    <div class="popup-item calculator-block image-calculator" style="display: block;">
        @include(env('THEME_RESOURCES') . 'pages.sharj.popup-order', ['template_id' => 'obraz'])
    </div>
    <div class="popup-layout"></div>
</div>


