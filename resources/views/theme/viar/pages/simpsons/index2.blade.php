@include(env('THEME_RESOURCES') . 'pages.portrait.breads')
<style>
    .kviz-radio span {
        display: flex;
        cursor: pointer;
        margin-bottom: 13px;
    }
    .breadcrumbs__link
    {
        color: white;
    }
</style>
@if($home_slides)
    @foreach($home_slides as $slide)
<div class="portrait-simpson__screen divider-container">
    <div class="section-frame">
        <div class="section-inner">
            <div class="simpson-screen">
                <div class="content">
                    <div class="title">
                        @lang("simpson.simpsons.slider.title")

                    </div>
                    <ul>
                        <li>
                            @lang("simpson.simpson_screen.content.text1")
                        </li>
                        <li>
                            @lang("simpson.simpson_screen.content.text2")
                        </li>
                        <li>
                            @lang("simpson.simpson_screen.content.text3")
                        </li>
                    </ul>

                    <a href="#" class="yellow-btn js-simps-calc">
                        @lang("simpson.yellow_btn")
                    </a>

                </div>
                <div class="img">
                    @php
                        $simpsonsTwoHeroPngSources = image_picture_sources(data_get($slide, 'png'), true);
                    @endphp
                    <picture>
                        @if(!empty($simpsonsTwoHeroPngSources['src_webp']))
                            <source srcset="{{ $simpsonsTwoHeroPngSources['src_webp'] }}" type="image/webp">
                        @endif
                        @if(!empty($simpsonsTwoHeroPngSources['src']) && !empty($simpsonsTwoHeroPngSources['type']))
                            <source srcset="{{ $simpsonsTwoHeroPngSources['src'] }}" type="{{ $simpsonsTwoHeroPngSources['type'] }}">
                        @endif
                        <img width="536" height="620" src="{{ $simpsonsTwoHeroPngSources['src'] }}" alt="">
                    </picture>
                    <div class="img-badge">
                        <div class="text">@lang("simpson.img_badge")</div>
                        <img width="71" height="78" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/i1.webp') }}" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    @endif
    <div class="custom-shape-divider-bottom-1686133295">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M600,112.77C268.63,112.77,0,65.52,0,7.23V120H1200V7.23C1200,65.52,931.37,112.77,600,112.77Z" class="shape-fill"></path>
        </svg>
    </div>
</div>





<section class="about__screen about__screen-simpson" id="about">
    <div class="section-frame section-m-frame">
        <div class="about__screen--wrapper">
            <div class="about-tabs">
                <div class="about-tab active">

                    <a href="#">
                        @lang("simpson.about_tabs.about_tab1")
                    </a>

                </div>

                <div class="about-tab">

                    <a href="#">
                        @lang("simpson.about_tabs.about_tab2")
                    </a>
                </div>

                <div class="about-tab">

                    <a href="#">
                        @lang("simpson.about_tabs.about_tab3")
                    </a>

                </div>

                <div class="about-tab">


                    <a href="#">
                        @lang("simpson.about_tabs.about_tab4")
                    </a>

                </div>
            </div>
        </div>
        <div class="about__screen--blocks">
            <div class="about__block">
                <div class="simpson-image">
                    <div class="simpson-title">
                        @lang("simpson.about__screen__blocks.simpson_title")
                    </div>
                    <div class="simpson-image-text">
                        @lang("simpson.about__screen__blocks.simpson_image_text")
                        <img src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/arr-bottom.svg') }}" width="80" height="80" alt="">
                    </div>
                    <div class="portrait-list__inner">

                        <div class="portrait-list__item">

                            @lang("simpson.portrait_list__item1")

                            <div class="portrait-list__img">
                                <picture>
                                    <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p1Min.webp') }}" type="image/webp">
                                    <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p1.webp') }}" type="image/webp">
                                    <img width="315" height="244" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p1.webp') }}" alt="">
                                </picture>

                                <a href="#" class="yellow-btn">
                                    @lang("gallery.trybuy_btn")
                                </a>

                            </div>
                        </div>

                        <div class="portrait-list__item">

                            @lang("simpson.portrait_list__item2")

                            <div class="portrait-list__img">
                                <picture>
                                    <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p2Min.webp') }}" type="image/webp">
                                    <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p2.webp') }}" type="image/webp">
                                    <img width="315" height="244" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p2.webp') }}" alt="">
                                </picture>

                                <a href="#" class="yellow-btn">
                                    @lang("gallery.trybuy_btn")
                                </a>

                            </div>
                        </div>


                        <div class="portrait-list__item">

                            @lang("simpson.portrait_list__item3")

                            <div class="portrait-list__img">
                                <picture>
                                    <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p3Min.webp') }}" type="image/webp">
                                    <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p3.webp') }}" type="image/webp">
                                    <img width="315" height="244" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p3.webp') }}" alt="">
                                </picture>
                                <a href="#" class="yellow-btn">
                                    @lang("gallery.trybuy_btn")
                                </a>
                            </div>
                        </div>

                        <div class="portrait-list__item">

                            @lang("simpson.portrait_list__item4")

                            <div class="portrait-list__img">
                                <picture>
                                    <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p4Min.webp') }}" type="image/webp">
                                    <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p4.webp') }}" type="image/webp">
                                    <img width="315" height="244" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p4.webp') }}" alt="">
                                </picture>

                                <a href="#" class="yellow-btn">
                                    @lang("gallery.trybuy_btn")
                                </a>

                            </div>
                        </div>

                        <div class="portrait-list__item">

                            @lang("simpson.portrait_list__item5")

                            <div class="portrait-list__img">
                                <picture>
                                    <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p5Min.webp') }}" type="image/webp">
                                    <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p5.webp') }}" type="image/webp">
                                    <img width="315" height="244" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p5.webp') }}" alt="">
                                </picture>

                                <a href="#" class="yellow-btn">
                                    @lang("gallery.trybuy_btn")
                                </a>

                            </div>
                        </div>


                        <div class="portrait-list__item">

                            @lang("simpson.portrait_list__item6")

                            <div class="portrait-list__img">
                                <picture>
                                    <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p6Min.webp') }}" type="image/webp">
                                    <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p6.webp') }}" type="image/webp">
                                    <img width="315" height="244" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p6.webp') }}" alt="">
                                </picture>

                                <a href="#" class="yellow-btn">
                                    @lang("gallery.trybuy_btn")
                                </a>

                            </div>
                        </div>


                        <div class="portrait-list__item">

                            @lang("simpson.portrait_list__item7")

                            <div class="portrait_list__img">
                                <picture>
                                    <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p7Min.webp') }}" type="image/webp">
                                    <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p7.webp') }}" type="image/webp">
                                    <img width="315" height="244" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p7.webp') }}" alt="">
                                </picture>
                                <a href="#" class="yellow-btn">
                                    @lang("gallery.trybuy_btn")
                                </a>

                            </div>
                        </div>


                        <div class="portrait-list__item">

                            @lang("simpson.portrait_list__item8")

                            <div class="portrait-list__img">
                                <picture>
                                    <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p8Min.webp') }}" type="image/webp">
                                    <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p8.webp') }}" type="image/webp">
                                    <img width="315" height="244" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p8.webp') }}" alt="">
                                </picture>
                                <a href="#" class="yellow-btn">
                                    @lang("gallery.trybuy_btn")
                                </a>
                            </div>
                        </div>


                    </div>


                    <div class="portrait-list__pagination">
                        <div class="pagination-button prev" disabled="">
                            <svg width="10" height="16" viewBox="0 0 10 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9.23549 14.6145C9.33594 14.5141 9.38616 14.3986 9.38616 14.268C9.38616 14.1374 9.33594 14.0219 9.23549 13.9215L3.31417 8.00014L9.23549 2.07882C9.33594 1.97838 9.38616 1.86286 9.38616 1.73228C9.38616 1.6017 9.33594 1.48619 9.23549 1.38574L8.48214 0.632393C8.3817 0.531947 8.26618 0.481724 8.1356 0.481724C8.00502 0.481724 7.88951 0.531947 7.78906 0.632393L0.767857 7.6536C0.667411 7.75405 0.617188 7.86956 0.617188 8.00014C0.617188 8.13072 0.667411 8.24623 0.767857 8.34668L7.78906 15.3679C7.88951 15.4683 8.00502 15.5186 8.1356 15.5186C8.26618 15.5186 8.3817 15.4683 8.48214 15.3679L9.23549 14.6145Z" fill="#FC8C5F"></path>
                            </svg>
                        </div>
                        <div class="pagination-button next">
                            <svg width="10" height="16" viewBox="0 0 10 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0.764508 14.6145C0.664062 14.5141 0.613838 14.3986 0.613838 14.268C0.613838 14.1374 0.664062 14.0219 0.764508 13.9215L6.68583 8.00014L0.764508 2.07882C0.664062 1.97838 0.613838 1.86286 0.613838 1.73228C0.613838 1.6017 0.664062 1.48619 0.764508 1.38574L1.51786 0.632393C1.6183 0.531947 1.73382 0.481724 1.8644 0.481724C1.99498 0.481724 2.11049 0.531947 2.21094 0.632393L9.23214 7.6536C9.33259 7.75405 9.38281 7.86956 9.38281 8.00014C9.38281 8.13072 9.33259 8.24623 9.23214 8.34668L2.21094 15.3679C2.11049 15.4683 1.99498 15.5186 1.8644 15.5186C1.73382 15.5186 1.6183 15.4683 1.51786 15.3679L0.764508 14.6145Z" fill="white"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="about__block hidden-block">
                <div class="about__block-types">


                    <h2 class="page-title">
                        @lang("simpson.about__block2.text1")
                    </h2>

                    @lang("simpson.about__block2.text2")


                    <div class="about__block-types-row">
                        <div class="img">
                            <div class="flex">
                                <img width="40" height="40" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/i1.svg') }}" alt="">

                                @lang("simpson.about__block_types_row.img1.flex.text1")

                                <svg width="55" height="40" viewBox="0 0 55 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M49.6863 39.3056C49.8551 39.5241 50.1691 39.5644 50.3876 39.3956L53.9488 36.6446C54.1673 36.4758 54.2076 36.1617 54.0388 35.9432C53.87 35.7247 53.556 35.6844 53.3374 35.8532L50.172 38.2986L47.7266 35.1331C47.5578 34.9146 47.2438 34.8743 47.0252 35.0431C46.8067 35.2119 46.7664 35.5259 46.9352 35.7445L49.6863 39.3056ZM50.082 38.9999C50.5779 39.0636 50.578 39.0631 50.578 39.0625C50.5781 39.0621 50.5782 39.0613 50.5783 39.0605C50.5785 39.0589 50.5788 39.0567 50.5791 39.0539C50.5798 39.0484 50.5807 39.0406 50.5819 39.0306C50.5843 39.0104 50.5875 38.9813 50.5915 38.9435C50.5995 38.8678 50.6103 38.7572 50.6218 38.6141C50.645 38.3278 50.6714 37.911 50.6854 37.3823C50.7135 36.3251 50.6921 34.8188 50.4953 33.0118C50.1019 29.3995 49.0063 24.5733 46.193 19.7312C40.5446 10.0089 28.0619 0.369221 0.908681 0.175397L0.901546 1.17537C27.7942 1.36734 39.8999 10.8899 45.3284 20.2335C48.0537 24.9243 49.1186 29.6068 49.5012 33.1201C49.6924 34.8759 49.7129 36.3366 49.6858 37.3557C49.6723 37.8652 49.6469 38.2639 49.6251 38.5335C49.6142 38.6683 49.6042 38.7708 49.597 38.8387C49.5935 38.8727 49.5906 38.898 49.5887 38.9143C49.5877 38.9225 49.587 38.9284 49.5866 38.9321C49.5863 38.934 49.5862 38.9352 49.5861 38.9359C49.586 38.9363 49.586 38.9364 49.586 38.9365C49.586 38.9365 49.586 38.9363 50.082 38.9999Z" fill="#FA7846"/>
                                </svg>
                            </div>
                            <picture>
                                <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/a4Min.webp') }}" type="image/webp">
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/a4.webp') }}" type="image/webp">
                                <img width="450" height="360" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/a4.webp') }}" alt="">
                            </picture>
                        </div>
                        <ul>
                            <li>
                                <img width="65" height="65" src="{{ asset(config('theme.current') . '/images/sharj/new/page/ai1.webp') }}" alt="">

                                @lang("simpson.about__block_types_row.ul.li1")

                            </li>
                            <li>
                                <img width="65" height="65" src="{{ asset(config('theme.current') . '/images/sharj/new/page/ai2.webp') }}" alt="">

                                @lang("simpson.about__block_types_row.ul.li2")

                            </li>

                            <li>
                                <img width="65" height="65" src="{{ asset(config('theme.current') . '/images/sharj/new/page/ai3.webp') }}" alt="">

                                @lang("simpson.about__block_types_row.ul.li3")

                            </li>

                        </ul>
                        <div class="img">
                            <div class="flex">
                                <img width="40" height="40" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/i2.svg') }}" alt="">

                                @lang("simpson.about__block_types_row.img2.flex.text1")


                                <svg width="55" height="40" viewBox="0 0 55 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M49.6863 39.3056C49.8551 39.5241 50.1691 39.5644 50.3876 39.3956L53.9488 36.6446C54.1673 36.4758 54.2076 36.1617 54.0388 35.9432C53.87 35.7247 53.556 35.6844 53.3374 35.8532L50.172 38.2986L47.7266 35.1331C47.5578 34.9146 47.2438 34.8743 47.0252 35.0431C46.8067 35.2119 46.7664 35.5259 46.9352 35.7445L49.6863 39.3056ZM50.082 38.9999C50.5779 39.0636 50.578 39.0631 50.578 39.0625C50.5781 39.0621 50.5782 39.0613 50.5783 39.0605C50.5785 39.0589 50.5788 39.0567 50.5791 39.0539C50.5798 39.0484 50.5807 39.0406 50.5819 39.0306C50.5843 39.0104 50.5875 38.9813 50.5915 38.9435C50.5995 38.8678 50.6103 38.7572 50.6218 38.6141C50.645 38.3278 50.6714 37.911 50.6854 37.3823C50.7135 36.3251 50.6921 34.8188 50.4953 33.0118C50.1019 29.3995 49.0063 24.5733 46.193 19.7312C40.5446 10.0089 28.0619 0.369221 0.908681 0.175397L0.901546 1.17537C27.7942 1.36734 39.8999 10.8899 45.3284 20.2335C48.0537 24.9243 49.1186 29.6068 49.5012 33.1201C49.6924 34.8759 49.7129 36.3366 49.6858 37.3557C49.6723 37.8652 49.6469 38.2639 49.6251 38.5335C49.6142 38.6683 49.6042 38.7708 49.597 38.8387C49.5935 38.8727 49.5906 38.898 49.5887 38.9143C49.5877 38.9225 49.587 38.9284 49.5866 38.9321C49.5863 38.934 49.5862 38.9352 49.5861 38.9359C49.586 38.9363 49.586 38.9364 49.586 38.9365C49.586 38.9365 49.586 38.9363 50.082 38.9999Z" fill="#FA7846"/>
                                </svg>
                            </div>
                            <picture>
                                <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/a1Min.webp') }}" type="image/webp">
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/a1.webp') }}" type="image/webp">
                                <img width="450" height="360" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/a1.webp') }}" alt="">
                            </picture>
                        </div>
                    </div>

                    <div class="page-title center-title">
                        @lang("simpson.about__screen__blocks.page_title.center_title")
                    </div>

                    <div class="about__block-types-row">
                        <div class="img">
                            <div class="flex mb-2">
                                <img width="40" height="40" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/i3.svg') }}" alt="">

                                @lang("simpson.about__screen__blocks.about__block_types_row2.img.flex")


                                <svg width="55" height="40" viewBox="0 0 55 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M49.6863 39.3056C49.8551 39.5241 50.1691 39.5644 50.3876 39.3956L53.9488 36.6446C54.1673 36.4758 54.2076 36.1617 54.0388 35.9432C53.87 35.7247 53.556 35.6844 53.3374 35.8532L50.172 38.2986L47.7266 35.1331C47.5578 34.9146 47.2438 34.8743 47.0252 35.0431C46.8067 35.2119 46.7664 35.5259 46.9352 35.7445L49.6863 39.3056ZM50.082 38.9999C50.5779 39.0636 50.578 39.0631 50.578 39.0625C50.5781 39.0621 50.5782 39.0613 50.5783 39.0605C50.5785 39.0589 50.5788 39.0567 50.5791 39.0539C50.5798 39.0484 50.5807 39.0406 50.5819 39.0306C50.5843 39.0104 50.5875 38.9813 50.5915 38.9435C50.5995 38.8678 50.6103 38.7572 50.6218 38.6141C50.645 38.3278 50.6714 37.911 50.6854 37.3823C50.7135 36.3251 50.6921 34.8188 50.4953 33.0118C50.1019 29.3995 49.0063 24.5733 46.193 19.7312C40.5446 10.0089 28.0619 0.369221 0.908681 0.175397L0.901546 1.17537C27.7942 1.36734 39.8999 10.8899 45.3284 20.2335C48.0537 24.9243 49.1186 29.6068 49.5012 33.1201C49.6924 34.8759 49.7129 36.3366 49.6858 37.3557C49.6723 37.8652 49.6469 38.2639 49.6251 38.5335C49.6142 38.6683 49.6042 38.7708 49.597 38.8387C49.5935 38.8727 49.5906 38.898 49.5887 38.9143C49.5877 38.9225 49.587 38.9284 49.5866 38.9321C49.5863 38.934 49.5862 38.9352 49.5861 38.9359C49.586 38.9363 49.586 38.9364 49.586 38.9365C49.586 38.9365 49.586 38.9363 50.082 38.9999Z" fill="#FA7846"/>
                                </svg>
                            </div>
                            <picture>
                                <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/a3Min.webp') }}" type="image/webp">
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/a3.webp') }}" type="image/webp">
                                <img width="450" height="360" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/a3.webp') }}" alt="">
                            </picture>
                        </div>
                        <ul>
                            <li>
                                <img width="65" height="65" src="{{ asset(config('theme.current') . '/images/sharj/new/page/ai4.webp') }}" alt="">

                                @lang("simpson.about__block_types_row2.ul.li1")


                            </li>
                            <li>
                                <img width="65" height="65" src="{{ asset(config('theme.current') . '/images/sharj/new/page/ai5.webp') }}" alt="">

                                @lang("simpson.about__block_types_row2.ul.li2")


                            </li>
                        </ul>
                        <div class="img">
                            <div class="flex">
                                <img width="40" height="40" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/i4.svg') }}" alt="">

                                @lang("simpson.about__block_types_row2.img.flex")


                                <svg width="55" height="40" viewBox="0 0 55 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M49.6863 39.3056C49.8551 39.5241 50.1691 39.5644 50.3876 39.3956L53.9488 36.6446C54.1673 36.4758 54.2076 36.1617 54.0388 35.9432C53.87 35.7247 53.556 35.6844 53.3374 35.8532L50.172 38.2986L47.7266 35.1331C47.5578 34.9146 47.2438 34.8743 47.0252 35.0431C46.8067 35.2119 46.7664 35.5259 46.9352 35.7445L49.6863 39.3056ZM50.082 38.9999C50.5779 39.0636 50.578 39.0631 50.578 39.0625C50.5781 39.0621 50.5782 39.0613 50.5783 39.0605C50.5785 39.0589 50.5788 39.0567 50.5791 39.0539C50.5798 39.0484 50.5807 39.0406 50.5819 39.0306C50.5843 39.0104 50.5875 38.9813 50.5915 38.9435C50.5995 38.8678 50.6103 38.7572 50.6218 38.6141C50.645 38.3278 50.6714 37.911 50.6854 37.3823C50.7135 36.3251 50.6921 34.8188 50.4953 33.0118C50.1019 29.3995 49.0063 24.5733 46.193 19.7312C40.5446 10.0089 28.0619 0.369221 0.908681 0.175397L0.901546 1.17537C27.7942 1.36734 39.8999 10.8899 45.3284 20.2335C48.0537 24.9243 49.1186 29.6068 49.5012 33.1201C49.6924 34.8759 49.7129 36.3366 49.6858 37.3557C49.6723 37.8652 49.6469 38.2639 49.6251 38.5335C49.6142 38.6683 49.6042 38.7708 49.597 38.8387C49.5935 38.8727 49.5906 38.898 49.5887 38.9143C49.5877 38.9225 49.587 38.9284 49.5866 38.9321C49.5863 38.934 49.5862 38.9352 49.5861 38.9359C49.586 38.9363 49.586 38.9364 49.586 38.9365C49.586 38.9365 49.586 38.9363 50.082 38.9999Z" fill="#FA7846"/>
                                </svg>
                            </div>
                            <picture>
                                <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/a2Min.webp') }}" type="image/webp">
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/a2.webp') }}" type="image/webp">
                                <img width="450" height="360" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/a2.webp') }}" alt="">
                            </picture>
                        </div>
                    </div>
                </div>
            </div>
            <div class="about__block about__second hidden-block brief-v">
                <div class="about-fit--block">
                    <div class="fit-block--inner">
                        <div class="fit-icon">
                            <svg
                                width="53"
                                height="52"
                                viewBox="0 0 53 52"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M51.5 26C51.5 39.8071 40.3071 51 26.5 51C12.6929 51 1.5 39.8071 1.5 26C1.5 12.1929 12.6929 1 26.5 1C40.3071 1 51.5 12.1929 51.5 26Z"
                                    stroke="#FA7846"
                                    stroke-width="2"
                                />
                                <path
                                    d="M24.4258 29.0752H26.8484V28.8578C26.8612 27.6114 27.3086 27.0297 28.3185 26.4225C29.5138 25.7129 30.2937 24.7733 30.2937 23.2712C30.2937 21.034 28.4911 19.73 25.9535 19.73C23.6332 19.73 21.7411 20.9445 21.6836 23.5013H24.2915C24.3299 22.4594 25.1033 21.9033 25.9407 21.9033C26.8036 21.9033 27.5004 22.4786 27.5004 23.3671C27.5004 24.2044 26.8931 24.7605 26.1069 25.2591C25.033 25.9367 24.4322 26.6206 24.4258 28.8578V29.0752ZM25.685 33.1661C26.5032 33.1661 27.2127 32.4821 27.2191 31.632C27.2127 30.7946 26.5032 30.1107 25.685 30.1107C24.8413 30.1107 24.1445 30.7946 24.1509 31.632C24.1445 32.4821 24.8413 33.1661 25.685 33.1661Z"
                                    fill="#FA7846"
                                />
                            </svg>
                        </div>
                        <div class="fit-content">

                            <div class="fit-title">
                                @lang("simpson.about__screen__blocks.fit_block__inner.fit_content.fit_title")
                            </div>

                            <div class="fit-text">
                                @lang("simpson.about__screen__blocks.fit_block__inner.fit_content.fit_text")

                            </div>
                        </div>
                    </div>
                    <div class="fit-image--bg">
                        <picture>
                            <source srcset="{{ asset(config('theme.current') . '/images/about-m1.webp') }}" type="image/webp" />
                            <source srcset="{{ asset(config('theme.current') . '/images/about-m1.png') }}" type="image/png" />
                            <img width="100" height="100" src="{{ asset(config('theme.current') . '/images/about-m1.png') }}" alt="" />
                        </picture>
                    </div>
                </div>
                <div class="about-offers">

                    <div class="about-offer">
                        <div class="offer-text">

                            @lang("simpson.about_offers.about_offer1")

                        </div>

                        <div class="offer-img">
                            <picture>
                                <source media="(max-width: 500px)" srcset="{{ asset(config('theme.current') . '/images/fit-1min.webp') }}" type="image/webp" />
                                <source media="(max-width: 500px)" srcset="{{ asset(config('theme.current') . '/images/fit-1min.jpg') }}" type="image/jpg" />
                                <source srcset="{{ asset(config('theme.current') . '/images/fit-1.webp') }}" type="image/webp" />
                                <source srcset="{{ asset(config('theme.current') . '/images/fit-1.jpg') }}" type="image/jpg" />
                                <img width="100" height="100" src="{{ asset(config('theme.current') . '/images/fit-1.jpg') }}" alt="" />
                            </picture>
                        </div>
                    </div>


                    <div class="about-offer">
                        <div class="offer-text">

                            @lang("simpson.about_offers.about_offer2")


                        </div>
                        <div class="offer-img">
                            <picture>
                                <source media="(max-width: 500px)" srcset="{{ asset(config('theme.current') . '/images/fit-2min.webp') }}" type="image/webp" />
                                <source media="(max-width: 500px)" srcset="{{ asset(config('theme.current') . '/images/fit-2min.jpg') }}" type="image/jpg" />
                                <source srcset="{{ asset(config('theme.current') . '/images/fit-2.webp') }}" type="image/webp" />
                                <source srcset="{{ asset(config('theme.current') . '/images/fit-2.jpg') }}" type="image/jpg" />
                                <img width="100" height="100" src="{{ asset(config('theme.current') . '/images/fit-2.jpg') }}" alt="" />
                            </picture>
                        </div>
                    </div>


                    <div class="about-offer">
                        <div class="offer-text">

                            @lang("simpson.about_offers.about_offer3")

                        </div>
                        <div class="offer-img">
                            <picture>
                                <source media="(max-width: 500px)" srcset="{{ asset(config('theme.current') . '/images/fit-3min.webp') }}" type="image/webp" />
                                <source media="(max-width: 500px)" srcset="{{ asset(config('theme.current') . '/images/fit-3min.jpg') }}" type="image/jpg" />
                                <source srcset="{{ asset(config('theme.current') . '/images/fit-3.webp') }}" type="image/webp" />
                                <source srcset="{{ asset(config('theme.current') . '/images/fit-3.jpg') }}" type="image/jpg" />
                                <img width="100" height="100" src="{{ asset(config('theme.current') . '/images/fit-3.jpg') }}" alt="" />
                            </picture>
                        </div>
                    </div>
                </div>
                <div class="about--notes">
                    <div class="about--note">

                        @lang("simpson.about.about__notes.about__note1")
                        <span>*</span>
                        <p>
                            Если вы планируете заказать портрет на свадьбу или юбилей,
                            но у вас нет совместной фотографии пары, то отправляйте
                            несколько раздельных фотографий супругов.
                        </p>

                    </div>

                    <div class="about--note">

                        @lang("simpson.about.about__notes.about__note2")
                        <span>*</span>
                        <p>
                            Если вы планируете заказать портрет на свадьбу или юбилей,
                            но у вас нет совместной фотографии пары, то отправляйте
                            несколько раздельных фотографий супругов.
                        </p>

                    </div>
                </div>

                <div class="hidden-trigger">
                    <a
                        href="#examples"
                        class="anchor ellipse-arrow ellipse-arrow_white"
                        aria-label="anchor link"
                    >
                        <i class="fa-arrow-down"></i>
                    </a>

                    @lang("simpson.about.hidden_trigger")

                </div>
            </div>
            <div class="about__block about__fifth hidden-block brief-v">
                <div class="about-deadline">
                    <div class="deadline-block">
                        <div class="deadline-title">
                            <img width="100" height="100" src="{{ asset(config('theme.current') . '/images/clock.png') }}" alt="" />

                            @lang("simpson.about.about_deadline.deadline_title")

                        </div>
                        <div class="deadline-items">
                            <div class="deadline-item">
                                <div class="deadline-img">
                                    <picture>
                                        <source
                                            srcset="{{ asset(config('theme.current') . '/images/three-days.webp') }}"
                                            type="image/webp"
                                        />
                                        <source
                                            srcset="{{ asset(config('theme.current') . '/images/three-days.jpg') }}"
                                            type="image/jpg"
                                        />
                                        <img width="100" height="100" src="{{ asset(config('theme.current') . '/images/three-days.jpg') }}" alt="" />
                                    </picture>
                                </div>
                                <div class="deadline-content">

                                    <div class="deadline-desc">
                                        @lang("simpson.about.about_deadline.deadline_items.deadline_item1.deadline_desc")

                                    </div>


                                    <div class="deadline-text">
                                        @lang("simpson.about.about_deadline.deadline_items.deadline_item1.deadline_text")
                                    </div>
                                </div>
                            </div>
                            <div class="deadline-item">
                                <div class="deadline-img">
                                    <picture>
                                        <source media="(max-width: 500px)" srcset="{{ asset(config('theme.current') . '/images/one-dayMin.webp') }}" type="image/webp" />
                                        <source media="(max-width: 500px)" srcset="{{ asset(config('theme.current') . '/images/one-dayMin.jpg') }}" type="image/jpg" />
                                        <source
                                            srcset="{{ asset(config('theme.current') . '/images/one-day.webp') }}"
                                            type="image/webp"
                                        />
                                        <source
                                            srcset="{{ asset(config('theme.current') . '/images/one-day.jpg') }}"
                                            type="image/jpg"
                                        />
                                        <img width="100" height="100" src="{{ asset(config('theme.current') . '/images/one-day.jpg') }}" alt="" />
                                    </picture>
                                </div>
                                <div class="deadline-content">

                                    <div class="deadline-desc">
                                        @lang("simpson.about.about_deadline.deadline_items.deadline_item2.deadline_desc")
                                    </div>

                                    <div class="deadline-text">
                                        @lang("simpson.about.about_deadline.deadline_items.deadline_item2.deadline_text")
                                    </div>
                                </div>
                            </div>
                            <div class="deadline-item">
                                <div class="deadline-img">
                                    <picture>
                                        <source
                                            srcset="{{ asset(config('theme.current') . '/images/on-date.webp') }}"
                                            type="image/webp"
                                        />
                                        <source
                                            srcset="{{ asset(config('theme.current') . '/images/on-date.jpg') }}"
                                            type="image/jpg"
                                        />
                                        <img width="100" height="100" src="{{ asset(config('theme.current') . '/images/on-date.jpg') }}" alt="" />
                                    </picture>
                                </div>
                                <div class="deadline-content">
                                    <div class="deadline-desc">
                                        @lang("simpson.about.about_deadline.deadline_items.deadline_item3.deadline_desc")
                                    </div>
                                    <div class="deadline-text">
                                        @lang("simpson.about.about_deadline.deadline_items.deadline_item3.deadline_text")
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="deadline-block">
                        <div class="deadline-title">
                            <img width="100" height="100" src="{{ asset(config('theme.current') . '/images/delivery.png') }}" alt="" />
                            @lang("simpson.about.deadline_block.deadline_title")
                        </div>
                        <div class="deadline-items">
                            <div class="deadline-item">
                                <div class="deadline-img">
                                    <picture>
                                        <source srcset="{{ asset(config('theme.current') . '/images/van.webp') }}" type="image/webp" />
                                        <source srcset="{{ asset(config('theme.current') . '/images/van.jpg') }}" type="image/jpg" />
                                        <img width="100" height="100" src="{{ asset(config('theme.current') . '/images/van.jpg') }}" alt="" />
                                    </picture>
                                </div>
                                <div class="deadline-content">
                                    <div class="deadline-desc">
                                        @lang("simpson.about.deadline_content.deadline_desc")
                                    </div>
                                </div>
                            </div>
                            <div class="deadline-item">
                                <div class="deadline-img">
                                    <picture>
                                        <source
                                            srcset="{{ asset(config('theme.current') . '/images/on-adress.webp') }}"
                                            type="image/webp"
                                        />
                                        <source
                                            srcset="{{ asset(config('theme.current') . '/images/on-adress.jpg') }}"
                                            type="image/jpg"
                                        />
                                        <img width="100" height="100" src="{{ asset(config('theme.current') . '/images/on-adress.jpg') }}" alt="" />
                                    </picture>
                                </div>
                                <div class="deadline-content">

                                    <div class="deadline-text">
                                        @lang("simpson.about.deadline_content2.deadline_text")

                                    </div>

                                    <div class="deadline-desc">
                                        @lang("simpson.about.deadline_content2.deadline_desc")

                                    </div>

                                </div>
                            </div>
                            <div class="deadline-item">
                                <div class="deadline-img">
                                    <picture>
                                        <source
                                            srcset="{{ asset(config('theme.current') . '/images/abroad.webp') }}"
                                            type="image/webp"
                                        />
                                        <source srcset="{{ asset(config('theme.current') . '/images/abroad.jpg') }}" type="image/jpg" />
                                        <img width="100" height="100" src="{{ asset(config('theme.current') . '/images/abroad.jpg') }}" alt="" />
                                    </picture>
                                </div>
                                <div class="deadline-content">

                                    <div class="deadline-text">
                                        @lang("simpson.about.deadline_content3.deadline_text")
                                    </div>

                                    <div class="deadline-desc">
                                        @lang("simpson.about.deadline_content3.deadline_desc")
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="courier-info">

                            <div class="courier-text">
                                @lang("simpson.about.courier_info.courier_text")
                            </div>

                            <div class="courier-items">
                                <div class="courier-item">
                                    <div class="courier-inner">
                                        <div class="courier-logo">
                                            <img width="100" height="100" src="{{ asset(config('theme.current') . '/images/venipak.png') }}" alt="" />
                                        </div>

                                        <div class="courier-txt">
                                            @lang("simpson.about.courier_item1.courier_txt")
                                        </div>

                                    </div>
                                </div>

                                <div class="courier-item">
                                    <div class="courier-inner">
                                        <div class="courier-logo">
                                            <img width="100" height="100" src="{{ asset(config('theme.current') . '/images/dpd.png') }}" alt="" />
                                        </div>
                                        <div class="courier-txt">

                                            @lang("simpson.about.courier_item2.courier_txt")

                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="hidden-trigger">
                    <a
                        href="#examples"
                        class="anchor ellipse-arrow ellipse-arrow_white"
                        aria-label="anchor link"
                    >
                        <i class="fa-arrow-down"></i>
                    </a>

                    @lang("simpson.about.about__block.hidden_trigger")


                </div>
            </div>
        </div>
    </div>
