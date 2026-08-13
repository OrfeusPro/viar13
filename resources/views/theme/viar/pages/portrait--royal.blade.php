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
        <meta itemprop="description"  content="{{$cleanName }}" >
    </div>

        @if($home_slides->count()>0)
            @include((config('theme.resource') ?: 'theme.viar.') . 'pages.portrait.breads')
            @include((config('theme.resource') ?: 'theme.viar.') . 'pages.portrait.slider--royal')
        @else
            <div class="portraits portrait__screen">
                <div class="portraits-wrap">
                    <div class="portraits-slider sl-slider">
                        <article class="portraits-slide">
                            <div class="section-frame">
                                @include((config('theme.resource') ?: 'theme.viar.') . 'pages.portrait.breads2')
                                @include((config('theme.resource') ?: 'theme.viar.') . 'pages.portrait.h_zero')
                            </div>
                        </article>
                    </div>
                </div>
            </div>
            <div class="ellipse">
                <img alt="img" src="{{ asset(env('THEME').'images/icon/ellipse-whete.svg') }}" decoding="async" height="99" width="1374" />
            </div>
        @endif

        @php

        $ba_items = $item->getMedia('new_before_items');
        $ba_items_after = $item->getMedia('new_after_items');
        $ba_items2 = $item->getMedia('works_examples_new');
        @endphp




        <section class="about__screen">
            <div class="section-frame section-m-frame">
                <h2 class="page-title h2_old">
                    @if ($h2_titles->zakaz_title_h2!='')
                        {!!   $h2_titles->zakaz_title_h2 !!}
                    @else
                        {{ trans('portrait.tabs_title') }}
                    @endif
                </h2>
                <div class="about__screen--wrapper">
                    <div class="about-tabs">

                        <div class="about-tab active">
                            <a href="#">{{ trans('portrait_royal.tab_picture_title') }}</a>
                        </div>
                        <div class="about-tab ">
                            <a href="#">{{ trans('portrait_royal.tab_two_types_title') }}</a>
                        </div>
                        <div class="about-tab">
                            <a href="#">{{ trans('portrait.tab2_title') }}</a>
                        </div>
                        <div class="about-tab ">
                            <a href="#">{{ trans('portrait.tab5_title') }}</a>
                        </div>
                    </div>
                </div>
                <div class="about__screen--blocks">
                    @include((config('theme.resource') ?: 'theme.viar.') . 'pages.portrait.tabs.picture',['is_hidden'=>false])
                    @include((config('theme.resource') ?: 'theme.viar.') . 'pages.portrait.tabs.two_types')
                    @include((config('theme.resource') ?: 'theme.viar.') . 'pages.portrait.tabs.second')
                    @include((config('theme.resource') ?: 'theme.viar.') . 'pages.portrait.tabs.fifth')
                </div>
            </div>
        </section>

        @include((config('theme.resource') ?: 'theme.viar.') . 'pages.portrait.style_example',['list'=>$list_example])

        @include((config('theme.resource') ?: 'theme.viar.') . 'pages.portrait.examples--large')
        {{--<div class="ellipse ellipse_black custom-ellipse">
            <img src="{{ asset(env('THEME').'images/icon/ellipse-black.svg') }}" alt="" loading="lazy" />
        </div>
        <div class="mobile-ell">
            <img src="{{ asset(env('THEME').'images/sizes/union.png') }}" alt="" />
            <div class="mobile-size-title">{{ trans('portrait.sizes__title') }}</div>
        </div>--}}
        @include((config('theme.resource') ?: 'theme.viar.') . 'pages.portrait.gift')

        @include((config('theme.resource') ?: 'theme.viar.') . 'pages.portrait.order_steps--small')
        @include((config('theme.resource') ?: 'theme.viar.') . 'pages.portrait.base_and_extra_services')
        @include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.item-card_part-about', ['info_block' => "hidden",'first_block' => "hidden"])
        @include((config('theme.resource') ?: 'theme.viar.') . 'pages.index.faq9')

        @include((config('theme.resource') ?: 'theme.viar.') . 'pages.portrait.popup-order')


        <script>
            (function(){
                function setPrice() {
                    let s_price = $(".popup-photo input[name='new_price']").val();
                    let c_price = $(".popup-photo input[name='new_people_count_price']").val();
                    if (!c_price) {
                        c_price = 0;
                    }
                    let o_price = parseFloat(s_price) + parseFloat(c_price);
                    $(".kviz-price span").text(o_price);
                    $(".popup-photo input[name='overall_price']").val(o_price);
                }

                window.quickOrderRoyal =  function (e){
                    var _id = '{{$item->id}}';
                    let size = $(e.currentTarget).data("size");
                    let gift = $(e.currentTarget).data('gift');

                    $(".popup-photo .select-style option[data-catid='" + _id + "']").prop(
                        "selected",
                        "selected"
                    );
                    $(`.select-size`).css("display", "none");
                    $(`.select-size[data-catid='${_id}']`).css("display", "block");
                    $(".popup-photo input[name='new_catid']").val(_id);

                    if($(".popup-photo input[name='has_gift']").length == 0){
                        $(".popup-photo").append($("<input type='hidden' name='has_gift' value='"+gift+"'/>"));
                    }else{
                        $(".popup-photo input[name='has_gift']").val(gift);
                    }
                    if (size) {
                        size = size.substring(0, size.length - 2);
                        size = size.trim();
                        size = size.split("х");
                        $(`.select-size[data-catid='${_id}']`)
                            .find("option[value='" + size[0] + "x" + size[1] + "']")
                            .prop("selected", "selected");
                        let price = $(`.select-size[data-catid='${_id}']`)
                            .find("option:selected")
                            .data("price");
                        $(".popup-photo input[name='new_size']").val(size[0] + "x" + size[1]);
                        $(".popup-photo input[name='new_price']").val(price);
                        setPrice();
                    } else {
                        size = $(`.select-size[data-catid='${_id}']`)
                            .find("option:selected")
                            .val();
                        let price = $(`.select-size[data-catid='${_id}']`)
                            .find("option:selected")
                            .data("price");

                        $(".popup-photo input[name='new_size']").val(size);
                        $(".popup-photo input[name='new_price']").val(price);
                        setPrice();
                    }

                    if ($(`.kviz-popup--portrait select[data-catid='${_id}']`).length > 0) {
                        $(".popup-photo").addClass("portrait-popup");
                        $(`.kviz-popup--portrait select`).css("display", "none");
                        $(`.kviz-popup--portrait select[data-catid='${_id}']`).css(
                            "display",
                            "block"
                        );
                        let count = $(
                            `.kviz-popup--portrait select[data-catid='${_id}'] option:selected`
                        ).val();
                        let count_price = $(
                            `.kviz-popup--portrait select[data-catid='${_id}'] option:selected`
                        ).data("count-price");
                        $(".popup-photo input[name='new_people_count']").val(count);
                        $(".popup-photo input[name='new_people_count_price']").val(count_price);
                        setPrice();
                    } else {
                        $(".popup-photo").removeClass("portrait-popup");
                        $(`.kviz-popup--portrait select`).css("display", "none");
                        $(".popup-photo input[name='new_people_count']").val(null);
                        $(".popup-photo input[name='new_people_count_price']").val(null);
                        setPrice();
                    }


                    e.preventDefault();
                    $(".popup-frame").css("display", "flex").hide().fadeIn();
                    $(".popup-photo").delay(500).fadeIn();
                }

                document.addEventListener("DOMContentLoaded", function (){
                    $('.js-fast_order--royal').on('click', window.quickOrderRoyal);
                });
            })();


        </script>
</div>
@include(  'partials.schema_reviews')
