<div class="tabs-item what-canvas active">
    <div class="height-content">
        <div class="tabs-height what-canvas-content">
            <div class="title">
                <h2>{!! $what_size['what_title'] !!}</h2>
            </div>
            <div class="what-items clearfix">
                <div class="what-item">
                    <img src="{{ asset('img/modular-item-img1.png') }}" alt="">
                    {!! $what_size['what_text1'] !!}
                </div>
                <div class="what-item">
                    <img src="{{ asset('img/modular-item-img2.png') }}" alt="">
                    {!! $what_size['what_text2'] !!}
                </div>
                <div class="what-item">
                    {!! $what_size['what_text3'] !!}
                </div>
            </div>
        </div>
    </div>
    <a data-collapse="what-canvas-content" class="collapse" data-show="{{ trans('gl.show_btn') }}"
        data-hide="{{ trans('gl.hide_btn') }}"
        href="javascript:void(0)"><span>{{ trans('gl.show_btn') }}</span></a>
</div>
