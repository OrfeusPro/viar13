<div class="tabs-item service-quality">
    <div class="height-content">
        <div class="tabs-height service-quality-content">
            <div class="service">
                <div class="title">
                    <h2>{{ $canvas_work_serv['why_viar'] }}</h2>
                    <h4>{{ $canvas_work_serv['we_love_clients'] }}</h4>
                </div>
                <div class="service-items clearfix">
                    <div class="service-item">
                        <img src="{{ asset('img/service-item-img.png' )}}"  @frontendAlt('partials/module_pics/serv_qual.blade.php', (asset('img/service-item-img.png' )), '', '')>
                        <p>{!! $canvas_work_serv['why1_title'] !!}</p>
                    </div>
                    <div class="service-item">
                        <img data-src="{{ asset('img/service-item-img2.png' )}}"  @frontendAlt('partials/module_pics/serv_qual.blade.php', (asset('img/service-item-img2.png' )), '', '')>
                        <p>{!! $canvas_work_serv['why2_title'] !!}</p>
                    </div>
                    <div class="service-item">
                        <img src="{{ asset('img/service-item-img3.png' )}}"  @frontendAlt('partials/module_pics/serv_qual.blade.php', (asset('img/service-item-img3.png' )), '', '')>
                        <p>{!! $canvas_work_serv['why3_title'] !!}</p>
                    </div>
                    <div class="service-item">
                        <img src="{{ asset('img/service-item-img4.png' )}}"  @frontendAlt('partials/module_pics/serv_qual.blade.php', (asset('img/service-item-img4.png' )), '', '')>
                        <p>{!! $canvas_work_serv['why4_title'] !!}</p>
                    </div>
                    <div class="service-item">
                        <img src="{{ asset('img/service-item-img5.png' )}}"  @frontendAlt('partials/module_pics/serv_qual.blade.php', (asset('img/service-item-img5.png' )), '', '')>
                        <p>{!! $canvas_work_serv['why5_title'] !!}</p>
                    </div>
                    <div class="service-item">
                        <img src="{{ asset('img/service-item-img6.png' )}}"  @frontendAlt('partials/module_pics/serv_qual.blade.php', (asset('img/service-item-img6.png' )), '', '')>
                        <p>
                            <p>{!! $canvas_work_serv['why6_title'] !!}</p>
                        </p>
                    </div>
                    <div class="service-item">
                        <img src="{{ asset('img/service-item-img7.png' )}}"  @frontendAlt('partials/module_pics/serv_qual.blade.php', (asset('img/service-item-img7.png' )), '', '')>
                        <p>
                            <p>{!! $canvas_work_serv['why7_title'] !!}</p>
                        </p>
                    </div>
                    <div class="service-item">
                        <img src="{{ asset('img/service-item-img8.png' )}}"  @frontendAlt('partials/module_pics/serv_qual.blade.php', (asset('img/service-item-img8.png' )), '', '')>
                        <p>
                            <p>{!! $canvas_work_serv['why8_title'] !!}</p>
                        </p>
                    </div>
                </div>
            </div>
            <div class="quality">
                <div class="title">
                    <h2>
                        <p>{!! $canvas_work_serv['imp_know_title'] !!}</p>
                    </h2>
                </div>
                <p>{!! $canvas_work_serv['imp_know_text'] !!}</p>
            </div>
        </div>
    </div>
    <a data-collapse="service-quality-content" class="collapse" href="javascript:void(0)"
            data-show="{{ trans('gl.show_btn') }}"
       data-hide="{{ trans('gl.hide_btn') }}"></a><span>{{ trans('gl.show_btn') }}</span></a>
</div>
