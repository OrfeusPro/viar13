<section class="packaging">
    <div class="title">
        {!! $canv_bot['up_title'] !!}
    </div>
    <div class="packaging-content">
        <div class="packaging-item clearfix">
            <div class="text">
                {!! $canv_bot['up_left_text'] !!}
            </div>
            <img src="{{ asset('img/packaging-img.png') }}" alt="" class="packaging-img">
        </div>
        <div class="package-size clearfix">
            <h4>{!! $canv_bot['up_def_title'] !!}</h4>
            {!! $canv_bot['up_sizes_list'] !!}
        </div>
    </div>
</section>