<style>
    .accordion-title a a:after {
        content: '';
        display: none;
    }

    .download .img {
        background: cover;
    }

    .generate .generate-content .gallery-photo .photo-filter .filter-accordion .accordion-content .product-download .download .img {
        width: 107px;
        height: 94px;
    }

    .generate .generate-content .gallery-photo .photo-filter .filter-accordion .accordion-content .product-download {
        margin: 0 -2%;
        display: flex;
        justify-content: center;
        overflow-y: hidden;
    }
</style>

<section class="generate generator__main">
    <div class="generate-content">
        <div class="title">
            <h2 class="js_item_name">{!! $item['name'] !!}</h2>
        </div>
        <div class="gallery-photo">
            <div class="photo-content">
                <div class="slider-tabs">
                    <ul class="tabs">
                        <li><a class="active" data-tabs="tabs-item1" href="javascript:void(0)">{!!
                                trans('gl.pic_on_wall') !!}</a></li>
                    </ul>
                    <div class="tabs-content">

                        <div class="picture-js tabs-item tabs-item1 active" id="tab1__generator">
                            @if($item['images'])
                            @foreach( json_decode($item['images']) as $img)
                            <div class="def__tab__img_sms def__imgs_rem">
                                <img src="{{ Voyager::image($img) }}" alt="">
                            </div>
                            @endforeach
                            @endif
                        </div>
                        <h4 class="sum__mod"><span id=glob_summ2>0</span> €</h4>
                    </div>
                </div>
                <div class="photo-filter">
                    <div class="filter-picture tabs-item tabs-item1 active">
                        <form>
                            <input type="hidden" name="full_price" class="js_full_price">
                            <div class="filter-accordion">
                                <div class="accordion-title h2_old open" data-collapse-summary="" aria-expanded="true">
                                    <a href="#"><span>1.</span> {!! $canvas_head['c_tab1_title'] !!}</a>
                                </div>
                                <div class="accordion-content">
                                    <div class="js_zone_no_drop">
                                        <div class="upl__btn">
                                            <img class="upl_ico" src="{{ asset('img/download-icon.png') }}" alt=""
                                                class="download-icon">
                                        </div>
                                        <div class="upl__btn">
                                            <img class="upl_ico" src="{{ asset('img/download-icon.png') }}" alt=""
                                                class="download-icon">
                                        </div>
                                        <div class="upl__btn">
                                            <img class="upl_ico" src="{{ asset('img/download-icon.png') }}" alt=""
                                                class="download-icon">
                                        </div>
                                    </div>
                                    {{-- <div class="product-download pd-graph">
                                        <label class="js-download">
                                            <div class="img">
                                                <img src="" alt="" class="download-img">
                                                <img src="{{ asset('img/download-icon.png') }}" alt=""
                                    class="download-icon">
                                </div>
                                <input type="file" onchange="previewFile(event)" class="imageFile" accept="image/jpeg"
                                    name="user_image" id="user_image" multiple>
                                </label>
                            </div> --}}
                    </div>

                    <div class="accordion-title h2_old open" data-collapse-summary="" aria-expanded="true">
                        <a href="#"><span>2.</span> {!! $canvas_head['c_tab2_title'] !!}</a></div>
                    <div class="accordion-content" aria-hidden="false">
                        <div class="shapes">
                            @include('pages._partials._includes._calc._primitive_forms')
                        </div>
                    </div>

                    <div class="accordion-title h2_old open" data-collapse-summary="" aria-expanded="true">
                        <a href="#"><span>3.</span> {!! $canvas_head['c_tab3_title'] !!}</a></div>
                    <div class="accordion-content" aria-hidden="false">
                        <div class="size">
                            <?php
                                            if (isset($is_oil_page)):
                                        ?>
                            {{-- @include('pages._partials._includes._calc._sizes') --}}
                            @include('partials.custom_sizes_calc_oil')
                            <?php else: ?>
                            @include('partials.custom_sizes_calc')
                            <?php endif; ?>
                        </div>
                    </div>


                    <div class="accordion-title h2_old open" data-collapse-summary="" aria-expanded="true">
                        <a href="#"><span>4.</span>{!! trans('gl.choose_person_count') !!}</a></div>
                    <div class="accordion-content" aria-hidden="false">
                        <div class="canvas-items">
                            <label class="canvas-item js__personal_checker" style="display:block;">
                                <span class="item_radio">
                                    <input checked="" name="f" type="radio"><span></span></span>
                                <label class="no__sel">{!! trans('gl.personal') !!}<i
                                        class="icon-icon1"></i></label>
                            </label>
                            <label class="canvas-item js__group_checker" style="display:block;">
                                <span class="item_radio">
                                    <input name="f" type="radio"><span></span></span>
                                <label class="no__sel">{!! trans('gl.group') !!}<i class="icon-icon1"></i></label>
                            </label>
                        </div>
                        <div class="size-person size__new sizes_unactive js__sizes">
                            <ul>
                                @isset($item['custom_users_prices'])
                                @php
                                  $custom_users_count = explode(',', $item['custom_users_prices']);
                                @endphp

                                @if(is_array($custom_users_count) && !empty($custom_users_count) &&
                                $custom_users_count[0] != "")
                                @foreach($custom_users_count as $user)
                                @php
                                    $price = get_string_between($user, '[', ']');
                                    $user_count = substr($user, 0, strpos($user, "["));
                                @endphp

                                <li class="js_custom_user" @if($user_count==1) style="display:none;" @endif
                                    data-count="{{ $user_count }}" data-price="{{ $price * $contry_mult }}"><a
                                        class="js_cust_count" href="javascript:void(0)" data-count="{{ $user_count }}">
                                        <strong>{{ $user_count }} <span> {!! trans('gl.chel') !!}</span></strong>
                                        <span style="color:green;">+</span>
                                        <strong>{{ $price * $contry_mult }} €</strong></a></li>
                                @endforeach
                                @endif
                                @endisset
                            </ul>
                        </div>
                        <div class="number-people js_peoples_nums sizes_unactive">
                            <label>{!! trans('gl.enter_person_count') !!}</label>
                            <div>
                                <input type="number" class="js_custom_user_count" type="text" name="user_count_custom">
                                <button>{!! trans('gl.send_text') !!}</button>
                            </div>
                            <span>{!! trans('gl.price_text') !!}: <strong><span class="js_price_ppl">0</span>
                                    €</strong></span>
                        </div>
                    </div>


                    <?php
                                if (!isset($is_oil)){
                                ?>
                    <div class="accordion-title h2_old open" data-collapse-summary="" aria-expanded="true">
                        <a href="#"><span>5.</span>{!! $canvas_head['c_tab5_title'] !!}</a></div>
                    <div class="accordion-content" aria-hidden="false">
                        <div class="execution">
                            <div data-id="maslom" class="execution-item js__calc_ex js_ex_1" data-execution="1">
                                <div class="img">
                                    <i class="icon-down-arrow"></i>
                                    <img src="{{ asset('img/execution-item1.png') }}" alt="">
                                </div>
                                <p>@lang('modular_pictures.index46')</p>
                            </div>
                            <div data-id="pechat" class="execution-item active js__calc_ex js_ex_2" data-execution="2">
                                <div class="img">
                                    <i class="icon-down-arrow"></i>
                                    <img src="{{ asset('img/execution-item2.png') }}" alt="">
                                </div>
                                <p>@lang('modular_pictures.index45')</p>
                            </div>

                        </div>
                    </div>
                    <?php
                                    }
                                 ?>


                    <div class="accordion-title h2_old" data-collapse-summary="" aria-expanded="true">
                        <a href="#"><span>6.</span> {!! $canvas_head['c_tab6_title'] !!}</a></div>
                    <div class="accordion-content" aria-hidden="false">
                        <div class="canvas-items js__holsts">
                            @forelse($galleryHolsts as $holst)
                            <div class="canvas-item js__canv_radio generator_blade_tpl" data-id="{{  $holst->id }}"
                                data-name="{{ $holst->getTranslatedAttribute('name', app()->getLocale()) }}"
                                data-ratio="{{ $holst->getTranslatedAttribute('density', app()->getLocale()) }}">
                                <input @if($loop->index == 1) checked @endif name="canvas_type" type="radio"
                                data-id="{{ $holst->id }}"
                                value="{{ $holst->price }}">
                                <label>{{ $holst->getTranslatedAttribute('name', app()->getLocale()) }}
                                    <span>{{ $holst->getTranslatedAttribute('density', app()->getLocale()) }}</span><i
                                        class="icon-icon1"></i></label>
                            </div>
                            @empty
                            @endforelse

                        </div>
                    </div>


                    <div class="accordion-title h2_old" data-collapse-summary="" aria-expanded="true">
                        <a href="#"><span>7.</span> <span> {!! trans('gl.hud_of_name') !!}</span></a></div>
                    <div class="accordion-content" aria-hidden="false">
                        <div class="decoration-items js_decors">
                            @forelse($galleryDecorations as $decoration)
                            <div class="decoration-item js_decor_item
                                            @if($decoration->price == 0) js_decor_null @endif" @if($decoration->price
                                == 0) style="display:none;" @endif
                                data-id="{{ $decoration->id }}">
                                <input name="decoration" type="radio" data-id="{{ $decoration->id }}"
                                    data-coef_sm="{{ $decoration->coef_sm }}" data-coef_md="{{ $decoration->coef_md }}"
                                    data-coef_lg="{{ $decoration->coef_lg }}" value="{{ $decoration->price }}">
                                <label>{{ $decoration->getTranslatedAttribute('name', app()->getLocale()) }}
                                    <i class="icon-icon1 hint__icon"><span
                                            class="hint__text">{{ $decoration->getTranslatedAttribute('hint', app()->getLocale()) }}</span></i></label>
                            </div>
                            @empty
                            @endforelse

                        </div>
                    </div>

                    <div class="accordion-title h2_old open"><span>8.</span> {!!$int_globs['rama_title'] !!}</div>
                    <div class="accordion-content">
                        @include('partials.rams')
                    </div>

                    <div class="accordion-title h2_old open" data-collapse-summary="" aria-expanded="true">
                        <a href="#"><span>9.</span> {!! trans('gl.choose_complect') !!}</a></div>
                    <div class="accordion-content" aria-hidden="false">
                        <div class="picking-items js_pick_items">
                            @forelse($galleryBoxes as $box)
                            <div class="picking-item" data-id="{{ $box->id }}">
                                <input name="boxes[]" type="checkbox" @if($box->id == 3) checked @endif
                                data-id="{{ $box->id }}"
                                value="{{ $box->price }}">
                                <label>{{ $box->getTranslatedAttribute('name', app()->getLocale()) }}
                                    <i class="icon-icon1"></i></label>
                            </div>
                            @empty
                            @endforelse
                        </div>
                    </div>
                    <div class="accordion-title h2_old open" data-collapse-summary="" aria-expanded="true">
                        <a href="#"><span>10.</span> <span> {!! trans('gl.comments') !!}</span></a></div>
                    <div class="accordion-content" aria-hidden="false">
                        <div class="comments">
                            <textarea class="js_text" name="user_comment" id="userComment"></textarea>
                            <label class="lbl_upl" for="real_file_input">
                                <input id="real_file_input" data-desc="{{ trans('gl.load_btn_text') }}"
                                    name="photo_ex" accept="image/*,image/heif,image/heic" type="file">
                            </label>
                        </div>
                    </div>
                </div>

                <div class="total-price">
                    <div class="total-title"><span>{!! trans('gl.itog_price') !!}</span></div>
                    <div class="sum">
                        <h4><span id=glob_summ>0</span> €</h4>
                        <div class="js_spinner"></div>
                    </div>
                    <a class="js_sbm__calc generator_btn" data-route="{{ route('add_item_to_basket_portrait') }}"
                        data-name="{{ $item['name'] }}" @isset($item['id']) data-id="{{ $item['id'] }}" @endisset
                        href="javascript:void(0)"><span>{!! trans('gl.order_btn') !!}</span></a>
                    <ul class="dost_items">
                        <li><strong>{!! trans('gl.srok_izg') !!}</strong></li>


                        @if(isset($is_oil_page))
                        <li>
                            <label>
                                <input checked type="radio" name="dost_time"
                                    value="{!! trans('gl.standart_price') !!}">
                                <span></span>
                                <span class="st_text">{!! trans('gl.standart_text') !!}</span>
                                <span>{!! trans('gl.standart_price') !!} €</span>
                            </label>
                        </li>
                        @else

                        <li>
                            <label>
                                <input checked type="radio" name="dost_time"
                                    value="{!! trans('gl.standart_price') !!}">
                                <span></span>
                                <span class="st_text">{!! trans('gl.standart_text_2') !!}</span>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="radio" name="dost_time" value="{!! trans('gl.express_price') !!}">
                                <span></span>
                                <span class="st_text">{!! trans('gl.express_text_2') !!}</span>
                            </label>
                        </li>
                        @endif
                    </ul>
                </div>

                </form>
            </div>
        </div>
    </div>
    </div>
    </div>
</section>
