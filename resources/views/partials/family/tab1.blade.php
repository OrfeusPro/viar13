<div class="filter-picture tabs-item tabs-item1 active">
    <form onsubmit="return false">
        <div class="filter-accordion">
            <div class="accordion-title h2_old open"><span>1.</span> {!! $canvas_head['c_tab1_title'] !!}</div>
            <div class="accordion-content">
               <div class="product-download pd-family" id="imgs">
                        <div class="js_zone">
                            <div class="img img__place">
                            </div>
                            <div class="img img__place">
                            </div>
                            <div class="img img__place">
                            </div>
                        </div>
                    </div>
            </div>
            <div class="accordion-title h2_old open"><span>2.</span> {!! $canvas_head['c_tab3_title'] !!}</div>
            <div class="accordion-content">
                <div class="size">
                    @php
                      $custom_sizes = explode(',',$data['sizes']);
                    @endphp
                <ul>
                    @if(is_array($custom_sizes) && !empty($custom_sizes) && $custom_sizes[0] != "")
                    @foreach($custom_sizes as $size)

                    @php
                        $price = get_string_between($size, '[', ']');
                        $size_clear = substr($size, 0, strpos($size, "["));
                        $size_clear_vals = explode('x', $size_clear);


                        $price_ex =  explode("-", $price);
                        if(count($price_ex)>1){
                            $old_price = $price_ex[0];
                            $new_price = $price_ex[1];
                        }else{
                            $old_price = $price;
                            $new_price = $price;
                        }
                    @endphp
                    <li>
                        <a class="calcSize js_size"
                        href="javascript:void(0)"
                        onclick="setSize({{ $size_clear_vals[0] }},{{ $size_clear_vals[1] }},5);"
                        data-size="{{ $size_clear_vals[0] }}x{{ $size_clear_vals[1] }}"
                        data-price="{{ $new_price*$contry_mult }}"
                        >
                            {{ $size_clear_vals[0] }} х {{ $size_clear_vals[1] }} {{ trans('gl.cm') }}
                            <strong>
                                    @if($old_price !=  $new_price )
                                    <strike style="color:#2d2d2d;">{{ $old_price*$contry_mult }} €</strike>
                                    <span style="color:#e2761d">{{ $new_price*$contry_mult }} €</span>
                                    @else
                                    {{ $new_price*$contry_mult }} €
                                    @endif
                            </strong>
                        </a>
                    </li>

                    @if($loop->first)
                    <script> window.onload = function(){
                        var s1 = '{!!  $size_clear_vals[0] !!}';
                        var s2 = '{!! $size_clear_vals[1] !!}';
                          setSize(s2,s2,5);
                    }</script>
                    @endif

                    @endforeach

                    @endif

                </div>
            </div>
            <div class="accordion-title h2_old open"><span>3.</span> {!! $canvas_head['c_tab6_title'] !!}</div>
            <div class="accordion-content">
                <div class="canvas-items">
                    @include('pages._partials._includes._calc._holsts')
                </div>
            </div>
            <div class="accordion-title h2_old open"><span>4.</span> {!! $canvas_head['c_tab8_title'] !!}</div>
            <div class="accordion-content">
                <div class="comments">
                    <textarea class="js_text" name="user_comment" id="userComment"></textarea>
                    <label class="lbl_upl" for="real_file_input">
                        <input
                        id="real_file_input" data-desc="{{ trans('gl.load_btn_text') }}"
                        name="photo_ex"
                        accept="image/*,image/heif,image/heic"
                        type="file">
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
                <h4><span class="js_summ"></span> &euro;</h4>
                <div class="js_spinner" style="display:none;text-align:center;">
                    <div class="spinner">
                        <div class="bounce1"></div>
                        <div class="bounce2"></div>
                        <div class="bounce3"></div>
                    </div>
                </div>
            </div>

            <a href="javascript:void(0)" class="js_add_fam"><span>{!!$int_globs['add_to_bask_title'] !!}</span></a>
            <ul class="dost_items dost_items1">
                <li><strong>{!! trans('gl.srok_izg') !!}</strong></li>
                <li>
                    <label>
                        <input checked type="radio" name="dost_time"
                         value="{!! trans('gl.standart_price') !!}">
                        <span></span>
                        <span class="st_text">{!! trans('gl.standart_text') !!}</span>
                        <span>{!! trans('gl.standart_price') !!} €</span>
                    </label>
                </li>

                <li>
                    <label>
                        <input type="radio" name="dost_time"
                        value="{!! trans('gl.express_price') !!}">
                        <span></span>
                        <span class="st_text">{!! trans('gl.express_text') !!}</span>
                        <span>{!! trans('gl.express_price') !!} €</span>
                    </label>
                </li>
            </ul>
        </div>
    </form>
</div>

