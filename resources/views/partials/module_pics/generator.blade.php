<section class="generate" id="generator">
    <div class="generate-content">
        <div class="title">
            <p>ViarStudia</p>
            <h4>@lang('collage.index128')</h4>
        </div>
        <div class="gallery-photo">
            <div class="photo-content">
                <div class="slider-tabs" id="sl_tabs">
                    <ul class="tabs">
                        <li><a class="active" data-tabs="tabs-item1"
                                href="javascript:void(0)">@lang('modular_pictures.index23')</a>
                        </li>
                        <li><a data-tabs="tabs-item2" href="javascript:void(0)">@lang('modular_pictures.index24')</a>
                        </li>
                    </ul>
                    <div class="tabs-content" id="tab__content">
                        @include('partials.module_pics.tab1')
                        @include('partials.module_pics.tab2')
                    </div>
                </div>
                <div class="photo-filter">
                    @include('partials.module_pics.tab1_form')
                    @include('partials.module_pics.tab2_form')
                </div>
            </div>
        </div>
    </div>
</section>