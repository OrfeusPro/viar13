<section class="create-picture">
    <div class="create-picture-content">
        <p>{!! trans('gl.cons_title') !!}</p>
        <div class="create-picture-items clearfix">
            <div class="create-picture-item">
                <img class="" src="{{ asset('img/create-picture-img3.png') }}"
                    data-src="{{ asset('img/create-picture-img3.png') }}" alt="">
            </div>
            <div class="create-picture-item">
                <img class="" src="{{ asset('img/create-picture-img4.png') }}"
                    data-src="{{ asset('img/create-picture-img4.png') }}" alt="">
            </div>
        </div>
        @if($cur_loc == '')
        <a href="/{{ $main_pages['modular_slug'] }}">{!! trans('gl.cons_link_text') !!}</a>
        @else
        <a href="/{{ $cur_loc }}/{{ $main_pages['modular_slug'] }}">{!! trans('gl.cons_link_text') !!}</a>
        @endif
        <i>{!! trans('gl.cons_bot_text') !!}</i>
    </div>
</section>
