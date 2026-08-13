<section class="generate">
    <div class="generate-content">
        <div class="title">
            <p>ViarStudia</p>
            <h4>{!! $data['top_title'] !!}</h4>
        </div>
        <div class="gallery-photo">
            <div class="photo-content">
                <div class="slider-tabs">
                    <ul class="tabs">
                        <li><a class="active" data-tabs="tabs-item1" href="javascript:void(0)">
                                {{ $canvas_head['calc_tab_1_main_title'] }}</a>
                        </li>
                        <li><a data-tabs="tabs-item2" href="javascript:void(0)">
                                {{ $canvas_head['calc_tab_2_main_title'] }}</a></li>
                    </ul>
                    <div class="picture tabs-item tabs-item1 active" id="tab_screen1" style="padding:0;">
                        <div class="PhotoEditor" id="PhotoEditor">
                            <div class="wrapper">
                                <div class="canvas_container">
                                    <canvas id='canvas'> </canvas>
                                </div>
                                <div class="settings">
                                    <div class="cur_text">
                                        <div class="color" data-tooltip="Цвет текста">
                                            <input type="color" value="#4ee7d8" id="cur_text_color" />
                                        </div>
                                        <div class="font" data-tooltip="Шрифт текста">
                                            <select id="cur_text_font">
                                                {{-- <option value="Montserrat" style="font-family:Montserrat">Montserrat
                                                </option>
                                                <option value="Modak" style="font-family:Modak">Modak</option>
                                                <option value="Fondamento" style="font-family:Fondamento">Fondamento
                                                </option>
                                                <option value="Merienda One" style="font-family:Merienda One">Merienda
                                                    One</option>
                                                <option value="Rock Salt" style="font-family:Rock Salt">Rock Salt
                                                </option>
                                                <option value="Covered By Your Grace"
                                                    style="font-family:Covered By Your Grace">Covered By Your Grace
                                                </option> --}}
                                            </select>

                                        </div>
                                    </div>
                                    <div class="range">
                                        <div class="caption">{!! trans('gl.skr_ugl') !!}</div>
                                        <input id="range_radius" class="slider" type="range" min="0" max="100"
                                            value="10">
                                    </div>
                                    <div class="range">
                                        <div class="caption">{!! trans('gl.mj_yach') !!}</div>
                                        <input id="range_between" class="slider" type="range" min="0" max="100"
                                            value="20">
                                    </div>
                                </div>
                            </div>
                            <div class="tools">
                                <div id="delete_background" class="tool icontool-delete_background"
                                    data-tooltip="{!! trans('gl.help_del_fon') !!}"> </div>
                                <div id="clear" class="tool icontool-clear"
                                    data-tooltip="{!! trans('gl.help_clear') !!}"> </div>
                                <div id="cell_delete" class="tool icontool-delete_cell"
                                    data-tooltip="{!! trans('gl.help_del_selected') !!}"></div>
                                <div id="img_delete" class="tool icontool-delete_img"
                                    data-tooltip="{!! trans('gl.help_del_foto') !!}"></div>
                                <div id="undo" class="tool icontool-undo"
                                    data-tooltip="{!! trans('gl.help_back') !!}"></div>
                                <div id="redo" class="tool icontool-redo"
                                    data-tooltip="{!! trans('gl.help_forv') !!}"></div>
                                <div id="photo_zoom_minus" class="tool icontool-zoom_out"
                                    data-tooltip="{!! trans('gl.help_sm') !!}"> </div>
                                <div id="photo_zoom_plus" class="tool icontool-zoom_in"
                                    data-tooltip="{!! trans('gl.help_big') !!}"> </div>
                                <div id="turn_right" class="tool icontool-turn_cw"
                                    data-tooltip="{!! trans('gl.help_right') !!}"></div>
                                <div id="turn_left" class="tool icontool-turn_ccw"
                                    data-tooltip="{!! trans('gl.help_left') !!}"></div>

                                <div id="smile" tabindex="0" class="tool icontool-smile"
                                    data-tooltip="{!! trans('gl.help_add_sm') !!}">
                                    <div class="window">
                                        <div class="smiles" id="smiles">

                                            <div class="accordion">
                                                <div class="accordion_header accordion_active">смайлы 1</div>
                                                <div class="accordion_body">
                                                    <div class="item"><img height="30"
                                                            src="/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png"
                                                            width="30" onload="this.style.opacity=1" /></div>
                                                    <div class="item"><img height="30"
                                                            src="/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png"
                                                            width="30" onload="this.style.opacity=1" /></div>
                                                    <div class="item"><img height="30"
                                                            src="/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png"
                                                            width="30" onload="this.style.opacity=1" /></div>
                                                    <div class="item"><img height="30"
                                                            src="/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png"
                                                            width="30" onload="this.style.opacity=1" /></div>
                                                    <div class="item"><img height="30"
                                                            src="/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png"
                                                            width="30" onload="this.style.opacity=1" /></div>
                                                    <div class="item"><img height="30"
                                                            src="/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png"
                                                            width="30" onload="this.style.opacity=1" /></div>
                                                    <div class="item"><img height="30"
                                                            src="/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png"
                                                            width="30" onload="this.style.opacity=1" /></div>
                                                    <div class="item"><img height="30"
                                                            src="/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png"
                                                            width="30" onload="this.style.opacity=1" /></div>
                                                </div>

                                                <div class="accordion_header">смайлы 2</div>
                                                <div class="accordion_body">
                                                    <div class="item"><img height="30"
                                                            src="/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png"
                                                            width="30" onload="this.style.opacity=1" /></div>
                                                    <div class="item"><img height="30"
                                                            src="/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png"
                                                            width="30" onload="this.style.opacity=1" /></div>
                                                    <div class="item"><img height="30"
                                                            src="/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png"
                                                            width="30" onload="this.style.opacity=1" /></div>
                                                    <div class="item"><img height="30"
                                                            src="/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png"
                                                            width="30" onload="this.style.opacity=1" /></div>
                                                    <div class="item"><img height="30"
                                                            src="/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png"
                                                            width="30" onload="this.style.opacity=1" /></div>
                                                    <div class="item"><img height="30"
                                                            src="/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png"
                                                            width="30" onload="this.style.opacity=1" /></div>
                                                    <div class="item"><img height="30"
                                                            src="/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png"
                                                            width="30" onload="this.style.opacity=1" /></div>
                                                    <div class="item"><img height="30"
                                                            src="/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png"
                                                            width="30" onload="this.style.opacity=1" /></div>
                                                    <div class="item"><img height="30"
                                                            src="/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png"
                                                            width="30" onload="this.style.opacity=1" /></div>
                                                    <div class="item"><img height="30"
                                                            src="/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png"
                                                            width="30" onload="this.style.opacity=1" /></div>
                                                    <div class="item"><img height="30"
                                                            src="/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png"
                                                            width="30" onload="this.style.opacity=1" /></div>
                                                    <div class="item"><img height="30"
                                                            src="/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png"
                                                            width="30" onload="this.style.opacity=1" /></div>
                                                    <div class="item"><img height="30"
                                                            src="/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png"
                                                            width="30" onload="this.style.opacity=1" /></div>
                                                    <div class="item"><img height="30"
                                                            src="/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png"
                                                            width="30" onload="this.style.opacity=1" /></div>
                                                    <div class="item"><img height="30"
                                                            src="/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png"
                                                            width="30" onload="this.style.opacity=1" /></div>
                                                    <div class="item"><img height="30"
                                                            src="/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png"
                                                            width="30" onload="this.style.opacity=1" /></div>
                                                </div>

                                                <div class="accordion_header">добавить свои</div>
                                                <div class="accordion_body">
                                                    <div class="item">
                                                        <img height="30" width="30" onload="this.style.opacity=1" />
                                                    </div>
                                                    <div class="item"><img height="30" width="30"
                                                            onload="this.style.opacity=1" /></div>
                                                    <div class="item"><img height="30" width="30"
                                                            onload="this.style.opacity=1" /></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="add_text" class="tool icontool-text"
                                    data-tooltip="{!! trans('gl.help_add_text') !!} !!}"></div>

                                <div id="fullscreen" class="tool icontool-fullscreen"
                                    data-tooltip="{!! trans('gl.help_full_screen') !!}"> </div>
                                <div id="loadPhotos_4" class="tool icontool-load_photos"
                                    data-tooltip="{!! trans('gl.help_load_all') !!}"></div>
                            </div>
                        </div>
                    </div>
                    <div class="interior tabs-item tabs-item2">
                        <div id="interior_container" class="interior_container">
                            <canvas id="canvas_interior"> </canvas>
                            <div class="tools">
                                <div id="interior_fullscreen" class="tool icontool-fullscreen"> </div>
                                <div id="interior_zoom_minus" class="tool icontool-zoom_out"> </div>
                                <div id="interior_zoom_plus" class="tool icontool-zoom_in"> </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="photo-filter acc__flow">
                    <div class="filter-picture tabs-item tabs-item1 active">
                        <form onsubmit="return false">
                            <div class="filter-accordion">
                                <div class="accordion-title h2_old open"><span>1.</span> {!! $data['choose_prod1'] !!}</div>
                                <div class="accordion-content">
                                    <div class="product-download pd-family" id="imgs2">
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
                            <div class="accordion-title h2_old open"><span>2.</span> {!! $data['choose_fon2'] !!}</div>
                            <div class="accordion-content">
                                <div class="product-download" id="backgrounds">
                                    <div class="img-item">
                                        <img src="" />
                                        <button class="delete"> </button>
                                    </div>
                                    <div class="img-item">
                                        <img src="" />
                                        <button class="delete"> </button>
                                    </div>
                                    <div class="img-item">
                                        <img src="" />
                                        <button class="delete"> </button>
                                    </div>

                                </div>
                            </div>
                            <div class="accordion-title h2_old open"><span>3.</span> {!! $data['choose_form3'] !!}</div>
                            <div class="accordion-content shapes__flow">
                                <div class="collage-shapes" id="templates">
                                </div>
                            </div>
                            <div class="accordion-title h2_old open"><span>4.</span> {!! $data['choose_size4'] !!}</div>
                            <div class="accordion-content">
                                <div class="size" id="sizes">
                                </div>
                            </div>
                            <div class="accordion-title h2_old"><span>5.</span> {!! $data['choose_holst5'] !!}</div>
                            <div class="accordion-content">
                                <div class="canvas-items js__holsts">
                                    @include('pages._partials._includes._calc._holsts')
                                </div>
                            </div>
                            <div class="accordion-title h2_old open"><span>6.</span> <span> {!! $data['comm6'] !!}</span></div>
                            <div class="accordion-content">
                                <div class="comments">
                                    <textarea class="js_text" name="user_comment" id="userComment"></textarea>
                                    <label class="lbl_upl" for="real_file_input">
                                        <input id="real_file_input" data-desc="{{ trans('gl.load_btn_text') }}"
                                            name="photo_ex" accept="image/*,image/heif,image/heic" type="file">
                                    </label>
                                    <div class="js_pick_items">
                                        @include('pages._partials._includes._calc._boxes')
                                    </div>
                                </div>
                            </div>
                    </div>

                    <div class="total-price">
                        <div class="total-title"><span>{!! trans('gl.itog_price') !!}</span></div>
                        <div class="sum">
                            <h4><span id="glob_summ">0</span> €</h4>
                            <div class="js_spinner" style="text-align: center;"></div>
                        </div>
                        <a class="sbm__collage" data-route="{{ route('add_item_to_basket_construct') }}"
                            data-name="{{ $data['meta_title' ] }}" href="javascript:void(0)"><span>{!!
                                trans('gl.order_btn') !!}</span></a>
                        <ul class="dost_items">
                            <li><strong>{!! trans('gl.srok_izg') !!}</strong></li>
                            <li>
                                <label>
                                    <input checked type="radio" name="dost_time"
                                        value="{!! trans('gl.standart_price') !!}">
                                    <span></span>
                                    <span class="st_text">{!! trans('gl.standart_text') !!}</span>
                                </label>
                            </li>

                            <li>
                                <label>
                                    <input type="radio" name="dost_time"
                                        value="{!! trans('gl.express_price') !!}">
                                    <span></span>
                                    <span class="st_text">{!! trans('gl.express_text') !!}</span>
                                </label>
                            </li>
                        </ul>
                    </div>
                    </form>
                </div>
                @include('partials.module_pics.tab2_form', ['no_rams' => 1])
            </div>
        </div>
    </div>
</section>
