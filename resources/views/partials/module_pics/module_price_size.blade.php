<div class="tabs-item prices-sizes">
    <div class="height-content">
        <div class="tabs-height prices-sizes-content">
            <div class="title">
                <h2>{{ $what_size['diff_size_title'] }}</h2>
            </div>
            <div class="prices-cintant">
                {!! $what_size['diff_size_text_top'] !!}
                <img src="{{ asset('img/prices-img4.png') }}"  class="prices-img1" @frontendAlt('partials/module_pics/module_price_size.blade.php', (asset('img/prices-img4.png')), '', '')>
                {!! $what_size['diff_size_text_center'] !!}
                <img src="{{ asset('img/prices-img5.png') }}"  class="prices-img2" @frontendAlt('partials/module_pics/module_price_size.blade.php', (asset('img/prices-img5.png')), '', '')>
                <h3>{!! $what_size['diff_size_bot_title'] !!}</h3>

                <div class="list clearfix">
                    <img src="{{ asset('img/prices-img6.png') }}"  class="prices-img3" @frontendAlt('partials/module_pics/module_price_size.blade.php', (asset('img/prices-img6.png')), '', '')>
                    {!! $what_size['diff_size_bot_list'] !!}
                </div>
            </div>
        </div>
    </div>
    <a data-collapse="prices-sizes-content" class="collapse" href="javascript:void(0)"
            data-show="{{ trans('gl.show_btn') }}"
            data-hide="{{ trans('gl.hide_btn') }}"><span>{{ trans('gl.show_btn') }}</span></a>
</div>