</section>

<section class="example-section divider-container example-section__simpson" id="examples">
    <div class="section-frame">

        <h2 class="simpson-title">
            @lang("simpson.examples.section_frame.simpson_title")

        </h2>

        <div class="examples-wrap example-flex">
            <div class="before-after__block">
                <div class="title-group">

                    <div class="before-after__title">
                        @lang("simpson.examples.section_frame.examples_wrap.before_after__title")

                    </div>

                    <div class="before-after__subtitle">
                        @lang("simpson.examples.section_frame.examples_wrap.before_after__subtitle")

                    </div>
                </div>

                <div class="ba-slider ba-slider1">
                    <picture>
                        <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/ex1.webp') }}" type="image/webp" />
                        <img width="300" height="435" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/ex1.webp') }}" data-image="{{ asset(config('theme.current') . '/images/sharj/new/simpson/ex1.webp') }}" alt="img"
                             loading="lazy" />
                    </picture>
                    <div class="resize">
                        <picture>
                            <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/ex4.webp') }}" type="image/webp" />
                            <img width="300" height="435" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/ex4.webp') }}" data-image="{{ asset(config('theme.current') . '/images/sharj/new/simpson/ex4.webp') }}" alt="img"
                                 loading="lazy" />
                        </picture>
                    </div>
                    <span class="handle"></span>
                </div>

                <div class="before-after__text">
                    @lang("simpson.before_after__block.before_after__text")
                </div>

                <div class="before-after__nav">
                    <div class="bf-arr bf-arr_l">
                        <svg width="5" height="9" viewBox="0 0 5 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M4.91409 8.45892C4.97136 8.3988 5 8.32966 5 8.2515C5 8.17335 4.97136 8.10421 4.91409 8.04409L1.5378 4.5L4.91409 0.955912C4.97136 0.895792 5 0.826653 5 0.748496C5 0.67034 4.97136 0.601202 4.91409 0.541081L4.48454 0.0901804C4.42726 0.0300598 4.3614 0 4.28694 0C4.21249 0 4.14662 0.0300598 4.08935 0.0901804L0.0859107 4.29258C0.0286369 4.35271 0 4.42184 0 4.5C0 4.57816 0.0286369 4.64729 0.0859107 4.70742L4.08935 8.90982C4.14662 8.96994 4.21249 9 4.28694 9C4.3614 9 4.42726 8.96994 4.48454 8.90982L4.91409 8.45892Z"
                                fill="white" />
                        </svg>
                    </div>
                    <ul class="bf-obj bf-objP">
                        <li class="active" data-imageB="{{ asset(config('theme.current') . '/images/sharj/new/simpson/ex1.webp') }}" data-imageA="{{ asset(config('theme.current') . '/images/sharj/new/simpson/ex3.webp') }}">1</li>
                        <li data-imageB="{{ asset(config('theme.current') . '/images/sharj/new/simpson/ex1.webp') }}" data-imageA="{{ asset(config('theme.current') . '/images/sharj/new/simpson/ex2.webp') }}">2</li>
                        <li data-imageB="{{ asset(config('theme.current') . '/images/sharj/new/simpson/ex4.webp') }}" data-imageA="{{ asset(config('theme.current') . '/images/sharj/new/simpson/ex3.webp') }}">3</li>
                    </ul>
                    <div class="bf-arr bf-arr_r">
                        <svg width="5" height="9" viewBox="0 0 5 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M0.0859108 8.45892C0.0286369 8.3988 0 8.32966 0 8.2515C0 8.17335 0.0286369 8.10421 0.0859108 8.04409L3.4622 4.5L0.0859108 0.955912C0.0286369 0.895792 0 0.826653 0 0.748496C0 0.67034 0.0286369 0.601202 0.0859108 0.541081L0.515464 0.0901804C0.572738 0.0300598 0.638603 0 0.713059 0C0.787515 0 0.853379 0.0300598 0.910653 0.0901804L4.91409 4.29258C4.97136 4.35271 5 4.42184 5 4.5C5 4.57816 4.97136 4.64729 4.91409 4.70742L0.910653 8.90982C0.853379 8.96994 0.787515 9 0.713059 9C0.638603 9 0.572738 8.96994 0.515464 8.90982L0.0859108 8.45892Z"
                                fill="white" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="examples-slider__block">
                <div class="title-group">

                    <div class="examples-slider__title">
                        @lang("simpson.examples.examples_slider__block.examples_slider__title")
                    </div>

                </div>
                <div class="examples-slider__inner">
                    <div class="examples-slide">
                        <a class="examples-slide__photo">
                            <picture>
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/ex1.webp') }}" type="image/webp" />
                                <img width="300" height="435" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/ex1.webp') }}" data-image="images/examples/examples-2.jpg" alt="img"
                                     loading="lazy" />
                            </picture>
                        </a>
                    </div>
                    <div class="examples-slide">
                        <a class="examples-slide__photo">
                            <picture>
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/ex2.webp') }}" type="image/webp" />
                                <img width="300" height="435" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/ex2.webp') }}" data-image="images/examples/examples-2.jpg" alt="img"
                                     loading="lazy" />
                            </picture>
                        </a>
                    </div>
                    <div class="examples-slide">
                        <a class="examples-slide__photo">
                            <picture>
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/ex3.webp') }}" type="image/webp" />
                                <img width="300" height="435" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/ex3.webp') }}" data-image="images/examples/examples-2.jpg" alt="img"
                                     loading="lazy" />
                            </picture>
                        </a>
                    </div>
                </div>
                <div class="examples-arrow">
                    <a href="#" class="examples-prev examples-prev2" aria-label="examples prev">
                        <svg width="48" height="51" viewBox="0 0 48 51" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M27.2335 31.1145C27.334 31.0141 27.3842 30.8986 27.3842 30.768C27.3842 30.6374 27.334 30.5219 27.2335 30.4215L21.3122 24.5001L27.2335 18.5788C27.334 18.4784 27.3842 18.3629 27.3842 18.2323C27.3842 18.1017 27.334 17.9862 27.2335 17.8857L26.4802 17.1324C26.3797 17.0319 26.2642 16.9817 26.1336 16.9817C26.0031 16.9817 25.8876 17.0319 25.7871 17.1324L18.7659 24.1536C18.6655 24.254 18.6152 24.3696 18.6152 24.5001C18.6152 24.6307 18.6655 24.7462 18.7659 24.8467L25.7871 31.8679C25.8876 31.9683 26.0031 32.0186 26.1336 32.0186C26.2642 32.0186 26.3797 31.9683 26.4802 31.8679L27.2335 31.1145Z"
                                fill="#fff" />
                        </svg>
                    </a>
                    <a href="#" class="examples-next examples-next2" aria-label="examples next">
                        <svg width="48" height="51" viewBox="0 0 48 51" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M20.7665 31.1145C20.666 31.0141 20.6158 30.8986 20.6158 30.768C20.6158 30.6374 20.666 30.5219 20.7665 30.4215L26.6878 24.5001L20.7665 18.5788C20.666 18.4784 20.6158 18.3629 20.6158 18.2323C20.6158 18.1017 20.666 17.9862 20.7665 17.8857L21.5198 17.1324C21.6203 17.0319 21.7358 16.9817 21.8664 16.9817C21.9969 16.9817 22.1124 17.0319 22.2129 17.1324L29.2341 24.1536C29.3345 24.254 29.3848 24.3696 29.3848 24.5001C29.3848 24.6307 29.3345 24.7462 29.2341 24.8467L22.2129 31.8679C22.1124 31.9683 21.9969 32.0186 21.8664 32.0186C21.7358 32.0186 21.6203 31.9683 21.5198 31.8679L20.7665 31.1145Z"
                                fill="#fff" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        <a href="#" class="yellow-btn">
            @lang("simpson.yellow_btn")
        </a>
    </div>
    <div class="custom-shape-divider-bottom-1685740923">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M600,112.77C268.63,112.77,0,65.52,0,7.23V120H1200V7.23C1200,65.52,931.37,112.77,600,112.77Z" class="shape-fill"></path>
        </svg>
    </div>
