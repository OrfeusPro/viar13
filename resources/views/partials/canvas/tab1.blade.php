<div class="filter-picture tabs-item tabs-item1 active">
    <form>
        <div class="filter-accordion">
            <div class="accordion-title h2_old open"><span>1.</span>
                {!! $canvas_head['c_tab1_title'] !!}</div>
            <div class="accordion-content">
                <div class="product-download pd-canvas" id="imgs">
                    <button type="button"><img src="" alt="" onload="this.style.opacity=1" />
                        <span class="delete"></span>
                    </button>
                    <button type="button"><img src="" alt="" onload="this.style.opacity=1" />
                        <span class="delete"></span>
                    </button>
                    <button type="button"><img src="" alt="" onload="this.style.opacity=1" />
                        <span class="delete"></span>
                    </button>
                </div>
            </div>
            <div class="accordion-title h2_old open"><span>2.</span> {!! $canvas_head['c_tab2_title'] !!}</span></div>
            <div class="accordion-content">
                <div class="shapes">
                    <ul>
                        <script>
                            window.onload = function(){setForm(0);}
                        </script>
                        <li><a class="calcForm active" href="javascript:void(0)" onclick="setForm(0)" data-id="1"><i
                                    class="icon-down-arrow"></i><img src="{{ asset('img/shapes-img1.png') }}"
                                    alt=""></a></li>
                        <li><a onclick="setForm(1)" class="calcForm" href="javascript:void(0)" data-id="2"><i
                                    class="icon-down-arrow"></i><img src="{{ asset('img/shapes-img2.png') }}"
                                    alt=""></a></li>
                        <li><a onclick="setForm(2)" class="calcForm" href="javascript:void(0)" data-id="3"><i
                                    class="icon-down-arrow"></i><img src="{{ asset('img/shapes-img3.png') }}"
                                    alt=""></a></li>
                    </ul>

                    {{-- @include('pages._partials._includes._calc._primitive_forms') --}}
                </div>
            </div>
            <div class="accordion-title h2_old open"><span>3.</span> {!! $canvas_head['c_tab3_title'] !!}</span></div>
            <div class="accordion-content">
                <div class="size" id="sizes">
                    {{-- @include('pages._partials._includes._calc._sizes') --}}
                </div>
            </div>
            {{-- <div class="accordion-title h2_old open"><span>4.</span> {!! $canvas_head['c_tab4_title'] !!}</span></div>
            <div class="accordion-content">
                <div class="effects">

                    @include('pages._partials._includes._calc._effects')

                </div>
            </div> --}}
            <div class="accordion-title h2_old open"><span>4.</span> {!! $canvas_head['c_tab5_title'] !!}</div>
            <div class="accordion-content">
                <div class="execution">

                    @include('pages._partials._includes._calc._executions')

                </div>
            </div>
            <div class="accordion-title h2_old open"><span>5.</span> {!! $canvas_head['c_tab6_title'] !!}</span>
            </div>
            <div class="accordion-content">
                <div class="canvas-items">

                    @include('pages._partials._includes._calc._holsts')

                </div>
            </div>
            <div class="accordion-title h2_old open"><span>6.</span>
                <b>{!! $canvas_head['c_tab7_title'] !!}</b></div>
            <div class="accordion-content">
                <div class="decoration-items">

                    @include('pages._partials._includes._calc._decorations')

                </div>
            </div>
            <div class="accordion-title h2_old open"><span>7.</span> <span> {!! $canvas_head['c_tab8_title'] !!}</span></div>
            <div class="accordion-content">
                <div class="comments">
                    <textarea class="js_text" name="user_comment" id="userComment"></textarea>
                    <label class="lbl_upl" for="real_file_input">
                        <input id="real_file_input" data-desc="{{ trans('gl.load_btn_text') }}" name="photo_ex"
                            accept="image/*,image/heif,image/heic" type="file">
                    </label>
                    {{-- <ul>
                        <li><a href="javascript:void(0)"><i class="icon-icon16"></i>{!! $canvas_head['tear_away_text'] !!}</a>
                        </li>
                        <li><a href="javascript:void(0)"><i class="icon-icon13"></i>{!! $canvas_head['share_text'] !!}</a>
                        </li>
                    </ul> --}}
                    <div class="js_pick_items">
                        @include('pages._partials._includes._calc._boxes')
                    </div>
                </div>
            </div>
        </div>
        <div class="total-price">
            <div class="total-title"><span>{!!$int_globs['total_price_title'] !!}</span></div>
            <div class="sum">
                <h4 id="totalSum"><span id="totalPrice"></span> &euro;</h4>
                <div class="js_spinner"></div>
            </div>
            <a class="js_add_basket_stena" href="javascript:void(0)" id="t1_submit_btn"
                data-name="{{ $canvas_head['c_right_top'] }}">
                <span>{!!$int_globs['add_to_bask_title'] !!}</span></a>

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
