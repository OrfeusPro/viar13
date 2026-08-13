@php
    /** @var \Illuminate\Support\Collection|\App\Models\SiteImage[] $siteImages */
    $siteImages = $siteImages ?? \App\Models\SiteImage::where("is_show",true)->get();
    $kviz_1 = site_image('home_kviz_1', 'images/kviz/kviz-1.png', ['collection' => $siteImages]);
    $kviz_2 = site_image('home_kviz_2', 'images/kviz/kviz-2.png', ['collection' => $siteImages]);
    $kviz_3 = site_image('home_kviz_3', 'images/kviz/kviz-2.png', ['collection' => $siteImages]);
@endphp


<section class="emotion">

    <div class="hidden">
        <div id="step0_default" data-img="{{ asset('storage/' . $bot_form_step0_default->image) }}"></div>
        <div id="step1_default" data-img="{{ asset('storage/' . $bot_form_step1_default->image) }}"></div>
        <div id="step2_default" data-img="{{ asset('storage/' . $bot_form_step2_default->image) }}"></div>
        <div id="step3_default" data-img="{{ asset('storage/' . $bot_form_step3_default->image) }}"></div>
    </div>

    <div class="section-frame">
        <div class="emotion-title">
            @if (Route::currentRouteName() == 'delivery_page')
                <div class="page-title">{!! $bot_form['f_title'] !!}</div>
            @elseif(Route::currentRouteName() == 'home')
                <div class="page-title">{!! $bot_form['f_title'] !!}</div>
            @elseif (Route::currentRouteName() == 'about')
                <h2 class="page-title">{!! $bot_form['f_title'] !!}</h2>
            @else
                <h2 class="page-title">{!! $bot_form['f_title'] !!}</h2>
            @endif

            <p>{!! $bot_form['f_desc'] !!}</p>
        </div>
        <form action="{{ route('send_photo_form') }}" method="POST" enctype="multipart/form-data"
            class="kviz js_quiz" id="q__form" data-zero="{!! $bot_form['js_zero'] !!}" data-first="{!! $bot_form['js_first'] !!}"
            data-second="{!! $bot_form['js_second'] !!}" data-all_done="{!! $bot_form['all_done'] !!}"
            data-third="{!! $bot_form['js_third'] !!}" data-items="{!! $bot_form['no_select'] !!}"
            data-file="{!! $bot_form['data_no_file'] !!}" data-messeger="{!! $bot_form['data_no_messager'] !!}">
            @csrf
            <input type="text" name="website" autocomplete="off" style="display:none">
            <input type="hidden" name="form_ts" value="{{ time() }}">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="kviz-item__title h3_old">{!! $bot_form['f_main_title'] !!}</div>
            <div class="kviz-item" data-step="1">
                <div class="kviz-content">
                    <div class="kviz-step">
                        <div class="step-counter">{!! $bot_form['f_step'] !!} 1 {!! $bot_form['f_step_from'] !!} 4</div>
                        <div class="kviz-tabs">
                            <i class="fa-arrow-next"></i>
                            <a href="#" class="kviz-tab kviz-tab_active" data-tab="1">{!! $bot_form['f_for'] !!}</a>
                            <a href="#" class="kviz-tab" data-tab="2"> {!! $bot_form['f_evt'] !!}</a>
                        </div>
                    </div>
                    <div class="stock-full stock-full_tablet">
                        @if (isset($bot_form_step1_default->image))
                            <picture>
                                @if ($webpSrc = image_webp_url('storage/' . $bot_form_step1_default->image))
                                    <source srcset="{{ $webpSrc }}" type="image/webp">
                                @endif
                                <source srcset="{{ asset('storage/' . $bot_form_step1_default->image) }}" type="image/jpeg">
                                <img loading="lazy" src="{{ asset('storage/' . $bot_form_step1_default->image) }}" data-stock="1" class="stock-photo lozad" alt="img">
                            </picture>
                        @endif
                    </div>
                    <div class="tab-group" data-tab="1">
                        <div class="kviz-group__title h4_old">{!! $bot_form['f_fro_who'] !!}</div>
                        <div class="kviz-group__full">
                            <div class="kviz-group">
                                @if ($bot_form_step1_items)
                                    @foreach ($bot_form_step1_items as $item)
                                        <label for="item1__{{ $loop->index }}" class="kviz-radio js-checkbox"
                                            data-stock="@if ($loop->odd) 1 @else 2 @endif">
                                            <em class="check"></em>

                                                <span>{{ $item->getTranslatedAttribute('name') }}</span>
                                                <input type="radio" name="step1" id="item1__{{ $loop->index }}"
                                                    @if ($item->image) data-img="{{ asset('storage/' . $item->image) }}"  @else data-img="{{ asset('storage/' . $bot_form_step1_default->image) }}" @endif
                                                    value="{{ $item->getTranslatedAttribute('name') }}">

                                        </label>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="tab-group" data-tab="2">
                        <div class="kviz-group__title h4_old">{!! $bot_form['f_evt'] !!}</div>
                        <div class="kviz-group kviz-group_tab">
                            @if ($bot_form_step0_items)
                                @foreach ($bot_form_step0_items as $item)
                                    <label for="item2__{{ $loop->index }}" class="kviz-radio js-checkbox"
                                        data-stock="@if ($loop->odd) 1 @else 2 @endif">
                                        <em class="check"></em>
                                            <span>{{ $item->getTranslatedAttribute('name') }}</span>
                                            <input id="item2__{{ $loop->index }}" type="radio" name="step0"
                                                @if ($item->image) data-img="{{ asset('storage/' . $item->image) }}"  @else data-img="{{ asset('storage/' . $bot_form_step0_default->image) }}" @endif
                                                value="{{ $item->getTranslatedAttribute('name') }}">

                                    </label>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="kviz-button">
                        <a href="#" class="kviz-next">{!! $bot_form['f_next_step_btn'] !!} <i class="fa-arrow-next"></i></a>
                    </div>
                </div>
                <div class="kviz-stock">
                    <div class="stock-full">
                        <svg class="kviz-arrow">
                            <use xlink:href="{{ asset(env('THEME') . 'sprite.svg#kviz-arrow') }}"></use>
                        </svg>
                        <div class="stock-gift">
                            <svg>
                                <use xlink:href="{{ asset(env('THEME') . 'sprite.svg#gift') }}"></use>
                            </svg>
                            <p>{!! $bot_form['f_banner_title'] !!}</p>
                        </div>
                        @if (isset($bot_form_step1_default->image))
                            <picture>
                                @if ($webpSrc = image_webp_url('storage/' . $bot_form_step1_default->image))
                                    <source srcset="{{ $webpSrc }}" type="image/webp">
                                @endif
                                <source srcset="{{ asset('storage/' . $bot_form_step1_default->image) }}" type="image/jpeg">
                                <img loading="lazy" src="{{ asset('storage/' . $bot_form_step1_default->image) }}" data-stock="1" class="stock-photo lozad" alt="img">
                            </picture>
                        @endif
                    </div>
                    <p class="stock-list__title">{!! $bot_form['after_end_got'] !!}</p>
                    <div class="stock-list">
                        <div class="stock-item">
                            <p>
                                {!! $bot_form['f_banner_bot1'] !!}
                            </p>
                            <picture>
                                <source srcset="{{ asset('images/kviz/kviz-1.webp') }}" type="image/webp">
                                <source srcset="{{ asset('images/kviz/kviz-1.png') }}">
                                <img loading="lazy" class="lozad"
                                    src="{{ asset('images/kviz/kviz-1.png') }}" alt="img">
                            </picture>
                        </div>
                        <div class="stock-item">
                            <p>{!! $bot_form['f_banner_bot2'] !!}</p>
                            <picture>
                                <source srcset="{{ asset('images/kviz/kviz-2.webp') }}" type="image/webp">
                                <source srcset="{{ asset('images/kviz/kviz-2.png') }}">
                                <img loading="lazy" class="lozad"
                                    src="{{ asset('images/kviz/kviz-2.png') }}" alt="img">
                            </picture>
                        </div>
                        <div class="stock-item">
                            <p>{!! $bot_form['f_banner_bot3'] !!}</p>
                            <picture>
                                <source srcset="{{ asset('images/kviz/kviz-3.webp') }}" type="image/webp">
                                <source srcset="{{ asset('images/kviz/kviz-3.png') }}">
                                <img loading="lazy" class="lozad"
                                    src="{{ asset('images/kviz/kviz-3.png') }}" alt="img">
                            </picture>
                        </div>
                    </div>
                </div>
            </div>
            <div class="kviz-item" data-step="2">
                <div class="kviz-content">
                    <div class="kviz-step">
                        <div class="step-counter">{!! $bot_form['f_step'] !!} 2 {!! $bot_form['f_step_from'] !!} 4</div>
                    </div>
                    <div class="stock-full stock-full_tablet">
                        @if (isset($bot_form_step2_default->image))
                            <picture>
                                @if ($webpSrc = image_webp_url('storage/' . $bot_form_step2_default->image))
                                    <source srcset="{{ $webpSrc }}" type="image/webp">
                                @endif
                                <source srcset="{{ asset('storage/' . $bot_form_step2_default->image) }}" type="image/jpeg">
                                <img loading="lazy" src="{{ asset('storage/' . $bot_form_step2_default->image) }}"
                                    data-stock="2" class="stock-photo lozad" alt="img">
                            </picture>
                        @endif
                    </div>
                    <div class="kviz-group__title h4_old">{!! $bot_form['js_second'] !!}
                    </div>
                    <div class="kviz-group__full">
                        <div class="kviz-group__item kviz-group">
                            @if ($bot_form_step2_items)
                                @foreach ($bot_form_step2_items as $item)
                                    <label for="item3__{{ $loop->index }}" class="kviz-radio js-checkbox"
                                        data-stock="@if ($loop->odd) 1 @else 2 @endif">
                                        <em class="check"></em>

                                            <span>{{ $item->getTranslatedAttribute('name') }}</span>
                                            <input id="item3__{{ $loop->index }}" type="radio" name="step2"
                                                @if ($item->image) data-img="{{ asset('storage/' . $item->image) }}"  @else data-img="{{ asset('storage/' . $bot_form_step2_default->image) }}" @endif
                                                value="{{ $item->getTranslatedAttribute('name') }}">

                                    </label>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="kviz-button">
                        <a href="#" class="kviz-next">{!! $bot_form['f_next_step_btn'] !!} <i class="fa-arrow-next"></i>
                        </a>
                        <a href="#" class="kviz-skip">{!! $bot_form['skip_quest'] !!}</a>
                    </div>
                </div>
                <div class="kviz-stock">
                    <div class="stock-full">
                        <svg class="kviz-arrow">
                            <use xlink:href="{{ asset(env('THEME') . 'sprite.svg#kviz-arrow') }}"></use>
                        </svg>
                        @if (isset($bot_form_step1_default->image))
                            <picture>
                                @if ($webpSrc = image_webp_url('storage/' . $bot_form_step1_default->image))
                                    <source srcset="{{ $webpSrc }}" type="image/webp">
                                @endif
                                <source srcset="{{ asset('storage/' . $bot_form_step1_default->image) }}" type="image/jpeg">
                                <img loading="lazy" src="{{ asset('storage/' . $bot_form_step1_default->image) }}" data-stock="1" class="stock-photo lozad" alt="img">
                            </picture>
                        @endif
                    </div>
                    <p class="stock-list__title">{!! $bot_form['after_end_got'] !!}</p>
                    <div class="stock-list">
                        <div class="stock-item">
                            <p>
                                {!! $bot_form['f_banner_bot1'] !!}
                            </p>
                            <picture>
                                <source srcset="{{ asset('images/kviz/kviz-1.webp') }}" type="image/webp">
                                <source srcset="{{ asset('images/kviz/kviz-1.png') }}">
                                <img loading="lazy" src="{{ asset('images/kviz/kviz-1.png') }}" alt="img"
                                    class="lozad">
                            </picture>
                        </div>
                        <div class="stock-item">
                            <p>{!! $bot_form['f_banner_bot2'] !!}</p>
                            <picture>
                                <source srcset="{{ asset('images/kviz/kviz-2.webp') }}" type="image/webp">
                                <source srcset="{{ asset('images/kviz/kviz-2.png') }}">
                                <img loading="lazy" src="{{ asset('images/kviz/kviz-2.png') }}" alt="img"
                                    class="lozad">
                            </picture>
                        </div>
                        <div class="stock-item">
                            <p>{!! $bot_form['f_banner_bot3'] !!}</p>
                            <picture>
                                <source srcset="{{ asset('images/kviz/kviz-3.webp') }}" type="image/webp">
                                <source srcset="{{ asset('images/kviz/kviz-3.png') }}">
                                <img loading="lazy" src="{{ asset('images/kviz/kviz-3.png') }}" alt="img"
                                    class="lozad">
                            </picture>
                        </div>
                    </div>
                </div>
            </div>
            <div class="kviz-item" data-step="3">
                <div class="kviz-content">
                    <div class="kviz-step">
                        <div class="step-counter">{!! $bot_form['f_step'] !!} 3 {!! $bot_form['f_step_from'] !!} 4</div>
                    </div>
                    <div class="stock-full stock-full_tablet">
                        @if (isset($bot_form_step3_default->image))
                            <picture>
                                @if ($webpSrc = image_webp_url('storage/' . $bot_form_step3_default->image))
                                    <source srcset="{{ $webpSrc }}" type="image/webp">
                                @endif
                                <source srcset="{{ asset('storage/' . $bot_form_step3_default->image) }}" type="image/jpeg">
                                <img loading="lazy" src="{{ asset('storage/' . $bot_form_step3_default->image) }}" data-stock="1" class="stock-photo lozad" alt="img">
                            </picture>
                        @endif
                    </div>
                    <div class="kviz-group__title h4_old">{!! $bot_form['js_third'] !!}</div>
                    <div class="kviz-group__full">
                        <div class="kviz-group__item kviz-group">
                            @if ($bot_form_step3_items)
                                @foreach ($bot_form_step3_items as $item)
                                    <label for="item4__{{ $loop->index }}" class="kviz-radio js-checkbox"
                                        data-stock="@if ($loop->odd) 1 @else 2 @endif">
                                        <em class="check"></em>

                                            <span>{{ $item->getTranslatedAttribute('name') }}</span>
                                            <input id="item4__{{ $loop->index }}" type="radio" name="step3"
                                                @if ($item->image) data-img="{{ asset('storage/' . $item->image) }}"  @else data-img="{{ asset('storage/' . $bot_form_step3_default->image) }}" @endif
                                                value="{{ $item->getTranslatedAttribute('name') }}">

                                        <svg class="radio-info">
                                            <use xlink:href="{{ asset(env('THEME') . 'sprite.svg#info') }}"></use>
                                        </svg>
                                        @if ($item['tooltip'] != '')
                                            <div class="kviz-radio-descriptor">
                                                <picture>
                                                    <source srcset="{{ asset('images/kviz-full/kviz-1.webp') }}"
                                                        type="image/webp">
                                                    <source srcset="{{ asset('images/kviz-full/kviz-1.jpg') }}"
                                                        type="image/jpeg">
                                                    <img loading="lazy"
                                                        src="{{ asset('images/kviz-full/kviz-1.jpg') }}"
                                                        class="lozad" alt="img">
                                                </picture>
                                                {!! $item->getTranslatedAttribute('tooltip') !!}
                                            </div>
                                        @endif
                                    </label>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <div class="kviz-button">
                        <a href="#" class="kviz-next">{!! $bot_form['f_next_step_btn'] !!} <i class="fa-arrow-next"></i></a>
                        <a href="#" class="kviz-skip">{!! $bot_form['skip_quest'] !!}</a>
                    </div>
                </div>

                <div class="kviz-stock">
                    <div class="stock-full">
                        <svg class="kviz-arrow">
                            <use xlink:href="{{ asset(env('THEME') . 'sprite.svg#kviz-arrow') }}"></use>
                        </svg>
                        <picture>
                            <source srcset="{{ asset('images/kviz-full/kviz-1.webp') }}" type="image/webp">
                            <source srcset="{{ asset('images/kviz-full/kviz-1.jpg') }}" type="image/jpeg">
                            <img loading="lazy" src="{{ asset('images/kviz-full/kviz-1.jpg') }}" data-stock="1"
                                class="stock-photo lozad" alt="img">
                        </picture>
                    </div>
                    <p class="stock-list__title">{!! $bot_form['after_end_got'] !!}</p>
                    <div class="stock-list">
                        <div class="stock-item">
                            <p>
                                {!! $bot_form['f_banner_bot1'] !!}
                            </p>
                            <picture>
                                <source srcset="{{ asset('images/kviz/kviz-1.webp') }}" type="image/webp">
                                <source srcset="{{ asset('images/kviz/kviz-1.png') }}">
                                <img loading="lazy" src="{{ asset('images/kviz/kviz-1.png') }}" alt="img"
                                    class="lozad">
                            </picture>
                        </div>
                        <div class="stock-item">
                            <p>{!! $bot_form['f_banner_bot2'] !!}</p>
                            <picture>
                                <source srcset="{{ asset('images/kviz/kviz-2.webp') }}" type="image/webp">
                                <source srcset="{{ asset('images/kviz/kviz-2.png') }}">
                                <img loading="lazy" src="{{ asset('images/kviz/kviz-2.png') }}" alt="img"
                                    class="lozad">
                            </picture>
                        </div>
                        <div class="stock-item">
                            <p>{!! $bot_form['f_banner_bot3'] !!}</p>
                            <picture>
                                <source srcset="{{ asset('images/kviz/kviz-3.webp') }}" type="image/webp">
                                <source srcset="{{ asset('images/kviz/kviz-3.png') }}">
                                <img loading="lazy" src="{{ asset('images/kviz/kviz-3.png') }}" alt="img"
                                    class="lozad">
                            </picture>
                        </div>
                    </div>
                </div>
            </div>
            <div class="kviz-item" data-step="4">
                <picture>
                    <source srcset="{{ asset('images/bg/kviz-finish.webp') }}" type="image/webp">
                    <source srcset="{{ asset('images/bg/kviz-finish.png') }}">
                    <img loading="lazy" src="{{ asset('images/bg/kviz-finish.png') }}" class="kviz-finsh-photo lozad"
                        alt="img" >
                </picture>
                <div class="kviz-content">
                    <div class="kviz-step">
                        <div class="step-counter">{!! $bot_form['all_done'] !!}</div>
                    </div>
                    <div class="kviz-finis-group">
                        <div class="kviz-input">
                            <span class="kviz-input__title">{!! $bot_form['enter_email'] !!}</span>
                            <div class="page-input__item">
                                <input type="email" name="email" placeholder="E-mail" required="">
                                <svg class="kviz-input__icon">
                                    <use xlink:href="{{ asset(env('THEME') . 'sprite.svg#mail') }}"></use>
                                </svg>
                            </div>
                        </div>
                        <div class="kviz-input">
                            <p class="kviz-input__title"> {!! $bot_form['phone'] !!}</p>
                            <div class="form-send__item">
                                <div class="form-send__input-item">
                                    <input type="text" id="phone" name="phone" class="form-send__input phone">
                                    <img loading="lazy" src="{{ asset('img/icons/phone.svg') }}" alt=""
                                        class="img-svg img-svg__posa lozad">
                                </div>
                            </div>
                        </div>
                        <div class="kviz-input">
                            <p class="kviz-input__title">{!! $bot_form['foto_portrait'] !!}</p>
                            <div class="file-save file-save__popup">
                                <div class="abs-close">
                                    X
                                </div>
                                <div class="file-save__item js-file-preview">
                                    <svg>
                                        <use xlink:href="{{ asset(env('THEME') . 'sprite.svg#save') }}"></use>
                                    </svg>
                                    <div class="file-save__title">
                                        <p>{!! $bot_form['load_photo'] !!}</p>
                                        <span>{!! $bot_form['prees_to_load'] !!}</span>
                                    </div>
                                </div>
                                <div class="file-save__item js-file-upload">
                                    <svg>
                                        <use xlink:href="{{ asset(env('THEME') . 'sprite.svg#picture') }}"></use>
                                    </svg>
                                    <div class="file-save__title">
                                        <p>photo_34567.jpg</p>
                                        <span>2 Mb</span>
                                    </div>
                                </div>
                                <div class="file-save__item js-file-multiple">
                                    <svg>
                                        <use xlink:href="{{ asset(env('THEME') . 'sprite.svg#check') }}"></use>
                                    </svg>
                                    <div class="file-save__title">
                                        <p class="file-title_green">Файлы загружены</p>
                                    </div>
                                </div>
                                <input id="file_images_upl" type="file" class="file-input kviz-file-input"
                                    name="images" multiple="" accept="image/*,image/heif,image/heic" aria-label="file input">
                            </div>
                        </div>
                        <div class="kviz-input">
                            <p class="kviz-input__title">{!! $bot_form['rachet_on'] !!}</p>
                            <div class="kviz-messege">
                                <div class="kviz-messege__tab js-checkbox">
                                    <svg>
                                        <use xlink:href="{{ asset(env('THEME') . 'sprite.svg#email') }}"></use>
                                    </svg>
                                    <label>
                                        <span>E-mail</span>
                                        <input type="radio" name="dest" value="E-mail">
                                    </label>
                                </div>
                                <div class="kviz-messege__tab kviz-messege__tab_wh js-checkbox">
                                    <svg>
                                        <use xlink:href="{{ asset(env('THEME') . 'sprite.svg#wh') }}"></use>
                                    </svg>
                                    <label>
                                        <span>WhatsApp</span>
                                        <input type="radio" name="dest" value="WhatsApp">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="kviz-button">
                        <label class="kviz-sub">
                            <span>{!! $bot_form['send'] !!}</span>
                            <input type="submit">
                        </label>
                        <div class="submit-gift">
                            <svg>
                                <use xlink:href="{{ asset(env('THEME') . 'sprite.svg#gift') }}"></use>
                            </svg>
                            <p>{!! $bot_form['gift_for_you'] !!}</p>
                            <svg class="submit-gift__arrow">
                                <use xlink:href="{{ asset(env('THEME') . 'sprite.svg#arrow-small') }}"></use>
                            </svg>
                        </div>
                    </div>
                    <div class="loading-bar">
                        <span>
                            Отправка заказа
                        </span>
                        <div class="l-progress">
                            <span></span><span></span><span></span><span></span><span></span><span></span>
                        </div>
                    </div>
                    <div class="kviz-politics">
                        <svg>
                            <use xlink:href="{{ asset(env('THEME') . 'sprite.svg#lock') }}"></use>
                        </svg>
                        <p>{!! $bot_form['privacy_text'] !!}</p>
                    </div>
                </div>
                <p class="stock-list__title"> {!! $bot_form['after_end_got'] !!}</p>
                <div class="kviz-stock">
                    <div class="stock-full stock-full_empty">
                        <svg class="kviz-arrow">
                            <use xlink:href="{{ asset(env('THEME') . 'sprite.svg#kviz-arrow') }}"></use>
                        </svg>
                        <div class="stock-gift stock-gift_finish">
                            <svg>
                                <use xlink:href="{{ asset(env('THEME') . 'sprite.svg#check') }}"></use>
                            </svg>
                            <p>{!! $bot_form['leave_contact'] !!}</p>
                        </div>
                    </div>
                    <div class="stock-list">
                        <div class="stock-item">
                            <p>{!! $bot_form['f_banner_bot1'] !!}</p>
                            <picture>
                                @if(!empty($kviz_1['src_webp']))
                                    <source srcset="{{ $kviz_1['src_webp'] }}" type="image/webp">
                                @endif
                                @if(!empty($kviz_1['type']))
                                    <source srcset="{{ $kviz_1['src'] }}" type="{{ $kviz_1['type'] }}">
                                @endif
                                <img loading="lazy" src="{{ $kviz_1['src'] }}" alt="{{ $kviz_1['alt'] ?? '' }}" title="{{ $kviz_1['title'] ?? '' }}" class="lozad">
                            </picture>
                        </div>
                        <div class="stock-item">
                            <p>{!! $bot_form['f_banner_bot2'] !!}</p>
                            <picture>
                                @if(!empty($kviz_2['src_webp']))
                                    <source srcset="{{ $kviz_2['src_webp'] }}" type="image/webp">
                                @endif
                                @if(!empty($kviz_2['type']))
                                    <source srcset="{{ $kviz_2['src'] }}" type="{{ $kviz_2['type'] }}">
                                @endif
                                <img loading="lazy" src="{{ $kviz_2['src'] }}" alt="{{ $kviz_2['alt'] ?? '' }}" title="{{ $kviz_2['title'] ?? '' }}" class="lozad">
                            </picture>
                        </div>
                        <div class="stock-item">
                            <p>{!! $bot_form['f_banner_bot3'] !!}</p>
                            <picture>
                                @if(!empty($kviz_3['src_webp']))
                                    <source srcset="{{ $kviz_3['src_webp'] }}" type="image/webp">
                                @endif
                                @if(!empty($kviz_3['type']))
                                    <source srcset="{{ $kviz_3['src'] }}" type="{{ $kviz_3['type'] }}">
                                @endif
                                <img loading="lazy" src="{{ $kviz_3['src'] }}" alt="{{ $kviz_3['alt'] ?? '' }}" title="{{ $kviz_3['title'] ?? '' }}" class="lozad">
                            </picture>
                        </div>
                    </div>
                </div>
            </div>
            <div class="kviz-item kviz-thanks__parent" data-step="5">
                <div class="kviz-thanks">
                    <img loading="lazy" src="{{ asset('images/icon/check-done.svg') }}"
                        class="kviz-thanks__icon lozad" alt="img">
                    <div class="kviz-thanks__title">
                        <div class="h3_old">{!! $bot_form['succ_thx'] !!}</div>
                        <p> {!! $bot_form['succ_text'] !!}</p>
                    </div>
                    <div class="kviz-thanks__info">
                        <p>{!! $bot_form['succ_write'] !!}</p>
                        <a href="{{ $bot_form['succ_whats_link'] }}" target="_blank" rel="noopener noreferrer">
                            <svg>
                                <use xlink:href="{{ asset(env('THEME') . 'sprite.svg#wh') }}"></use>
                            </svg>
                            {!! $bot_form['succ_whats_text'] !!}
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
