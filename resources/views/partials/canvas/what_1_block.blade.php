<div class="tabs-item what-canvas active">
    <div class="height-content">
        <div class="tabs-height what-canvas-content">
            <div class="title">
                <h2>{!! $canvas_what['title'] !!}</h2>
            </div>
            <div class="what-items clearfix">
                <div class="what-item">
                    <img class="" src="{{ asset('img/what-item-img1.png') }}"
                        data-src="{{ asset('img/what-item-img1.png') }}" alt="">
                    {!! $canvas_what['top_text'] !!}
                </div>
                <div class="what-item">
                    <img class="" src="{{ asset('img/what-item-img2.png') }}"
                        data-src="{{ asset('img/what-item-img2.png') }}" alt="">
                    {!! $canvas_what['left_text'] !!}
                </div>
                <div class="what-item">
                    <img class="" src="{{ asset('img/what-item-img3.png') }}"
                        data-src="{{ asset('img/what-item-img3.png') }}" alt="">
                    {!! $canvas_what['right_text'] !!}
                </div>
                <div class="what-item">
                    <img class="" src="{{ asset('img/what-item-img4.png') }}"
                        data-src="{{ asset('img/what-item-img4.png') }}" alt="">
                    {!! $canvas_what['bot_left_text'] !!}
                </div>
                <div class="what-item">
                    <img class="" src="{{ asset('img/what-item-img5.png') }}"
                        data-src="{{ asset('img/what-item-img5.png') }}" alt="">
                    {!! $canvas_what['bot_right_text'] !!}
                </div>
                <div class="what-item">
                    <img class="" src="{{ asset('img/what-item-img6.png') }}"
                        data-src="{{ asset('img/what-item-img6.png') }}" alt="">
                    {!! $canvas_what['bot_bot_text'] !!}
                </div>
            </div>
        </div>
    </div>
    <a data-collapse="what-canvas-content" class="collapse" href="javascript:void(0)"
        data-show="{{ trans('gl.show_btn') }}" data-hide="{{ trans('gl.hide_btn') }}">
        <span data-show="{{ trans('gl.show_btn') }}"
            data-hide="{{ trans('gl.hide_btn') }}">{{ trans('gl.show_btn') }}</span>
    </a>
</div>
