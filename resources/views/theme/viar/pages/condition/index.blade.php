@include(config('theme.resource') . 'pages.condition.breads')

<div class="condition-page main-first">
    <div class="section-frame">
        <div class="condition-page__inner">
            <h1 class="page-title condition-page__title">
                @lang('pages.condition.title')
            </h1>
            <div class="condition-page__updated">
                @lang('pages.condition.last_modified', ['date' => $condition_updated_at ?? ''])
            </div>
            @lang('pages.condition.terms_viar')
            <div class="condition-list">
                <div class="condition-item">
                    <div class="title">
                        <img width="45" height="45" src="{{ asset(config('theme.current') . '/images/condition/icon1.svg') }}" alt="">
                        <h2>@lang('pages.condition.terms')</h2>
                    </div>
                    <div class="content">
                        <div class="text">
                            @lang('pages.condition.terms_text')
                        </div>
                    </div>
                </div>
                <div class="condition-item">
                    <div class="title">
                        <img width="45" height="45" src="{{ asset(config('theme.current') . '/images/condition/icon2.svg') }}" alt="">
                        <h2>@lang('pages.condition.register')</h2>
                    </div>
                    <div class="content">
                        <div class="text">
                            @lang('pages.condition.register_text')
                        </div>
                    </div>
                </div>
                <div class="condition-item">
                    <div class="title">
                        <img width="45" height="45" src="{{ asset(config('theme.current') . '/images/condition/icon3.svg') }}" alt="">
                        <h2>@lang('pages.condition.prices')</h2>
                    </div>
                    <div class="content">
                        <div class="text">
                            @lang('pages.condition.prices_text')
                        </div>
                    </div>
                </div>
                <div class="condition-item">
                    <div class="title">
                        <img width="45" height="45" src="{{ asset(config('theme.current') . '/images/condition/icon4.svg') }}" alt="">
                        <h2>@lang('pages.condition.delivery')</h2>
                    </div>
                    <div class="content">
                        <div class="text">
                            @lang('pages.condition.delivery_text')
                        </div>
                    </div>
                </div>
                <div class="condition-item">
                    <div class="title">
                        <img width="45" height="45" src="{{ asset(config('theme.current') . '/images/condition/icon5.svg') }}" alt="">
                        <h2>@lang('pages.condition.confidentiality')</h2>
                    </div>
                    <div class="content">
                        <div class="text">
                            @lang('pages.condition.confidentiality_text')
                        </div>
                    </div>
                </div>
                <div class="condition-item">
                    <div class="title">
                        <img width="45" height="45" src="{{ asset(config('theme.current') . '/images/condition/icon6.svg') }}" alt="">
                        <h2>@lang('pages.condition.gift_card')</h2>
                    </div>
                    <div class="content">
                        <div class="text">
                            @lang('pages.condition.gift_card_text')
                        </div>
                    </div>
                </div>
                <div class="condition-item">
                    <div class="title">
                        <img width="45" height="45" src="{{ asset(config('theme.current') . '/images/condition/icon7.svg') }}" alt="">
                        <h2>@lang('pages.condition.property')</h2>
                    </div>
                    <div class="content">
                        <div class="text">
                            @lang('pages.condition.property_text')
                        </div>
                    </div>
                </div>
                <div class="condition-item">
                    <div class="title">
                        <img width="45" height="45" src="{{ asset(config('theme.current') . '/images/condition/icon8.svg') }}" alt="">
                        <h2>@lang('pages.condition.return_product')</h2>
                    </div>
                    <div class="content">
                        <div class="text">
                            @lang('pages.condition.return_product_text')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include(config('theme.resource') . 'pages.index.faq9')

@include(config('theme.resource') . 'pages.gallery.zpart_viarcanvas_is')
