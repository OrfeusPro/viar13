<div class="mobule-container">
    <div class="section-frame">
        <div class="module-container__inner">
            <div class="module-catalog">
                <div class="module-catalog__title">
                    @lang('gallery.catalog')
                </div>
                <div class="module-catalog__inner">
                    <ul>
                        <li class="/*mc-js-list*/  @if(!isset($category->url)) active @endif">
                            <a href="{{ route('hb.gallery.module', $type) }}">@lang('gallery.all')</a>
                        </li>
                        @foreach ($categories as $item)
                            <li class="/*mc-js-list*/ @if(isset($category->url) && $category->url  == $item->url) active @endif">
                                <a href="{{ route('hb.gallery.category', ["type"=> $type, "category"=> $item->url]) }}">{{ translated_value($item, 'name', $item->name) }}</a>
                            </li>
                        @endforeach

                    </ul>
                </div>
            </div>
            <div class="mc-block">
                <div class="mc-mobile-title module-catalog__title">
                    @lang('gallery.catalog')
                </div>
                <div class="mc-top-row">
                    <div class="mc-filter-box">
                        <div class="filter-item mc-js-filter">
                            <a href="#">
                                <span>@lang('cart.size')</span>
                                <svg width="13" height="8" viewBox="0 0 13 8" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12.2184 0.137457C12.1316 0.0458193 12.0317 4.23244e-08 11.9188 4.72591e-08C11.8059 5.21938e-08 11.7061 0.0458193 11.6192 0.137457L6.5 5.53952L1.38076 0.137458C1.29392 0.0458198 1.19405 5.16054e-07 1.08116 5.20989e-07C0.968269 5.25924e-07 0.868403 0.0458198 0.781562 0.137458L0.130261 0.824744C0.0434205 0.916381 -3.05028e-07 1.02176 -2.99821e-07 1.14089C-2.94614e-07 1.26002 0.0434205 1.36541 0.130261 1.45705L6.2004 7.86254C6.28724 7.95418 6.38711 8 6.5 8C6.61289 8 6.71276 7.95418 6.7996 7.86254L12.8697 1.45705C12.9566 1.36541 13 1.26002 13 1.14089C13 1.02176 12.9566 0.916381 12.8697 0.824743L12.2184 0.137457Z"
                                        fill="#FC8C5F" />
                                </svg>
                            </a>
                            <div class="filter-item--wrapper filter-sizes">
                                <p>@lang('gallery.select_size'):</p>

                                @php
                                    $list_sizes = [
                                        "30x40",
                                        "40x60",
                                        "50x70",
                                        "55x80",
                                        "60x90",
                                        "70x100",
                                        "80x120",
                                        "95x140"
                                    ]

                                    // ,
                                    //     "30x30",
                                    //     "40x40",
                                    //     "50x50",
                                    //     "70x70",
                                    //     "85x85",
                                    //     "100x100",
                                    //     "40x30",
                                    //     "60x40",
                                    //     "70x50",
                                    //     "55x80",
                                    //     "90x60",
                                    //     "100x70",
                                    //     "120x80",
                                    //     "140x95",
                                @endphp

                                <ul class="c-sizeCheck">
                                    @foreach ($list_sizes as $item_size)
                                    <li @if($item_size."_over" == \Request::get('size') ?? "") class="active" @endif  @if($item_size."_smaller" == \Request::get('size') ?? "") class="active" @endif>
                                        <div class="checkbox-item">
                                            <label>
                                                <input type="checkbox" name="activities" @if($item_size."_over" == \Request::get('size') ?? "") checked @endif  @if($item_size."_smaller" == \Request::get('size') ?? "") checked @endif>
                                                <span class="checkmark"></span>
                                                <p>{{ explode("x",$item_size)[0] }}cm x {{ explode("x",$item_size)[1] }}cm</p>
                                            </label>
                                        </div>
                                        <div class="checkbox-settings">
                                            @if($category)
                                                <p @if($item_size."_over" == \Request::get('size') ?? "") class="active" @endif><a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'color'=> \Request::get('color') ?? "", 'size'=> $item_size."_over", 'order'=> \Request::get('order') ?? "" ]) }}">@lang("gallery.size_over")</a></p>
                                                <p @if($item_size."_smaller" == \Request::get('size') ?? "") class="active" @endif><a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'color'=> \Request::get('color') ?? "", 'size'=> $item_size."_smaller", 'order'=> \Request::get('order') ?? "" ]) }}">@lang("gallery.size_smaller")</a></p>
                                            @else
                                                <p @if($item_size."_over" == \Request::get('size') ?? "") class="active" @endif><a href="{{ route('hb.gallery.module', [$page->url, 'color'=> \Request::get('color') ?? "", 'size'=> $item_size."_over", 'order'=> \Request::get('order') ?? "" ]) }}">@lang("gallery.size_over")</a></p>
                                                <p @if($item_size."_smaller" == \Request::get('size') ?? "") class="active" @endif><a href="{{ route('hb.gallery.module', [$page->url, 'color'=> \Request::get('color') ?? "", 'size'=> $item_size."_smaller", 'order'=> \Request::get('order') ?? "" ]) }}">@lang("gallery.size_smaller")</a></p>
                                            @endif
                                        </div>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <div class="filter-item mc-js-filter">
                            <a href="#">
                                <span>@lang('gallery.color')</span>
                                <svg width="13" height="8" viewBox="0 0 13 8" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12.2184 0.137457C12.1316 0.0458193 12.0317 4.23244e-08 11.9188 4.72591e-08C11.8059 5.21938e-08 11.7061 0.0458193 11.6192 0.137457L6.5 5.53952L1.38076 0.137458C1.29392 0.0458198 1.19405 5.16054e-07 1.08116 5.20989e-07C0.968269 5.25924e-07 0.868403 0.0458198 0.781562 0.137458L0.130261 0.824744C0.0434205 0.916381 -3.05028e-07 1.02176 -2.99821e-07 1.14089C-2.94614e-07 1.26002 0.0434205 1.36541 0.130261 1.45705L6.2004 7.86254C6.28724 7.95418 6.38711 8 6.5 8C6.61289 8 6.71276 7.95418 6.7996 7.86254L12.8697 1.45705C12.9566 1.36541 13 1.26002 13 1.14089C13 1.02176 12.9566 0.916381 12.8697 0.824743L12.2184 0.137457Z"
                                        fill="#FC8C5F" />
                                </svg>
                            </a>
                            <div class="filter-item filter-item--wrapper filter-color">
                                <p>@lang('gallery.select_color'):</p>
                                <ul class="color-grid color">
                                    @foreach ($colors as $color)
                                        @if($category)
                                            <li><a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'color'=> $color->id, 'size'=> \Request::get('size') ?? "", 'order'=> \Request::get('order') ?? "" ]) }}" name="{{ $color->id }}"><div style="background: {{ $color->color }}; height: 29px; border: 1px solid #f3f3f3"></div></a></li>
                                        @else
                                            <li><a href="{{ route('hb.gallery.module', [$page->url, 'color'=> $color->id, 'size'=> \Request::get('size') ?? "", 'order'=> \Request::get('order') ?? "" ]) }}" name="{{ $color->id }}"><div style="background: {{ $color->color }}; height: 29px; border: 1px solid #f3f3f3"></div></a></li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        {{-- <div class="filter-item mc-js-filter">
                            <a href="#">
                                <span>@lang("gallery.forma")</span>
                                <svg width="13" height="8" viewBox="0 0 13 8" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12.2184 0.137457C12.1316 0.0458193 12.0317 4.23244e-08 11.9188 4.72591e-08C11.8059 5.21938e-08 11.7061 0.0458193 11.6192 0.137457L6.5 5.53952L1.38076 0.137458C1.29392 0.0458198 1.19405 5.16054e-07 1.08116 5.20989e-07C0.968269 5.25924e-07 0.868403 0.0458198 0.781562 0.137458L0.130261 0.824744C0.0434205 0.916381 -3.05028e-07 1.02176 -2.99821e-07 1.14089C-2.94614e-07 1.26002 0.0434205 1.36541 0.130261 1.45705L6.2004 7.86254C6.28724 7.95418 6.38711 8 6.5 8C6.61289 8 6.71276 7.95418 6.7996 7.86254L12.8697 1.45705C12.9566 1.36541 13 1.26002 13 1.14089C13 1.02176 12.9566 0.916381 12.8697 0.824743L12.2184 0.137457Z"
                                        fill="#FC8C5F" />
                                </svg>
                            </a>
                            <div class="filter-item--wrapper filter-forms">
                                <p>@lang("gallery.forma_select"):</p>
                                <div class="forms-grid">
                                    <div class="active">
                                        <img src="{{ asset(env('THEME') . 'images') }}/reproduction/1.svg" alt="">
                                    </div>
                                    <div>
                                        <img src="{{ asset(env('THEME') . 'images') }}/reproduction/2.svg" alt="">
                                    </div>
                                    <div>
                                        <img src="{{ asset(env('THEME') . 'images') }}/reproduction/3.svg" alt="">
                                    </div>
                                    <div>
                                        <img src="{{ asset(env('THEME') . 'images') }}/reproduction/4.svg" alt="">
                                    </div>
                                    <div>
                                        <img src="{{ asset(env('THEME') . 'images') }}/reproduction/5.svg" alt="">
                                    </div>
                                    <div>
                                        <img src="{{ asset(env('THEME') . 'images') }}/reproduction/6.svg" alt="">
                                    </div>
                                </div>
                            </div>
                        </div> --}}

                        <div class="filter-item mb-filter mc-js-filter">
                            <a href="#">
                                <span>@lang('gallery.main_cat')</span>
                                <svg width="13" height="8" viewBox="0 0 13 8" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12.2184 0.137457C12.1316 0.0458193 12.0317 4.23244e-08 11.9188 4.72591e-08C11.8059 5.21938e-08 11.7061 0.0458193 11.6192 0.137457L6.5 5.53952L1.38076 0.137458C1.29392 0.0458198 1.19405 5.16054e-07 1.08116 5.20989e-07C0.968269 5.25924e-07 0.868403 0.0458198 0.781562 0.137458L0.130261 0.824744C0.0434205 0.916381 -3.05028e-07 1.02176 -2.99821e-07 1.14089C-2.94614e-07 1.26002 0.0434205 1.36541 0.130261 1.45705L6.2004 7.86254C6.28724 7.95418 6.38711 8 6.5 8C6.61289 8 6.71276 7.95418 6.7996 7.86254L12.8697 1.45705C12.9566 1.36541 13 1.26002 13 1.14089C13 1.02176 12.9566 0.916381 12.8697 0.824743L12.2184 0.137457Z"
                                        fill="#FC8C5F" />
                                </svg>
                            </a>
                            <div class="filter-item--wrapper">
                                <div class="module-catalog__inner">
                                    <ul>
                                        <li class="mc-js-list">
                                            <a href="{{ route('hb.gallery.module', $type) }}">@lang('gallery.all')</a>
                                        </li>

                                        @foreach ($categories as $item)
                                            <li class="mc-js-list"><a href="{{ route('hb.gallery.category', ["type"=> $type, "category"=> $item->url]) }}">{{ translated_value($item, 'name', $item->name) }}</a> </li>
                                        @endforeach

                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="filter-item mb-filter mc-js-filter">
                            <a href="#">
                                <span>@lang("gallery.filters")</span>
                                <svg width="13" height="8" viewBox="0 0 13 8" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12.2184 0.137457C12.1316 0.0458193 12.0317 4.23244e-08 11.9188 4.72591e-08C11.8059 5.21938e-08 11.7061 0.0458193 11.6192 0.137457L6.5 5.53952L1.38076 0.137458C1.29392 0.0458198 1.19405 5.16054e-07 1.08116 5.20989e-07C0.968269 5.25924e-07 0.868403 0.0458198 0.781562 0.137458L0.130261 0.824744C0.0434205 0.916381 -3.05028e-07 1.02176 -2.99821e-07 1.14089C-2.94614e-07 1.26002 0.0434205 1.36541 0.130261 1.45705L6.2004 7.86254C6.28724 7.95418 6.38711 8 6.5 8C6.61289 8 6.71276 7.95418 6.7996 7.86254L12.8697 1.45705C12.9566 1.36541 13 1.26002 13 1.14089C13 1.02176 12.9566 0.916381 12.8697 0.824743L12.2184 0.137457Z"
                                        fill="#FC8C5F" />
                                </svg>
                            </a>
                            <div class="filter-item--wrapper filter-color">
                                <div class="module-catalog__inner">
                                    <ul>
                                        <li class="mc-js-list">
                                            <a href="#">
                                                <span>@lang('gallery.select_size')</span>
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M9.13746 3.78156C9.04582 3.8684 9 3.96827 9 4.08116C9 4.19406 9.04582 4.29392 9.13746 4.38076L14.5395 9.5L9.13746 14.6192C9.04582 14.7061 9 14.8059 9 14.9188C9 15.0317 9.04582 15.1316 9.13746 15.2184L9.82474 15.8697C9.91638 15.9566 10.0218 16 10.1409 16C10.26 16 10.3654 15.9566 10.457 15.8697L16.8625 9.7996C16.9542 9.71276 17 9.61289 17 9.5C17 9.38711 16.9542 9.28724 16.8625 9.2004L10.457 3.13026C10.3654 3.04342 10.26 3 10.1409 3C10.0218 3 9.91638 3.04342 9.82474 3.13026L9.13746 3.78156Z"
                                                        fill="#1E2533" />
                                                </svg>
                                            </a>
                                            <ul class="mc-subcat np-subcat">
                                                <ul class="c-sizeCheck">

                                                    @foreach ($list_sizes as $item_size)
                                                    <li @if($item_size."_over" == \Request::get('size') ?? "") class="active" @endif  @if($item_size."_smaller" == \Request::get('size') ?? "") class="active" @endif>
                                                        <div class="checkbox-item">
                                                            <label>
                                                                <input type="checkbox" name="activities" @if($item_size."_over" == \Request::get('size') ?? "") checked @endif  @if($item_size."_smaller" == \Request::get('size') ?? "") checked @endif>
                                                                <span class="checkmark"></span>
                                                                <p>{{ explode("x",$item_size)[0] }}cm x {{ explode("x",$item_size)[1] }}cm</p>
                                                            </label>
                                                        </div>
                                                        <div class="checkbox-settings">
                                                            @if($category)
                                                                <p @if($item_size."_smaller" == \Request::get('size') ?? "") class="active" @endif><a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'color'=> \Request::get('color') ?? "", 'size'=> $item_size."_smaller", 'order'=> \Request::get('order') ?? "" ]) }}">-</a></p>/
                                                                <p @if($item_size."_over" == \Request::get('size') ?? "") class="active" @endif><a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'color'=> \Request::get('color') ?? "", 'size'=> $item_size."_over", 'order'=> \Request::get('order') ?? "" ]) }}">+</a></p>
                                                            @else
                                                                <p @if($item_size."_smaller" == \Request::get('size') ?? "") class="active" @endif><a href="{{ route('hb.gallery.module', [$page->url, 'color'=> \Request::get('color') ?? "", 'size'=> $item_size."_smaller", 'order'=> \Request::get('order') ?? "" ]) }}">-</a></p>/
                                                                <p @if($item_size."_over" == \Request::get('size') ?? "") class="active" @endif><a href="{{ route('hb.gallery.module', [$page->url, 'color'=> \Request::get('color') ?? "", 'size'=> $item_size."_over", 'order'=> \Request::get('order') ?? "" ]) }}">+</a></p>
                                                            @endif
                                                        </div>
                                                    </li>
                                                    @endforeach

                                                </ul>
                                            </ul>
                                        </li>
                                        <li class="mc-js-list">
                                            <a href="#">
                                                <span>@lang('gallery.color')</span>
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M9.13746 3.78156C9.04582 3.8684 9 3.96827 9 4.08116C9 4.19406 9.04582 4.29392 9.13746 4.38076L14.5395 9.5L9.13746 14.6192C9.04582 14.7061 9 14.8059 9 14.9188C9 15.0317 9.04582 15.1316 9.13746 15.2184L9.82474 15.8697C9.91638 15.9566 10.0218 16 10.1409 16C10.26 16 10.3654 15.9566 10.457 15.8697L16.8625 9.7996C16.9542 9.71276 17 9.61289 17 9.5C17 9.38711 16.9542 9.28724 16.8625 9.2004L10.457 3.13026C10.3654 3.04342 10.26 3 10.1409 3C10.0218 3 9.91638 3.04342 9.82474 3.13026L9.13746 3.78156Z"
                                                        fill="#1E2533" />
                                                </svg>
                                            </a>
                                            <ul class="mc-subcat np-subcat">
                                                <ul class="color-grid">
                                                    @foreach ($colors as $color)
                                                        @if($category)
                                                            <li style="padding-right: 0px;"><a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'color'=> $color->id, 'size'=> \Request::get('size') ?? "", 'order'=> \Request::get('order') ?? "" ]) }}" name="{{ $color->id }}"><div style="background: {{ $color->color }}; height: 29px; border: 1px solid #f3f3f3; width: 100%;"></div></a></li>
                                                        @else
                                                            <li style="padding-right: 0px;"><a href="{{ route('hb.gallery.module', [$page->url, 'color'=> $color->id, 'size'=> \Request::get('size') ?? "", 'order'=> \Request::get('order') ?? "" ]) }}" name="{{ $color->id }}"><div style="background: {{ $color->color }}; height: 29px; border: 1px solid #f3f3f3:width: 100%;"></div></a></li>
                                                        @endif
                                                    @endforeach

                                                </ul>
                                            </ul>
                                        </li>

                                        {{-- <li class="mc-js-list">
                                            <a href="#">
                                                <span>@lang('gallery.forma')</span>
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M9.13746 3.78156C9.04582 3.8684 9 3.96827 9 4.08116C9 4.19406 9.04582 4.29392 9.13746 4.38076L14.5395 9.5L9.13746 14.6192C9.04582 14.7061 9 14.8059 9 14.9188C9 15.0317 9.04582 15.1316 9.13746 15.2184L9.82474 15.8697C9.91638 15.9566 10.0218 16 10.1409 16C10.26 16 10.3654 15.9566 10.457 15.8697L16.8625 9.7996C16.9542 9.71276 17 9.61289 17 9.5C17 9.38711 16.9542 9.28724 16.8625 9.2004L10.457 3.13026C10.3654 3.04342 10.26 3 10.1409 3C10.0218 3 9.91638 3.04342 9.82474 3.13026L9.13746 3.78156Z"
                                                        fill="#1E2533" />
                                                </svg>
                                            </a>
                                            <ul class="mc-subcat np-subcat">
                                                <div class="forms-grid">
                                                    <div class="active">
                                                        <img src="{{ asset(env('THEME') . 'images') }}/reproduction/1.svg" alt="">
                                                    </div>
                                                    <div>
                                                        <img src="{{ asset(env('THEME') . 'images') }}/reproduction/2.svg" alt="">
                                                    </div>
                                                    <div>
                                                        <img src="{{ asset(env('THEME') . 'images') }}/reproduction/3.svg" alt="">
                                                    </div>
                                                    <div>
                                                        <img src="{{ asset(env('THEME') . 'images') }}/reproduction/4.svg" alt="">
                                                    </div>
                                                    <div>
                                                        <img src="{{ asset(env('THEME') . 'images') }}/reproduction/5.svg" alt="">
                                                    </div>
                                                    <div>
                                                        <img src="{{ asset(env('THEME') . 'images') }}/reproduction/6.svg" alt="">
                                                    </div>
                                                </div>
                                            </ul>
                                        </li> --}}

                                    </ul>
                                </div>

                            </div>
                        </div>
                        <div class="filter-item mb-filter mc-js-filter pop-sort">
                            <a href="#">
                                <span id="isort2">
                                    @if(\Request::get('order') == 'cheap')
                                        @lang("gallery.sort_by_price_low")
                                    @elseif(\Request::get('order') == 'expensive')
                                        @lang("gallery.sort_by_price_high")
                                    @elseif(\Request::get('order') == 'new')
                                        @lang("gallery.sort_by_new")
                                    @else
                                        @lang("gallery.sort_by_popular")
                                    @endif
                                </span>
                                <svg width="13" height="8" viewBox="0 0 13 8" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12.2184 0.137457C12.1316 0.0458193 12.0317 4.23244e-08 11.9188 4.72591e-08C11.8059 5.21938e-08 11.7061 0.0458193 11.6192 0.137457L6.5 5.53952L1.38076 0.137458C1.29392 0.0458198 1.19405 5.16054e-07 1.08116 5.20989e-07C0.968269 5.25924e-07 0.868403 0.0458198 0.781562 0.137458L0.130261 0.824744C0.0434205 0.916381 -3.05028e-07 1.02176 -2.99821e-07 1.14089C-2.94614e-07 1.26002 0.0434205 1.36541 0.130261 1.45705L6.2004 7.86254C6.28724 7.95418 6.38711 8 6.5 8C6.61289 8 6.71276 7.95418 6.7996 7.86254L12.8697 1.45705C12.9566 1.36541 13 1.26002 13 1.14089C13 1.02176 12.9566 0.916381 12.8697 0.824743L12.2184 0.137457Z"
                                        fill="#FC8C5F" />
                                </svg>
                            </a>
                            <div class="filter-item--wrapper filter-sort">
                                <ul class="sort-it">
                                    <li @if(\Request::get('order') == '') class="active" @endif>
                                        @if($category)
                                            <a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "" ]) }}">
                                        @else
                                            <a href="{{ route('hb.gallery.module', [$page->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> ""]) }}">
                                        @endif
                                            <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g class="clip0_309_246">
                                                    <path
                                                        d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z"
                                                        fill="#FA7846" />
                                                </g>
                                                <defs>
                                                    <clipPath class="clip0_309_246">
                                                        <rect width="15" height="15" fill="white" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                            <span>@lang("gallery.sort_by_popular")</span>
                                        </a>
                                    </li>
                                    <li @if(\Request::get('order') == 'new') class="active" @endif>
                                        @if($category)
                                            <a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "new" ]) }}">
                                        @else
                                            <a href="{{ route('hb.gallery.module', [$page->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "new"]) }}">
                                        @endif
                                            <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g class="clip0_309_246">
                                                    <path
                                                        d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z"
                                                        fill="#FA7846" />
                                                </g>
                                                <defs>
                                                    <clipPath class="clip0_309_246">
                                                        <rect width="15" height="15" fill="white" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                            <span>@lang("gallery.sort_by_new")</span>
                                        </a>
                                    </li>
                                    <li @if(\Request::get('order') == 'cheap') class="active" @endif>
                                        @if($category)
                                            <a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "cheap" ]) }}">
                                        @else
                                            <a href="{{ route('hb.gallery.module', [$page->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "cheap"]) }}">
                                        @endif
                                            <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g class="clip0_309_246">
                                                    <path
                                                        d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z"
                                                        fill="#FA7846" />
                                                </g>
                                                <defs>
                                                    <clipPath class="clip0_309_246">
                                                        <rect width="15" height="15" fill="white" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                            <span>@lang("gallery.sort_by_price_low")</span>
                                        </a>
                                    </li>
                                    <li @if(\Request::get('order') == 'expensive') class="active" @endif>
                                        @if($category)
                                            <a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "expensive" ]) }}">
                                        @else
                                            <a href="{{ route('hb.gallery.module', [$page->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "expensive"]) }}">
                                        @endif
                                            <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g class="clip0_309_246">
                                                    <path
                                                        d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z"
                                                        fill="#FA7846" />
                                                </g>
                                                <defs>
                                                    <clipPath class="clip0_309_246">
                                                        <rect width="15" height="15" fill="white" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                            <span>@lang("gallery.sort_by_price_high")</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    @if($category)
                        <form class="mc-search" method="GET" action="{{ route('hb.gallery.category', [$page->url, $category->url ]) }}">
                    @else
                        <form class="mc-search" method="GET" action="{{ route('hb.gallery.module', $page->url) }}">
                    @endif
                            <div class="mc-input">
                                <input type="search" name="search" placeholder="@lang("gallery.search")">
                            </div>
                            <button type="submit">
                                <svg width="25" height="25" viewBox="0 0 25 25" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_307_1523)">
                                        <path
                                            d="M25 23.8953L16.5272 15.4225C17.911 13.7884 18.75 11.6791 18.75 9.37501C18.75 4.20531 14.5447 0 9.37501 0C4.20536 0 0 4.20531 0 9.37501C0 14.5447 4.20531 18.75 9.37501 18.75C11.6791 18.75 13.7884 17.911 15.4225 16.5272L23.8953 25L25 23.8953ZM9.37501 17.1875C5.06745 17.1875 1.56252 13.6826 1.56252 9.37501C1.56252 5.06745 5.06745 1.56252 9.37501 1.56252C13.6826 1.56252 17.1875 5.06745 17.1875 9.37501C17.1875 13.6826 13.6826 17.1875 9.37501 17.1875Z"
                                            fill="white" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_307_1523">
                                            <rect width="25" height="25" fill="white" />
                                        </clipPath>
                                    </defs>
                                </svg>
                            </button>
                        </form>
                </div>
                <div class="mc-next-row">
                    <div class="mc-f-selected-container">

                        @if(\Request::get('color'))
                            @foreach ($colors as $color)
                                @if(\Request::get('color') == $color->id)
                                    @if($category)
                                        {{-- <li><a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'color'=> $color->id, 'size'=> \Request::get('size') ?? "" ]) }}" name="{{ $color->id }}"><div style="background: {{ $color->color }}; height: 29px; border: 1px solid #f3f3f3"></div></a></li> --}}
                                        <div class="mc-f-selected">
                                            <div  style="background: {{ $color->color }}; height: 29px; border: 1px solid #f3f3f3; width: 87%;">

                                            </div>
                                            <a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'size'=> \Request::get('size') ?? "", 'order'=> \Request::get('order') ?? "" ]) }}">
                                            <svg width="8" height="8" viewBox="0 0 8 8" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <rect width="10.4429" height="0.870241"
                                                    transform="matrix(0.707093 -0.707121 0.707093 0.707121 0.00146484 7.38428)" fill="#1E2533" />
                                                <rect width="10.4429" height="0.870241"
                                                    transform="matrix(-0.707092 -0.707121 0.707092 -0.707121 7.38428 8)" fill="#1E2533" />
                                            </svg>
                                            </a>
                                        </div>
                                    @else

                                        <div class="mc-f-selected">
                                            <div  style="background: {{ $color->color }}; height: 29px; border: 1px solid #f3f3f3; width: 87%;">

                                            </div>
                                            <a href="{{ route('hb.gallery.module', [$page->url, 'size'=> \Request::get('size') ?? "", 'order'=> \Request::get('order') ?? "" ]) }}">
                                            <svg width="8" height="8" viewBox="0 0 8 8" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <rect width="10.4429" height="0.870241"
                                                    transform="matrix(0.707093 -0.707121 0.707093 0.707121 0.00146484 7.38428)" fill="#1E2533" />
                                                <rect width="10.4429" height="0.870241"
                                                    transform="matrix(-0.707092 -0.707121 0.707092 -0.707121 7.38428 8)" fill="#1E2533" />
                                            </svg>
                                            </a>
                                        </div>

                                        {{-- <li><a href="{{ route('hb.gallery.module', [$page->url, 'color'=> $color->id, 'size'=> \Request::get('size') ?? "" ]) }}" name="{{ $color->id }}"><div style="background: {{ $color->color }}; height: 29px; border: 1px solid #f3f3f3"></div></a></li> --}}
                                    @endif
                                @endif
                            @endforeach
                        @endif

                        @if(\Request::get('size'))
                            @if($category)
                                <div class="mc-f-selected">
                                    <div>
                                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M3.41667 3.125H3.125V3.41667V6.33333H3.70833V4.12081L6.1271 6.53957L6.53957 6.1271L4.12081 3.70833H6.33333V3.125H3.41667ZM11.5833 3.125H11.875V3.41667V6.33333H11.2917V4.12081L8.8729 6.53957L8.46043 6.1271L10.8792 3.70833H8.66667V3.125H11.5833ZM11.875 11.875H11.5833H8.66667V11.2917H10.8792L8.46043 8.8729L8.8729 8.46043L11.2917 10.8792V8.66667H11.875V11.5833V11.875ZM3.41667 11.875H3.125V11.5833V8.66667H3.70833V10.8792L6.1271 8.46043L6.53957 8.8729L4.12081 11.2917H6.33333V11.875H3.41667Z"
                                                fill="#FA7846" />
                                        </svg>
                                        <span>{{ explode("x",\Request::get('size'))[0]}}x{{ explode("_", explode("x",\Request::get('size'))[1])[0]}} cm</span>
                                    </div>
                                    <a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'color'=> \Request::get('color') ?? "", 'order'=> \Request::get('order') ?? "" ]) }}">
                                    <svg width="8" height="8" viewBox="0 0 8 8" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <rect width="10.4429" height="0.870241"
                                            transform="matrix(0.707093 -0.707121 0.707093 0.707121 0.00146484 7.38428)" fill="#1E2533" />
                                        <rect width="10.4429" height="0.870241"
                                            transform="matrix(-0.707092 -0.707121 0.707092 -0.707121 7.38428 8)" fill="#1E2533" />
                                    </svg>
                                    </a>
                                </div>
                            @else

                                <div class="mc-f-selected">
                                    <div>
                                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M3.41667 3.125H3.125V3.41667V6.33333H3.70833V4.12081L6.1271 6.53957L6.53957 6.1271L4.12081 3.70833H6.33333V3.125H3.41667ZM11.5833 3.125H11.875V3.41667V6.33333H11.2917V4.12081L8.8729 6.53957L8.46043 6.1271L10.8792 3.70833H8.66667V3.125H11.5833ZM11.875 11.875H11.5833H8.66667V11.2917H10.8792L8.46043 8.8729L8.8729 8.46043L11.2917 10.8792V8.66667H11.875V11.5833V11.875ZM3.41667 11.875H3.125V11.5833V8.66667H3.70833V10.8792L6.1271 8.46043L6.53957 8.8729L4.12081 11.2917H6.33333V11.875H3.41667Z"
                                                fill="#FA7846" />
                                        </svg>
                                        <span>{{ explode("x",\Request::get('size'))[0]}}x{{ explode("_", explode("x",\Request::get('size'))[1])[0]}} cm</span>
                                    </div>
                                    <a href="{{ route('hb.gallery.module', [$page->url, 'color'=> \Request::get('color') ?? "", 'order'=> \Request::get('order') ?? "" ]) }}">
                                    <svg width="8" height="8" viewBox="0 0 8 8" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <rect width="10.4429" height="0.870241"
                                            transform="matrix(0.707093 -0.707121 0.707093 0.707121 0.00146484 7.38428)" fill="#1E2533" />
                                        <rect width="10.4429" height="0.870241"
                                            transform="matrix(-0.707092 -0.707121 0.707092 -0.707121 7.38428 8)" fill="#1E2533" />
                                    </svg>
                                    </a>
                                </div>
                                {{-- <li><a href="{{ route('hb.gallery.module', [$page->url, 'color'=> $color->id, 'size'=> \Request::get('size') ?? "" ]) }}" name="{{ $color->id }}"><div style="background: {{ $color->color }}; height: 29px; border: 1px solid #f3f3f3"></div></a></li> --}}
                            @endif
                        @endif
                        {{--
                        <div class="mc-f-selected">
                            <div>
                                Города
                            </div>
                            <svg width="8" height="8" viewBox="0 0 8 8" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect width="10.4429" height="0.870241"
                                    transform="matrix(0.707093 -0.707121 0.707093 0.707121 0.00146484 7.38428)" fill="#1E2533" />
                                <rect width="10.4429" height="0.870241"
                                    transform="matrix(-0.707092 -0.707121 0.707092 -0.707121 7.38428 8)" fill="#1E2533" />
                            </svg>
                        </div>
                        <div class="mc-f-selected">
                            <div>
                                Города
                            </div>
                            <svg width="8" height="8" viewBox="0 0 8 8" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect width="10.4429" height="0.870241"
                                    transform="matrix(0.707093 -0.707121 0.707093 0.707121 0.00146484 7.38428)" fill="#1E2533" />
                                <rect width="10.4429" height="0.870241"
                                    transform="matrix(-0.707092 -0.707121 0.707092 -0.707121 7.38428 8)" fill="#1E2533" />
                            </svg>
                        </div>
                        <div class="mc-f-selected">
                            <div>
                                Города
                            </div>
                            <svg width="8" height="8" viewBox="0 0 8 8" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect width="10.4429" height="0.870241"
                                    transform="matrix(0.707093 -0.707121 0.707093 0.707121 0.00146484 7.38428)" fill="#1E2533" />
                                <rect width="10.4429" height="0.870241"
                                    transform="matrix(-0.707092 -0.707121 0.707092 -0.707121 7.38428 8)" fill="#1E2533" />
                            </svg>
                        </div>
                        <div class="mc-f-selected">
                            <div>
                                <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M3.41667 3.125H3.125V3.41667V6.33333H3.70833V4.12081L6.1271 6.53957L6.53957 6.1271L4.12081 3.70833H6.33333V3.125H3.41667ZM11.5833 3.125H11.875V3.41667V6.33333H11.2917V4.12081L8.8729 6.53957L8.46043 6.1271L10.8792 3.70833H8.66667V3.125H11.5833ZM11.875 11.875H11.5833H8.66667V11.2917H10.8792L8.46043 8.8729L8.8729 8.46043L11.2917 10.8792V8.66667H11.875V11.5833V11.875ZM3.41667 11.875H3.125V11.5833V8.66667H3.70833V10.8792L6.1271 8.46043L6.53957 8.8729L4.12081 11.2917H6.33333V11.875H3.41667Z"
                                        fill="#FA7846" />
                                </svg>
                                <span>100х70см</span>
                            </div>
                            <svg width="8" height="8" viewBox="0 0 8 8" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect width="10.4429" height="0.870241"
                                    transform="matrix(0.707093 -0.707121 0.707093 0.707121 0.00146484 7.38428)" fill="#1E2533" />
                                <rect width="10.4429" height="0.870241"
                                    transform="matrix(-0.707092 -0.707121 0.707092 -0.707121 7.38428 8)" fill="#1E2533" />
                            </svg>
                        </div>
                        <div class="mc-f-selected">
                            <div>
                                <img width="24" height="24" src="{{ asset(env('THEME') . 'images') }}/module/size1.svg"
                                    alt="">
                            </div>
                            <svg width="8" height="8" viewBox="0 0 8 8" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect width="10.4429" height="0.870241"
                                    transform="matrix(0.707093 -0.707121 0.707093 0.707121 0.00146484 7.38428)" fill="#1E2533" />
                                <rect width="10.4429" height="0.870241"
                                    transform="matrix(-0.707092 -0.707121 0.707092 -0.707121 7.38428 8)" fill="#1E2533" />
                            </svg>
                        </div>
--}}

                    </div>
                    <div class="filter-popular">
                        <p>@lang("gallery.sort_by"):</p>
                        <div class="pop-sort filter-item mc-js-filter">
                            <a href="#">
                                <span id="isort">
                                    @if(\Request::get('order') == 'cheap')
                                        @lang("gallery.sort_by_price_low")
                                    @elseif(\Request::get('order') == 'expensive')
                                        @lang("gallery.sort_by_price_high")
                                    @elseif(\Request::get('order') == 'new')
                                        @lang("gallery.sort_by_new")
                                    @else
                                        @lang("gallery.sort_by_popular")
                                    @endif
                                </span>
                                <svg width="13" height="8" viewBox="0 0 13 8" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12.2184 0.137457C12.1316 0.0458193 12.0317 4.23244e-08 11.9188 4.72591e-08C11.8059 5.21938e-08 11.7061 0.0458193 11.6192 0.137457L6.5 5.53952L1.38076 0.137458C1.29392 0.0458198 1.19405 5.16054e-07 1.08116 5.20989e-07C0.968269 5.25924e-07 0.868403 0.0458198 0.781562 0.137458L0.130261 0.824744C0.0434205 0.916381 -3.05028e-07 1.02176 -2.99821e-07 1.14089C-2.94614e-07 1.26002 0.0434205 1.36541 0.130261 1.45705L6.2004 7.86254C6.28724 7.95418 6.38711 8 6.5 8C6.61289 8 6.71276 7.95418 6.7996 7.86254L12.8697 1.45705C12.9566 1.36541 13 1.26002 13 1.14089C13 1.02176 12.9566 0.916381 12.8697 0.824743L12.2184 0.137457Z"
                                        fill="#FC8C5F" />
                                </svg>
                            </a>
                            <div class="filter-item--wrapper filter-sort">
                                <p>@lang("gallery.sort_by"):</p>
                                <ul class="sort-it">
                                    <li @if(\Request::get('order') == '') class="active" @endif>
                                        @if($category)
                                            <a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "" ]) }}">
                                        @else
                                            <a href="{{ route('hb.gallery.module', [$page->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> ""]) }}">
                                        @endif
                                            <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g class="clip0_309_246">
                                                    <path
                                                        d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z"
                                                        fill="#FA7846" />
                                                </g>
                                                <defs>
                                                    <clipPath class="clip0_309_246">
                                                        <rect width="15" height="15" fill="white" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                            <span>@lang("gallery.sort_by_popular")</span>
                                        </a>
                                    </li>
                                    <li @if(\Request::get('order') == 'new') class="active" @endif>
                                        @if($category)
                                            <a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "new" ]) }}">
                                        @else
                                            <a href="{{ route('hb.gallery.module', [$page->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "new"]) }}">
                                        @endif
                                            <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g class="clip0_309_246">
                                                    <path
                                                        d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z"
                                                        fill="#FA7846" />
                                                </g>
                                                <defs>
                                                    <clipPath class="clip0_309_246">
                                                        <rect width="15" height="15" fill="white" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                            <span>@lang("gallery.sort_by_new")</span>
                                        </a>
                                    </li>
                                    <li @if(\Request::get('order') == 'cheap') class="active" @endif>
                                        @if($category)
                                            <a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "cheap" ]) }}">
                                        @else
                                            <a href="{{ route('hb.gallery.module', [$page->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "cheap"]) }}">
                                        @endif
                                            <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g class="clip0_309_246">
                                                    <path
                                                        d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z"
                                                        fill="#FA7846" />
                                                </g>
                                                <defs>
                                                    <clipPath class="clip0_309_246">
                                                        <rect width="15" height="15" fill="white" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                            <span>@lang("gallery.sort_by_price_low")</span>
                                        </a>
                                    </li>
                                    <li @if(\Request::get('order') == 'expensive') class="active" @endif>
                                        @if($category)
                                            <a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "expensive" ]) }}">
                                        @else
                                            <a href="{{ route('hb.gallery.module', [$page->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "expensive"]) }}">
                                        @endif
                                            <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g class="clip0_309_246">
                                                    <path
                                                        d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z"
                                                        fill="#FA7846" />
                                                </g>
                                                <defs>
                                                    <clipPath class="clip0_309_246">
                                                        <rect width="15" height="15" fill="white" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                            <span>@lang("gallery.sort_by_price_high")</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="mc-info">@lang("gallery.found"): <span>{{ $items->total() }}</span></p>
                <div class="mc-grid">

                    {{--
                    <div class="catalog-content">
                        <div class="title">
                            <h3>{{ $page['name'] }}</h3>
                        </div>
                        <div class="catalog-items clearfix">
                            @foreach ($items as $item)
                                @php
                                    $images = json_decode($item->images, true);
                                    $image = '/storage/' . $images[0];
                                @endphp
                                <div class="catalog-item">
                                    <div class="img">
                                        <img alt="{{ App\Models\GalleryItem::getTransName($item->id) }}"
                                             title="{{ App\Models\GalleryItem::getTransName($item->id) }}" class=""
                                             src="{{ $image }}" data-src="{{ $image }}"/>
                                    </div>
                                    <h5>{{ App\Models\GalleryItem::getTransName($item->id) }}</h5>
                                    <i>{{ $glob['art_jenre_text'] }}
                                        <span>{{ App\Models\GalleryItem::getTransJenre($item->id) }}</span></i>
                                    <i>{{ $glob['art_style_text'] }} <span>
                                            {{ App\Models\GalleryItem::getTransStyle($item->id) }}</span></i>
                                    <i>{{ $glob['art_size_text'] }}
                                        <span>@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.custom_sizes_calc_cat', ['current_first_size' => true])</span>
                                    </i>
                                    <p>
                                        {{ $glob['art_price_text'] }}
                                        {{ $glob['art_price_from_text'] }}
                                        <span> {{ $item->minSumPrice }} €</span>
                                    </p>
                                    <a data-id="{{ $item->id }}" href="{{ URL::current() . '/item/' . $item->id }}"
                                       tabindex="{{ $item->id }}"><span>{{ $glob['art_order_text'] }}<i></i></span></a>
                                </div>
                            @endforeach
                        </div>
                        {{ $items->links() }}
                    </div>
                     --}}

                    @foreach ($items as $item)
                        @php
                            $images = json_decode($item->images, true);
                            if(isset($images[1])){
                                $image = '/storage/' . $images[1];
                            }
                            else{
                                $image = '/storage/' . $images[0];
                            }

                        @endphp
                        <div class="mc-item /*mc-disc*/">
                            {{--
                            <div class="bs-discount">
                                -50 %
                            </div>
                            --}}
                            <div class="mc-item__inner">
                                <div class="mc-item__img">
                                    <a href="{{ route('hb.gallery.item.single', ['type' => $type, 'item' => $item->id]) }}">
                                        <picture>
                                            @if ($webpSrc = image_webp_url($image))
                                                <source srcset="{{ $webpSrc }}" type="image/webp">
                                            @endif
                                            {{-- <source srcset="{{ asset(env('THEME') . 'images') }}/gallery/7.jpg" type="image/jpeg"> --}}
                                            {{-- <img width="515" height="527" src="{{ asset(env('THEME') . 'images') }}/gallery/7.jpg" alt="ViarCanvas" loading="lazy"> --}}
                                            <img width="515" height="527" @altAttrs($item, 'images', $image, null, App\Models\GalleryItem::getTransName($item->id)) class="" src="https://viarcanvas.com/{{ $image }}" data-src="{{ $image }}" loading="lazy"/>
                                        </picture>
                                    </a>
                                </div>
                                <div class="mc-item__content">
                                    <div class="mc-item__title">
                                        <a href="{{ route('hb.gallery.item.single', ['type' => $type, 'item' => $item->id]) }}">{{ App\Models\GalleryItem::getTransName($item->id) }}</a>
                                    </div>
                                    <div class="mc-item__info">
                                        <p class="mc-item-size">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M5.46667 5H5V5.46667V10.1333H5.93333V6.5933L9.80335 10.4633L10.4633 9.80335L6.5933 5.93333H10.1333V5H5.46667ZM18.5333 5H19V5.46667V10.1333H18.0667V6.5933L14.1966 10.4633L13.5367 9.80335L17.4067 5.93333H13.8667V5H18.5333ZM19 19H18.5333H13.8667V18.0667H17.4067L13.5367 14.1966L14.1966 13.5367L18.0667 17.4067V13.8667H19V18.5333V19ZM5.46667 19H5V18.5333V13.8667H5.93333V17.4067L9.80335 13.5367L10.4633 14.1966L6.5933 18.0667H10.1333V19H5.46667Z"
                                                    fill="#FA7846"></path>
                                            </svg>
                                            <span>@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.custom_sizes_calc_cat', ['current_first_size' => true])</span>
                                        </p>
                                        <p class="mc-avail">
                                            <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_214_3)">
                                                    <path
                                                        d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z"
                                                        fill="#1F9750" />
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_214_3">
                                                        <rect width="15" height="15" fill="white" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                            <span>@lang('cart_new.in_stock')</span>
                                        </p>
                                    </div>
                                    <div class="mc-item__info">
                                        <div class="mc-price">
                                            <p>
                                                @if($item->minSumPrice) @lang('gl.price_from_text') <span class="mc-m-price">@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.custom_sizes_calc_cat', ['full_current_price' => true])</span> @endif
                                            </p>
                                        </div>
                                        <a href="{{ route('hb.gallery.item.single', ['type' => $type, 'item' => $item->id]) }}" tabindex="{{ $item->id }}" class="mc-btn mm-btn">
                                            @lang('collage.index12')
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach


                    {{--
                    <div class="mc-item mc-disc">
                        <div class="bs-discount">
                            -50 %
                        </div>
                        <div class="mc-item__inner">
                            <div class="mc-item__img">
                                <picture>
                                    <source srcset="{{ asset(env('THEME').'images') }}/gallery/9.webp" type="image/webp">
                                    <source srcset="{{ asset(env('THEME') . 'images') }}/gallery/9.jpg" type="image/jpeg">
                                    <img width="515" height="527" src="{{ asset(env('THEME') . 'images') }}/gallery/9.jpg"
                                        alt="ViarCanvas" loading="lazy">
                                </picture>
                            </div>
                            <div class="mc-item__content">
                                <div class="mc-item__title">
                                    Модульная картина “Ночной Лос-Анджелес”
                                </div>
                                <div class="mc-item__info">
                                    <p class="mc-item-size">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M5.46667 5H5V5.46667V10.1333H5.93333V6.5933L9.80335 10.4633L10.4633 9.80335L6.5933 5.93333H10.1333V5H5.46667ZM18.5333 5H19V5.46667V10.1333H18.0667V6.5933L14.1966 10.4633L13.5367 9.80335L17.4067 5.93333H13.8667V5H18.5333ZM19 19H18.5333H13.8667V18.0667H17.4067L13.5367 14.1966L14.1966 13.5367L18.0667 17.4067V13.8667H19V18.5333V19ZM5.46667 19H5V18.5333V13.8667H5.93333V17.4067L9.80335 13.5367L10.4633 14.1966L6.5933 18.0667H10.1333V19H5.46667Z"
                                                fill="#FA7846"></path>
                                        </svg>
                                        <span>100х70см</span>
                                    </p>
                                    <p class="mc-avail">
                                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g clip-path="url(#clip0_214_3)">
                                                <path
                                                    d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z"
                                                    fill="#1F9750" />
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_214_3">
                                                    <rect width="15" height="15" fill="white" />
                                                </clipPath>
                                            </defs>
                                        </svg>
                                        <span>@lang('cart_new.in_stock')</span>
                                    </p>
                                </div>
                                <div class="mc-item__info">
                                    <div class="mc-price">
                                        <span class="mc-n-price">150€</span>
                                        <span class="mc-o-price">300€</span>
                                    </div>
                                    <a href="#" class="mc-btn mm-btn">
                                        Заказать
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    --}}
                </div>


                <div class="blog__list-nav">
                    <a href="#">
                        {{-- Смотреть больше --}}
                    </a>

                    @if (!is_array($items))
                        {{ $items->links('theme.viar.blog.paginate') }}
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>
