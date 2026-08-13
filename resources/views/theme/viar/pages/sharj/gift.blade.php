<style>
    /* .portrait-gift__grid .portrait-content {
        padding: 130px !important;
    }

    @media (max-width: 1440px) {
        .portrait-gift__grid .portrait-content {
            padding: 80px !important;
        }
    }

    @media (max-width: 768px) {
        .portrait-gift__grid .portrait-content {
            padding: 80px 20px 0 !important;
        }
    } */

    .portrait-gift__grid .portrait-img img {
        object-fit: contain;
    }

    .portrait-gift__grid .portrait-img picture {
        height: 100%;
        width: 100%;
    }
</style>
<section class="portrait-gift__section" >
    <div class="section-inner">
        <div class="portrait-gift__grid">
            <div class="portrait-content">
                <div class="portrait-content__inner">
                    <div class="page-title">
                        @lang('sharj.translate33')
                    </div>
                    <p>@lang('sharj.translate34') </p>
{{--                    <div class="default-btn js-simps-calc">@lang('sharj.translate1')</div>--}}
                    <a href="#sharj" class="default-btn">@lang('sharj.translate1')</a>

                    <div class="absolute-elements">
                        <div class="portrait-gift">
                            <img loading="lazy" width="149" height="138"
                                src="{{ asset(env('THEME') . 'images') }}/collage/gift.svg" alt="Viar Image">
                            <p> @lang('sharj.translate35')</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="portrait-img">
                <picture>
                    <source media="(max-width: 576px)"
                        srcset="{{ asset(env('THEME') . 'images') }}/sharj/new/categories/gift3Min.webp?v=1"
                        type="image/webp">
                    <source srcset="{{ asset(env('THEME') . 'images') }}/sharj/new/categories/gift3.webp?v=1"
                        type="image/webp">
                    <img width="800" height="451"
                        src="{{ asset(env('THEME') . 'images') }}/sharj/new/categories/gift3.webp?v=1" alt="">
                </picture>
            </div>
        </div>
    </div>
</section>
