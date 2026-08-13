@include((config('theme.resource') ?: 'theme.viar.') . 'pages.portrait.breads')
{{--Вставить сюда код микроразметки продуктов--}}
<div>
    @php
        $name=$home_slides[0]['title'];
        $strippedName = strip_tags($name);
        $cleanName = str_replace('"', '', $strippedName);
        $s_items = $item->getMedia('our_works_new');
    @endphp
    <meta itemprop="name" content="{{ $cleanName }}" />
    @if ($s_items)
        @foreach ($s_items as $image)
            <link itemprop="image" href="{{ $image->getUrl() }}">
        @endforeach
    @endif
    <div itemprop="offers" itemtype="https://schema.org/Offer" itemscope>
        <link itemprop="url" href="{{ url(Request::url()) }}" />
        <meta itemprop="availability" content="https://schema.org/InStock" />
        <meta itemprop="priceCurrency" content="EUR" />
        <meta itemprop="price" content="{{$item->price_from }}"/>
        @php
            $mt = Carbon\Carbon::now();
        @endphp
        <meta itemprop="description"  content="{{$cleanName }}" >
        <meta itemprop="priceValidUntil" content="{{ $mt->toDateTimeString() }}" />
    </div>
</div>
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
    /* .portrait-list__item img
    {
        height: 451px!important;
    } */
</style>

@include((config('theme.resource') ?: 'theme.viar.') . 'pages.sharj.slider-page')



@include((config('theme.resource') ?: 'theme.viar.') . 'pages.sharj.decoration')

<section class="black-section divider-container">
    <div class="section-frame">
        <div class="section-inner">
            @include((config('theme.resource') ?: 'theme.viar.') . 'pages.sharj.categories')

            {{-- @include((config('theme.resource') ?: 'theme.viar.') . 'pages.sharj.default-pattern') --}}
            @include((config('theme.resource') ?: 'theme.viar.') . 'pages.sharj.default-pattern-new-items')

            @include((config('theme.resource') ?: 'theme.viar.') . 'pages.sharj.form')


            <div class="steps-order mt-xl">
            @include((config('theme.resource') ?: 'theme.viar.') . 'pages.sharj.steps-order')
            </div>

{{--            @include((config('theme.resource') ?: 'theme.viar.') . 'pages.sharj.popup-order')--}}
            <div class="btn-container js-simps-calc">
                <a href="#" class="default-btn btn-xl">
                    <p>@lang("sharj.translate97")</p>
                    <span>@lang("sharj.translate98")</span>
                    <svg width="167" height="159" viewBox="0 0 167 159" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M166.375 155.329C166.558 155.122 166.537 154.806 166.33 154.624L162.95 151.653C162.743 151.471 162.427 151.491 162.244 151.698C162.062 151.906 162.082 152.222 162.29 152.404L165.294 155.045L162.654 158.049C162.471 158.257 162.492 158.572 162.699 158.755C162.906 158.937 163.222 158.917 163.405 158.709L166.375 155.329ZM2.99992 0.5C2.50832 0.408744 2.50815 0.409648 2.50787 0.411187C2.50762 0.412548 2.50722 0.414722 2.50672 0.417439C2.50572 0.422873 2.50427 0.430836 2.50237 0.441308C2.49858 0.462252 2.49301 0.493231 2.4858 0.534078C2.47138 0.615774 2.45037 0.736947 2.42375 0.896276C2.3705 1.21493 2.29481 1.68623 2.20448 2.29957C2.02382 3.52625 1.78461 5.32128 1.54944 7.6C1.07913 12.1572 0.624778 18.6505 0.687457 26.4023C0.812782 41.9021 3.00527 62.455 11.2874 82.6269C19.5727 102.806 33.9528 122.604 58.4398 136.566C82.9218 150.526 117.463 158.628 166.032 155.498L165.968 154.5C117.537 157.621 83.2032 149.535 58.9351 135.698C34.6722 121.863 20.4273 102.255 12.2125 82.2471C3.99466 62.2318 1.81212 41.8162 1.68742 26.3942C1.62509 18.6853 2.07698 12.2294 2.54416 7.70265C2.77773 5.43941 3.01509 3.65877 3.1938 2.44528C3.28316 1.83854 3.35785 1.37362 3.41008 1.06109C3.43619 0.904819 3.45668 0.786652 3.47058 0.707905C3.47753 0.668532 3.48283 0.639015 3.48636 0.619518C3.48813 0.60977 3.48945 0.602527 3.49032 0.597809C3.49075 0.59545 3.49105 0.593812 3.49127 0.59263C3.49145 0.591626 3.49152 0.591256 2.99992 0.5Z" fill="#FA7846"/>
                    </svg>
                </a>
            </div>


        </div>
    </div>
    <div class="custom-shape-divider-bottom-1685740923">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M600,112.77C268.63,112.77,0,65.52,0,7.23V120H1200V7.23C1200,65.52,931.37,112.77,600,112.77Z" class="shape-fill shape-fill-white"></path>
        </svg>
    </div>
</section>

@include((config('theme.resource') ?: 'theme.viar.') . 'pages.index.faq9')
        <div class="popup-wrapper">
            <div class="popup-item calculator-block image-calculator" style="display: block;">
                @include((config('theme.resource') ?: 'theme.viar.') . 'pages.sharj.popup-order', ['template_id' => 'sharj'])
            </div>
            <div class="popup-layout"></div>
        </div>

<div class="popup-wrapper-template">
    <div class="popup-item calculator-block image-calculator" style="display: block;">
        @include((config('theme.resource') ?: 'theme.viar.') . 'pages.sharj.popup-order', ['template_id' => 'obraz'])
    </div>
    <div class="popup-layout"></div>
</div>

@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.item-card_part-about')
<br>
@include(  'partials.schema_reviews')
