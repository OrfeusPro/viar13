<div class="tabs-item shipping-time">
    <div class="height-content">
        <div class="tabs-height shipping-time-content">
            <div class="shipping-item1">
                <div class="title">
                    <img src="{{ asset('img/shipping-title-icon1.png') }}" alt="">
                    <h2>{{ $canvas_del_time['f_title'] }}</h2>
                </div>
                <div class="item-content clearfix">
                    <img src="{{ asset('img/shipping-item-img1.png') }}" alt="">
                    <div class="text">
                        {!! $canvas_del_time['f_text'] !!}
                    </div>
                </div>
            </div>
            <div class="shipping-item2">
                <div class="title">
                    <img src="{{ asset('img/shipping-title-icon2.png') }}" alt="">
                    <h2> {!! $canvas_del_time['s_title'] !!}</h2>
                </div>
                <div class="item-content clearfix">
                    <img src="{{ asset('img/shipping-item-img2.png') }}" alt="">
                    <div class="text">
                        {!! $canvas_del_time['s_text_left'] !!}
                    </div>
                    <div class="text-item">
                        <p>{!! $canvas_del_time['s_text_right'] !!}</p>
                    </div>
                </div>
            </div>
            <div class="shipping-item3">
                <div class="title">
                    <img src="{{ asset('img/shipping-title-icon3.png') }}" alt="">
                    <h2>{!! $canvas_del_time['th_title'] !!}</h2>
                </div>
                <h5>{!! $canvas_del_time['th_sub_title'] !!}</h5>
                <div class="shipping-sum clearfix">
                    <div class="sum-item">
                        <img src="{{ asset('img/sum-item-img1.png') }}" alt="">
                        <div>
                            {!! $canvas_del_time['th_left_text'] !!}
                        </div>
                    </div>
                    <div class="sum-item">
                        <img src="{{ asset('img/sum-item-img2.png') }}" alt="">
                        <div>
                            {!! $canvas_del_time['th_right_text'] !!}
                        </div>
                    </div>
                </div>
                {!! $canvas_del_time['th_bot_text'] !!}
                <div class="partner clearfix">
                    <img src="{{ asset('img/partner-img1.png') }}" alt="">
                    <img src="{{ asset('img/partner-img2.png') }}" alt="">
                    {!! $canvas_del_time['th_partners_list'] !!}
                </div>
            </div>
            <div class="shipping-item4">
                <div class="title">
                    <img src="{{ asset('img/shipping-title-icon4.png') }}" alt="">
                    <h2>{!! $canvas_del_time['for_title'] !!}</h2>
                </div>
                <div class="item-content clearfix">
                    <img src="{{ asset('img/shipping-item-img3.png') }}" alt="">
                    <div class="text">
                        <h5>{!! $canvas_del_time['for_top_text'] !!}</h5>
                        {!! $canvas_del_time['for_bot_text'] !!}
                        <div>
                            {!! $canvas_del_time['att_bot_text'] !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <a data-collapse="shipping-time-content" class="collapse" href="javascript:void(0)"
        data-show="{{ trans('gl.show_btn') }}"
        data-hide="{{ trans('gl.hide_btn') }}"><span>{{ trans('gl.show_btn') }}</span></a>
</div>