</section>

<section class="simpson-pattern">
    <div class="section-frame">
        <div class="section-inner">
            <div class="simpson-pattern__grid">
                <div class="col">
                    <div class="title-block">

                        @lang("simpson.simpson_pattern.simpson_pattern__grid.title_block")

                    </div>
                    <div class="simpson-pattern__row">
                        <div class="item">
                            <div class="img">

                                @lang("simpson.simpson_pattern.simpson_pattern__row.item1.img")


                                <picture>
                                    <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/s-pattern1Min.webp') }}" type="image/webp">
                                    <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/s-pattern1.webp') }}" type="image/webp">
                                    <img width="124" height="106" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/s-pattern1.webp') }}" alt="">
                                </picture>
                            </div>
                        </div>
                        <div class="item">
                            <div class="img">

                                @lang("simpson.simpson_pattern.simpson_pattern__row.item2.img")


                                <picture>
                                    <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/s-pattern2Min.webp') }}" type="image/webp">
                                    <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/s-pattern2.webp') }}" type="image/webp">
                                    <img width="271" height="189" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/s-pattern2.webp') }}" alt="">
                                </picture>
                            </div>
                        </div>
                        <div class="item">
                            <div class="img">

                                @lang("simpson.simpson_pattern.simpson_pattern__row.item3.img")


                                <picture>
                                    <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/s-pattern3.webp') }}?v=1" type="image/webp">
                                    <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/s-pattern3.webp') }}?v=1" type="image/webp">
                                    <img width="299" height="224" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/s-pattern3.webp') }}?v=1" alt="">
                                </picture>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="list">

                        @lang("simpson.simpson_pattern.simpson_pattern__grid.col.list.text1")


                        <ul>
                            <li>
                                <div class="icon">
                                    <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10.1952 1.6997C9.28114 1.82861 8.55457 2.36767 8.15028 3.22314L7.93934 3.66259L7.92176 10.2075L7.91004 16.7583H7.13075C6.18739 16.7583 5.77137 16.8286 5.28504 17.063C3.72645 17.8247 3.32215 19.9106 4.48231 21.1997C4.66395 21.4048 6.17567 22.4712 9.53309 24.7739C12.1698 26.5786 14.4257 28.1138 14.5487 28.1841C14.7245 28.2895 14.8358 28.313 15.0761 28.2895C15.369 28.2661 15.5682 28.1372 20.2147 24.9556C23.2616 22.8755 25.1718 21.5278 25.3886 21.3169C26.4608 20.2622 26.4022 18.4634 25.2597 17.4556C24.6737 16.9341 24.0761 16.7583 22.869 16.7583H22.0897L22.078 10.2075L22.0604 3.66259L21.8729 3.27002C21.5214 2.52588 20.9999 2.04541 20.2792 1.79931C19.8925 1.67041 19.8807 1.67041 15.205 1.66455C12.6268 1.65869 10.371 1.67627 10.1952 1.6997ZM19.8397 3.55127C19.9511 3.604 20.0975 3.73291 20.1737 3.83252L20.3026 4.01416L20.3319 10.9985C20.3612 17.936 20.3612 17.9829 20.4843 18.1411C20.7831 18.5395 20.7831 18.5454 22.3827 18.5747C23.7362 18.604 23.8534 18.6099 24.0057 18.727C24.3632 18.9907 24.4745 19.5649 24.2401 19.9282C24.1815 20.0103 22.0839 21.4927 19.5702 23.2212L15.0057 26.3677L10.4472 23.2329C7.93934 21.5102 5.83582 20.0278 5.77723 19.9458C5.60731 19.7173 5.58973 19.2368 5.73621 19.0024C5.97645 18.6216 6.02918 18.6099 7.58778 18.5747C9.13465 18.5454 9.23426 18.522 9.51551 18.147L9.63856 17.9829L9.66786 10.9985C9.69715 4.18994 9.69715 4.0083 9.81434 3.84423C9.87293 3.75048 10.0077 3.62744 10.1073 3.56884C10.2772 3.46338 10.5057 3.45752 14.9589 3.45752C19.16 3.45752 19.6639 3.46923 19.8397 3.55127Z" fill="white"/>
                                        <path d="M14.1797 10.1367V10.5996L13.9746 10.6582C13.4355 10.8047 12.8789 11.3086 12.6094 11.8828C12.4746 12.1699 12.4512 12.293 12.4512 12.7734C12.4512 13.2656 12.4688 13.377 12.6211 13.6934C12.8262 14.1445 13.2832 14.5957 13.752 14.8066C14.0508 14.9473 14.1914 14.9707 14.9004 14.9941C15.6621 15.0293 15.7207 15.0352 15.8262 15.1699C15.9727 15.3457 15.9668 15.5332 15.8145 15.7266L15.6973 15.8789H14.2324H12.7676L12.7852 16.7695L12.8027 17.666L13.4941 17.6836L14.1797 17.7012V18.1348V18.5742H15.0586H15.9375V18.1055C15.9375 17.707 15.9551 17.6309 16.043 17.6016C16.0957 17.584 16.3066 17.4902 16.5059 17.3965C16.9395 17.1797 17.2969 16.8105 17.5078 16.3594C17.6484 16.0664 17.666 15.9492 17.666 15.4395C17.666 14.9121 17.6484 14.8184 17.4961 14.502C17.2734 14.0566 16.8809 13.6641 16.4355 13.4414C16.1074 13.2832 16.0195 13.2715 15.252 13.2422C14.4902 13.2129 14.4082 13.2012 14.291 13.084C14.1211 12.9141 14.1211 12.6328 14.291 12.4629C14.4141 12.3398 14.4668 12.334 15.8203 12.3164L17.2266 12.2988V11.4199V10.5469H16.582H15.9375V10.1074V9.66797H15.0586H14.1797V10.1367Z" fill="white"/>
                                    </svg>
                                </div>

                                @lang("simpson.simpson_pattern.simpson_pattern__grid.col.list.ul.li1")


                            </li>
                            <li>
                                <div class="icon">
                                    <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M18.3934 1.96908C18.2469 2.03353 16.3778 3.99642 11.5379 9.17611C7.88753 13.096 4.84651 16.3949 4.78206 16.5062C4.50081 17.0628 4.95784 17.8128 5.58479 17.8128C5.78987 17.8128 6.48128 17.6781 10.0965 16.9222C10.5711 16.8226 10.9754 16.7581 10.9989 16.7757C11.0164 16.7933 10.659 19.1136 10.202 21.9261C9.74495 24.7386 9.37581 27.1351 9.37581 27.2523C9.37581 27.8441 10.1024 28.2894 10.659 28.0374C10.8231 27.9671 12.6278 26.0687 17.5262 20.8245C21.1824 16.9105 24.2176 13.6058 24.2762 13.4886C24.5516 12.9085 24.1004 12.1878 23.4617 12.1878C23.3446 12.1878 22.1024 12.4281 20.702 12.721C19.3074 13.0199 18.1473 13.2425 18.1297 13.2249C18.1121 13.2074 18.4578 10.8812 18.8914 8.04525C19.3309 5.21517 19.6883 2.83627 19.6883 2.75423C19.6883 2.49056 19.4422 2.15072 19.161 2.00423C18.8621 1.85775 18.6864 1.84603 18.3934 1.96908ZM16.6766 10.1546C15.9324 14.971 15.95 14.7894 16.3133 15.1527C16.6649 15.5042 16.7059 15.5042 19.6121 14.889C20.2742 14.7484 20.6668 14.6898 20.6375 14.7367C20.5907 14.8187 11.7723 24.2581 11.7489 24.2581C11.7371 24.2581 11.743 24.2113 11.7547 24.1527C11.8426 23.7542 13.1258 15.6156 13.1258 15.4632C13.1258 15.1878 12.9032 14.8304 12.6571 14.7015C12.5399 14.637 12.3348 14.5902 12.1766 14.5902C12.0242 14.5902 11.1395 14.7484 10.2137 14.9417C9.28792 15.1351 8.4969 15.2874 8.46175 15.2816C8.42073 15.2699 10.3953 13.1195 12.8446 10.5003C15.2996 7.88119 17.3094 5.74837 17.327 5.76009C17.3387 5.77181 17.0457 7.75228 16.6766 10.1546Z" fill="white"/>
                                    </svg>
                                </div>

                                @lang("simpson.simpson_pattern.simpson_pattern__grid.col.list.ul.li2")


                            </li>


                            <li>
                                <div class="icon">
                                    <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10.8685 1.29452C9.83725 1.44687 8.92905 2.35507 8.80015 3.36874L8.75913 3.7203L7.82163 3.75546C7.306 3.77304 6.75522 3.82577 6.59116 3.86679C5.51304 4.14804 4.61655 4.88632 4.13022 5.88827C3.72593 6.72616 3.7435 6.2164 3.76108 16.4644L3.77866 25.693L3.94272 26.1617C4.45835 27.6207 5.61264 28.5348 7.16538 28.7164C7.52866 28.7574 10.2181 28.7691 15.3802 28.7574C23.9291 28.734 23.2845 28.7691 24.1693 28.3297C25.183 27.8316 25.9212 26.8707 26.1615 25.7457C26.2845 25.1539 26.2904 7.37655 26.1673 6.79647C25.9037 5.58358 25.1771 4.64022 24.1107 4.13632C23.513 3.84921 23.2201 3.79062 22.1771 3.75546L21.2396 3.7203L21.1986 3.36874C21.0697 2.32577 20.1673 1.44687 19.0716 1.28866C18.5677 1.21835 11.3666 1.22421 10.8685 1.29452ZM18.7494 5.00937V6.26913H14.9994H11.2494V5.00937V3.7496H14.9994H18.7494V5.00937ZM8.80015 6.63827C8.89975 7.41171 9.4564 8.14413 10.2357 8.51327L10.6341 8.70077H14.9994H19.3646L19.763 8.51327C20.5306 8.14999 21.099 7.40585 21.1986 6.63827L21.2396 6.29843H22.1009H22.9564L23.2083 6.47421C23.3431 6.56796 23.5189 6.76718 23.5951 6.9078L23.7298 7.16562V16.2418C23.7298 25.0543 23.724 25.3238 23.6185 25.5348C23.472 25.816 23.2318 26.0445 22.9564 26.1558C22.763 26.2379 21.8607 26.2496 14.9876 26.2496C7.58139 26.2496 7.22397 26.2437 6.99546 26.1383C6.70835 26.0094 6.33921 25.5933 6.29819 25.3473C6.28061 25.2476 6.27475 21.0875 6.28061 16.1012L6.29819 7.04257L6.47397 6.79062C6.57358 6.65585 6.76694 6.48007 6.91343 6.4039C7.15366 6.27499 7.22983 6.26913 7.96811 6.28085L8.75913 6.29843L8.80015 6.63827Z" fill="white"/>
                                        <path d="M18.2217 13.8925C18.0283 13.9804 17.4189 14.5488 15.8486 16.1132L13.7392 18.2226L12.8896 17.3671C12.4209 16.8984 11.9346 16.4589 11.8115 16.3945C11.6592 16.3183 11.4717 16.2773 11.2431 16.2773C10.5928 16.2773 10.083 16.7402 10.0303 17.3671C9.98338 17.9472 10.0244 18.0117 11.5654 19.5585C12.3388 20.3437 13.0771 21.041 13.206 21.1054C13.4873 21.2519 13.9502 21.2578 14.2666 21.1289C14.6123 20.9824 19.6924 15.8964 19.8564 15.5273C20.1318 14.9179 19.9092 14.2265 19.3408 13.9335C18.9599 13.7343 18.5967 13.7226 18.2217 13.8925Z" fill="white"/>
                                    </svg>
                                </div>

                                @lang("simpson.simpson_pattern.simpson_pattern__grid.col.list.ul.li3")

                            </li>

                            <li>
                                <div class="icon">
                                    <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8.05664 1.95703C7.83984 2.05078 7.64648 2.26172 7.56445 2.47852C7.52344 2.58398 7.5 3.49219 7.5 5.06836V7.49414L4.98633 7.51172C2.54883 7.5293 2.4668 7.53516 2.30859 7.65234C2.2207 7.7168 2.0918 7.8457 2.02734 7.93359L1.9043 8.0918V17.8125V27.5332L2.02734 27.6914C2.0918 27.7793 2.2207 27.9082 2.30859 27.9727L2.4668 28.0957H12.1875H21.9082L22.0664 27.9727C22.1543 27.9082 22.2832 27.7793 22.3477 27.6914C22.4648 27.5332 22.4707 27.4512 22.4883 25.0195L22.5059 22.5059L25.0195 22.4883C27.4512 22.4707 27.5332 22.4648 27.6914 22.3477C27.7793 22.2832 27.9082 22.1543 27.9727 22.0664L28.0957 21.9082V12.1934V2.47852L27.9375 2.27344C27.6094 1.83984 28.459 1.875 17.8008 1.875C9.96094 1.88086 8.20312 1.89258 8.05664 1.95703ZM10.8398 3.76758C10.8398 3.77344 10.5117 4.10742 10.1074 4.51172L9.375 5.24414V4.49414V3.75H10.1074C10.5117 3.75 10.8398 3.75586 10.8398 3.76758ZM12.4805 6.82617L9.4043 9.90234L9.38672 8.89453L9.375 7.88672L11.4375 5.81836L13.5059 3.75H14.5312H15.5566L12.4805 6.82617ZM14.8242 9.16992L9.4043 14.5898L9.38672 13.582L9.375 12.5742L13.7812 8.16211L18.1934 3.75H19.2188H20.2441L14.8242 9.16992ZM17.168 11.5137L9.4043 19.2773L9.38672 18.2695L9.375 17.2617L16.125 10.5059L22.8809 3.75H23.9062H24.9316L17.168 11.5137ZM19.5 13.8691L12.7441 20.625H11.7188H10.6934L18.457 12.8613L26.2207 5.09766L26.2383 6.10547L26.25 7.11328L19.5 13.8691ZM7.51172 15.6328L7.5293 21.8965L7.6875 22.1016C8.00977 22.5293 7.55273 22.5 14.4375 22.5H20.625V24.375V26.25H12.1875H3.75V17.8125V9.375H5.625H7.5L7.51172 15.6328ZM21.8438 16.2129L17.4316 20.625H16.4062H15.3809L20.8008 15.2051L26.2207 9.78516L26.2383 10.793L26.25 11.8008L21.8438 16.2129ZM24.1875 18.5566L22.1191 20.625H21.0938H20.0684L23.1445 17.5488L26.2207 14.4727L26.2383 15.4805L26.25 16.4883L24.1875 18.5566ZM26.25 19.8926V20.625H25.5059H24.7559L25.4883 19.8926C25.8926 19.4883 26.2266 19.1602 26.2324 19.1602C26.2441 19.1602 26.25 19.4883 26.25 19.8926Z" fill="white"/>
                                    </svg>
                                </div>

                                @lang("simpson.simpson_pattern.simpson_pattern__grid.col.list.ul.li4")


                            </li>


                            <li>
                                <div class="icon">
                                    <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M23.4375 11.25H18.75V13.125H23.4375C23.6861 13.125 23.9246 13.2238 24.1004 13.3996C24.2762 13.5754 24.375 13.8139 24.375 14.0625V15.9375H20.625C19.8793 15.9382 19.1644 16.2348 18.6371 16.7621C18.1098 17.2894 17.8132 18.0043 17.8125 18.75V19.6875C17.8132 20.4332 18.1098 21.1481 18.6371 21.6754C19.1644 22.2027 19.8793 22.4993 20.625 22.5H26.25V14.0625C26.2493 13.3168 25.9527 12.6019 25.4254 12.0746C24.8981 11.5473 24.1832 11.2507 23.4375 11.25ZM20.625 20.625C20.3764 20.625 20.1379 20.5262 19.9621 20.3504C19.7863 20.1746 19.6875 19.9361 19.6875 19.6875V18.75C19.6875 18.5014 19.7863 18.2629 19.9621 18.0871C20.1379 17.9113 20.3764 17.8125 20.625 17.8125H24.375V20.625H20.625Z" fill="white"/>
                                        <path d="M15 22.5H16.875L11.25 6.5625H9.375L3.75 22.5H5.625L7.21312 17.8125H13.4128L15 22.5ZM7.84781 15.9375L10.1888 9.02719H10.4381L12.7781 15.9375H7.84781Z" fill="white"/>
                                    </svg>
                                </div>

                                @lang("simpson.simpson_pattern.simpson_pattern__grid.col.list.ul.li5")


                            </li>


                            <li>
                                <div class="icon">
                                    <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g clip-path="url(#clip0_1497_1780)">
                                            <path d="M5.63874 0.986291C5.21882 1.13766 4.83308 1.57711 4.76472 1.98727C4.75007 2.08981 4.73542 3.15426 4.73542 4.35543V6.54293H2.87019C1.01472 6.54293 1.00007 6.54293 0.75593 6.66012C-0.245046 7.12399 -0.230398 8.53512 0.780344 9.00387C0.990305 9.10153 1.09284 9.10641 2.86531 9.12106L4.73054 9.13571L4.74519 11.0009C4.75984 12.7734 4.76472 12.8759 4.86238 13.0859C5.35066 14.1406 6.85456 14.0673 7.25984 12.9736C7.2989 12.8662 7.31843 12.2558 7.31843 10.9765L7.32331 9.13571L9.41316 9.12106C11.4053 9.10641 11.5079 9.10153 11.7178 9.00387C12.7286 8.53512 12.7432 7.12399 11.7423 6.66012C11.4981 6.54293 11.4932 6.54293 9.41316 6.54293H7.32331V4.28219C7.32331 1.68941 7.32331 1.70406 6.93269 1.31832C6.59089 0.971642 6.05866 0.839807 5.63874 0.986291Z" fill="white"/>
                                            <path d="M21.9189 2.74884C21.8212 2.7879 21.6796 2.86114 21.6015 2.91974C21.3085 3.1297 2.4462 22.0555 2.36808 22.2166C2.23624 22.4754 2.21671 22.949 2.31925 23.2274C2.50968 23.7205 3.02237 24.0721 3.5546 24.0721C4.10636 24.0721 3.48624 24.658 13.8525 14.2918C20.6786 7.46075 23.4081 4.70196 23.4862 4.54571C23.7939 3.9256 23.54 3.13458 22.9296 2.82208C22.6806 2.69513 22.1679 2.65606 21.9189 2.74884Z" fill="white"/>
                                            <path d="M13.6182 19.4824C12.9883 19.5898 12.5 20.1465 12.5 20.752C12.5 21.25 12.8662 21.7627 13.3594 21.9531C13.5889 22.041 13.7988 22.0459 18.75 22.0459C23.7012 22.0459 23.9111 22.041 24.1406 21.9531C24.6338 21.7627 25 21.25 25 20.752C25 20.2686 24.6729 19.79 24.1943 19.5703L23.9502 19.458L18.8965 19.4531C16.1182 19.4482 13.7402 19.4629 13.6182 19.4824Z" fill="white"/>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_1497_1780">
                                                <rect width="25" height="25" fill="white"/>
                                            </clipPath>
                                        </defs>
                                    </svg>
                                </div>

                                @lang("simpson.simpson_pattern.simpson_pattern__grid.col.list.ul.li6")


                            </li>


                        </ul>
                    </div>
                </div>
            </div>
            <div class="simpson-pattern__slider">
                <div class="sp-slider">

                    <div class="portrait-list__item">

                        @lang("simpson.simpson-pattern__slider.portrait-list__item1")


                        <div class="portrait-list__img">
                            <picture>
                                <!-- <source media="(max-width: 576px)" srcset="images/sharj/new/simpson/p1Min.webp" type="image/webp"> -->
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/s-slider1.webp') }}" type="image/webp">
                                <img width="315" height="265" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/s-slider1.webp') }}" alt="">
                            </picture>

                            <a href="#" class="yellow-btn">
                                @lang("gallery.trybuy_btn")
                            </a>

                        </div>
                    </div>

                    <div class="portrait-list__item">

                        @lang("simpson.simpson-pattern__slider.portrait-list__item2")


                        <div class="portrait-list__img">
                            <picture>
                                <!-- <source media="(max-width: 576px)" srcset="images/sharj/new/simpson/p1Min.webp" type="image/webp"> -->
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/s-slider2.webp') }}" type="image/webp">
                                <img width="315" height="265" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/s-slider2.webp') }}" alt="">
                            </picture>

                            <a href="#" class="yellow-btn">
                                @lang("gallery.trybuy_btn")
                            </a>

                        </div>
                    </div>

                    <div class="portrait-list__item">

                        @lang("simpson.simpson-pattern__slider.portrait-list__item3")

                        <div class="portrait-list__img">
                            <picture>
                                <!-- <source media="(max-width: 576px)" srcset="images/sharj/new/simpson/p1Min.webp" type="image/webp"> -->
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/s-slider3.webp') }}" type="image/webp">
                                <img width="200" height="265" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/s-slider3.webp') }}" alt="">
                            </picture>

                            <a href="#" class="yellow-btn">
                                @lang("gallery.trybuy_btn")
                            </a>

                        </div>
                    </div>

                    <div class="portrait-list__item">

                        @lang("simpson.simpson-pattern__slider.portrait-list__item4")

                        <div class="portrait-list__img">
                            <picture>
                                <!-- <source media="(max-width: 576px)" srcset="images/sharj/new/simpson/p1Min.webp" type="image/webp"> -->
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/s-slider4.webp') }}" type="image/webp">
                                <img width="430" height="265" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/s-slider4.webp') }}" alt="">
                            </picture>

                            <a href="#" class="yellow-btn">
                                @lang("gallery.trybuy_btn")
                            </a>

                        </div>
                    </div>

                    <div class="portrait-list__item">

                        @lang("simpson.simpson-pattern__slider.portrait-list__item5")

                        <div class="portrait-list__img">
                            <picture>
                                <!-- <source media="(max-width: 576px)" srcset="images/sharj/new/simpson/p1Min.webp" type="image/webp"> -->
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/s-slider1.webp') }}" type="image/webp">
                                <img width="315" height="265" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/s-slider1.webp') }}" alt="">
                            </picture>

                            <a href="#" class="yellow-btn">
                                @lang("gallery.trybuy_btn")
                            </a>

                        </div>
                    </div>
                    <div class="portrait-list__item">

                        @lang("simpson.simpson-pattern__slider.portrait-list__item6")

                        <div class="portrait-list__img">
                            <picture>
                                <!-- <source media="(max-width: 576px)" srcset="images/sharj/new/simpson/p1Min.webp" type="image/webp"> -->
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/s-slider2.webp') }}" type="image/webp">
                                <img width="315" height="265" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/s-slider2.webp') }}" alt="">
                            </picture>

                            <a href="#" class="yellow-btn">
                                @lang("gallery.trybuy_btn")
                            </a>

                        </div>
                    </div>
                </div>
                <div class="examples-arrow">
                    <a href="#" class="examples-prev examples-prev2" aria-label="examples prev">
                        <svg width="48" height="51" viewBox="0 0 48 51" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M27.2335 31.1145C27.334 31.0141 27.3842 30.8986 27.3842 30.768C27.3842 30.6374 27.334 30.5219 27.2335 30.4215L21.3122 24.5001L27.2335 18.5788C27.334 18.4784 27.3842 18.3629 27.3842 18.2323C27.3842 18.1017 27.334 17.9862 27.2335 17.8857L26.4802 17.1324C26.3797 17.0319 26.2642 16.9817 26.1336 16.9817C26.0031 16.9817 25.8876 17.0319 25.7871 17.1324L18.7659 24.1536C18.6655 24.254 18.6152 24.3696 18.6152 24.5001C18.6152 24.6307 18.6655 24.7462 18.7659 24.8467L25.7871 31.8679C25.8876 31.9683 26.0031 32.0186 26.1336 32.0186C26.2642 32.0186 26.3797 31.9683 26.4802 31.8679L27.2335 31.1145Z"
                                fill="#fff" />
                        </svg>
                    </a>
                    <a href="#" class="examples-next examples-next2" aria-label="examples next">
                        <svg width="48" height="51" viewBox="0 0 48 51" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M20.7665 31.1145C20.666 31.0141 20.6158 30.8986 20.6158 30.768C20.6158 30.6374 20.666 30.5219 20.7665 30.4215L26.6878 24.5001L20.7665 18.5788C20.666 18.4784 20.6158 18.3629 20.6158 18.2323C20.6158 18.1017 20.666 17.9862 20.7665 17.8857L21.5198 17.1324C21.6203 17.0319 21.7358 16.9817 21.8664 16.9817C21.9969 16.9817 22.1124 17.0319 22.2129 17.1324L29.2341 24.1536C29.3345 24.254 29.3848 24.3696 29.3848 24.5001C29.3848 24.6307 29.3345 24.7462 29.2341 24.8467L22.2129 31.8679C22.1124 31.9683 21.9969 32.0186 21.8664 32.0186C21.7358 32.0186 21.6203 31.9683 21.5198 31.8679L20.7665 31.1145Z"
                                fill="#fff" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="simpson-form">
    <div class="section-frame">
        <div class="section-inner">
            <form action="#" class="simpson-form__inner">

                <div class="simpson-title">
                    @lang("simpson.simpson-form__inner.simpson-title")
                </div>


                <p>
                    @lang("simpson.simpson-form__inner.p")
                </p>

                <div class="form-inner">
                    <img src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/homer.webp') }}" width="250" height="525" alt="">
                    <div class="form-grid">
                        <div class="kviz-input">

                            <p class="kviz-input__title">
                                @lang("simpson.simpson-form.kviz-input1.kviz-input__title")
                            </p>

                            <div class="page-input__item">
                                <input type="email" name="email" placeholder="E-mail" required="">
                                <svg class="kviz-input__icon">
                                    <use xlink:href=" {{ asset(env('THEME') . 'images') }}/sprite.svg#mail"></use>
                                </svg>
                            </div>
                        </div>
                        <div class="kviz-input">

                            <p class="kviz-input__title">
                                @lang("simpson.simpson-form.kviz-input2.kviz-input__title")
                            </p>

                            <div class="file-save">
                                <div class="abs-close" style="display: none;">
                                    X
                                </div>
                                <div class="file-save__item js-file-preview" style="">
                                    <svg>
                                        <use xlink:href="{{ asset(config('theme.current') . '/sprite.svg#save') }}"></use>
                                    </svg>
                                    <div class="file-save__title">

                                        <p>
                                            @lang("simpson.load_image")
                                        </p>

                                        <span>
                                             @lang("simpson.click_to_add_photo")
                                        </span>

                                    </div>
                                </div>
                                <div class="file-save__item js-file-upload" style="display: none;">
                                    <svg>
                                        <use xlink:href="sprite.svg#picture"></use>
                                    </svg>
                                    <div class="file-save__title">
                                        <p>photo_34567.jpg</p>
                                        <span>2 Mb</span>
                                    </div>
                                </div>
                                <div class="file-save__item js-file-multiple" style="display: none;">
                                    <svg>
                                        <use xlink:href="sprite.svg#check"></use>
                                    </svg>

                                    <div class="file-save__title">
                                        <p class="file-title_green">
                                            @lang("portrait.form_files_loaded")
                                        </p>
                                    </div>

                                </div>
                                <input type="file" class="file-input file-input_save" name="file[]" multiple="" accept="image/*,image/heif,image/heic" aria-label="file input">
                            </div>
                        </div>

                        <div class="kviz-input">

                            <p class="kviz-input__title">
                                @lang("simpson.simpson-form.kviz-input3.kviz-input__title")
                            </p>

                            <div class="page-input__item phone-input">
                                <div class="banner__input-item">
                                    <input type="text" id="phone2" name="phone" class="banner__input phone" required>
                                    <img src="https://viarcanvas.com/img/icons/phone.svg" alt="" class="img-svg img-svg__posa">
                                </div>
                            </div>
                        </div>

                        <div class="kviz-input">

                            <p class="kviz-input__title">
                                @lang("simpson.simpson-form.kviz-input4.kviz-input__title")
                            </p>

                            <div class="textarea">
                                <textarea name=""></textarea>
                            </div>
                        </div>

                        <button type="submit" class="yellow-btn">
                            @lang("stock.submit")
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<section class="portrait-gift__section portrait-gift__simpson">
    <div class="section-inner">
        <div class="portrait-gift__grid">
            <div class="portrait-content">
                <div class="simpson-title">

                    @lang("simpson.portrait-gift__section.portrait-content.simpson-title")

                </div>


                <p>
                    @lang("simpson.portrait-gift__section.portrait-content.p")
                </p>

                <a href="#" class="yellow-btn">
                    @lang("simpson.yellow_btn")
                </a>

            </div>
            <div class="portrait-img">
                <picture>
                    <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/gift2Min.webp') }}" type="image/webp">
                    <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/gift2.webp') }}" type="image/webp">
                    <img width="800" height="451" src="{{ asset(config('theme.current') . '/images/sharj/simpson/gift2.webp') }}" alt="">
                </picture>
            </div>
        </div>
    </div>
