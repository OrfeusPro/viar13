<style>
    select#u_count {
        width: 100%;
    }
</style>
<section
    class="tabs-container @if(isset($item['is_left_image'])) @if($item['is_left_image'] == 1) tabs-container6 @endif @endif">
    <div class="tabs-items">
        <div class="tabs-item what-canvas active">
            <div class="height-content">
                <div class="tabs-height what-canvas-content">
                    <div class="title">
                        <h2>{{ trans('gl.what_is_text') }}<span>{{ $item['shortname'] }}</span></h2>
                    </div>
                    <div class="what-items clearfix">
                        <div class="what-item">
                            @isset($is_oil)
                                <img src="{{ asset('img/what-item-img20.png') }}" class=""
                                     data-src="{{ asset('img/what-item-img20.png') }}" alt="">
                            @endisset
                            @if($item['add_image3_inner'])
                                @php
                                    $graphicTabImageSources = image_picture_sources($item['add_image3_inner'], true);
                                @endphp
                                <picture>
                                    @if(!empty($graphicTabImageSources['src_webp']))
                                        <source srcset="{{ $graphicTabImageSources['src_webp'] }}" type="image/webp">
                                    @endif
                                    @if(!empty($graphicTabImageSources['src']) && !empty($graphicTabImageSources['type']))
                                        <source srcset="{{ $graphicTabImageSources['src'] }}" type="{{ $graphicTabImageSources['type'] }}">
                                    @endif
                                    <img src="{{ $graphicTabImageSources['src'] }}" class=""
                                         data-src="{{ $graphicTabImageSources['src'] }}" @altAttrs(['type' => \App\Models\GalleryItem::class, 'id' => data_get($item, 'id')], 'add_image3_inner', data_get($item, 'add_image3_inner'), null, data_get($item, 'name'))>
                                </picture>
                            @endif
                            {!! $item['description'] !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tabs-item requirements">
            <div class="height-content">
                <div class="tabs-height requirements-content">
                    <div class="title">
                        <h2>{{ $data['photo_req_title'] }}</h2>
                    </div>
                    <div class="requirements-text clearfix">
                        <img src="{{ asset('img/requirements-img3.png') }}" alt="">
                        {!! $data['photo_req_text1'] !!}
                    </div>
                    <h3>{{ $data['photo_req_title2'] }}</h3>
                    <div class="requirements-items clearfix">
                        <div class="requirements-item">
                            {!! $data['photo_req_text2'] !!}
                            <img src="{{ asset('img/requirements-item-img1.png') }}" alt="">
                        </div>
                        <div class="requirements-item">
                            {!! $data['photo_req_text3'] !!}
                            <img src="{{ asset('img/requirements-item-img2.png') }}" alt="">
                        </div>
                        <div class="requirements-item">
                            {!! $data['photo_req_text4'] !!}
                            <img src="{{ asset('img/requirements-item-img3.png') }}" alt="">
                        </div>
                    </div>
                    {!! $data['photo_req_text5'] !!}
                </div>
            </div>
            <a data-collapse="requirements-content" class="collapse" href="javascript:void(0)"
               data-show="{{ trans('gl.show_btn') }}"
               data-hide="{{ trans('gl.hide_btn') }}"><span>{{ trans('gl.show_btn') }}</span></a>
        </div>
        <div class="tabs-item prices-sizes">
            <div class="height-content">
                <div class="tabs-height prices-sizes-content">
                    <div class="title">
                        <h2>{{ trans('gl.diff_sizes') }}</h2>
                    </div>
                    <div class="prices-content2">
                        <img src="{{ asset('img/prices-content2.png') }}" alt="">
                        <div class="prices-slider">
                            {{-- sizes --}}
                            @php
                                $custom_sizes = explode(',', $item['sizes_cals']);

                                if(isset($custom_sizes[0])){
                                $custom_price0 = get_string_between($custom_sizes[0], '[', ']');
                                }else{
                                $custom_price0 = 0;
                                }

                                if(isset($custom_sizes[1])){
                                $custom_price1 = get_string_between($custom_sizes[1], '[', ']');
                                }else{
                                $custom_price1 = 0;
                                }

                                if(isset($custom_sizes[2])){
                                $custom_price2 = get_string_between($custom_sizes[2], '[', ']');
                                }else{
                                $custom_price2 = 0;
                                }

                                if(isset($custom_sizes[3])){
                                $custom_price3 = get_string_between($custom_sizes[3], '[', ']');
                                }else{
                                $custom_price3 = 0;
                                }

                                if(isset($custom_sizes[4])){
                                $custom_price4 = get_string_between($custom_sizes[4], '[', ']');
                                }else{
                                $custom_price4 = 0;
                                }

                                if(isset($custom_sizes[5])){
                                $custom_price5 = get_string_between($custom_sizes[5], '[', ']');
                                }else{
                                $custom_price5 = 0;
                                }
                            @endphp
                            {{-- $item --}}
                            {{-- endsizes --}}
                            <div class="persons-items js_persons_all" data-var="{{ $gall_type_prive_val }}"
                                 id="js_prices_list" data-persons={{ $item['custom_users_prices'] }}>
                                <div class="persons-item">
                                    <h5>30{{ trans('gl.cm') }} х 40{{ trans('gl.cm') }}</h5>
                                    <div class="item">
                                        <div class="persons-title">
                                            {!! trans('gl.enter_people_count') !!}
                                        </div>
                                        <div class="input">
                                            @php
                                                $custom_users_count = explode(',', $item['custom_users_prices']);
                                            @endphp
                                            <select name="users_count" id="u_count">
                                                <option value="#"></option>
                                                @if(is_array($custom_users_count) && !empty($custom_users_count) &&
                                                $custom_users_count[0] != "")
                                                    @foreach($custom_users_count as $user)
                                                        @php
                                                            $price = get_string_between($user, '[', ']');
                                                            $user_count = substr($user, 0, strpos($user, "["));
                                                        @endphp
                                                        <option value="{{ $price }}">{{ $user_count }}</option>
                                                    @endforeach
                                                @endif
                                            </select>

                                            @foreach($sizes as $size)
                                                @if($size->size == '30x40')
                                                    <input class="js_size_val" type="hidden" name="size_val"
                                                           value="{{ $custom_price0 }}">
                                                @endif
                                            @endforeach
                                        </div>
                                        <div class="sum">
                                            <p>{{ trans('gl.total_price') }}</p>
                                        </div>
                                        <strong><span class="js__total">0</span> &euro;</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="persons-items">
                                <div class="persons-item">
                                    <h5>40{{ trans('gl.cm') }} х 60{{ trans('gl.cm') }}</h5>
                                    <div class="item">
                                        <div class="persons-title">
                                            {!! trans('gl.enter_people_count') !!}
                                        </div>
                                        <div class="input">
                                            @php
                                                $custom_users_count = explode(',', $item['custom_users_prices']);
                                            @endphp
                                            <select name="users_count" id="u_count">
                                                <option value="#"></option>
                                                @if(is_array($custom_users_count) && !empty($custom_users_count) &&
                                                $custom_users_count[0] != "")
                                                    @foreach($custom_users_count as $user)
                                                        @php
                                                            $price = get_string_between($user, '[', ']');
                                                            $user_count = substr($user, 0, strpos($user, "["));
                                                        @endphp
                                                        <option value="{{ $price }}">{{ $user_count }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @foreach($sizes as $size)
                                                @if($size->size == '40x60')
                                                    <input class="js_size_val" type="hidden" name="size_val"
                                                           value="{{ $custom_price2 }}">
                                                @endif
                                            @endforeach
                                        </div>
                                        <div class="sum">
                                            <p>{{ trans('gl.total_price') }}</p>
                                        </div>
                                        <strong><span class="js__total">0</span> &euro;</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="persons-items">
                                <div class="persons-item">
                                    <h5>50{{ trans('gl.cm') }} х 70{{ trans('gl.cm') }}</h5>
                                    <div class="item">
                                        <div class="persons-title">
                                            {!! trans('gl.enter_people_count') !!}
                                        </div>
                                        <div class="input">
                                            @php
                                                $custom_users_count = explode(',', $item['custom_users_prices']);
                                            @endphp
                                            <select name="users_count" id="u_count">
                                                <option value="#"></option>
                                                @if(is_array($custom_users_count) && !empty($custom_users_count) &&
                                                $custom_users_count[0] != "")
                                                    @foreach($custom_users_count as $user)
                                                        @php
                                                            $price = get_string_between($user, '[', ']');
                                                            $user_count = substr($user, 0, strpos($user, "["));
                                                        @endphp
                                                        <option value="{{ $price }}">{{ $user_count }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @foreach($sizes as $size)
                                                @if($size->size == '50x70')
                                                    <input class="js_size_val" type="hidden" name="size_val"
                                                           value="{{ $custom_price3 }}">
                                                @endif
                                            @endforeach
                                        </div>
                                        <div class="sum">
                                            <p>{{ trans('gl.total_price') }}</p>
                                        </div>
                                        <strong><span class="js__total">0</span> &euro;</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="persons-items">
                                <div class="persons-item">
                                    <h5>60{{ trans('gl.cm') }} х 90{{ trans('gl.cm') }}</h5>
                                    <div class="item">
                                        <div class="persons-title">
                                            {!! trans('gl.enter_people_count') !!}
                                        </div>
                                        <div class="input">
                                            @php
                                                $custom_users_count = explode(',', $item['custom_users_prices']);
                                            @endphp
                                            <select name="users_count" id="u_count">
                                                <option value="#"></option>
                                                @if(is_array($custom_users_count) && !empty($custom_users_count) &&
                                                $custom_users_count[0] != "")
                                                    @foreach($custom_users_count as $user)
                                                        @php
                                                            $price = get_string_between($user, '[', ']');
                                                            $user_count = substr($user, 0, strpos($user, "["));
                                                        @endphp
                                                        <option value="{{ $price }}">{{ $user_count }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @foreach($sizes as $size)
                                                @if($size->size == '60x90')
                                                    <input class="js_size_val" type="hidden" name="size_val"
                                                           value="{{ $custom_price4 }}">
                                                @endif
                                            @endforeach
                                        </div>
                                        <div class="sum">
                                            <p>{{ trans('gl.total_price') }}</p>
                                        </div>
                                        <strong><span class="js__total">0</span> &euro;</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="persons-items">
                                <div class="persons-item">
                                    <h5>70{{ trans('gl.cm') }} х 100{{ trans('gl.cm') }}</h5>
                                    <div class="item">
                                        <div class="persons-title">
                                            {!! trans('gl.enter_people_count') !!}
                                        </div>
                                        <div class="input">
                                            @php
                                                $custom_users_count = explode(',', $item['custom_users_prices']);
                                            @endphp
                                            <select name="users_count" id="u_count">
                                                <option value="#"></option>
                                                @if(is_array($custom_users_count) && !empty($custom_users_count) &&
                                                $custom_users_count[0] != "")
                                                    @foreach($custom_users_count as $user)
                                                        @php
                                                            $price = get_string_between($user, '[', ']');
                                                            $user_count = substr($user, 0, strpos($user, "["));
                                                        @endphp
                                                        <option value="{{ $price }}">{{ $user_count }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @foreach($sizes as $size)
                                                @if($size->size == '70x100')
                                                    <input class="js_size_val" type="hidden" name="size_val"
                                                           value="{{ $custom_price5 }}">
                                                @endif
                                            @endforeach
                                        </div>
                                        <div class="sum">
                                            <p>{{ trans('gl.total_price') }}</p>
                                        </div>
                                        <strong><span class="js__total">0</span> &euro;</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="persons-items">
                                <div class="persons-item">
                                    <h5>80{{ trans('gl.cm') }} х 120{{ trans('gl.cm') }}</h5>
                                    <div class="item">
                                        <div class="persons-title">
                                            {!! trans('gl.enter_people_count') !!}
                                        </div>
                                        <div class="input">
                                            @php
                                                $custom_users_count = explode(',', $item['custom_users_prices']);
                                            @endphp
                                            <select name="users_count" id="u_count">
                                                <option value="#"></option>
                                                @if(is_array($custom_users_count) && !empty($custom_users_count) &&
                                                $custom_users_count[0] != "")
                                                    @foreach($custom_users_count as $user)
                                                        @php
                                                            $price = get_string_between($user, '[', ']');
                                                            $user_count = substr($user, 0, strpos($user, "["));
                                                        @endphp
                                                        <option value="{{ $price }}">{{ $user_count }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @foreach($sizes as $size)
                                                @if($size->size == '80x120')
                                                    <input class="js_size_val" type="hidden" name="size_val"
                                                           value="{{ $size->price }}">
                                                @endif
                                            @endforeach
                                        </div>
                                        <div class="sum">
                                            <p>{{ trans('gl.total_price') }}</p>
                                        </div>
                                        <strong><span class="js__total">0</span> &euro;</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {!! $data['price_size_after_form_list'] !!}
                    </div>
                    <div class="components">
                        <div class="components-title">
                            <h2>{!! $data['img_part_title'] !!}</h2>
                        </div>
                        <div class="components-items">
                            <div class="components-item">
                                <div class="img">
                                    <img src="{{ asset('img/components-item-img.png') }}" alt="">
                                </div>
                                {!! $data['img_part_text1'] !!}
                            </div>
                            <div class="components-item">
                                <div class="img">
                                    <img src=" {{ asset('img/components-item-img2.png') }}" alt="">
                                </div>
                                {!! $data['img_part_text2'] !!}
                            </div>
                            <div class="components-item">
                                <div class="img">
                                    <img src=" {{ asset('img/components-item-img3.png') }}" alt="">
                                </div>
                                {!! $data['img_part_text3'] !!}
                            </div>
                            <div class="components-item">
                                <div class="img">
                                    <img src=" {{ asset('img/components-item-img4.png') }}" alt="">
                                </div>
                                {!! $data['img_part_text4'] !!}
                            </div>
                            <div class="components-item">
                                <div class="img">
                                    <img src=" {{ asset('img/components-item-img5.png') }}" alt="">
                                </div>
                                {!! $data['img_part_text5'] !!}
                            </div>
                        </div>
                    </div>
                    <div class="quality">
                        <div class="quality-title">
                            <h2>{!! $data['img_qual_title'] !!}</h2>
                        </div>
                        <div class="quality-content">
                            <img src="{{ asset('img/quality-img.png') }}" alt="">
                            <div class="quality-items">
                                <div class="quality-item">
                                    {!! $data['img_qual_text1'] !!}
                                </div>
                                <div class="quality-item">
                                    {!! $data['img_qual_text2'] !!}
                                </div>
                                <div class="quality-item">
                                    {!! $data['img_qual_text3'] !!}
                                </div>
                                <div class="quality-item">
                                    {!! $data['img_qual_text4'] !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="waiting">
                        {!! $data['img_qual_bot_title'] !!}
                        <a href="javascript:void(0)"><span>{!! $data['img_qual_order_text'] !!}</span></a>
                    </div>
                </div>
            </div>
            <a data-collapse="prices-sizes-content" class="collapse" href="javascript:void(0)">
                <span data-show="{{ trans('gl.show_btn') }}"
                      data-hide="{{ trans('gl.hide_btn') }}">{{ trans('gl.show_btn') }}</span>
            </a>
        </div>
        <div class="tabs-item tabs-work">
            <div class="height-content">
                <div class="tabs-height tabs-work-content">
                    <div class="title">
                        <h2>{{ trans('gl.all_our_works') }}</h2>
                    </div>
                    <div class="tabs-work-slider">
                        @if(isset($is_oil))
                            @if($graph_items)
                                @php
                                    $works = json_decode($graph_items, true);
                                @endphp
                                @foreach(array_chunk($works,2) as $item)
                                    <div class="tabs-work-item chunk">
                                        @php
                                            $i=0;
                                        @endphp
                                        @foreach($item as $img)
                                            @php
                                                $i++;
                                            @endphp
                                            <div class="tab-chunk tab-chunk-{{ $i }}">
                                                @php
                                                    $img = str_replace('\\', '/', $img);
                                                @endphp
                                                <img class="lazy"
                                                     src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs="
                                                     data-src="/storage/{{ $img  }}" alt="">
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            @endif
                        @else
                            @if($graph_works)
                                @php
                                    $works = json_decode($graph_works, true);
                                @endphp
                                @foreach(array_chunk($works,2) as $item)
                                    <div class="tabs-work-item chunk">
                                        @php
                                            $i=0;
                                        @endphp
                                        @foreach($item as $img)
                                            @php
                                                $i++;
                                            @endphp
                                            <div class="tab-chunk tab-chunk-{{ $i }}">
                                                @php
                                                    $img = str_replace('\\', '/', $img);
                                                @endphp
                                                <img class="lazy"
                                                     src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8Xw8AAoMBgDTD2qgAAAAASUVORK5CYII="
                                                     data-src="/storage/{{ $img }}" alt="">
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            <a data-collapse="tabs-work-content" class="collapse" href="javascript:void(0)">
                <span data-show="{{ trans('gl.show_btn') }}"
                      data-hide="{{ trans('gl.hide_btn') }}">{{ trans('gl.show_btn') }}</span>
            </a>
        </div>
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
                                <img src="{{ asset('img/service-item-img.png' )}}" alt="">
                                <p>{!! $canvas_work_serv['why1_title'] !!}</p>
                            </div>
                            <div class="service-item">
                                <img src="{{ asset('img/service-item-img2.png' )}}" alt="">
                                <p>{!! $canvas_work_serv['why2_title'] !!}</p>
                            </div>
                            <div class="service-item">
                                <img src="{{ asset('img/service-item-img3.png' )}}" alt="">
                                <p>{!! $canvas_work_serv['why3_title'] !!}</p>
                            </div>
                            <div class="service-item">
                                <img src="{{ asset('img/service-item-img4.png' )}}" alt="">
                                <p>{!! $canvas_work_serv['why4_title'] !!}</p>
                            </div>
                            <div class="service-item">
                                <img src="{{ asset('img/service-item-img5.png' )}}" alt="">
                                <p>{!! $canvas_work_serv['why5_title'] !!}</p>
                            </div>
                            <div class="service-item">
                                <img src="{{ asset('img/service-item-img6.png' )}}" alt="">
                                <p>
                                <p>{!! $canvas_work_serv['why6_title'] !!}</p>
                                </p>
                            </div>
                            <div class="service-item">
                                <img src="{{ asset('img/service-item-img7.png' )}}" alt="">
                                <p>
                                <p>{!! $canvas_work_serv['why7_title'] !!}</p>
                                </p>
                            </div>
                            <div class="service-item">
                                <img src="{{ asset('img/service-item-img8.png' )}}" alt="">
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
            <a data-collapse="service-quality-content" class="collapse" href="javascript:void(0)">
                <span data-show="{{ trans('gl.show_btn') }}"
                      data-hide="{{ trans('gl.hide_btn') }}">{{ trans('gl.show_btn') }}</span>
            </a>
        </div>
        @include('partials.canvas.timings_6_block')
    </div>
</section>
