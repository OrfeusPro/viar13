<style>
    .portrait-list__img {
    cursor: pointer;
    }
    .popup-wrapper .formalization__subrow
    {
    display: none;
    }
    #sharj .kviz-radio > label > span,#obraz .kviz-radio > label > span {
        padding-left: 30px;
        /* white-space: nowrap; */
    }

    #sharj .kviz-radio .check, #obraz .kviz-radio .check {
        position: absolute;
        /* top: 50%;
        left: 0;
        transform: translateY(-50%); */
    }
</style>
<form class="formalization simpson-formalization" id="{{$template_id}}" >
            <div style="display: none">
                <div class="js_canvas_type_item kviz-radio_active" data-price="0"></div>
                <input type="checkbox" class="js_art_decor" checked value="0">
                <input type="hidden" name="obraz_img" value="">
                <input type="hidden" name="obraz_title" value="">
                <div class="js_set_item kviz-radio_active"><input type="hidden" name="" value="0"></div>
                <div class="js__frame_item kviz-radio_active" data-price="0"></div>
            </div>

            <div class="formalization__block">
                <div class="formalization__block--top">
                    <div class="formalization__block--top-inner">

                        @if ($template_id=='sharj' && isset($main_page))
                        <h2 class="block-title h2_old">
                            @if ($h2_titles->fourth_block_h2!='')
                                {!! $h2_titles->fourth_block_h2 !!}
                            @else
                                @lang("sharj.translate37")
                            @endif
                        </h2>
                        @else
                            <div class="block-title h2_old">
                            @lang("sharj.translate37")
                            </div>
                        @endif

                        <div class="formalization__subrow" style="display: none;">
                            <div class="text">
                                <p><span class="orange">@lang('portrait_royal.popup_subtitle')</span></p>
                                <p>@lang('portrait_royal.popup_subtitle_2')</p>
                                <svg width="115" height="52" viewBox="0 0 115 52" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M114.231 40.4564C114.336 40.2012 114.215 39.9088 113.96 39.8032L109.801 38.0829C109.546 37.9774 109.254 38.0986 109.148 38.3538C109.043 38.609 109.164 38.9014 109.419 39.007L113.115 40.5361L111.586 44.2323C111.481 44.4875 111.602 44.7799 111.857 44.8855C112.112 44.991 112.405 44.8698 112.51 44.6146L114.231 40.4564ZM1.00022 0.530259C0.51664 0.657361 0.516794 0.657944 0.517027 0.658829C0.517211 0.659522 0.517523 0.660702 0.517889 0.662081C0.518621 0.664839 0.519669 0.668776 0.521038 0.673883C0.523775 0.684097 0.527792 0.698984 0.533113 0.718457C0.543755 0.757397 0.559612 0.814662 0.580881 0.889512C0.623422 1.03922 0.687614 1.25928 0.77503 1.54375C0.949857 2.11269 1.21761 2.93941 1.59086 3.9764C2.33729 6.05016 3.50609 8.96614 5.19808 12.3438C8.5809 19.0967 14.0621 27.7089 22.453 35.1217C30.8486 42.5387 42.1519 48.7497 57.1588 50.6954C72.1616 52.6406 90.832 50.3186 113.96 40.7271L113.577 39.8034C90.5712 49.3442 72.0787 51.6215 57.2873 49.7037C42.5 47.7864 31.3781 41.6721 23.115 34.3723C14.8473 27.0683 9.43659 18.5721 6.09217 11.8959C4.42055 8.55891 3.2669 5.68011 2.53177 3.63774C2.16424 2.61666 1.90145 1.80498 1.73092 1.25003C1.64566 0.972555 1.58347 0.759299 1.5428 0.616175C1.52246 0.544613 1.50751 0.49059 1.49774 0.454837C1.49286 0.436968 1.48927 0.423657 1.48695 0.415013C1.48579 0.410694 1.48495 0.407539 1.48443 0.405555C1.48416 0.404567 1.48401 0.403969 1.48387 0.403468C1.48379 0.403165 1.48379 0.403158 1.00022 0.530259Z"
                                        fill="#FA7846"></path>
                                </svg>
                            </div>
                            <picture>
                                {{--<source media="(max-width: 576px)"
                                        srcset="{{ ver_asset('images/sharj/subRow1.webp') }}" type="image/webp">
                                <source srcset="{{ ver_asset('images/sharj/subRow1.webp') }}" type="image/webp">--}}
                     <img class="popup-dyn-image" width="194" height="236" src="{{ ver_asset('images/sharj/subRow1.webp') }}" alt="">
                            </picture>
                        </div>
                        <div class="formalization__col">
                            <div class="formalization-items">
                                <div class="formalization-item">
                                    <div class="formalization-box">
                                        <div class="formalization-tab">
                                            <img width="25" height="25"  src="{{ ver_asset('images/sharj/format1.svg') }}" alt="">
                                            <p>@lang("sharj.translate38")</p>
                                            <span class="tab-icon"></span>
                                        </div>
                                        <div class="formalization-content">
                                            <div class="formalization-content--inner">
                                                <div class="kviz-c-group kviz-full js_plot_type">
                                                    <div class="kviz-group__item kviz-grid">
                                                        <div class="kviz-radio js-checkbox kviz-radio_active">
                                                            <em class="check"></em>
                                                            <label>
                                                                <span>{{ trans('portrait_royal.form_group_type_plot') }}</span>
                                                                <input type="radio" name="type" value="canvas"
                                                                       checked="checked">
                                                                <picture class="kviz-image">
                                                                    <source
                                                                        srcset="{{ asset(config('theme.current') . 'images/sharj/new/categories/holstSharj.webp') }}"
                                                                        type="image/webp">
                                                                    <img width="112" height="119"
                                                                         src="{{ asset(config('theme.current') . 'images/sharj/new/categories/holstSharj.webp') }}"
                                                                         alt="">
                                                                </picture>
                                                            </label>
                                                        </div>
                                                        <div class="kviz-radio js-checkbox">
                                                            <em class="check"></em>
                                                            <label>
                                                                <span>{{ trans('portrait_royal.form_group_type_paper') }}</span>
                                                                <input type="radio" name="type" value="paper">
                                                                <picture class="kviz-image">
                                                                    <source
                                                                        srcset="{{ asset(config('theme.current') . 'images/sharj/new/categories/paperSharj.webp') }}"
                                                                        type="image/webp">
                                                                    <img width="112" height="119"
                                                                         src="{{ asset(config('theme.current') . 'images/sharj/new/categories/paperSharj.webp') }}"
                                                                         alt="">
                                                                </picture>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="formalization-prompt">
                                        <div class="formalization-prompt--wrapper">
                                            <div class="formalization-prompt--inner">
                                                <img width="56" height="58"
                                                     src="{{ ver_asset('images/sharj/ficon1.webp') }}" alt="">
                                                <p>{{ trans('portrait_royal.form_group_type_hint') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="formalization-item">
                                    <div class="formalization-box">
                                        <div class="formalization-tab">
                                            <img width="25" height="25"
                                                 src="{{ ver_asset('images/sharj/format2.svg') }}" alt="">
                                            <p>{{ trans('portrait_royal.form_group_upload_title') }}</p>
                                            <span class="tab-icon"></span>
                                        </div>
                                        <div class="formalization-content" style="">
                                            <div class="formalization-content--inner">
                                                <div class="vz-art kviz-input">
                                                    <p class="vz-art kviz-input__title">
                                                        {!! trans('portrait_buy_form.step1_desc') !!}
                                                    </p>
                                                    <div class="file-save file-save__popup">
                                                        <div class="abs-close">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                 height="24" viewBox="0 0 24 24" fill="none"
                                                                 stroke="currentColor" stroke-width="2"
                                                                 stroke-linecap="round" stroke-linejoin="round"
                                                                 class="lucide lucide-x">
                                                                <line x1="18" x2="6" y1="6" y2="18"></line>
                                                                <line x1="6" x2="18" y1="6" y2="18"></line>
                                                            </svg>
                                                        </div>
                                                        <div class="file-save__item js-file-preview">
                                                            <svg>
                                                                <use
                                                                    xlink:href="{{ asset(env('THEME').'sprite.svg#save') }}"></use>
                                                            </svg>
                                                            <div class="file-save__title">
                                                                <p>{{ trans('homepage_new.load_photo') }}</p>
                                                                <span>{{ trans('homepage_new.pree_to_add_photo') }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="file-save__item js-file-upload">
                                                            <svg>
                                                                <use
                                                                    xlink:href="{{ asset(env('THEME').'sprite.svg#picture') }}"></use>
                                                            </svg>
                                                            <div class="file-save__title">
                                                                <p>photo_34567.jpg</p>
                                                                <span>2 Mb</span>
                                                            </div>
                                                        </div>
                                                        <div class="file-save__item js-file-multiple">
                                                            <svg>
                                                                <use
                                                                    xlink:href="{{ asset(env('THEME').'sprite.svg#check') }}"></use>
                                                            </svg>
                                                            <div class="file-save__title">
                                                                <p class="file-title_green">{{ trans('portrait.form_files_loaded') }}</p>
                                                            </div>
                                                        </div>
                                                        <input type="file" class="file-input" name="file[]" multiple=""
                                                               accept="image/*,image/heif,image/heic" aria-label="file input">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="images-container"></div>
                                        </div>
                                    </div>
                                    <div class="formalization-prompt" style="">
                                        <div class="formalization-prompt--wrapper">
                                            <div class="formalization-prompt--inner">
                                                <img width="56" height="58"
                                                     src="{{ ver_asset('images/sharj/ficon2.webp') }}" alt="">
                                                <p>{!! trans('portrait_buy_form.step1_bot_desc') !!}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="formalization-item"  >
                                    <div class="formalization-box">
                                        <div class="formalization-tab">
                                            <img width="25" height="25"
                                                 src="{{ ver_asset('images/sharj/format3.svg') }}" alt="">
                                            <p>@lang('portrait_royal.form_group_size_title')</p>
                                            <span class="tab-icon"></span>
                                        </div>
                                        <div class="formalization-content" style="">
                                            <div class="formalization-content--inner">
                                                <p class="underline-text sizesPopup-js">{{ trans('portrait_royal.form_group_size_preview') }}</p>
                                                <?php if ($template_id=='obraz') {$map_size=$temp_map_size;}  ?>
                                                <?php if ($template_id=='sharj') {$map_size=$map_size;}  ?>

                                                @foreach($map_size as $name=>$list)
                                                @php $is_first = $loop->index==0 @endphp
                                                <div class="kviz-row kviz-c-group js_sizes size_for size_for__{{$name}} {{$loop->index==0 ? 'active' :''}}"
                                                    @if(!$is_first) style="display: none" @endif>
                                                    @foreach ( collect($list)->chunk(5) as $_chunk)
                                                        @php $is_first = $is_first && $loop->index==0 @endphp
                                                        @php $is_first_inner =  $loop->index==0 @endphp
                                                        <div class="kviz-group__item">
                                                            @foreach($_chunk as $_item)

                                                                <div class=" calcSize kviz-radio js-checkbox  kviz-radio js-checkbox @if($is_first_inner && $loop->index ==0) kviz-radio_active @endif " data-stock="1" data-price="{{$_item->price}}">
                                                                    <em class="check"></em>
                                                                    <label>

                                                                             <span>{{ $_item->size[0] }}x{{ $_item->size[1] }} {{ trans('gl.cm') }} -
                                                                                 @if($_item->price_old != null)
                                                                                     <del>{{ $_item->price_old }}€</del> <b>{{ $_item->price }}€</b>
                                                                                 @else
                                                                                     {{ $_item->price_old }}€
                                                                                 @endif
                                                                             </span>

                                                                        <span class="jcf-radio"> <input type="radio" name="size" data-price="{{$_item->price}}"
                                                                               @if($is_first && $loop->index ==0) checked @endif
                                                                                      value="{{$_item->size[0]}}x{{$_item->size[1]}}" data-full-size="{{$_item->full_size}}"><span></span> </span>

                                                                    </label>
                                                                    {!! sale_icon($_item->full_size) !!}
                                                                </div>

                                                            @endforeach
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="formalization-prompt" style="">
                                        <div class="formalization-prompt--wrapper">
                                            <div class="formalization-prompt--inner">
                                                <img width="56" height="58"
                                                     src="{{ ver_asset('images/sharj/ficon3.webp') }}" alt="">
                                                <p>{!! trans('portrait_buy_form.step3_bot_desc') !!}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="formalization-item">
                                    <div class="formalization-box">
                                        <div class="formalization-tab">
                                            <img width="25" height="25"
                                                 src="{{ ver_asset('images/sharj/format4.svg') }}" alt="">
                                            <p>@lang('portrait_royal.form_group_people_title')</p>
                                            <span class="tab-icon"></span>
                                        </div>
                                        <div class="formalization-content" style="display: none;">
                                            <div class="formalization-content--inner">
                                                <div class="kviz-row">
                                                    <div class="
                                  kviz-group__item
                                  kviz-c-group kviz-custom-flexcol
                                ">
                                                        <div class="
                                    kviz-radio
                                    js-checkbox
                                    kviz-radio_active
                                    kviz-types
                                  " data-stock="1" data-count="1">
                                                            <em class="check"></em>
                                                            <label>
                                                                <span>{!! trans('portrait_buy_form.step4_personal') !!}</span>
                                                                <input type="radio" name="count" value="1">
                                                            </label>
                                                        </div>
                                                        <div class="kviz-radio js-checkbox kviz-types" data-stock="2"
                                                             data-count="2">
                                                            <em class="check"></em>
                                                            <label>
                                                                <span>{!! trans('portrait_buy_form.step4_group') !!}</span>
                                                                <input type="radio" name="count" value="Group">
                                                            </label>
                                                        </div>
                                                        <div class="input-group input_disabled">
                                                            <label
                                                                for="count1">{!! trans('portrait_buy_form.sterp4_enter_persons_count') !!}</label>
                                                            <input id="count1" class="kviz-input" type="text"
                                                                   disabled="">
                                                        </div>
                                                    </div>
                                                    <div class=" kviz-group__item kviz-c-group input_disabled ">
                                                        @foreach($list_person_price as $_item)
                                                            <div class="kviz-radio js__personal_checker js-checkbox kviz-types"
                                                                 data-stock="1">
                                                                <em class="check"></em>
                                                                <label>
                                                                    <span>{{$_item->count}} {!! trans('gl.chel') !!} + <b>{{$_item->price}}€</b></span>
                                                                    <input type="radio" name="users" value="{{$_item->count}}" data-count="{{$_item->count}}" data-price="{{$_item->price}}" disabled="">
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="formalization-prompt" style="display: none;">
                                        <div class="formalization-prompt--wrapper">
                                            <div class="formalization-prompt--inner">
                                                <img width="56" height="58" src="{{ asset('images/prompt4.png') }}"
                                                     alt="">
                                                <p>{!! trans('portrait_buy_form.step4_bot_desc') !!}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="formalization-item frame_for frame_for__canvas">
                                    <div class="formalization-box">
                                        <div class="formalization-tab">
                                            <img width="25" height="25"
                                                 src="{{ ver_asset('images/sharj/format5.svg') }}" alt="">
                                            <p>@lang('portrait_royal.form_group_frame_title')</p>
                                            <span class="tab-icon"></span>
                                        </div>
                                        <div class="formalization-content" style="">
                                            <div class="formalization-content--inner">
                                                <div class="frame-wrapper">
                                                    <div class="rc-filter-box">
                                                        <div class="filter-item mc-js-filter">
                                                            <a href="#">
                                                                <span>@lang("gallery.color")</span>
                                                                <svg width="13" height="8" viewBox="0 0 13 8"
                                                                     fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M12.2184 0.137457C12.1316 0.0458193 12.0317 4.23244e-08 11.9188 4.72591e-08C11.8059 5.21938e-08 11.7061 0.0458193 11.6192 0.137457L6.5 5.53952L1.38076 0.137458C1.29392 0.0458198 1.19405 5.16054e-07 1.08116 5.20989e-07C0.968269 5.25924e-07 0.868403 0.0458198 0.781562 0.137458L0.130261 0.824744C0.0434205 0.916381 -3.05028e-07 1.02176 -2.99821e-07 1.14089C-2.94614e-07 1.26002 0.0434205 1.36541 0.130261 1.45705L6.2004 7.86254C6.28724 7.95418 6.38711 8 6.5 8C6.61289 8 6.71276 7.95418 6.7996 7.86254L12.8697 1.45705C12.9566 1.36541 13 1.26002 13 1.14089C13 1.02176 12.9566 0.916381 12.8697 0.824743L12.2184 0.137457Z"
                                                                        fill="#FC8C5F"></path>
                                                                </svg>
                                                            </a>
                                                            <div class="filter-item--wrapper filter-color">
                                                                <p>@lang("gallery.select_color"):</p>
                                                                <ul class="color-grid" id="ram_colors">
                                                                    @foreach($map_frame->list_color as $_color)
                                                                        <li data-ramcolorid="{{$_color->id}}"
                                                                            style="background: {{$_color->color}};"></li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <div class="filter-item mc-js-filter">
                                                            <a href="#">
                                                                <span>@lang("gallery.material")</span>
                                                                <svg width="13" height="8" viewBox="0 0 13 8"
                                                                     fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M12.2184 0.137457C12.1316 0.0458193 12.0317 4.23244e-08 11.9188 4.72591e-08C11.8059 5.21938e-08 11.7061 0.0458193 11.6192 0.137457L6.5 5.53952L1.38076 0.137458C1.29392 0.0458198 1.19405 5.16054e-07 1.08116 5.20989e-07C0.968269 5.25924e-07 0.868403 0.0458198 0.781562 0.137458L0.130261 0.824744C0.0434205 0.916381 -3.05028e-07 1.02176 -2.99821e-07 1.14089C-2.94614e-07 1.26002 0.0434205 1.36541 0.130261 1.45705L6.2004 7.86254C6.28724 7.95418 6.38711 8 6.5 8C6.61289 8 6.71276 7.95418 6.7996 7.86254L12.8697 1.45705C12.9566 1.36541 13 1.26002 13 1.14089C13 1.02176 12.9566 0.916381 12.8697 0.824743L12.2184 0.137457Z"
                                                                        fill="#FC8C5F"></path>
                                                                </svg>
                                                            </a>
                                                            <div class="filter-item--wrapper" style="display: none;">
                                                                <p>@lang("gallery.select_material")</p>
                                                                <ul class="filter-material" id="ram_materials">
                                                                    @foreach($map_frame->list_material as $_material)
                                                                        <li data-rammaterialid="{{$_material->id}}">
                                                                            <svg width="15" height="15"
                                                                                 viewBox="0 0 15 15" fill="none"
                                                                                 xmlns="http://www.w3.org/2000/svg">
                                                                                <g class="clip0_309_246">
                                                                                    <path
                                                                                        d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z"
                                                                                        fill="#FA7846"></path>
                                                                                </g>
                                                                                <defs>
                                                                                    <clipPath class="clip0_309_246">
                                                                                        <rect width="15" height="15"
                                                                                              fill="white"></rect>
                                                                                    </clipPath>
                                                                                </defs>
                                                                            </svg>
                                                                            <span>{{$_material->name}}</span>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="rc-row">
                                                        <div class="rc-f-selected-container">
                                                            {{--<div class="mc-f-selected" data-id="1">
                                                                <div>
                                                                    Золотой
                                                                </div>
                                                                <svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <rect width="10.4429" height="0.870241" transform="matrix(0.707093 -0.707121 0.707093 0.707121 0.00146484 7.38428)" fill="#1E2533"></rect>
                                                                    <rect width="10.4429" height="0.870241" transform="matrix(-0.707092 -0.707121 0.707092 -0.707121 7.38428 8)" fill="#1E2533"></rect>
                                                                </svg>
                                                            </div>
                                                            <div class="mc-f-selected" data-id="2">
                                                                <div>
                                                                    Дерево
                                                                </div>
                                                                <svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <rect width="10.4429" height="0.870241" transform="matrix(0.707093 -0.707121 0.707093 0.707121 0.00146484 7.38428)" fill="#1E2533"></rect>
                                                                    <rect width="10.4429" height="0.870241" transform="matrix(-0.707092 -0.707121 0.707092 -0.707121 7.38428 8)" fill="#1E2533"></rect>
                                                                </svg>
                                                            </div>--}}
                                                        </div>
                                                        <div class="mc-search">
                                                            <div class="mc-input">
                                                                <input id="ram_search"
                                                                       data-route="https://viarcanvas.com/get/ram_search"
                                                                       type="search" name="search_id"
                                                                       placeholder="@lang('gallery.search_sku')">
                                                            </div>
                                                            <button>
                                                                <svg width="18" height="18" viewBox="0 0 18 18"
                                                                     fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <g clip-path="url(#clip0_461_7169)">
                                                                        <path
                                                                            d="M18 17.2046L11.8996 11.1042C12.8959 9.92765 13.5 8.40895 13.5 6.75001C13.5 3.02783 10.4722 0 6.75001 0C3.02786 0 0 3.02783 0 6.75001C0 10.4722 3.02783 13.5 6.75001 13.5C8.40895 13.5 9.92765 12.8959 11.1042 11.8996L17.2046 18L18 17.2046ZM6.75001 12.375C3.64857 12.375 1.12501 9.85145 1.12501 6.75001C1.12501 3.64857 3.64857 1.12501 6.75001 1.12501C9.85145 1.12501 12.375 3.64857 12.375 6.75001C12.375 9.85145 9.85145 12.375 6.75001 12.375Z"
                                                                            fill="white"></path>
                                                                    </g>
                                                                    <defs>
                                                                        <clipPath id="clip0_461_7169">
                                                                            <rect width="18" height="18"
                                                                                  fill="white"></rect>
                                                                        </clipPath>
                                                                    </defs>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="frame-block">
                                                        <div class="frames-list">
                                                            @foreach($map_frame->map->{\App\Models\CanvasRam::TYPE_BAGUETTE} as $_frame)
                                                                <div class="frame-item js__frame_item"
                                                                     data-code="{{ $_frame->id }}"
                                                                     data-price="{{ $_frame->price }}"
                                                                     data-src="{{ asset("/storage/".$_frame->img) }}"
                                                                     data-width="10">
                                                                    <picture>
                                                                        <source
                                                                            srcset="{{ asset("/storage/".$_frame->img) }}"
                                                                            type="image/jpeg">
                                                                        <img width="150" height="150"
                                                                             src="{{ asset("/storage/".$_frame->img) }}"
                                                                             alt="Viar" loading="lazy">
                                                                    </picture>
                                                                    <div class="fi-info">
                                                                        <p> @lang("gallery.code"):
                                                                            <span>{{ $_frame->id }}</span>
                                                                        </p>
                                                                        <b>+{{ $_frame->price }} €</b>
                                                                    </div>
                                                                    <div class="frame-info">
                                                                        <ul>
                                                                            <li>
                                                                                <svg width="8" height="8"
                                                                                     viewBox="0 0 8 8" fill="none"
                                                                                     xmlns="http://www.w3.org/2000/svg">
                                                                                    <g clip-path="url(#clip0_261_1144)">
                                                                                        <path
                                                                                            d="M7.90987 1.17996C7.78973 1.0598 7.5949 1.0598 7.47473 1.17996L2.48706 6.16764L0.525259 4.20583C0.405118 4.08565 0.210289 4.08565 0.0901174 4.20583C-0.0300391 4.32598 -0.0300391 4.5208 0.0901174 4.64097L2.26951 6.82036C2.38962 6.94047 2.58451 6.9405 2.70465 6.82036L7.90987 1.6151C8.03002 1.49493 8.03002 1.30012 7.90987 1.17996Z"
                                                                                            fill="#1F9750"></path>
                                                                                    </g>
                                                                                    <defs>
                                                                                        <clipPath id="clip0_261_1144">
                                                                                            <rect width="8" height="8"
                                                                                                  fill="white"></rect>
                                                                                        </clipPath>
                                                                                    </defs>
                                                                                </svg>
                                                                                <span>@lang('cart_new.in_stock')</span>
                                                                            </li>
                                                                            <li>
                                                                                <span>@lang("gallery.code"):</span>
                                                                                <span>{{ $_frame->id }}</span>
                                                                            </li>
                                                                            @isset($_frame->material->first()->name)
                                                                                <li>
                                                                                    <span>@lang("gallery.material"):</span>
                                                                                    <span>{{ $_frame->material->first()->getTranslatedAttribute('name') }}</span>
                                                                                </li>
                                                                            @endisset
                                                                            @isset($_frame->color->first()->name)
                                                                                <li>
                                                                                    <span>@lang("gallery.shade"):</span>
                                                                                    <span>{{ $_frame->color->first()->getTranslatedAttribute('name') }}</span>
                                                                                </li>
                                                                            @endisset
                                                                            <li>
                                                                                <span>@lang("modular_pictures.index28"):</span>
                                                                                <span>{{ $_frame->width }} cm</span>
                                                                            </li>
                                                                            <li>
                                                                                <span>@lang("modular_pictures.index30"):</span>
                                                                                <span>{{ $_frame->height }} cm</span>
                                                                            </li>
                                                                            <li>
                                                                                <span>@lang("gallery.ram_total_price"):</span>
                                                                                <span>{{ $_frame->price }} €</span>
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                    <div class="frame-js-popup">
                                                                        <svg width="28" height="28" viewBox="0 0 28 28"
                                                                             fill="none"
                                                                             xmlns="http://www.w3.org/2000/svg">
                                                                            <circle cx="14" cy="14" r="14"
                                                                                    fill="#FA7846"></circle>
                                                                            <g clip-path="url(#clip0_877_8)">
                                                                                <path
                                                                                    d="M22.6 21.9106L17.313 16.6236C18.1764 15.604 18.7 14.2878 18.7 12.85C18.7 9.62412 16.0759 7 12.85 7C9.62414 7 7 9.62412 7 12.85C7 16.0759 9.62412 18.7 12.85 18.7C14.2878 18.7 15.604 18.1764 16.6236 17.313L21.9106 22.6L22.6 21.9106ZM12.85 17.725C10.1621 17.725 7.97501 15.5379 7.97501 12.85C7.97501 10.1621 10.1621 7.97501 12.85 7.97501C15.5379 7.97501 17.725 10.1621 17.725 12.85C17.725 15.5379 15.5379 17.725 12.85 17.725Z"
                                                                                    fill="white"></path>
                                                                                <rect width="6.26573" height="0.68813"
                                                                                      transform="matrix(-2.0104e-05 -1 1 2.0104e-05 12.4766 15.9268)"
                                                                                      fill="white"></rect>
                                                                                <rect width="6.26574" height="0.658255"
                                                                                      transform="matrix(-1 -2.03991e-05 -2.05255e-05 -1 15.9277 13.0557)"
                                                                                      fill="white"></rect>
                                                                            </g>
                                                                            <defs>
                                                                                <clipPath id="clip0_877_8">
                                                                                    <rect width="15" height="15"
                                                                                          fill="white"
                                                                                          transform="translate(7 7)"></rect>
                                                                                </clipPath>
                                                                            </defs>
                                                                        </svg>
                                                                    </div>
                                                                    <div class="frame-radio"></div>
                                                                    <a href="#"
                                                                       class="mf-popup frame-js-popup"> @lang("gl.read_more") </a>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="formalization-prompt" style="">
                                        <div class="formalization-prompt--wrapper">
                                            <div class="formalization-prompt--inner">
                                                <img src="{{ asset('images/prompt7.png') }}" alt="">
                                                <p>@lang('portrait_royal.form_group_frame_note')</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="formalization-item frame_for frame_for__paper" style="display: none">
                                    <div class="formalization-box">
                                        <div class="formalization-tab">
                                            <img width="25" height="25"
                                                 src="{{ ver_asset('images/sharj/format5.svg') }}" alt="">
                                            <p>@lang('portrait_royal.form_group_frame2_title')</p>
                                            <span class="tab-icon"></span>
                                        </div>
                                        <div class="formalization-content" style="">
                                            <div class="formalization-content--inner no-padding">
                                                <div class="frame-outerContainer">
                                                    <div class="frame-trigger">
                                                        <div class="frame-trigger-tab kviz-radio_active">
                                                            <em class="check"></em>
                                                            @lang("simpson.popup-wrapper.formalization-item5.frame-trigger-tab1")
                                                        </div>
                                                        <div class="frame-trigger-tab">
                                                            <em class="check"></em>
                                                            @lang("simpson.popup-wrapper.formalization-item5.frame-trigger-tab2")
                                                        </div>
                                                    </div>
                                                    <div class="frame-blocks">
                                                        <div class="frame-wrapper">
                                                            <div class="frame-info__row">
                                                                @lang("simpson.popup-wrapper.formalization-item5.frame-wrapper1.frame-info__row")
                                                            </div>
                                                            <div class="frame-block">
                                                                <div class="frames-list">
                                                                    @foreach($map_frame->map->{\App\Models\CanvasRam::TYPE_PAPER} as $_frame)
                                                                        <div class="frame-item frame-standard js__frame_item"
                                                                             data-code="{{ $_frame->id }}"
                                                                             data-price="{{ $_frame->price }}"
                                                                             data-src="{{ asset("/storage/".$_frame->img) }}"
                                                                             data-width="10">
                                                                            <picture>
                                                                                <source
                                                                                    srcset="{{ asset("/storage/".$_frame->img) }}"
                                                                                    type="image/jpeg">
                                                                                <img width="150" height="150"
                                                                                     src="{{ asset("/storage/".$_frame->img) }}"
                                                                                     alt="Viar" loading="lazy">
                                                                            </picture>
                                                                            <div class="frame-info">
                                                                                <ul>
                                                                                    <li>
                                                                                        <svg width="8" height="8"
                                                                                             viewBox="0 0 8 8" fill="none"
                                                                                             xmlns="http://www.w3.org/2000/svg">
                                                                                            <g clip-path="url(#clip0_261_1144)">
                                                                                                <path
                                                                                                    d="M7.90987 1.17996C7.78973 1.0598 7.5949 1.0598 7.47473 1.17996L2.48706 6.16764L0.525259 4.20583C0.405118 4.08565 0.210289 4.08565 0.0901174 4.20583C-0.0300391 4.32598 -0.0300391 4.5208 0.0901174 4.64097L2.26951 6.82036C2.38962 6.94047 2.58451 6.9405 2.70465 6.82036L7.90987 1.6151C8.03002 1.49493 8.03002 1.30012 7.90987 1.17996Z"
                                                                                                    fill="#1F9750"></path>
                                                                                            </g>
                                                                                            <defs>
                                                                                                <clipPath
                                                                                                    id="clip0_261_1144">
                                                                                                    <rect width="8"
                                                                                                          height="8"
                                                                                                          fill="white"></rect>
                                                                                                </clipPath>
                                                                                            </defs>
                                                                                        </svg>
                                                                                        <span>@lang('cart_new.in_stock')</span>
                                                                                    </li>
                                                                                    <li>
                                                                                        <span>@lang("gallery.code"):</span>
                                                                                        <span>{{ $_frame->id }}</span>
                                                                                    </li>

                                                                                    @isset($_frame->material->first()->name)
                                                                                        <li>
                                                                                            <span>@lang("gallery.material"):</span>
                                                                                            <span>{{ $_frame->material->first()->getTranslatedAttribute('name') }}</span>
                                                                                        </li>
                                                                                    @endisset
                                                                                    @isset($_frame->color->first()->name)
                                                                                        <li>
                                                                                            <span>@lang("gallery.shade"):</span>
                                                                                            <span>{{ $_frame->color->first()->getTranslatedAttribute('name') }}</span>
                                                                                        </li>
                                                                                    @endisset
                                                                                    <li>
                                                                                        <span>@lang("modular_pictures.index28"):</span>
                                                                                        <span>{{ $_frame->width }} cm</span>
                                                                                    </li>
                                                                                    <li>
                                                                                        <span>@lang("modular_pictures.index30"):</span>
                                                                                        <span>{{ $_frame->height }} cm</span>
                                                                                    </li>
                                                                                    <li>
                                                                                        <span>@lang("gallery.ram_total_price"):</span>
                                                                                        <span>{{ $_frame->price }} €</span>
                                                                                    </li>
                                                                                </ul>
                                                                            </div>
                                                                            <a href="#" class="mf-popup frame-js-popup">
                                                                                @lang("gl.read_more") </a>
                                                                            <div class="frame-bottom">
                                                                                <div class="frame-radio"></div>
                                                                                @isset($_frame->color->first()->name)
                                                                                    <p>@lang('gallery.color'): <span class="orange">{{ $_frame->color->first()->getTranslatedAttribute('name') }}</span>
                                                                                    </p>
                                                                                @endif
                                                                            </div>

                                                                        </div>
                                                                    @endforeach

                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="frame-wrapper hidden-block">
                                                            <div class="rc-filter-box">
                                                                <div class="filter-item mc-js-filter">
                                                                    <a href="#">
                                                                        <span>@lang('gallery.color')</span>
                                                                        <svg width="13" height="8" viewBox="0 0 13 8"
                                                                             fill="none"
                                                                             xmlns="http://www.w3.org/2000/svg">
                                                                            <path
                                                                                d="M12.2184 0.137457C12.1316 0.0458193 12.0317 4.23244e-08 11.9188 4.72591e-08C11.8059 5.21938e-08 11.7061 0.0458193 11.6192 0.137457L6.5 5.53952L1.38076 0.137458C1.29392 0.0458198 1.19405 5.16054e-07 1.08116 5.20989e-07C0.968269 5.25924e-07 0.868403 0.0458198 0.781562 0.137458L0.130261 0.824744C0.0434205 0.916381 -3.05028e-07 1.02176 -2.99821e-07 1.14089C-2.94614e-07 1.26002 0.0434205 1.36541 0.130261 1.45705L6.2004 7.86254C6.28724 7.95418 6.38711 8 6.5 8C6.61289 8 6.71276 7.95418 6.7996 7.86254L12.8697 1.45705C12.9566 1.36541 13 1.26002 13 1.14089C13 1.02176 12.9566 0.916381 12.8697 0.824743L12.2184 0.137457Z"
                                                                                fill="#FC8C5F"></path>
                                                                        </svg>
                                                                    </a>
                                                                    <div class="filter-item--wrapper filter-color">
                                                                        <p>@lang("gallery.select_color"):</p>
                                                                        <ul class="color-grid" id="ram_colors">
                                                                            <li data-ramcolorid="1"
                                                                                style="background: #d4b725;"></li>
                                                                            <li data-ramcolorid="2"
                                                                                style="background: #ffea00;"></li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                                <div class="filter-item mc-js-filter">
                                                                    <a href="#">
                                                                        <span>@lang("gallery.material")</span>
                                                                        <svg width="13" height="8" viewBox="0 0 13 8"
                                                                             fill="none"
                                                                             xmlns="http://www.w3.org/2000/svg">
                                                                            <path
                                                                                d="M12.2184 0.137457C12.1316 0.0458193 12.0317 4.23244e-08 11.9188 4.72591e-08C11.8059 5.21938e-08 11.7061 0.0458193 11.6192 0.137457L6.5 5.53952L1.38076 0.137458C1.29392 0.0458198 1.19405 5.16054e-07 1.08116 5.20989e-07C0.968269 5.25924e-07 0.868403 0.0458198 0.781562 0.137458L0.130261 0.824744C0.0434205 0.916381 -3.05028e-07 1.02176 -2.99821e-07 1.14089C-2.94614e-07 1.26002 0.0434205 1.36541 0.130261 1.45705L6.2004 7.86254C6.28724 7.95418 6.38711 8 6.5 8C6.61289 8 6.71276 7.95418 6.7996 7.86254L12.8697 1.45705C12.9566 1.36541 13 1.26002 13 1.14089C13 1.02176 12.9566 0.916381 12.8697 0.824743L12.2184 0.137457Z"
                                                                                fill="#FC8C5F"></path>
                                                                        </svg>
                                                                    </a>
                                                                    <div class="filter-item--wrapper">
                                                                        <p>@lang("gallery.select_material")</p>
                                                                        <ul class="filter-material" id="ram_materials">
                                                                            <li data-rammaterialid="1">
                                                                                <svg width="15" height="15"
                                                                                     viewBox="0 0 15 15" fill="none"
                                                                                     xmlns="http://www.w3.org/2000/svg">
                                                                                    <g class="clip0_309_246">
                                                                                        <path
                                                                                            d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z"
                                                                                            fill="#FA7846"></path>
                                                                                    </g>
                                                                                    <defs>
                                                                                        <clipPath class="clip0_309_246">
                                                                                            <rect width="15" height="15"
                                                                                                  fill="white"></rect>
                                                                                        </clipPath>
                                                                                    </defs>
                                                                                </svg>
                                                                                <span>Пластик</span>
                                                                            </li>
                                                                            <li data-rammaterialid="2">
                                                                                <svg width="15" height="15"
                                                                                     viewBox="0 0 15 15" fill="none"
                                                                                     xmlns="http://www.w3.org/2000/svg">
                                                                                    <g class="clip0_309_246">
                                                                                        <path
                                                                                            d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z"
                                                                                            fill="#FA7846"></path>
                                                                                    </g>
                                                                                    <defs>
                                                                                        <clipPath class="clip0_309_246">
                                                                                            <rect width="15" height="15"
                                                                                                  fill="white"></rect>
                                                                                        </clipPath>
                                                                                    </defs>
                                                                                </svg>
                                                                                <span>Дерево</span>
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="rc-row">
                                                                <div class="rc-f-selected-container">
                                                                    <div class="mc-f-selected" data-id="1">
                                                                        <div>
                                                                            Золотой
                                                                        </div>
                                                                        <svg width="8" height="8" viewBox="0 0 8 8"
                                                                             fill="none"
                                                                             xmlns="http://www.w3.org/2000/svg">
                                                                            <rect width="10.4429" height="0.870241"
                                                                                  transform="matrix(0.707093 -0.707121 0.707093 0.707121 0.00146484 7.38428)"
                                                                                  fill="#1E2533"></rect>
                                                                            <rect width="10.4429" height="0.870241"
                                                                                  transform="matrix(-0.707092 -0.707121 0.707092 -0.707121 7.38428 8)"
                                                                                  fill="#1E2533"></rect>
                                                                        </svg>
                                                                    </div>
                                                                    <div class="mc-f-selected" data-id="2">
                                                                        <div>
                                                                            Дерево
                                                                        </div>
                                                                        <svg width="8" height="8" viewBox="0 0 8 8"
                                                                             fill="none"
                                                                             xmlns="http://www.w3.org/2000/svg">
                                                                            <rect width="10.4429" height="0.870241"
                                                                                  transform="matrix(0.707093 -0.707121 0.707093 0.707121 0.00146484 7.38428)"
                                                                                  fill="#1E2533"></rect>
                                                                            <rect width="10.4429" height="0.870241"
                                                                                  transform="matrix(-0.707092 -0.707121 0.707092 -0.707121 7.38428 8)"
                                                                                  fill="#1E2533"></rect>
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                                <div class="mc-search">
                                                                    <div class="mc-input">
                                                                        <input id="ram_search"
                                                                               data-route="https://viarcanvas.com/get/ram_search"
                                                                               type="search" name="search_id"
                                                                               placeholder="@lang('gallery.search_sku')">
                                                                    </div>
                                                                    <button>
                                                                        <svg width="18" height="18" viewBox="0 0 18 18"
                                                                             fill="none"
                                                                             xmlns="http://www.w3.org/2000/svg">
                                                                            <g clip-path="url(#clip0_461_7169)">
                                                                                <path
                                                                                    d="M18 17.2046L11.8996 11.1042C12.8959 9.92765 13.5 8.40895 13.5 6.75001C13.5 3.02783 10.4722 0 6.75001 0C3.02786 0 0 3.02783 0 6.75001C0 10.4722 3.02783 13.5 6.75001 13.5C8.40895 13.5 9.92765 12.8959 11.1042 11.8996L17.2046 18L18 17.2046ZM6.75001 12.375C3.64857 12.375 1.12501 9.85145 1.12501 6.75001C1.12501 3.64857 3.64857 1.12501 6.75001 1.12501C9.85145 1.12501 12.375 3.64857 12.375 6.75001C12.375 9.85145 9.85145 12.375 6.75001 12.375Z"
                                                                                    fill="white"></path>
                                                                            </g>
                                                                            <defs>
                                                                                <clipPath id="clip0_461_7169">
                                                                                    <rect width="18" height="18"
                                                                                          fill="white"></rect>
                                                                                </clipPath>
                                                                            </defs>
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <div class="frame-block">
                                                                <div class="frames-list">
                                                                    @foreach($map_frame->map->{\App\Models\CanvasRam::TYPE_PAPER_PREMIUM} as $_frame)
                                                                        <div class="frame-item js__frame_item"
                                                                             data-code="{{ $_frame->id }}"
                                                                             data-price="{{ $_frame->price }}"
                                                                             data-src="{{ asset("/storage/".$_frame->img) }}"
                                                                             data-width="10">
                                                                            <picture>
                                                                                <source
                                                                                    srcset="{{ asset("/storage/".$_frame->img) }}"
                                                                                    type="image/jpeg">
                                                                                <img width="150" height="150"
                                                                                     src="{{ asset("/storage/".$_frame->img) }}"
                                                                                     alt="Viar" loading="lazy">
                                                                            </picture>
                                                                            <div class="fi-info">
                                                                                <p> @lang("gallery.code"):
                                                                                    <span>{{ $_frame->id }}</span>
                                                                                </p>
                                                                                <b>+{{ $_frame->price }} €</b>
                                                                            </div>
                                                                            <div class="frame-info">
                                                                                <ul>
                                                                                    <li>
                                                                                        <svg width="8" height="8"
                                                                                             viewBox="0 0 8 8" fill="none"
                                                                                             xmlns="http://www.w3.org/2000/svg">
                                                                                            <g clip-path="url(#clip0_261_1144)">
                                                                                                <path
                                                                                                    d="M7.90987 1.17996C7.78973 1.0598 7.5949 1.0598 7.47473 1.17996L2.48706 6.16764L0.525259 4.20583C0.405118 4.08565 0.210289 4.08565 0.0901174 4.20583C-0.0300391 4.32598 -0.0300391 4.5208 0.0901174 4.64097L2.26951 6.82036C2.38962 6.94047 2.58451 6.9405 2.70465 6.82036L7.90987 1.6151C8.03002 1.49493 8.03002 1.30012 7.90987 1.17996Z"
                                                                                                    fill="#1F9750"></path>
                                                                                            </g>
                                                                                            <defs>
                                                                                                <clipPath id="clip0_261_1144">
                                                                                                    <rect width="8" height="8"
                                                                                                          fill="white"></rect>
                                                                                                </clipPath>
                                                                                            </defs>
                                                                                        </svg>
                                                                                        <span>@lang('cart_new.in_stock')</span>
                                                                                    </li>
                                                                                    <li>
                                                                                        <span>@lang("gallery.code"):</span>
                                                                                        <span>{{ $_frame->id }}</span>
                                                                                    </li>
                                                                                    @isset($_frame->material->first()->name)
                                                                                        <li>
                                                                                            <span>@lang("gallery.material"):</span>
                                                                                            <span>{{ $_frame->material->first()->getTranslatedAttribute('name') }}</span>
                                                                                        </li>
                                                                                    @endisset
                                                                                    @isset($_frame->color->first()->name)
                                                                                        <li>
                                                                                            <span>@lang("gallery.shade"):</span>
                                                                                            <span>{{ $_frame->color->first()->getTranslatedAttribute('name') }}</span>
                                                                                        </li>
                                                                                    @endisset
                                                                                    <li>
                                                                                        <span>@lang("modular_pictures.index28"):</span>
                                                                                        <span>{{ $_frame->width }} cm</span>
                                                                                    </li>
                                                                                    <li>
                                                                                        <span>@lang("modular_pictures.index30"):</span>
                                                                                        <span>{{ $_frame->height }} cm</span>
                                                                                    </li>
                                                                                    <li>
                                                                                        <span>@lang("gallery.ram_total_price"):</span>
                                                                                        <span>{{ $_frame->price }} €</span>
                                                                                    </li>
                                                                                </ul>
                                                                            </div>
                                                                            <div class="frame-js-popup">
                                                                                <svg width="28" height="28" viewBox="0 0 28 28"
                                                                                     fill="none"
                                                                                     xmlns="http://www.w3.org/2000/svg">
                                                                                    <circle cx="14" cy="14" r="14"
                                                                                            fill="#FA7846"></circle>
                                                                                    <g clip-path="url(#clip0_877_8)">
                                                                                        <path
                                                                                            d="M22.6 21.9106L17.313 16.6236C18.1764 15.604 18.7 14.2878 18.7 12.85C18.7 9.62412 16.0759 7 12.85 7C9.62414 7 7 9.62412 7 12.85C7 16.0759 9.62412 18.7 12.85 18.7C14.2878 18.7 15.604 18.1764 16.6236 17.313L21.9106 22.6L22.6 21.9106ZM12.85 17.725C10.1621 17.725 7.97501 15.5379 7.97501 12.85C7.97501 10.1621 10.1621 7.97501 12.85 7.97501C15.5379 7.97501 17.725 10.1621 17.725 12.85C17.725 15.5379 15.5379 17.725 12.85 17.725Z"
                                                                                            fill="white"></path>
                                                                                        <rect width="6.26573" height="0.68813"
                                                                                              transform="matrix(-2.0104e-05 -1 1 2.0104e-05 12.4766 15.9268)"
                                                                                              fill="white"></rect>
                                                                                        <rect width="6.26574" height="0.658255"
                                                                                              transform="matrix(-1 -2.03991e-05 -2.05255e-05 -1 15.9277 13.0557)"
                                                                                              fill="white"></rect>
                                                                                    </g>
                                                                                    <defs>
                                                                                        <clipPath id="clip0_877_8">
                                                                                            <rect width="15" height="15"
                                                                                                  fill="white"
                                                                                                  transform="translate(7 7)"></rect>
                                                                                        </clipPath>
                                                                                    </defs>
                                                                                </svg>
                                                                            </div>
                                                                            <div class="frame-radio"></div>
                                                                            <a href="#"
                                                                               class="mf-popup frame-js-popup"> @lang("gl.read_more") </a>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="formalization-prompt" style="">
                                        <div class="formalization-prompt--wrapper">
                                            <div class="formalization-prompt--inner">
                                                <img src="{{ asset('images/prompt7.png') }}" alt="">
                                                <p>@lang('portrait_royal.form_group_frame_note')</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="formalization-item">
                                    <div class="formalization-box">
                                        <div class="formalization-tab">
                                            <img width="25" height="25"
                                                 src="{{ ver_asset('images/sharj/format6.svg') }}" alt="">
                                            <p>{{ trans('portrait_buy_form.step_comment_title') }}</p>
                                            <span class="tab-icon"></span>
                                        </div>
                                        <div class="formalization-content">
                                            <div class="formalization-content--inner">
                                                <div class="input-group input-comments">
                                                    <label
                                                        for="comments1">{{ trans('portrait_buy_form.step_comment_ex') }}</label>
                                                    <div class="textarea-wrapper">
                                                        <textarea id="comments1" class="order_comments" name="comments"></textarea>
                                                    </div>
                                                </div>
                                                <div class="vz-art kviz-input">
                                                    <p class="vz-art kviz-input__title">
                                                        {{ trans('portrait_buy_form.step_comment_label') }}</p>
                                                    <div class="file-save file-save__popup">
                                                        <div class="abs-close">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                 height="24" viewBox="0 0 24 24" fill="none"
                                                                 stroke="currentColor" stroke-width="2"
                                                                 stroke-linecap="round" stroke-linejoin="round"
                                                                 class="lucide lucide-x">
                                                                <line x1="18" x2="6" y1="6" y2="18"></line>
                                                                <line x1="6" x2="18" y1="6" y2="18"></line>
                                                            </svg>
                                                        </div>
                                                        <div class="file-save__item js-file-preview">
                                                            <svg>
                                                                <use xlink:href="{{ asset(env('THEME').'sprite.svg#save') }}"></use>
                                                            </svg>
                                                            <div class="file-save__title">
                                                                <p>{{ trans('homepage_new.load_photo') }}</p>
                                                                <span>{{ trans('homepage_new.pree_to_add_photo') }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="file-save__item js-file-upload">
                                                            <svg>
                                                                <use xlink:href="{{ asset(env('THEME').'sprite.svg#picture') }}"></use>
                                                            </svg>
                                                            <div class="file-save__title">
                                                                <p>photo_34567.jpg</p>
                                                                <span>2 Mb</span>
                                                            </div>
                                                        </div>
                                                        <div class="file-save__item js-file-multiple">
                                                            <svg>
                                                                <use xlink:href="{{ asset(env('THEME').'sprite.svg#check') }}"></use>
                                                            </svg>
                                                            <div class="file-save__title">
                                                                <p class="file-title_green">Файлы загружены</p>
                                                            </div>
                                                        </div>
                                                        <input type="file" class="file-input" name="file[]" multiple=""
                                                               accept="image/*,image/heif,image/heic" aria-label="file input">
                                                    </div>
                                                </div>
                                                <div class="images-container"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="formalization-prompt">
                                        <div class="formalization-prompt--wrapper">
                                            <div class="formalization-prompt--inner">
                                                <img src="{{ asset('images/prompt9.png') }}" alt="">
                                                <p>{{ trans('portrait_buy_form.step_comment_bot_desc') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>

                </div>
                <div class="formalization__block--bottom">
                    <div class="formalization__final">
                        <div class="formalizaton__submit">
                            <div class="formalization-price">
                                {!! trans('portrait_buy_form.final_cost') !!} <span><span
                                        class="js_price">0</span> €</span>
                            </div>
                            <div class="formalization-btn js_submit_form" data-action="/basket/add/portrait"
                                 data-name="{{$item->name}}"
                                 data-pid="{{$item->id}}"> {!! trans('portrait_buy_form.final_add_to_bask') !!}</div>
                        </div>
                        <div class="formalization-bottom--check">
                            <div class="kviz-wrap">
                                <p>{!! trans('portrait_buy_form.final_pack') !!}</p>
                                <div class="kviz-c-row kviz-c-group">
                                    @if($sets)
                                        @foreach($sets as $set)
                                            <div
                                                class="set__item kviz-radio js_set_item js-checkbox @if($loop->last) kviz-radio_active @endif"
                                                data-stock="1">
                                                <em class="check"></em>
                                                <label>
                    <span>{{ $set->getTranslatedAttribute('name', app()->getLocale()) }}
                        <img src="{{ asset('images/icon/info.svg') }}" alt=""/></span>
                                                    <input class="js_set" @if($loop->last) checked @endif type="radio"
                                                           name="equipment" data-id="{{ $set['id'] }}"
                                                           value="{{ $set->price }}"/>
                                                </label>

                                                <div class="pic-pop">
                                                    <picture>
                                                        <source srcset="" type="image/webp">
                                                        <source srcset="{{ Voyager::image($set->image) }}"
                                                                type="image/jpeg">
                                                        <img loading="lazy" src="{{ Voyager::image($set->image) }}"
                                                             @altAttrs($set, 'image', data_get($set, 'image'))>
                                                    </picture>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            <div class="kviz-wrap">
                                <p>{!! trans('gl.srok_izg') !!}</p>
                                <div class="kviz-c-row kviz-c-group">
                                    <div class="kviz-radio js_terms js-checkbox kviz-radio_active" data-stock="1">
                                        <div class="check check-border"></div>
                                        <label>
                                            <span class="js_term__selected">{!! $AProductionTime->standart_text !!} {{ $AProductionTime->standart_price }} €</span>
                                            <input type="radio" name="equipment"
                                                   class="js_time"
                                                   value="{{ $AProductionTime->standart_price }}">
                                        </label>
                                    </div>
                                    <div class="kviz-radio js_terms js-checkbox" data-stock="2">
                                        <div class="check check-border"></div>
                                        <label>
                                            <span class="js_term__selected">{!! $AProductionTime->express_text !!} {{ $AProductionTime->express_price }} € </span>
                                            <input class="js_time" type="radio" name="equipment"  value="{{ $AProductionTime->express_price }}">
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="close-btn">
                    <img width="30" height="30" src="{{ ver_asset('images/sharj/cancel.svg') }}" alt="">
                </div>
            </div>
        </form>