</section>

<section class="steps-order divider-container section-block steps-order-simpson">
    <div class="section-frame">
        <div class="section-inner">
            <div class="title-row">

                <div class="simpson-title">
                    @lang("simpson.steps-order-simpson.title-row.simpson-title")
                </div>

                <p>
                    @lang("simpson.steps-order-simpson.title-row.p")
                </p>

                <div class="steps-icons">
                    <picture>
                        <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/stepsIcon1Min.webp') }}" type="image/webp">
                        <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/stepsIcon1.webp') }}" type="image/webp">
                        <img class="homerUp" width="115" height="113" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/stepsIcon1.webp') }}" alt="">
                    </picture>
                    <picture>
                        <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/stepsIcon2Min.webp') }}" type="image/webp">
                        <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/stepsIcon2.webp') }}" type="image/webp">
                        <img class="homerBottom" width="115" height="113" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/stepsIcon2.webp') }}" alt="">
                    </picture>
                </div>
            </div>

            <div class="simpson-steps">
                <div class="simpson-steps__item">
                    <div class="img">
                        <picture>
                            <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/mask1Min.webp') }}" type="image/webp">
                            <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/mask1.webp') }}" type="image/webp">
                            <img width="170" height="170" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/mask1.webp') }}" alt="">
                        </picture>
                    </div>

                    <p class="name">
                        @lang("simpson.steps-order-simpson.simpson-steps.simpson-steps__item1.p1")
                    </p>

                    <p class="text">
                        @lang("simpson.steps-order-simpson.simpson-steps.simpson-steps__item1.p2")
                    </p>

                </div>
                <div class="simpson-steps__item">
                    <div class="img">
                        <picture>
                            <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/mask2Min.webp') }}" type="image/webp">
                            <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/mask2.webp') }}" type="image/webp">
                            <img width="170" height="170" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/mask2.webp') }}" alt="">
                        </picture>
                    </div>

                    <p class="name">
                        @lang("simpson.steps-order-simpson.simpson-steps.simpson-steps__item2.p1")
                    </p>

                    <p class="text">
                        @lang("simpson.steps-order-simpson.simpson-steps.simpson-steps__item2.p2")
                    </p>

                </div>


                <div class="simpson-steps__item">
                    <div class="img">
                        <picture>
                            <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/mask3Min.webp') }}" type="image/webp">
                            <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/mask3.webp') }}" type="image/webp">
                            <img width="170" height="170" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/mask3.webp') }}" alt="">
                        </picture>
                    </div>

                    <p class="name">
                        @lang("simpson.steps-order-simpson.simpson-steps.simpson-steps__item3.p1")
                    </p>

                    <p class="text">
                        @lang("simpson.steps-order-simpson.simpson-steps.simpson-steps__item3.p2")
                    </p>

                </div>

                <div class="simpson-steps__item">
                    <div class="img">
                        <picture>
                            <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/mask4Min.webp') }}" type="image/webp">
                            <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/mask4.webp') }}" type="image/webp">
                            <img width="170" height="170" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/mask4.webp') }}" alt="">
                        </picture>
                    </div>

                    <p class="name">
                        @lang("simpson.steps-order-simpson.simpson-steps.simpson-steps__item4.p1")
                    </p>

                    <p class="text">
                        @lang("simpson.steps-order-simpson.simpson-steps.simpson-steps__item4.p2")
                    </p>

                </div>


                <div class="simpson-steps__item">
                    <div class="img">
                        <picture>
                            <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/mask5Min.webp') }}" type="image/webp">
                            <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/mask5.webp') }}" type="image/webp">
                            <img width="170" height="170" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/mask5.webp') }}" alt="">
                        </picture>
                    </div>

                    <p class="name">
                        @lang("simpson.steps-order-simpson.simpson-steps.simpson-steps__item5.p1")
                    </p>

                    <p class="text">
                        @lang("simpson.steps-order-simpson.simpson-steps.simpson-steps__item5.p2")
                    </p>

                </div>


                <div class="simpson-steps__item">
                    <div class="img">
                        <picture>
                            <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/mask6Min.webp') }}" type="image/webp">
                            <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/mask6.webp') }}" type="image/webp">
                            <img width="170" height="170" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/mask6.webp') }}" alt="">
                        </picture>
                    </div>

                    <p class="name">
                        @lang("simpson.steps-order-simpson.simpson-steps.simpson-steps__item6.p1")
                    </p>

                    <p class="text">
                        @lang("simpson.steps-order-simpson.simpson-steps.simpson-steps__item6.p2")
                    </p>

                </div>

            </div>



            <div class="steps-order__top simpson-delivery">
                <div class="steps-order__top-item">
                    <img width="100" height="100" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/express.svg') }}" alt="">

                    @lang("simpson.steps-order__top.steps-order__top-item1")

                </div>

                <div class="steps-order__top-item">
                    <img width="100" height="100" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/standart.svg') }}" alt="">

                    @lang("simpson.steps-order__top.steps-order__top-item2")

                </div>

            </div>


