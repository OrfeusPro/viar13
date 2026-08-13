<div class="tabs-item requirements">
    <div class="height-content">
        <div class="tabs-height requirements-content">
            <div class="title">
                <h2>{!! $canvas_req['req_title'] !!}</h2>
            </div>
            <div class="requirements-file clearfix">
                <div class="file-item">
                    <img src="{{ asset('img/file-item-img1.png') }}" alt="">
                    {!! $canvas_req['req_item1'] !!}
                </div>
                <div class="file-item">
                    <img src="{{ asset('img/file-item-img2.png') }}" alt="">
                    {!! $canvas_req['req_item2'] !!}
                </div>
                <div class="file-item">
                    <img src="{{ asset('img/file-item-img3.png') }}" alt="">
                    {!! $canvas_req['req_item3'] !!}
                </div>
            </div>
            <div class="social-photo">
                <div class="social-title">
                    <h3>{!! $canvas_req['req_photo_title'] !!}</h3>
                    <img src="{{ asset('img/social-title-img.png') }}" alt="">
                </div>
                <div class="social-items clearfix">
                    <div class="social-item">
                        <div class="text">
                            {!! $canvas_req['req_photo_1'] !!}
                        </div>
                        <div class="text">
                            {!! $canvas_req['req_photo_2'] !!}
                        </div>
                    </div>
                    <div class="social-item">
                        <div class="text">
                            {!! $canvas_req['req_photo_3'] !!}
                        </div>
                    </div>
                    <div class="social-item">
                        <div class="text">
                            {!! $canvas_req['req_photo_4'] !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="take-images clearfix">
                <div class="take-title">
                    <div class="take-text">
                        <h3>{!! $canvas_req['req_img_title'] !!}</h3>
                        {!! $canvas_req['req_img_text'] !!}
                    </div>
                </div>
                <div class="take-img clearfix">
                    <div class="take-item">
                        <img src="{{ asset('img/take-item-img1.png') }}" alt="" class="take-item-img1">
                        <div class="text">
                            <img src="{{ asset('img/take-item-after1.png') }}" class=""
                                data-src="{{ asset('img/take-item-after1.png') }}" alt="">
                            {!! $canvas_req['req_blank_pay'] !!}
                        </div>
                    </div>
                    <div class="take-item">
                        <img src="{{ asset('img/take-item-img2.png') }}" alt="" class="take-item-img2">
                        <div class="text">
                            <img src="{{ asset('img/take-item-after2.png') }}" alt="">
                            {!! $canvas_req['req_int_free'] !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="search-image clearfix">
                <div class="search-image-left search-image-item">
                    <div class="search-title">
                        <h3>{!! $canvas_req['req_img_sub_title'] !!}</h3>
                    </div>
                    {!! $canvas_req['req_img_left_text'] !!}
                </div>
                <div class="search-image-right search-image-item">
                    <div class="paid">
                        <h3>{!! $canvas_req['req_img_right_title'] !!}</h3>
                        <div class="paid-text clearfix">
                            <div class="text">
                                {!! $canvas_req['req_img_right_text'] !!}
                            </div>
                            <img src="{{ asset('img/paid-img.png') }}" alt="">
                        </div>
                        <p>{!! $canvas_req['req_zap_right_title'] !!}</p>
                        <img src="{{ asset('img/paid-img2.png') }}" alt="" class="paid-img2">
                    </div>
                </div>
                <div class="search-image-left2">
                    <p>{!! $canvas_req['req_ins_filter'] !!}</p>
                    <img src="{{ asset('img/screen-img.png') }}" alt="">
                </div>
                <div class="search-image-right2">
                    {!! $canvas_req['req_zap_right_text'] !!}
                </div>
            </div>
            <div class="ready-links">
                <div class="ready-title">
                    <h3> {!! $canvas_req['req_ready_links_title'] !!}</h3>
                </div>
                <div class="ready-items clearfix">
                    <div class="ready-item">
                        <a href="{!! $canvas_req['req_link1_lnk'] !!}"></a>
                        <img src="{{ Voyager::image( $canvas_req['req_link1_img'] ) }}" alt="">
                        <h5><span></span>{!! $canvas_req['req_link1_title'] !!}</h5>
                    </div>
                    <div class="ready-item">
                        <a href="{!! $canvas_req['req_link2_link'] !!}"></a>
                        <img src="{{ Voyager::image( $canvas_req['req_link2_img'] ) }}" alt="">
                        <h5><span></span>{!! $canvas_req['req_link2_title'] !!}</h5>
                    </div>
                    <div class="ready-item">
                        <a href="{!! $canvas_req['req_link3_link'] !!}"></a>
                        <img src="{{ Voyager::image( $canvas_req['req_link3_img'] ) }}" alt="">
                        <h5><span></span>{!! $canvas_req['req_link3_title'] !!}</h5>
                    </div>
                    <div class="ready-item">
                        <a href="{!! $canvas_req['req_link4_link'] !!}"></a>
                        <img src="{{ Voyager::image( $canvas_req['req_link4_img'] ) }}" alt="">
                        <h5><span></span>{!! $canvas_req['req_link4_title'] !!}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <a data-collapse="requirements-content" class="collapse" href="javascript:void(0)"
        data-show="{{ trans('gl.show_btn') }}" data-hide="{{ trans('gl.hide_btn') }}"><span
            data-show="{{ trans('gl.show_btn') }}"
            data-hide="{{ trans('gl.hide_btn') }}">{{ trans('gl.show_btn') }}</span></a>
</div>
