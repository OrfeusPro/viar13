<div class="tabs-item prices-sizes">
    <div class="height-content">
        <div class="tabs-height prices-sizes-content">
            <div class="title">
                <h2>{{ $canvas_price['title'] }}</h2>
            </div>
            <div class="prices-cintant">
                <h5>{{ $canvas_price['sub_title'] }}</h5>
                <img src="{{ asset('img/prices-img1.png') }}" alt="" class="prices-img1">
                {!! $canvas_price['top_text'] !!}
                <h5>{!! $canvas_price['sub_title3'] !!}</h5>
                <img src="{{ asset('img/prices-img2.png') }}" alt="" class="prices-img2">
                {!! $canvas_price['sub_title3_desc'] !!}
                <a href="{{ $canvas_price['goto_gall_link'] }}"><span>{{ $canvas_price['goto_gall'] }}</span></a>
                <h3>{{ $canvas_price['price_title'] }}</h3>
                <div class="list clearfix">
                    <img src="{{ asset('img/prices-img3.png') }}" alt="" class="prices-img3">
                    {!! $canvas_price['price_list'] !!}
                </div>
            </div>
        </div>
    </div>
    <a data-collapse="prices-sizes-content" class="collapse" href="javascript:void(0)"
        data-show="{{ trans('gl.show_btn') }}"
        data-hide="{{ trans('gl.hide_btn') }}"><span>{{ trans('gl.show_btn') }}</span></a>
</div>
