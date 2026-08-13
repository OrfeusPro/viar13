<div class="filter-picture tabs-item tabs-item1 active" id="tab_screen1">
    <form>
        <div class="filter-accordion">
            <div class="accordion-title h2_old open"><span>1.</span>
                {!! $tab['c_tab1_title'] !!}
            </div>
            <div class="accordion-content">
                <div class="product-download pd-modules mod__pd">
                    <div class="download">
                        <div class="img">
                            <img src="" alt="" class="download-img">
                            <img src="{{ asset('img/download-icon.png') }}" alt="" class="download-icon">
                        </div>
                        <div class="delete"></div>
                        <input type="file" class="imageFile" accept="image/*,image/heif,image/heic" name="user_image[]">
                    </div>
        </div>
</div>
<div class="accordion-title h2_old"><span>2.</span>
    {!! $tab['c_tab2_title'] !!}
</div>
<div class="accordion-content modular-shapes">
    <p>{!! trans('gl.choose_product_before') !!}</p>
</div>

<div class="accordion-title h2_old open"><span>3.</span>
    {!! $tab['c_tab3_title'] !!}
</div>
<div class="accordion-content">
    <div class="modular-size">
        <div class="size-item width">
            <p>@lang('modular_pictures.index28'):
                <input type="number" name="whole-width" min="60" value="90" max="360" step=".1">
                @lang('modular_pictures.index29')
            </p>
            <div class="range_box">
                <input type="range" class="zoomRange range-input" id="range-w" min="60" value="90" max="360"
                    step=".1" />
                <div class="changeSm">
                    <div><span>60</span> <i>{{ $int_globs['cm'] }}</i></div>
                    <div><span>360</span> <i>{{ $int_globs['cm'] }}</i></div>
                </div>
            </div>
        </div>
        <div class="size-item height">
            <p>@lang('modular_pictures.index30'):
                <input type="number" name="whole-height" step=".1">
                @lang('modular_pictures.index29')
            </p>
            <div class="range_box">
                <input type="range" class="zoomRange range-input" id="range-h" min="0" value="30" max="300" step=".1" />
                <div class="changeSm">
                    <div><span>0</span> <i>@lang('modular_pictures.index29')</i>
                    </div>
                    <div><span>300</span> <i>@lang('modular_pictures.index29')</i>
                    </div>
                </div>
            </div>
        </div>
        {{-- <div class="size-item space">
                        <p>Расстояние между блоками:
                            <input type="number" step=".1" min="1" max="30"> см
                        </p>
                        <div class="range_box">
                            <input type="range" class="zoomRange range-input" id="range-s" min="1" value="2" max="30" step=".1" style="background-size: 100%;">
                            <div class="changeSm">
                                <div><span>1</span> <i>см</i></div>
                                <div><span>30</span> <i>см</i></div>
                            </div>
                        </div>
                    </div> --}}


    </div>
</div>

<div class="accordion-title h2_old open"><span>4.</span>
    {!! $tab['c_tab5_title'] !!}
</div>
<div class="accordion-content">
    <div class="execution">

        @include('pages._partials._includes._calc._executions')

    </div>
</div>

<div class="accordion-title h2_old open"><span>5.</span>
    {!! $tab['c_tab6_title'] !!}
</div>
<div class="accordion-content">
    <div class="canvas-items">

        @include('pages._partials._includes._calc._holsts')

    </div>
</div>

<div class="accordion-title h2_old open"><span>6.</span>
    <b>{!! $tab['c_tab7_title'] !!}</b>
</div>
<div class="accordion-content">
    <div class="decoration-items">

        @include('pages._partials._includes._calc._decorations')

    </div>
</div>

<div class="accordion-title h2_old open"><span>7.</span>
    <span> {!! $tab['c_tab8_title'] !!} </span>
</div>
<div class="accordion-content">
    <div class="comments">
        <textarea class="js_text" name="user_comment" id="userComment"></textarea>
        <label class="lbl_upl" for="real_file_input">
            <input id="real_file_input" data-desc="{{ trans('gl.load_btn_text') }}" name="photo_ex"
                accept="image/*,image/heif,image/heic" type="file">
        </label>
    </div>
</div>
</div>
<div class="total-price">
    <div class="total-title"><span>@lang('modular_pictures.index47')</span></div>
    <div class="sum">
        <h4 id="totalPrice">0 &euro;</h4>
        <div class="js_spinner"></div>
    </div>
    <a href="javascript:void(0)" class="sbm__tab1" id="t1_submit_btn"><span>@lang('modular_pictures.index37')</span></a>
    <ul class="dost_items dost_items1">
        <li><strong>{!! trans('gl.srok_izg') !!}</strong></li>
        <li>
            <label>
                <input checked type="radio" name="dost_time" value="{!! trans('gl.standart_price') !!}">
                <span></span>
                <span class="st_text">{!! trans('gl.standart_text') !!}</span>
                <span>{!! trans('gl.standart_price') !!} €</span>
            </label>
        </li>

        <li>
            <label>
                <input type="radio" name="dost_time" value="{!! trans('gl.express_price') !!}">
                <span></span>
                <span class="st_text">{!! trans('gl.express_text') !!}</span>
                <span>{!! trans('gl.express_price') !!} €</span>
            </label>
        </li>
    </ul>
</div>
</form>
</div>