{{--            Попап форма заказать портрет --}}
            <form class="formalization simpson-formalization">
                <div class="formalization__block">
                    <div class="formalization__block--top">
                        <div class="formalization__block--top-inner">
                            <div class="simpson-titleBlock">

                                @lang("simpson.simpson-formalization.simpson-titleBlock")

                                <img width="148" height="179" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/formTitle1.webp') }}" alt="">
                            </div>

                            <div class="formalization__col">


                                <div class="formalization-items">

{{--                                    /// Фон--}}
                                    <div class="formalization-item">
                                        <div class="formalization-box">
                                            <div class="formalization-tab">
                                                <img width="25" height="25" src="{{ asset(config('theme.current') . '/images/sharj/new/format7.svg') }}" alt="">


                                                @lang("simpson.formalization-items.formalization-item1.formalization-tab")


                                                <span class="tab-icon"></span>
                                            </div>
                                            <div class="formalization-content">
                                                <div class="formalization-content--inner no-padding">
                                                    <div class="background-list">
                                                        <div class="portrait-list__inner">
                                                            <div class="portrait-list__item">

                                                                @lang("simpson.formalization-items.formalization-item1.portrait-list__item1")



                                                                <div class="portrait-list__img">
                                                                    <picture>
                                                                        <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p1Min.webp') }}" type="image/webp">
                                                                        <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p1.webp') }}" type="image/webp">
                                                                        <img width="157" height="116" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p1.webp') }}" alt="">
                                                                    </picture>
                                                                    <div class="zoom-in">
                                                                        <svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                            <g clip-path="url(#clip0_1809_176)">
                                                                                <path d="M8.32 7.95235L5.50025 5.13259C5.96077 4.58878 6.24001 3.8868 6.24001 3.12C6.24001 1.39953 4.84046 0 3.12 0C1.39954 0 0 1.39953 0 3.12C0 4.84048 1.39953 6.24001 3.12 6.24001C3.8868 6.24001 4.58878 5.96077 5.13259 5.50025L7.95235 8.32L8.32 7.95235ZM3.12 5.72C1.68645 5.72 0.520006 4.55356 0.520006 3.12C0.520006 1.68645 1.68645 0.520006 3.12 0.520006C4.55356 0.520006 5.72 1.68645 5.72 3.12C5.72 4.55356 4.55356 5.72 3.12 5.72Z" fill="white"/>
                                                                                <rect width="3.34173" height="0.367003" transform="matrix(-2.0104e-05 -1 1 2.0104e-05 2.91992 4.76074)" fill="white"/>
                                                                                <rect width="3.34173" height="0.351069" transform="matrix(-1 -2.03991e-05 -2.05255e-05 -1 4.76172 3.22949)" fill="white"/>
                                                                            </g>
                                                                            <defs>
                                                                                <clipPath id="clip0_1809_176">
                                                                                    <rect width="8" height="8" fill="white"/>
                                                                                </clipPath>
                                                                            </defs>
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="portrait-list__item">

                                                                @lang("simpson.formalization-items.formalization-item1.portrait-list__item2")

                                                                <div class="portrait-list__img">
                                                                    <picture>
                                                                        <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p2Min.webp') }}" type="image/webp">
                                                                        <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p2.webp') }}" type="image/webp">
                                                                        <img width="157" height="116" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p2.webp') }}" alt="">
                                                                    </picture>
                                                                    <div class="zoom-in">
                                                                        <svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                            <g clip-path="url(#clip0_1809_176)">
                                                                                <path d="M8.32 7.95235L5.50025 5.13259C5.96077 4.58878 6.24001 3.8868 6.24001 3.12C6.24001 1.39953 4.84046 0 3.12 0C1.39954 0 0 1.39953 0 3.12C0 4.84048 1.39953 6.24001 3.12 6.24001C3.8868 6.24001 4.58878 5.96077 5.13259 5.50025L7.95235 8.32L8.32 7.95235ZM3.12 5.72C1.68645 5.72 0.520006 4.55356 0.520006 3.12C0.520006 1.68645 1.68645 0.520006 3.12 0.520006C4.55356 0.520006 5.72 1.68645 5.72 3.12C5.72 4.55356 4.55356 5.72 3.12 5.72Z" fill="white"/>
                                                                                <rect width="3.34173" height="0.367003" transform="matrix(-2.0104e-05 -1 1 2.0104e-05 2.91992 4.76074)" fill="white"/>
                                                                                <rect width="3.34173" height="0.351069" transform="matrix(-1 -2.03991e-05 -2.05255e-05 -1 4.76172 3.22949)" fill="white"/>
                                                                            </g>
                                                                            <defs>
                                                                                <clipPath id="clip0_1809_176">
                                                                                    <rect width="8" height="8" fill="white"/>
                                                                                </clipPath>
                                                                            </defs>
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="portrait-list__item">

                                                                @lang("simpson.formalization-items.formalization-item1.portrait-list__item3")


                                                                <div class="portrait-list__img">
                                                                    <picture>
                                                                        <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p3Min.webp') }}" type="image/webp">
                                                                        <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p3.webp') }}" type="image/webp">
                                                                        <img width="157" height="116" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p3.webp') }}" alt="">
                                                                    </picture>
                                                                    <div class="zoom-in">
                                                                        <svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                            <g clip-path="url(#clip0_1809_176)">
                                                                                <path d="M8.32 7.95235L5.50025 5.13259C5.96077 4.58878 6.24001 3.8868 6.24001 3.12C6.24001 1.39953 4.84046 0 3.12 0C1.39954 0 0 1.39953 0 3.12C0 4.84048 1.39953 6.24001 3.12 6.24001C3.8868 6.24001 4.58878 5.96077 5.13259 5.50025L7.95235 8.32L8.32 7.95235ZM3.12 5.72C1.68645 5.72 0.520006 4.55356 0.520006 3.12C0.520006 1.68645 1.68645 0.520006 3.12 0.520006C4.55356 0.520006 5.72 1.68645 5.72 3.12C5.72 4.55356 4.55356 5.72 3.12 5.72Z" fill="white"/>
                                                                                <rect width="3.34173" height="0.367003" transform="matrix(-2.0104e-05 -1 1 2.0104e-05 2.91992 4.76074)" fill="white"/>
                                                                                <rect width="3.34173" height="0.351069" transform="matrix(-1 -2.03991e-05 -2.05255e-05 -1 4.76172 3.22949)" fill="white"/>
                                                                            </g>
                                                                            <defs>
                                                                                <clipPath id="clip0_1809_176">
                                                                                    <rect width="8" height="8" fill="white"/>
                                                                                </clipPath>
                                                                            </defs>
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="portrait-list__item">

                                                                @lang("simpson.formalization-items.formalization-item1.portrait-list__item4")

                                                                <div class="portrait-list__img">
                                                                    <picture>
                                                                        <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p4Min.webp') }}" type="image/webp">
                                                                        <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p4.webp') }}" type="image/webp">
                                                                        <img width="157" height="116" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p4.webp') }}" alt="">
                                                                    </picture>
                                                                    <div class="zoom-in">
                                                                        <svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                            <g clip-path="url(#clip0_1809_176)">
                                                                                <path d="M8.32 7.95235L5.50025 5.13259C5.96077 4.58878 6.24001 3.8868 6.24001 3.12C6.24001 1.39953 4.84046 0 3.12 0C1.39954 0 0 1.39953 0 3.12C0 4.84048 1.39953 6.24001 3.12 6.24001C3.8868 6.24001 4.58878 5.96077 5.13259 5.50025L7.95235 8.32L8.32 7.95235ZM3.12 5.72C1.68645 5.72 0.520006 4.55356 0.520006 3.12C0.520006 1.68645 1.68645 0.520006 3.12 0.520006C4.55356 0.520006 5.72 1.68645 5.72 3.12C5.72 4.55356 4.55356 5.72 3.12 5.72Z" fill="white"/>
                                                                                <rect width="3.34173" height="0.367003" transform="matrix(-2.0104e-05 -1 1 2.0104e-05 2.91992 4.76074)" fill="white"/>
                                                                                <rect width="3.34173" height="0.351069" transform="matrix(-1 -2.03991e-05 -2.05255e-05 -1 4.76172 3.22949)" fill="white"/>
                                                                            </g>
                                                                            <defs>
                                                                                <clipPath id="clip0_1809_176">
                                                                                    <rect width="8" height="8" fill="white"/>
                                                                                </clipPath>
                                                                            </defs>
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                            </div>


                                                            <div class="portrait-list__item">

                                                                @lang("simpson.formalization-items.formalization-item1.portrait-list__item5")

                                                                <div class="portrait-list__img">
                                                                    <picture>
                                                                        <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p5Min.webp') }}" type="image/webp">
                                                                        <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p5.webp') }}" type="image/webp">
                                                                        <img width="157" height="116" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p5.webp') }}" alt="">
                                                                    </picture>
                                                                    <div class="zoom-in">
                                                                        <svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                            <g clip-path="url(#clip0_1809_176)">
                                                                                <path d="M8.32 7.95235L5.50025 5.13259C5.96077 4.58878 6.24001 3.8868 6.24001 3.12C6.24001 1.39953 4.84046 0 3.12 0C1.39954 0 0 1.39953 0 3.12C0 4.84048 1.39953 6.24001 3.12 6.24001C3.8868 6.24001 4.58878 5.96077 5.13259 5.50025L7.95235 8.32L8.32 7.95235ZM3.12 5.72C1.68645 5.72 0.520006 4.55356 0.520006 3.12C0.520006 1.68645 1.68645 0.520006 3.12 0.520006C4.55356 0.520006 5.72 1.68645 5.72 3.12C5.72 4.55356 4.55356 5.72 3.12 5.72Z" fill="white"/>
                                                                                <rect width="3.34173" height="0.367003" transform="matrix(-2.0104e-05 -1 1 2.0104e-05 2.91992 4.76074)" fill="white"/>
                                                                                <rect width="3.34173" height="0.351069" transform="matrix(-1 -2.03991e-05 -2.05255e-05 -1 4.76172 3.22949)" fill="white"/>
                                                                            </g>
                                                                            <defs>
                                                                                <clipPath id="clip0_1809_176">
                                                                                    <rect width="8" height="8" fill="white"/>
                                                                                </clipPath>
                                                                            </defs>
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                            </div>


                                                            <div class="portrait-list__item">

                                                                @lang("simpson.formalization-items.formalization-item1.portrait-list__item6")

                                                                <div class="portrait-list__img">
                                                                    <picture>
                                                                        <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p6Min.webp') }}" type="image/webp">
                                                                        <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p6.webp') }}" type="image/webp">
                                                                        <img width="157" height="116" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p6.webp') }}" alt="">
                                                                    </picture>
                                                                    <div class="zoom-in">
                                                                        <svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                            <g clip-path="url(#clip0_1809_176)">
                                                                                <path d="M8.32 7.95235L5.50025 5.13259C5.96077 4.58878 6.24001 3.8868 6.24001 3.12C6.24001 1.39953 4.84046 0 3.12 0C1.39954 0 0 1.39953 0 3.12C0 4.84048 1.39953 6.24001 3.12 6.24001C3.8868 6.24001 4.58878 5.96077 5.13259 5.50025L7.95235 8.32L8.32 7.95235ZM3.12 5.72C1.68645 5.72 0.520006 4.55356 0.520006 3.12C0.520006 1.68645 1.68645 0.520006 3.12 0.520006C4.55356 0.520006 5.72 1.68645 5.72 3.12C5.72 4.55356 4.55356 5.72 3.12 5.72Z" fill="white"/>
                                                                                <rect width="3.34173" height="0.367003" transform="matrix(-2.0104e-05 -1 1 2.0104e-05 2.91992 4.76074)" fill="white"/>
                                                                                <rect width="3.34173" height="0.351069" transform="matrix(-1 -2.03991e-05 -2.05255e-05 -1 4.76172 3.22949)" fill="white"/>
                                                                            </g>
                                                                            <defs>
                                                                                <clipPath id="clip0_1809_176">
                                                                                    <rect width="8" height="8" fill="white"/>
                                                                                </clipPath>
                                                                            </defs>
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                            </div>


                                                            <div class="portrait-list__item">

                                                                @lang("simpson.formalization-items.formalization-item1.portrait-list__item7")

                                                                <div class="portrait-list__img">
                                                                    <picture>
                                                                        <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p7Min.webp') }}" type="image/webp">
                                                                        <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p7.webp') }}" type="image/webp">
                                                                        <img width="157" height="116" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p7.webp') }}" alt="">
                                                                    </picture>
                                                                    <div class="zoom-in">
                                                                        <svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                            <g clip-path="url(#clip0_1809_176)">
                                                                                <path d="M8.32 7.95235L5.50025 5.13259C5.96077 4.58878 6.24001 3.8868 6.24001 3.12C6.24001 1.39953 4.84046 0 3.12 0C1.39954 0 0 1.39953 0 3.12C0 4.84048 1.39953 6.24001 3.12 6.24001C3.8868 6.24001 4.58878 5.96077 5.13259 5.50025L7.95235 8.32L8.32 7.95235ZM3.12 5.72C1.68645 5.72 0.520006 4.55356 0.520006 3.12C0.520006 1.68645 1.68645 0.520006 3.12 0.520006C4.55356 0.520006 5.72 1.68645 5.72 3.12C5.72 4.55356 4.55356 5.72 3.12 5.72Z" fill="white"/>
                                                                                <rect width="3.34173" height="0.367003" transform="matrix(-2.0104e-05 -1 1 2.0104e-05 2.91992 4.76074)" fill="white"/>
                                                                                <rect width="3.34173" height="0.351069" transform="matrix(-1 -2.03991e-05 -2.05255e-05 -1 4.76172 3.22949)" fill="white"/>
                                                                            </g>
                                                                            <defs>
                                                                                <clipPath id="clip0_1809_176">
                                                                                    <rect width="8" height="8" fill="white"/>
                                                                                </clipPath>
                                                                            </defs>
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                            </div>


                                                            <div class="portrait-list__item">

                                                                @lang("simpson.formalization-items.formalization-item1.portrait-list__item8")

                                                                <div class="portrait-list__img">
                                                                    <picture>
                                                                        <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p8Min.webp') }}" type="image/webp">
                                                                        <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p8.webp') }}" type="image/webp">
                                                                        <img width="157" height="116" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/p8.webp') }}" alt="">
                                                                    </picture>
                                                                    <div class="zoom-in">
                                                                        <svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                            <g clip-path="url(#clip0_1809_176)">
                                                                                <path d="M8.32 7.95235L5.50025 5.13259C5.96077 4.58878 6.24001 3.8868 6.24001 3.12C6.24001 1.39953 4.84046 0 3.12 0C1.39954 0 0 1.39953 0 3.12C0 4.84048 1.39953 6.24001 3.12 6.24001C3.8868 6.24001 4.58878 5.96077 5.13259 5.50025L7.95235 8.32L8.32 7.95235ZM3.12 5.72C1.68645 5.72 0.520006 4.55356 0.520006 3.12C0.520006 1.68645 1.68645 0.520006 3.12 0.520006C4.55356 0.520006 5.72 1.68645 5.72 3.12C5.72 4.55356 4.55356 5.72 3.12 5.72Z" fill="white"/>
                                                                                <rect width="3.34173" height="0.367003" transform="matrix(-2.0104e-05 -1 1 2.0104e-05 2.91992 4.76074)" fill="white"/>
                                                                                <rect width="3.34173" height="0.351069" transform="matrix(-1 -2.03991e-05 -2.05255e-05 -1 4.76172 3.22949)" fill="white"/>
                                                                            </g>
                                                                            <defs>
                                                                                <clipPath id="clip0_1809_176">
                                                                                    <rect width="8" height="8" fill="white"/>
                                                                                </clipPath>
                                                                            </defs>
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                            </div>


                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="formalization-prompt">
                                            <div class="formalization-prompt--wrapper">
                                                <div class="formalization-prompt--inner">
                                                    <img width="56" height="58" src="{{ asset(config('theme.current') . '/images/sharj/new/ficon1.webp') }}" alt="" />

                                                    <p>
                                                        @lang("simpson.formalization-items.formalization-item1.formalization-prompt--inner.p")
                                                    </p>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

{{--                                    /// Количество людей--}}

                                    @include('partials.simpsons.form.persons')
{{--                                    //// Вид портрета (Канвас)--}}

                                    <div class="formalization-item">
                                        <div class="formalization-box">
                                            <div class="formalization-tab">
                                                <img width="25" height="25" src="{{ asset(config('theme.current') . '/images/sharj/new/format1.svg') }}" alt="">

                                                @lang("simpson.formalization-items.formalization-item3.formalization-tab")

                                                <span class="tab-icon"></span>
                                            </div>
                                            <div class="formalization-content">
                                                <div class="formalization-content--inner">
                                                    <div class="kviz-c-group kviz-full">
                                                        <div class="kviz-group__item kviz-grid">

                                                            <div class="kviz-radio js-checkbox kviz-radio_active">
                                                                <div class="check"></div>
                                                                <label>

                                                                    <span>
                                                                        @lang("simpson.formalization-items.formalization-item3.kviz-radio1.span")
                                                                    </span>

                                                                    <input
                                                                        type="radio"
                                                                        name="types"
                                                                        value="@lang("simpson.formalization-items.formalization-item3.kviz-radio1.value_for_input") Эконом (плотность 280г/м2)">

                                                                    <picture class="kviz-image">
                                                                        <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/simpsonHolst.webp') }}" type="image/webp">
                                                                        <img width="112" height="119" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/simpsonHolst.webp') }}" alt="">
                                                                    </picture>

                                                                </label>
                                                            </div>

                                                            <div class="kviz-radio js-checkbox">
                                                                <div class="check"></div>
                                                                <label>

                                                                    <span>
                                                                        @lang("simpson.formalization-items.formalization-item3.kviz-radio2.span")
                                                                    </span>


                                                                    <input
                                                                        type="radio"
                                                                        name="types"
                                                                        value="@lang("simpson.formalization-items.formalization-item3.kviz-radio2.value_for_input")">

                                                                    <picture class="kviz-image">
                                                                        <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/simpsonPaper.webp') }}" type="image/webp">
                                                                        <img width="112" height="119" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/simpsonPaper.webp') }}" alt="">
                                                                    </picture>

                                                                </label>
                                                            </div>


                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="formalization-prompt">
                                            <div class="formalization-prompt--wrapper">
                                                <div class="formalization-prompt--inner">
                                                    <img width="56" height="58" src="{{ asset(config('theme.current') . '/images/sharj/new/ficon1.webp') }}" alt="" />

                                                    <p>
                                                        @lang("simpson.formalization-items.formalization-item3.formalization-prompt--inner.p")
                                                    </p>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

{{--                                    /// Загрузить фото--}}

                                    @include('partials.simpsons.form.photo')

{{--                                    ////  Размеры--}}
                                    @include('partials.simpsons.form.sizes')

{{--                                    ////  Рамка--}}
                                    @include('partials.simpsons.form.frames')

{{--                                    /// Комментарии--}}
                                    @include('partials.simpsons.form.comments')

{{--                                    @include('partials.simpsons.form.form')--}}
{{--                                    @include('partials.simpsons.form.canvas')--}}
{{--                                    @include('partials.simpsons.form.art_decor')--}}



                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="formalization__block--bottom">
                        <div class="formalization__final">
                            @include('partials.simpsons.form.final')
                        </div>
                    </div>

                </div>
            </form>




        </div>
    </div>
    <div class="custom-shape-divider-bottom-1685740923">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M600,112.77C268.63,112.77,0,65.52,0,7.23V120H1200V7.23C1200,65.52,931.37,112.77,600,112.77Z" class="shape-fill shape-fill-white"></path>
        </svg>
    </div>
</section>

<section class="service-info section-block service-info__simpson">
    <div class="section-frame">
        <div class="section-inner">
            <div class="sectionDefault-tabs">

                <div class="sectionDefault-tabs__item active">
                    @lang("simpson.service-info__simpson.sectionDefault-tabs__item1")
                </div>

                <div class="sectionDefault-tabs__item">
                    @lang("simpson.service-info__simpson.sectionDefault-tabs__item2")
                </div>

            </div>
            <div class="service-info__content">
                <div class="service-info__block">

                    <div class="service-info__row">
                        <div class="image-group">
                            <picture>
                                <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/service1Min.webp') }}" type="image/webp">
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/service1.webp') }}" type="image/webp">
                                <img width="315" height="275" src="{{ asset(config('theme.current') . '/images/sharj/new/service1.webp') }}" alt="">
                            </picture>
                            <picture>
                                <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/service2Min.webp') }}" type="image/webp">
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/service2.webp') }}" type="image/webp">
                                <img width="315" height="275" src="{{ asset(config('theme.current') . '/images/sharj/new/service2.webp') }}" alt="">
                            </picture>
                        </div>
                        <div class="content">


                            <div class="content-item">
                                <div class="p name">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="10" cy="10" r="10" fill="white"/>
                                        <circle cx="10" cy="10" r="10" fill="#02BC4D"/>
                                        <g clip-path="url(#clip0_1534_1841)">
                                            <path d="M9.08659 14.3879C8.86917 14.3879 8.65925 14.3061 8.49868 14.1586L5.28114 11.2022C4.92752 10.8773 4.90441 10.3276 5.22928 9.97331C5.55479 9.62094 6.10396 9.5972 6.4582 9.92146L9.03036 12.2856L13.4856 7.36929C13.8079 7.01317 14.3584 6.98631 14.7145 7.30869C15.07 7.63107 15.0975 8.18086 14.7745 8.53698L9.73135 14.103C9.57515 14.2742 9.35836 14.3767 9.12657 14.3879C9.11283 14.3879 9.09971 14.3879 9.08659 14.3879Z" fill="white"/>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_1534_1841">
                                                <rect width="10" height="7.30476" fill="white" transform="translate(5 7.08301)"/>
                                            </clipPath>
                                        </defs>
                                    </svg>


                                    <p>
                                        @lang("simpson.service-info__simpson.service-info__row1.content-item1.p1")
                                    </p>

                                </div>


                                <p>
                                    @lang("simpson.service-info__simpson.service-info__row1.content-item1.p2")
                                </p>

                            </div>


                            <div class="content-item">
                                <div class="p name">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="10" cy="10" r="10" fill="white"/>
                                        <circle cx="10" cy="10" r="10" fill="#02BC4D"/>
                                        <g clip-path="url(#clip0_1534_1841)">
                                            <path d="M9.08659 14.3879C8.86917 14.3879 8.65925 14.3061 8.49868 14.1586L5.28114 11.2022C4.92752 10.8773 4.90441 10.3276 5.22928 9.97331C5.55479 9.62094 6.10396 9.5972 6.4582 9.92146L9.03036 12.2856L13.4856 7.36929C13.8079 7.01317 14.3584 6.98631 14.7145 7.30869C15.07 7.63107 15.0975 8.18086 14.7745 8.53698L9.73135 14.103C9.57515 14.2742 9.35836 14.3767 9.12657 14.3879C9.11283 14.3879 9.09971 14.3879 9.08659 14.3879Z" fill="white"/>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_1534_1841">
                                                <rect width="10" height="7.30476" fill="white" transform="translate(5 7.08301)"/>
                                            </clipPath>
                                        </defs>
                                    </svg>

                                    <p>
                                        @lang("simpson.service-info__simpson.service-info__row1.content-item2.p1")
                                    </p>

                                </div>

                                <p>
                                    @lang("simpson.service-info__simpson.service-info__row1.content-item2.p2")
                                </p>

                            </div>

                        </div>



                    </div>

                    <div class="service-info__row">
                        <div class="image-group">
                            <picture>
                                <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/service3Min.webp') }}" type="image/webp">
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/service3.webp') }}" type="image/webp">
                                <img width="315" height="275" src="{{ asset(config('theme.current') . '/images/sharj/new/service3.webp') }}" alt="">
                            </picture>
                            <picture>
                                <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/service4Min.webp') }}" type="image/webp">
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/service4.webp') }}" type="image/webp">
                                <img width="315" height="275" src="{{ asset(config('theme.current') . '/images/sharj/new/service4.webp') }}" alt="">
                            </picture>
                        </div>
                        <div class="content">
                            <div class="content-item">
                                <div class="p name">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="10" cy="10" r="10" fill="white"/>
                                        <circle cx="10" cy="10" r="10" fill="#02BC4D"/>
                                        <g clip-path="url(#clip0_1534_1841)">
                                            <path d="M9.08659 14.3879C8.86917 14.3879 8.65925 14.3061 8.49868 14.1586L5.28114 11.2022C4.92752 10.8773 4.90441 10.3276 5.22928 9.97331C5.55479 9.62094 6.10396 9.5972 6.4582 9.92146L9.03036 12.2856L13.4856 7.36929C13.8079 7.01317 14.3584 6.98631 14.7145 7.30869C15.07 7.63107 15.0975 8.18086 14.7745 8.53698L9.73135 14.103C9.57515 14.2742 9.35836 14.3767 9.12657 14.3879C9.11283 14.3879 9.09971 14.3879 9.08659 14.3879Z" fill="white"/>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_1534_1841">
                                                <rect width="10" height="7.30476" fill="white" transform="translate(5 7.08301)"/>
                                            </clipPath>
                                        </defs>
                                    </svg>

                                    <p>
                                        @lang("simpson.service-info__simpson.service-info__row2.content-item1.p1")
                                    </p>

                                </div>


                                <p>
                                    @lang("simpson.service-info__simpson.service-info__row2.content-item1.p2")
                                </p>

                            </div>


                            <div class="content-item">
                                <div class="p name">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="10" cy="10" r="10" fill="white"/>
                                        <circle cx="10" cy="10" r="10" fill="#02BC4D"/>
                                        <g clip-path="url(#clip0_1534_1841)">
                                            <path d="M9.08659 14.3879C8.86917 14.3879 8.65925 14.3061 8.49868 14.1586L5.28114 11.2022C4.92752 10.8773 4.90441 10.3276 5.22928 9.97331C5.55479 9.62094 6.10396 9.5972 6.4582 9.92146L9.03036 12.2856L13.4856 7.36929C13.8079 7.01317 14.3584 6.98631 14.7145 7.30869C15.07 7.63107 15.0975 8.18086 14.7745 8.53698L9.73135 14.103C9.57515 14.2742 9.35836 14.3767 9.12657 14.3879C9.11283 14.3879 9.09971 14.3879 9.08659 14.3879Z" fill="white"/>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_1534_1841">
                                                <rect width="10" height="7.30476" fill="white" transform="translate(5 7.08301)"/>
                                            </clipPath>
                                        </defs>
                                    </svg>

                                    <p>
                                        @lang("simpson.service-info__simpson.service-info__row2.content-item2.p1")
                                    </p>

                                </div>

                                <p>
                                    @lang("simpson.service-info__simpson.service-info__row2.content-item2.p2")
                                </p>

                            </div>

                        </div>
                    </div>


                    <div class="service-info__row">
                        <div class="image-group">
                            <picture>
                                <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/service5Min.webp') }}" type="image/webp">
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/service5.webp') }}" type="image/webp">
                                <img width="315" height="275" src="{{ asset(config('theme.current') . '/images/sharj/new/service5.webp') }}" alt="">
                            </picture>
                            <picture>
                                <source media="(max-width: 576px)" srcset="{{ asset(config('theme.current') . '/images/sharj/new/service6Min.webp') }}" type="image/webp">
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/service6.webp') }}" type="image/webp">
                                <img width="315" height="275" src="{{ asset(config('theme.current') . '/images/sharj/new/service6.webp') }}" alt="">
                            </picture>
                        </div>
                        <div class="content">

                            <div class="content-item">
                                <div class="p name">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="10" cy="10" r="10" fill="white"/>
                                        <circle cx="10" cy="10" r="10" fill="#02BC4D"/>
                                        <g clip-path="url(#clip0_1534_1841)">
                                            <path d="M9.08659 14.3879C8.86917 14.3879 8.65925 14.3061 8.49868 14.1586L5.28114 11.2022C4.92752 10.8773 4.90441 10.3276 5.22928 9.97331C5.55479 9.62094 6.10396 9.5972 6.4582 9.92146L9.03036 12.2856L13.4856 7.36929C13.8079 7.01317 14.3584 6.98631 14.7145 7.30869C15.07 7.63107 15.0975 8.18086 14.7745 8.53698L9.73135 14.103C9.57515 14.2742 9.35836 14.3767 9.12657 14.3879C9.11283 14.3879 9.09971 14.3879 9.08659 14.3879Z" fill="white"/>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_1534_1841">
                                                <rect width="10" height="7.30476" fill="white" transform="translate(5 7.08301)"/>
                                            </clipPath>
                                        </defs>
                                    </svg>

                                    <p>
                                        @lang("simpson.service-info__simpson.service-info__row3.content-item1.p1")
                                    </p>

                                </div>

                                <p>
                                    @lang("simpson.service-info__simpson.service-info__row3.content-item1.p2")
                                </p>

                            </div>


                            <div class="content-item">
                                <div class="p name">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="10" cy="10" r="10" fill="white"/>
                                        <circle cx="10" cy="10" r="10" fill="#02BC4D"/>
                                        <g clip-path="url(#clip0_1534_1841)">
                                            <path d="M9.08659 14.3879C8.86917 14.3879 8.65925 14.3061 8.49868 14.1586L5.28114 11.2022C4.92752 10.8773 4.90441 10.3276 5.22928 9.97331C5.55479 9.62094 6.10396 9.5972 6.4582 9.92146L9.03036 12.2856L13.4856 7.36929C13.8079 7.01317 14.3584 6.98631 14.7145 7.30869C15.07 7.63107 15.0975 8.18086 14.7745 8.53698L9.73135 14.103C9.57515 14.2742 9.35836 14.3767 9.12657 14.3879C9.11283 14.3879 9.09971 14.3879 9.08659 14.3879Z" fill="white"/>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_1534_1841">
                                                <rect width="10" height="7.30476" fill="white" transform="translate(5 7.08301)"/>
                                            </clipPath>
                                        </defs>
                                    </svg>

                                    <p>
                                        @lang("simpson.service-info__simpson.service-info__row3.content-item2.p1")
                                    </p>

                                </div>

                                <p>
                                    @lang("simpson.service-info__simpson.service-info__row3.content-item2.p2")
                                </p>

                            </div>


                        </div>
                    </div>

                </div>


                <div class="service-info__block hidden-block">
                    <div class="service-info__row">
                        <div class="image-group">
                            <picture>
                                <!-- <source media="(max-width: 576px)" srcset="images/sharj/new/service1Min.webp" type="image/webp"> -->
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/service7.webp') }}" type="image/webp">
                                <img width="315" height="275" src="{{ asset(config('theme.current') . '/images/sharj/new/service7.webp') }}" alt="">
                            </picture>
                            <picture>
                                <!-- <source media="(max-width: 576px)" srcset="images/sharj/new/service2Min.webp" type="image/webp"> -->
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/service8.webp') }}" type="image/webp">
                                <img width="315" height="275" src="{{ asset(config('theme.current') . '/images/sharj/new/service8.webp') }}" alt="">
                            </picture>
                        </div>
                        <div class="content">
                            <div class="content-text">

                                <div class="title">
                                    @lang("simpson.service-info__simpson.service-info__row3.content-text.title")
                                    <p>Оформления в багетную рамку</p>
                                    <p>от 30 €</p>
                                </div>

                                <p>
                                    @lang("simpson.service-info__simpson.service-info__row3.content-text.p")
                                    Мы не используем стандартные программы, <br>
                                    что делает картину индивидуальной!
                                </p>

                            </div>
                        </div>
                    </div>



                    <div class="service-info__row">
                        <div class="image-group">
                            <picture>
                                <!-- <source media="(max-width: 576px)" srcset="images/sharj/new/service3Min.webp" type="image/webp"> -->
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/service9.webp') }}" type="image/webp">
                                <img width="315" height="275" src="{{ asset(config('theme.current') . '/images/sharj/new/service9.webp') }}" alt="">
                            </picture>
                            <picture>
                                <!-- <source media="(max-width: 576px)" srcset="images/sharj/new/service4Min.webp" type="image/webp"> -->
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/service10.webp') }}" type="image/webp">
                                <img width="315" height="275" src="{{ asset(config('theme.current') . '/images/sharj/new/service10.webp') }}" alt="">
                            </picture>
                        </div>
                        <div class="content">
                            <div class="content-text">

                                <div class="title">
                                    @lang("simpson.service-info__simpson.service-info__row4.content-text.title")
                                    <p>Пропитка картины маслом или акрилом!</p>
                                    <p>5 €</p>
                                </div>

                                <p>
                                    @lang("simpson.service-info__simpson.service-info__row4.content-text.p")
                                    Для продления срока службы картины
                                </p>


                            </div>
                        </div>
                    </div>


                    <div class="service-info__row">
                        <div class="image-group">
                            <picture>
                                <!-- <source media="(max-width: 576px)" srcset="images/sharj/new/service5Min.webp" type="image/webp"> -->
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/service11.webp') }}" type="image/webp">
                                <img width="315" height="275" src="{{ asset(config('theme.current') . '/images/sharj/new/service11.webp') }}" alt="">
                            </picture>
                            <picture>
                                <!-- <source media="(max-width: 576px)" srcset="images/sharj/new/service6Min.webp" type="image/webp"> -->
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/service12.webp') }}" type="image/webp">
                                <img width="315" height="275" src="{{ asset(config('theme.current') . '/images/sharj/new/service12.webp') }}" alt="">
                            </picture>
                        </div>
                        <div class="content">
                            <div class="content-text">

                                <div class="title">

                                    @lang("simpson.service-info__simpson.service-info__row5.content-text.title")
                                    <p>Експресс изготовление
                                        (в течении 1 дня)</p>
                                    <p>5 €</p>

                                </div>

                                <p>
                                    @lang("simpson.service-info__simpson.service-info__row5.content-text.p")
                                    Мы не используем стандартные программы, <br>
                                    что делает картину индивидуальной!
                                </p>


                            </div>
                        </div>
                    </div>


                </div>
            </div>

            <div class="service-info__bottom">

                <p>
                    @lang("simpson.service-info__bottom.p")
                </p>

                <svg width="111" height="84" viewBox="0 0 111 84" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0.378491 74.9313C-0.0533335 75.2756 -0.12428 75.9048 0.220024 76.3366L5.83082 83.3736C6.17512 83.8054 6.8043 83.8763 7.23612 83.532C7.66794 83.1877 7.73889 82.5585 7.39459 82.1267L2.40722 75.8716L8.66231 70.8843C9.09413 70.54 9.16508 69.9108 8.82077 69.479C8.47647 69.0471 7.84729 68.9762 7.41547 69.3205L0.378491 74.9313ZM109.5 1C108.504 1.08757 108.504 1.08727 108.504 1.08731C108.504 1.08776 108.504 1.08813 108.504 1.08904C108.504 1.09086 108.504 1.094 108.505 1.09846C108.505 1.10737 108.507 1.12154 108.508 1.14087C108.511 1.17953 108.516 1.23884 108.521 1.3181C108.531 1.4766 108.546 1.71483 108.559 2.02701C108.587 2.65144 108.613 3.57146 108.605 4.74115C108.589 7.08109 108.439 10.4169 107.899 14.382C106.82 22.3177 104.186 32.7396 97.9828 42.744C91.788 52.7348 82.0173 62.3373 66.6056 68.6424C51.181 74.9527 30.0502 77.9824 1.11396 74.7195L0.889857 76.7069C30.0626 79.9965 51.5486 76.9632 67.3629 70.4934C83.19 64.0184 93.2802 54.1235 99.6826 43.7979C106.077 33.4859 108.776 22.7723 109.881 14.6516C110.434 10.5886 110.589 7.16595 110.605 4.75453C110.613 3.54854 110.586 2.59464 110.557 1.93907C110.543 1.61126 110.528 1.35796 110.516 1.18495C110.511 1.09844 110.506 1.03199 110.502 0.986334C110.5 0.963505 110.499 0.945872 110.498 0.933526C110.497 0.927352 110.497 0.922499 110.497 0.918979C110.497 0.91722 110.496 0.915583 110.496 0.914701C110.496 0.913399 110.496 0.91243 109.5 1Z" fill="#FA7846"/>
                </svg>


                <a href="#" class="default-btn js-examples">
                    @lang("simpson.service-info__bottom.a")
                </a>

            </div>

        </div>
    </div>
</section>

</main>

<!----
<footer class="vz-art vz-footer">
    <div class="section-frame">
        <div class="vz-art footer-bar">
            <div class="vz-art footer-info">
                <div class="vz-art footer-logo">
                    <a href="#" class="vz-art footer-logo__item" aria-label="footer logo">
                        <img src="{{ asset(config('theme.current') . '/images/logo.svg') }}" alt="img" loading="lazy" />
                    </a>
                    <div class="vz-art footer-social footer-social_mob">
                        <ul>
                            <li>
                                <a href="#" target="_blank" aria-label="footer social" rel="noopener noreferrer">
                                    <i class="fa-facebook"></i>
                                </a>
                            </li>
                            <li>
                                <a href="#" target="_blank" aria-label="footer social" rel="noopener noreferrer">
                                    <i class="fa-instagram"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="vz-art footer-contact">
                    <a href="tel:+371270444470" class="vz-art footer-contact__item">
                        <svg>
                            <use xlink:href="sprite.svg#footer-phone"></use>
                        </svg>
                        <span>
                <b>+371 270444470</b>
              </span>
                    </a>
                    <a href="tel:+37125444744" class="vz-art footer-contact__item">
                        <svg>
                            <use xlink:href="sprite.svg#footer-phone"></use>
                        </svg>
                        <span>
                <b>+371 25444744</b>
              </span>
                    </a>
                    <a href="mailto:orders@viarcanvas.com" class="vz-art footer-contact__item">
                        <svg>
                            <use xlink:href="sprite.svg#footer-mail"></use>
                        </svg>
                        <span>orders@viarcanvas.com</span>
                    </a>
                    <a href="#" target="_blank" class="vz-art footer-contact__item" rel="noopener noreferrer">
                        <svg>
                            <use xlink:href="sprite.svg#map"></use>
                        </svg>
                        <span>Riga, Lubanas 65, LV1073 Daugavpils, Stacijas 129R,
                LV5401</span>
                    </a>
                </div>
                <div class="vz-art footer-social footer-social_pc">
                    <h3>Мы в соцсетях:</h3>
                    <ul>
                        <li>
                            <a href="#" target="_blank" aria-label="footer social facebok" rel="noopener noreferrer">
                                <i class="fa-facebook"></i>
                            </a>
                        </li>
                        <li>
                            <a href="#" target="_blank" aria-label="footer social instagram" rel="noopener noreferrer">
                                <i class="fa-instagram"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="vz-art footer-list">
                <h3><span class="vz-art faq-icon"></span> Стили портрета</h3>
                <ul>
                    <li>
                        <a href="#">Печать фото на холсте</a>
                    </li>
                    <li>
                        <a href="#">Портрет по фото</a>
                    </li>
                    <li>
                        <a href="#">Каталог готовых картин</a>
                    </li>
                    <li>
                        <a href="#">Карикатура/шарж</a>
                    </li>
                    <li>
                        <a href="#">Модульные картины</a>
                    </li>
                    <li>
                        <a href="#">Дигитальный портрет</a>
                    </li>
                    <li>
                        <a href="#">Портрет маслом и акрилом</a>
                    </li>
                    <li>
                        <a href="#">Репродукции художников</a>
                    </li>
                    <li>
                        <a href="#">Коллаж на холсте</a>
                    </li>
                    <li>
                        <a href="#">Портрет в образе</a>
                    </li>
                </ul>
            </div>
            <div class="vz-art footer-list">
                <h3><span class="vz-art faq-icon"></span> Виды портрета</h3>
                <ul>
                    <li>
                        <a href="#">Женский портрет</a>
                    </li>
                    <li>
                        <a href="#">Мужской портрет</a>
                    </li>
                    <li>
                        <a href="#">Парный портрет</a>
                    </li>
                    <li>
                        <a href="#">Семейный портрет</a>
                    </li>
                    <li>
                        <a href="#">Свадебный портрет</a>
                    </li>
                    <li>
                        <a href="#">Портрет родителей</a>
                    </li>
                    <li>
                        <a href="#">Портрет руководителя</a>
                    </li>
                    <li>
                        <a href="#">Портрет на юбилей</a>
                    </li>
                    <li>
                        <a href="#">Детский портрет</a>
                    </li>
                    <li>
                        <a href="#">Портрет питомца</a>
                    </li>
                </ul>
            </div>
            <div class="vz-art footer-list">
                <h3><span class="vz-art faq-icon"></span> Информация</h3>
                <ul>
                    <li>
                        <a href="#">Подарочная карта</a>
                    </li>
                    <li>
                        <a href="#">Оплата и доставка</a>
                    </li>
                    <li>
                        <a href="#">Акции</a>
                    </li>
                    <li>
                        <a href="#">Сервис клиента</a>
                    </li>
                    <li>
                        <a href="#">Советы и идеи</a>
                    </li>
                    <li>
                        <a href="#">Все стили</a>
                    </li>
                    <li>
                        <a href="#">Размеры и цены</a>
                    </li>
                    <li>
                        <a href="#">Обрамление картин</a>
                    </li>
                    <li>
                        <a href="#">Контакты</a>
                    </li>
                    <li>
                        <a href="#">Вопросы и ответы</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="vz-art footer-copy">
        <div class="section-frame">
            <div class="vz-art footer-copy__content">
                <p class="vz-art copy-title">© 2021 VIARCANVAS ®</p>
                <div class="vz-art footer-pay">
                    <img src="{{ asset(config('theme.current') . '/images/icon/visa.svg') }}" alt="img" loading="lazy" />
                    <img src="{{ asset(config('theme.current') . '/images/icon/master.svg') }}" alt="img" loading="lazy" />
                </div>
            </div>
        </div>
    </div>
</footer>
-->

<a href="#top" class="vz-art scroll-button anchor" aria-label="anchor link">
    <i class="fa-arrow-next"></i>
</a>

<div class="vz-art popup-frame target-frame">


    <form action="#" method="POST" enctype="multipart/form-data"
          class="vz-art js-popup target-box popup-photo submit-form">
        <i class="vz-art fa-close popup-close"></i>

        <div class="vz-art page-title popup-photo-title h2_old">
            @lang("simpson.target-frame.popup-photo-title")
        </div>

        <div class="vz-art popup-group">

            <div class="vz-art kviz-input">

                <p class="vz-art kviz-input__title">
                    @lang("simpson.target-frame.kviz-input1.kviz-input__title")
                </p>

                <div class="vz-art page-input__item">
                    <input type="email" name="email" placeholder="E-mail" required="" />
                    <svg class="vz-art kviz-input__icon">
                        <use xlink:href="sprite.svg#mail"></use>
                    </svg>
                </div>
            </div>

            <div class="vz-art kviz-input">

                <p class="vz-art kviz-input__title">
                    @lang("simpson.target-frame.kviz-input2.kviz-input__title")
                </p>

                <div class="vz-art page-input__item phone-input">
                    <div class="country-item country-item-active">
                        <img src="{{ asset(config('theme.current') . '/images/flag/lv.svg') }}" alt="img" loading="lazy" />
                    </div>
                    <div class="country-list">
                        <div class="country-item">
                            <img src="{{ asset(config('theme.current') . '/images/flag/lv.svg') }}" alt="img" loading="lazy" />
                            <p>Латвия</p>
                            <span data-mask="+371 99 99-99-99" data-placeholder="+371 00 00-00-00">+371</span>
                        </div>
                        <div class="country-item">
                            <img src="{{ asset(config('theme.current') . '/images/flag/lt.svg') }}" alt="img" loading="lazy" />
                            <p>Литва</p>
                            <span data-mask="+370 999 9-99-99" data-placeholder="+370 000 0-00-00">+370</span>
                        </div>
                        <div class="country-item">
                            <img src="{{ asset(config('theme.current') . '/images/flag/es.svg') }}" alt="img" loading="lazy" />
                            <p>Эстония</p>
                            <span data-mask="+372 99 999-99-99" data-placeholder="+372 00 000-00-00">+359</span>
                        </div>
                        <div class="country-item">
                            <img src="{{ asset(config('theme.current') . '/images/flag/fl.svg') }}" alt="img" loading="lazy" />
                            <p>Финляндия</p>
                            <span data-mask="+358 99 999-99-99" data-placeholder="+358 00 000-00-00">+359</span>
                        </div>
                        <div class="country-item">
                            <img src="{{ asset(config('theme.current') . '/images/flag/no.svg') }}" alt="img" loading="lazy" />
                            <p>Норвегия</p>
                            <span data-mask="+47 99 999-99-99" data-placeholder="+47 00 000-00-00">+359</span>
                        </div>
                        <div class="country-item">
                            <img src="{{ asset(config('theme.current') . '/images/flag/pl.svg') }}" alt="img" loading="lazy" />
                            <p>Польша</p>
                            <span data-mask="+48 99 999-99-99" data-placeholder="+48 00 000-00-00">+359</span>
                        </div>
                        <div class="country-item">
                            <img src="{{ asset(config('theme.current') . '/images/flag/ge.svg') }}" alt="img" loading="lazy" />
                            <p>Германия</p>
                            <span data-mask="+49 99 999-99-99" data-placeholder="+49 00 000-00-00">+359</span>
                        </div>
                        <div class="country-item">
                            <img src="{{ asset(config('theme.current') . '/images/flag/da.svg') }}" alt="img" loading="lazy" />
                            <p>Дания</p>
                            <span data-mask="+45 99 999-99-99" data-placeholder="+45 00 000-00-00">+359</span>
                        </div>
                        <div class="country-item">
                            <img src="{{ asset(config('theme.current') . '/images/flag/cz.svg') }}" alt="img" loading="lazy" />
                            <p>Чехия</p>
                            <span data-mask="+420 99 999-99-99" data-placeholder="+420 00 000-00-00">+359</span>
                        </div>
                        <div class="country-item">
                            <img src="{{ asset(config('theme.current') . '/images/flag/se.svg') }}" alt="img" loading="lazy" />
                            <p>Швеция</p>
                            <span data-mask="+46 99 999-99-99" data-placeholder="+46 00 000-00-00">+359</span>
                        </div>
                        <div class="country-item">
                            <img src="{{ asset(config('theme.current') . '/images/flag/nl.svg') }}" alt="img" loading="lazy" />
                            <p>Нидерланды</p>
                            <span data-mask="+31 99 999-99-99" data-placeholder="+31 00 000-00-00">+359</span>
                        </div>
                        <div class="country-item">
                            <img src="{{ asset(config('theme.current') . '/images/flag/be.svg') }}" alt="img" loading="lazy" />
                            <p>Бельгия</p>
                            <span data-mask="+32 99 999-99-99" data-placeholder="+32 00 000-00-00">+359</span>
                        </div>
                    </div>
                    <input type="text" name="phone" class="input-mask input-counter" autocomplete="off" placeholder="+371"
                           required="" />
                    <svg class="vz-art kviz-input__icon">
                        <use xlink:href="{{ asset(config('theme.current') . '/sprite.svg#phone') }}"></use>
                    </svg>
                </div>
            </div>


        </div>
        <div class="vz-art popup-grid">
            <div class="vz-art popup-grid__file">
                <div class="vz-art kviz-input">

                    <p class="vz-art kviz-input__title">
                        @lang("portrait_buy_form.step1_desc")
                    </p>

                    <div class="file-save file-save__popup">
                        <div class="file-save__item js-file-preview">
                            <svg>
                                <use xlink:href="{{ asset(config('theme.current') . '/sprite.svg#save') }}"></use>
                            </svg>
                            <div class="file-save__title">

                                <p>
                                    @lang("simpson.load_image")
                                </p>

                                <span>
                                     @lang("simpson.click_to_add_photo")
                                </span>

                            </div>
                        </div>
                        <div class="file-save__item js-file-upload">
                            <svg>
                                <use xlink:href="sprite.svg#picture"></use>
                            </svg>
                            <div class="file-save__title">
                                <p>photo_34567.jpg</p>
                                <span>2 Mb</span>
                            </div>
                        </div>
                        <input type="file" id="#file13" class="file-input" name="file" accept="image/*,image/heif,image/heic" aria-label="file input" />
                    </div>
                    <div class="file-save file-save__popup file-save_hide">
                        <div class="file-save__item js-file-preview">
                            <svg>
                                <use xlink:href="{{ asset(config('theme.current') . '/sprite.svg#save') }}"></use>
                            </svg>
                            <div class="file-save__title">

                                <p>
                                    @lang("simpson.load_image")
                                </p>

                                <span>
                                     @lang("simpson.click_to_add_photo")
                                </span>

                            </div>
                        </div>
                        <div class="file-save__item js-file-upload">
                            <svg>
                                <use xlink:href="sprite.svg#picture"></use>
                            </svg>
                            <div class="file-save__title">
                                <p>photo_34567.jpg</p>
                                <span>2 Mb</span>
                            </div>
                        </div>
                        <input type="file" id="#file22" class="file-input file-input_hide" name="file2" accept="image/*,image/heif,image/heic"
                               aria-label="file input" />
                    </div>
                    <div class="file-save file-save__popup file-save_hide">
                        <div class="file-save__item js-file-preview">
                            <svg>
                                <use xlink:href="{{ asset(config('theme.current') . '/sprite.svg#save') }}"></use>
                            </svg>
                            <div class="file-save__title">

                                <p>
                                    @lang("simpson.load_image")
                                </p>

                                <span>
                                    @lang("simpson.click_to_add_photo")
                                </span>


                            </div>
                        </div>
                        <div class="file-save__item js-file-upload">
                            <svg>
                                <use xlink:href="sprite.svg#picture"></use>
                            </svg>
                            <div class="file-save__title">
                                <p>photo_34567.jpg</p>
                                <span>2 Mb</span>
                            </div>
                        </div>
                        <input type="file" class="file-input file-input_hide" name="file3[]" accept="image/*,image/heif,image/heic"
                               aria-label="file input" />
                    </div>
                    <div class="file-save file-save__popup file-save_hide">
                        <div class="file-save__item js-file-preview">
                            <svg>
                                <use xlink:href="{{ asset(config('theme.current') . '/sprite.svg#save') }}"></use>
                            </svg>
                            <div class="file-save__title">


                                <p>
                                    @lang("simpson.load_image")
                                </p>

                                <span>
                                     @lang("simpson.click_to_add_photo")
                                 </span>


                            </div>
                        </div>
                        <div class="file-save__item js-file-upload">
                            <svg>
                                <use xlink:href="sprite.svg#picture"></use>
                            </svg>
                            <div class="file-save__title">
                                <p>photo_34567.jpg</p>
                                <span>2 Mb</span>
                            </div>
                        </div>
                        <input type="file" class="file-input file-input_hide" name="file4[]" accept="image/*,image/heif,image/heic"
                               aria-label="file input" />
                    </div>
                    <div class="file-save file-save__popup file-save_hide">
                        <div class="file-save__item js-file-preview">
                            <svg>
                                <use xlink:href="{{ asset(config('theme.current') . '/sprite.svg#save') }}"></use>
                            </svg>
                            <div class="file-save__title">

                                <p>
                                    @lang("simpson.load_image")
                                </p>

                                <span>
                                     @lang("simpson.click_to_add_photo")
                                 </span>


                            </div>
                        </div>
                        <div class="file-save__item js-file-upload">
                            <svg>
                                <use xlink:href="sprite.svg#picture"></use>
                            </svg>
                            <div class="file-save__title">
                                <p>photo_34567.jpg</p>
                                <span>2 Mb</span>
                            </div>
                        </div>
                        <input type="file" class="file-input file-input_hide" name="file5[]" accept="image/*,image/heif,image/heic"
                               aria-label="file input" />
                    </div>
                    <div class="file-save file-save__popup file-save_hide">
                        <div class="file-save__item js-file-preview">
                            <svg>
                                <use xlink:href="{{ asset(config('theme.current') . '/sprite.svg#save') }}"></use>
                            </svg>
                            <div class="file-save__title">

                                <p>
                                    @lang("simpson.load_image")
                                </p>

                                <span>
                                     @lang("simpson.click_to_add_photo")
                                 </span>

                            </div>
                        </div>
                        <div class="file-save__item js-file-upload">
                            <svg>
                                <use xlink:href="sprite.svg#picture"></use>
                            </svg>
                            <div class="file-save__title">
                                <p>photo_34567.jpg</p>
                                <span>2 Mb</span>
                            </div>
                        </div>
                        <input type="file" class="file-input file-input_hide" name="file6[]" accept="image/*,image/heif,image/heic"
                               aria-label="file input" />
                    </div>
                    <div class="file-save file-save__popup file-save_hide">
                        <div class="file-save__item js-file-preview">
                            <svg>
                                <use xlink:href="{{ asset(config('theme.current') . '/sprite.svg#save') }}"></use>
                            </svg>
                            <div class="file-save__title">

                                <p>
                                    @lang("simpson.load_image")
                                </p>

                                <span>
                                      @lang("simpson.click_to_add_photo")
                                </span>

                            </div>
                        </div>
                        <div class="file-save__item js-file-upload">
                            <svg>
                                <use xlink:href="sprite.svg#picture"></use>
                            </svg>
                            <div class="file-save__title">
                                <p>photo_34567.jpg</p>
                                <span>2 Mb</span>
                            </div>
                        </div>
                        <input type="file" class="file-input file-input_hide" name="file7[]" accept="image/*,image/heif,image/heic"
                               aria-label="file input" />
                    </div>
                    <div class="file-save file-save__popup file-save_hide">
                        <div class="file-save__item js-file-preview">
                            <svg>
                                <use xlink:href="{{ asset(config('theme.current') . '/sprite.svg#save') }}"></use>
                            </svg>
                            <div class="file-save__title">

                                <p>
                                    @lang("simpson.load_image")
                                </p>

                                <span>
                                      @lang("simpson.click_to_add_photo")
                                </span>


                            </div>
                        </div>
                        <div class="file-save__item js-file-upload">
                            <svg>
                                <use xlink:href="sprite.svg#picture"></use>
                            </svg>
                            <div class="file-save__title">
                                <p>photo_34567.jpg</p>
                                <span>2 Mb</span>
                            </div>
                        </div>
                        <input type="file" class="file-input file-input_hide" name="file8[]" accept="image/*,image/heif,image/heic"
                               aria-label="file input" />
                    </div>
                    <div class="file-save file-save__popup file-save_hide">
                        <div class="file-save__item js-file-preview">
                            <svg>
                                <use xlink:href="{{ asset(config('theme.current') . '/sprite.svg#save') }}"></use>
                            </svg>
                            <div class="file-save__title">

                                <p>
                                    @lang("simpson.load_image")
                                </p>

                                <span>
                                      @lang("simpson.click_to_add_photo")
                                </span>

                            </div>
                        </div>
                        <div class="file-save__item js-file-upload">
                            <svg>
                                <use xlink:href="sprite.svg#picture"></use>
                            </svg>
                            <div class="file-save__title">
                                <p>photo_34567.jpg</p>
                                <span>2 Mb</span>
                            </div>
                        </div>
                        <input type="file" class="file-input file-input_hide" name="file9[]" accept="image/*,image/heif,image/heic"
                               aria-label="file input" />
                    </div>
                    <div class="file-save file-save__popup file-save_hide">
                        <div class="file-save__item js-file-preview">
                            <svg>
                                <use xlink:href="{{ asset(config('theme.current') . '/sprite.svg#save') }}"></use>
                            </svg>
                            <div class="file-save__title">

                                <p>
                                    @lang("simpson.load_image")
                                </p>

                                <span>
                                      @lang("simpson.click_to_add_photo")
                                </span>


                            </div>
                        </div>
                        <div class="file-save__item js-file-upload">
                            <svg>
                                <use xlink:href="sprite.svg#picture"></use>
                            </svg>
                            <div class="file-save__title">
                                <p>photo_34567.jpg</p>
                                <span>2 Mb</span>
                            </div>
                        </div>
                        <input type="file" class="file-input file-input_hide" name="file10[]" accept="image/*,image/heif,image/heic"
                               aria-label="file input" />
                    </div>
                </div>
                <a href="#" class="file-add">
                    <svg>
                        <use xlink:href="sprite.svg#plus"></use>
                    </svg>

                    <span>
                        @lang("portrait_buy_form.step1_load_more")
                    </span>
                </a>
                <div class="kviz-input kviz-input_mob">
                    <p class="vz-art kviz-input__title">
                        Укажите желаемый размер потрета
                    </p>
                    <select name="size" class="select-page select-size">
                        <option value="100x70 см">100x70 см</option>
                        <option value="200x70 см">200x70 см</option>
                        <option value="300x70 см">300x70 см</option>
                        <option value="400x70 см">400x70 см</option>
                    </select>
                </div>
                <div class="kviz-input kviz-input_mob">
                    <p class="vz-art kviz-input__title">Укажите желаемый стиль</p>
                    <select name="styles" class="select-page select-style">
                        <option value="Бьюти шарж">Бьюти шарж</option>
                        <option value="Бьюти арт">Бьюти арт</option>
                        <option value="Бьюти гранж">Бьюти гранж</option>
                        <option value="Бьюти шарж">Бьюти шарж</option>
                    </select>
                </div>
                <div class="box">
                    <div class="h3_old">Подарочная упаковка</div>
                    <div class="box-list">
                        <div class="kviz-radio box-radio kviz-radio_active js-checkbox">
                            <div class="vz-art check"></div>
                            <label>
                                <span>Без упаковки</span>
                                <input type="radio" checked="checked" name="box" value="Без упаковки" />
                            </label>
                        </div>
                        <div class="kviz-radio box-radio js-checkbox">
                            <div class="vz-art check"></div>
                            <label>
                                <span>Бумага</span>
                                <input type="radio" name="box" value="Бумага" />
                            </label>
                        </div>
                        <div class="kviz-radio box-radio js-checkbox">
                            <div class="vz-art check"></div>
                            <label>
                                <span>Кейс</span>
                                <input type="radio" name="box" value="Кейс" />
                            </label>
                        </div>
                    </div>
                </div>
                <label class="popup-photo__submit">
                    <input type="submit" />@lang("stock.submit")
                </label>
                <div class="kviz-politics">
                    <svg>
                        <use xlink:href="sprite.svg#lock"></use>
                    </svg>
                    <p>
                        Ваши данные в безопасности. Нажимая на кнопку «Отправить», вы
                        соглашаетесь с
                        <a href="politics.html" target="_blank" rel="noopener noreferrer">Политикой конфиденциальности</a>
                    </p>
                </div>
            </div>
            <div class="vz-art popup-grid__select kviz-input_pc">
                <div class="vz-art kviz-input">
                    <p class="vz-art kviz-input__title">
                        Укажите желаемый размер потрета
                    </p>
                    <select name="size" class="select-page select-size">
                        <option value="100x70 см">100x70 см</option>
                        <option value="200x70 см">200x70 см</option>
                        <option value="300x70 см">300x70 см</option>
                        <option value="400x70 см">400x70 см</option>
                    </select>
                </div>
                <div class="vz-art kviz-input">
                    <p class="vz-art kviz-input__title">Укажите желаемый стиль</p>
                    <select name="styles" class="select-page select-style">
                        <option value="Бьюти шарж">Бьюти шарж</option>
                        <option value="Бьюти арт">Бьюти арт</option>
                        <option value="Бьюти гранж">Бьюти гранж</option>
                        <option value="Бьюти шарж">Бьюти шарж</option>
                    </select>
                </div>
            </div>
        </div>
        <picture>
            <source srcset="./images/portrait-form.webp') }}" type="image/webp" />
            <source srcset="./images/portrait-form.png') }}" />
            <img src="./images/portrait-form.png') }}" class="vz-art photo-mokap" alt="img" loading="lazy" />
        </picture>
    </form>
    <form action="#" method="POST" class="vz-art js-popup target-box popup-login">
        <i class="vz-art fa-close popup-close"></i>
        <div class="vz-art page-title popup-photo-title h2_old">Войти</div>
        <div class="vz-art popup-log-group">
            <div class="vz-art kviz-input">
                <p class="vz-art kviz-input__title">Укажите Ваш E-mail</p>
                <div class="vz-art page-input__item">
                    <input type="email" name="email" placeholder="E-mail" required="" />
                    <svg class="vz-art kviz-input__icon">
                        <use xlink:href="sprite.svg#mail"></use>
                    </svg>
                </div>
            </div>
            <div class="vz-art kviz-input">
                <p class="vz-art kviz-input__title">Введите пароль</p>
                <div class="vz-art page-input__item">
                    <input type="password" name="password" required="" />
                    <svg class="vz-art kviz-input__icon" style="width: 24px; height: 24px">
                        <use xlink:href="sprite.svg#lock-popup"></use>
                    </svg>
                </div>
            </div>
        </div>
        <div class="popup-log-save">
            <div class="vz-art popup-log-check log-check_active js-checkbox">
          <span class="vz-art log-check">
            <svg class="vz-art kviz-input__icon">
              <use xlink:href="sprite.svg#log"></use>
            </svg>
          </span>
                <p>Запомнить меня</p>
                <input type="checkbox" checked="checked" name="save" />
            </div>
            <a href="#">Забыли пароль?</a>
        </div>
        <label class="vz-art log-sub"> <input type="submit" />Войти </label>
        <div class="vz-art log-creat">
            <p>Еще не зарегистрированы?</p>
            <a href="#" class="js-popup-registration">Создать аккаунт</a>
        </div>
        <div class="vz-art log-social">
            <h3>
                <span>Или</span>
            </h3>
            <p>Войти с помощью:</p>
            <ul>
                <li>
                    <a href="#">
                        <i class="fa-facebook"></i>
                    </a>
                </li>
            </ul>
        </div>
    </form>
    <form action="#" method="POST" class="vz-art js-popup target-box popup-registration">
        <i class="vz-art fa-close popup-close"></i>
        <div class="vz-art page-title popup-photo-title h2_old">Создать аккаунт</div>
        <div class="vz-art registration-group">
            <div class="vz-art kviz-input">
                <p class="vz-art kviz-input__title">
                    Введите Ваше Имя<span>*</span>
                </p>
                <div class="vz-art page-input__item">
                    <input type="text" name="name" placeholder="Имя" required="" />
                    <svg class="vz-art kviz-input__icon" style="width: 24px; height: 24px">
                        <use xlink:href="sprite.svg#user-popup"></use>
                    </svg>
                </div>
            </div>
            <div class="vz-art kviz-input">
                <p class="vz-art kviz-input__title">Введите Фамилию</p>
                <div class="vz-art page-input__item">
                    <input type="text" name="surname" placeholder="Фамилия" />
                    <svg class="vz-art kviz-input__icon" style="width: 24px; height: 24px">
                        <use xlink:href="sprite.svg#user-popup"></use>
                    </svg>
                </div>
            </div>
            <div class="vz-art kviz-input">
                <p class="vz-art kviz-input__title">
                    Укажите Ваш E-mail<span>*</span>
                </p>
                <div class="vz-art page-input__item">
                    <input type="email" name="email" placeholder="E-mail" required="" />
                    <svg class="vz-art kviz-input__icon">
                        <use xlink:href="sprite.svg#mail"></use>
                    </svg>
                </div>
            </div>
            <div class="vz-art kviz-input">
                <p class="vz-art kviz-input__title">
                    Укажите Ваш номер телефона<span>*</span>
                </p>
                <div class="vz-art page-input__item phone-input">
                    <div class="country-item country-item-active">
                        <img src="./images/flag/lv.svg') }}" alt="img" loading="lazy" />
                    </div>
                    <div class="country-list">
                        <div class="country-item">
                            <img src="./images/flag/lv.svg') }}" alt="img" loading="lazy" />
                            <p>Латвия</p>
                            <span data-mask="+371 99 99-99-99" data-placeholder="+371 00 00-00-00">+371</span>
                        </div>
                        <div class="country-item">
                            <img src="./images/flag/lt.svg') }}" alt="img" loading="lazy" />
                            <p>Литва</p>
                            <span data-mask="+370 999 9-99-99" data-placeholder="+370 000 0-00-00">+370</span>
                        </div>
                        <div class="country-item">
                            <img src="./images/flag/es.svg') }}" alt="img" loading="lazy" />
                            <p>Эстония</p>
                            <span data-mask="+372 99 999-99-99" data-placeholder="+372 00 000-00-00">+359</span>
                        </div>
                        <div class="country-item">
                            <img src="./images/flag/fl.svg') }}" alt="img" loading="lazy" />
                            <p>Финляндия</p>
                            <span data-mask="+358 99 999-99-99" data-placeholder="+358 00 000-00-00">+359</span>
                        </div>
                        <div class="country-item">
                            <img src="./images/flag/no.svg') }}" alt="img" loading="lazy" />
                            <p>Норвегия</p>
                            <span data-mask="+47 99 999-99-99" data-placeholder="+47 00 000-00-00">+359</span>
                        </div>
                        <div class="country-item">
                            <img src="./images/flag/pl.svg') }}" alt="img" loading="lazy" />
                            <p>Польша</p>
                            <span data-mask="+48 99 999-99-99" data-placeholder="+48 00 000-00-00">+359</span>
                        </div>
                        <div class="country-item">
                            <img src="./images/flag/ge.svg') }}" alt="img" loading="lazy" />
                            <p>Германия</p>
                            <span data-mask="+49 99 999-99-99" data-placeholder="+49 00 000-00-00">+359</span>
                        </div>
                        <div class="country-item">
                            <img src="./images/flag/da.svg') }}" alt="img" loading="lazy" />
                            <p>Дания</p>
                            <span data-mask="+45 99 999-99-99" data-placeholder="+45 00 000-00-00">+359</span>
                        </div>
                        <div class="country-item">
                            <img src="./images/flag/cz.svg') }}" alt="img" loading="lazy" />
                            <p>Чехия</p>
                            <span data-mask="+420 99 999-99-99" data-placeholder="+420 00 000-00-00">+359</span>
                        </div>
                        <div class="country-item">
                            <img src="./images/flag/se.svg') }}" alt="img" loading="lazy" />
                            <p>Швеция</p>
                            <span data-mask="+46 99 999-99-99" data-placeholder="+46 00 000-00-00">+359</span>
                        </div>
                        <div class="country-item">
                            <img src="./images/flag/nl.svg') }}" alt="img" loading="lazy" />
                            <p>Нидерланды</p>
                            <span data-mask="+31 99 999-99-99" data-placeholder="+31 00 000-00-00">+359</span>
                        </div>
                        <div class="country-item">
                            <img src="./images/flag/be.svg') }}" alt="img" loading="lazy" />
                            <p>Бельгия</p>
                            <span data-mask="+32 99 999-99-99" data-placeholder="+32 00 000-00-00">+359</span>
                        </div>
                    </div>
                    <input type="text" name="phone" class="input-mask input-counter" autocomplete="off" placeholder="+371"
                           required="" />
                    <svg class="vz-art kviz-input__icon">
                        <use xlink:href="sprite.svg#phone"></use>
                    </svg>
                </div>
            </div>
            <div class="vz-art kviz-input">
                <p class="vz-art kviz-input__title">Введите пароль<span>*</span></p>
                <div class="vz-art page-input__item">
                    <input type="password" name="password" required="" />
                    <svg class="vz-art kviz-input__icon" style="width: 24px; height: 24px">
                        <use xlink:href="sprite.svg#lock-popup"></use>
                    </svg>
                </div>
            </div>
            <div class="vz-art kviz-input">
                <p class="vz-art kviz-input__title">
                    Повторите пароль<span>*</span>
                </p>
                <div class="vz-art page-input__item">
                    <input type="password" name="password2" required="" />
                    <svg class="vz-art kviz-input__icon" style="width: 24px; height: 24px">
                        <use xlink:href="sprite.svg#lock-popup"></use>
                    </svg>
                </div>
            </div>
            <label class="registration-sub">
                <input type="submit" />Зарегистрироваться
            </label>
            <div class="kviz-politics registration-politics">
                <svg>
                    <use xlink:href="sprite.svg#lock"></use>
                </svg>
                <p>
                    Ваши данные в безопасности. Нажимая на кнопку «Отправить», вы
                    соглашаетесь с
                    <a href="politics.html" target="_blank" rel="noopener noreferrer">Политикой конфиденциальности</a>
                </p>
            </div>
        </div>
        <div class="vz-art log-social">
            <h3>
                <span>Или</span>
            </h3>
            <p>Зарегистрироваться с помощью</p>
            <ul>
                <li>
                    <a href="#">
                        <i class="fa-facebook"></i>
                    </a>
                </li>
            </ul>
        </div>
    </form>
    <div class="js-popup thanks">
        <div class="kviz-thanks">
            <img src="./images/icon/check-done.svg" class="kviz-thanks__icon" alt="img" loading="lazy" />
            <div class="kviz-thanks__title">
                <div class="h3_old">Спасибо за доверие!</div>
                <p>
                    Мы свяжемся с вами в ближайшее время. Время работы нашего офиса —
                    ежедневно, с&nbsp;9:00 до 23:00 по Москве
                </p>
            </div>
            <div class="kviz-thanks__info">
                <p>Если у вас есть срочный вопрос, напишите нам WhatsApp</p>
                <a href="#" target="_blank" rel="noopener noreferrer">
                    <svg>
                        <use xlink:href="sprite.svg#wh"></use>
                    </svg>
                    Написать WhatsApp
                </a>
            </div>
        </div>
    </div>
    <div class="js-popup target-box popup-rframe">
        <div class="popup-rframe--inner">
            <i class="vz-art fa-close popup-close"></i>
            <div class="pf-title"> Детали Рамы </div>
            <div class="pf-content">
                <div class="pf-img">
                    <picture>
                        <source srcset="https://viarcanvas.com/theme/viar/images/cardreproduction/frame.jpg" type="image/jpeg">
                        <img width="418" height="414" src="https://viarcanvas.com/theme/viar/images/cardreproduction/frame.jpg" alt="ViarCanvas">
                    </picture>
                </div>
                <div class="pf-info">
                    <ul>
                        <li>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_261_7572)">
                                    <path d="M19.7747 2.94941C19.4743 2.64902 18.9872 2.64902 18.6868 2.94941L6.21765 15.4186L1.31315 10.5141C1.01279 10.2136 0.525724 10.2136 0.225293 10.5141C-0.0750978 10.8145 -0.0750978 11.3015 0.225293 11.6019L5.67378 17.0504C5.97405 17.3507 6.46128 17.3508 6.76163 17.0504L19.7747 4.03727C20.0751 3.73684 20.0751 3.24981 19.7747 2.94941Z" fill="#1F9750"></path>
                                </g>
                                <defs>
                                    <clipPath id="clip0_261_7572">
                                        <rect width="20" height="20" fill="white"></rect>
                                    </clipPath>
                                </defs>
                            </svg>
                            <span>В наличии</span>
                        </li>
                        <li>
                            <span>Код:</span>
                            <span>17</span>
                        </li>
                        <li>
                            <span>@lang("gallery.material"):</span>
                            <span>Бронзовый</span>
                        </li>
                        <li>
                            <span>Оттенок:</span>
                            <span>5.2 cm</span>
                        </li>
                        <li>
                            <span>Ширина:</span>
                            <span>5.2 cm</span>
                        </li>
                        <li>
                            <span>Высота:</span>
                            <span>Материал:</span>
                        </li>
                        <li>
                            <span>Цена за погонный метр (с роботой):</span>
                            <span>10 €</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>



<!--
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous" type="4fae777018b264e4555e536c-text/javascript"></script>
 <script src="{{ ver_asset(env('THEME').'js/portrait_calc.js') }}"></script>
<script src="{{ver_asset(config('theme.current').'js/custom.js')}}"></script>

-->

@include(env('THEME_RESOURCES') . 'pages.simpsons.modal')


@include(config('theme.resource') . 'pages.gallery.zpart_viarcanvas_is')
