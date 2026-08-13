@extends('layots.common')
@section('title', $page['name'])

@section('og_tags')
    <meta property="og:title" content="{{ $page['name'] }}"/>
@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/gallery-modular-catalog.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/gallery-reproductions-catalog.css') }}"/>
@endsection

@section('content')

    {{ Breadcrumbs::render('gallery_category_add', $page) }}
    <section class="gallery-reproductions-catalog">
        <div class="gallery-reproductions-catalog-content">
            <div class="container">
                <div class="catalog-reproductions clearfix">
                    <div class="catalog-filter clearfix">
                        <div class="filter-title">
                            <p>{{ $page['name'] }}</p>
                        </div>
                        <div class="filter-content">
                            <h3>{{ $page['name'] }}</h3>
                            <div class="filter-item">
                                <h4>{{ $glob['sub_cats_title'] }}</h4>
                                <ul>
                                    @foreach ($categories as $item)
                                        <li class="active">
                                            <a
                                                    href="{{ url('/gallery/' . $page->url . '/' . $item->url) }}">{{ $item->name }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="filter-item tags">
                                <h4>{{ $glob['holl_type'] }}</h4>
                                @foreach ($tags as $tag)
                                    <div class="checkbox">
                                        <input name="{{ $tag->id }}" type="checkbox"/>
                                        <label>{{ $tag->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="filter-item">
                                <h4>{{ $glob['color_select'] }}</h4>
                                <div class="color" style="padding: 10px 0;">
                                    @foreach ($colors as $color)
                                        <div name="{{ $color->id }}" style="background: {{ $color->color }};"></div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="filter-item">
                                <button><span>{{ $glob['find_btn_text'] }}</span></button>
                            </div>
                        </div>
                    </div>
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
                                        <img @altAttrs(['type' => \App\Models\GalleryItem::class, 'id' => data_get($item, 'id')], 'images', $image, null, App\Models\GalleryItem::getTransName(data_get($item, 'id'))) class=""
                                             src="{{ $image }}" data-src="{{ $image }}"/>
                                    </div>
                                    <h5>{{ App\Models\GalleryItem::getTransName($item->id) }}</h5>
                                    <i>{{ $glob['art_jenre_text'] }}
                                        <span>{{ App\Models\GalleryItem::getTransJenre($item->id) }}</span></i>
                                    <i>{{ $glob['art_style_text'] }} <span>
                                            {{ App\Models\GalleryItem::getTransStyle($item->id) }}</span></i>
                                    <i>{{ $glob['art_size_text'] }}
                                        <span>{{ App\Models\GalleryItem::getSizeByItemId($item->id) }}</span>
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
                </div>
                <div class="popular-content">
                    <div class="title">
                        <h3>{{ $glob['popular_text'] }} {{ $page['name'] }}</h3>
                        <ul>
                            <li><i class="icon-icon6"></i></li>
                            <li><i class="icon-icon6"></i></li>
                            <li><i class="icon-icon6"></i></li>
                            <li><i class="icon-icon6"></i></li>
                            <li><i class="icon-icon6"></i></li>
                        </ul>
                    </div>
                    <div class="popular-slider pop__arts">
                        @foreach ($pop_arts as $item)
                            @php $image = '/storage/' . json_decode($item->images)[0]; @endphp
                            <div class="popular-item">
                                <div>
                                    <div class="img">
                                        <img class="" @altAttrs(['type' => \App\Models\GalleryItem::class, 'id' => data_get($item, 'id')], 'images', $image, null, data_get($item, 'name'))
                                             src="{{ $image }}"
                                             data-src="{{ $image }}">
                                    </div>
                                    <h5>
                                        {{ $item->name }}
                                    </h5>
                                    <i>{{ $glob['art_jenre_text'] }}
                                        <span> {{ $item->genre }}</span></i>
                                    <i>{{ $glob['art_style_text'] }}
                                        <span> {{ $item->style }}</span></i>
                                    <i>{{ $glob['art_size_text'] }}
                                        <span> {{ App\Models\GalleryItem::getSizeByItemId($item->id) }}</span></i>
                                    <p>{{ $glob['art_price_text'] }}
                                        <span> {{ $glob['art_price_from_text'] }} {{ $item->price_from }} €</span>
                                    </p>
                                    <a
                                            href="{{ URL::current() . '/item/' . $item->id }}"><span>{{ $glob['art_order_text'] }}<i></i></span></a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="popular-content">
                    <div class="title">
                        <h3>{{ $glob['recomm_text'] }} {{ $page['name'] }}</h3>
                        <ul>
                            <li><i class="icon-icon6"></i></li>
                            <li><i class="icon-icon6"></i></li>
                            <li><i class="icon-icon6"></i></li>
                            <li><i class="icon-icon6"></i></li>
                            <li><i class="icon-icon6"></i></li>
                        </ul>
                    </div>
                    <div class="popular-slider rec__arts">
                        @foreach ($rec_arts as $item)
                            @php $image = '/storage/' . json_decode($item->images)[0]; @endphp
                            <div class="popular-item">
                                <div>
                                    <div class="img">
                                        <img class="" @altAttrs(['type' => \App\Models\GalleryItem::class, 'id' => data_get($item, 'id')], 'images', $image, null, data_get($item, 'name'))
                                             src="{{ $image }}"
                                             data-src="{{ $image }}">
                                    </div>
                                    <h5>
                                        {{ $item->name }}
                                    </h5>
                                    <i>{{ $glob['art_jenre_text'] }}
                                        <span> {{ $item->genre }}</span></i>
                                    <i>{{ $glob['art_style_text'] }}
                                        <span> {{ $item->style }}</span></i>
                                    <i>{{ $glob['art_size_text'] }}
                                        <span> {{ App\Models\GalleryItem::getSizeByItemId($item->id) }}</span></i>
                                    <p>{{ $glob['art_price_text'] }}
                                        <span> {{ $glob['art_price_from_text'] }} {{ $item->price_from }} €</span>
                                    </p>
                                    <a
                                            href="{{ URL::current() . '/item/' . $item->id }}"><span>{{ $glob['art_order_text'] }}<i></i></span></a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="popular-content">
                    <div class="title">
                        <h3>{{ $glob['all_cats_arts_text'] }}</h3>
                        <ul>
                            <li><i class="icon-icon6"></i></li>
                            <li><i class="icon-icon6"></i></li>
                            <li><i class="icon-icon6"></i></li>
                            <li><i class="icon-icon6"></i></li>
                            <li><i class="icon-icon6"></i></li>
                        </ul>
                    </div>
                    <div class="popular-slider all__cats">
                        @foreach ($all_arts as $item)
                            @php $image = '/storage/' . json_decode($item->images)[0]; @endphp
                            <div class="popular-item">
                                <div>
                                    <div class="img">
                                        <img class="" @altAttrs(['type' => \App\Models\GalleryItem::class, 'id' => data_get($item, 'id')], 'images', $image, null, data_get($item, 'name'))
                                             src="{{ $image }}"
                                             data-src="{{ $image }}">
                                    </div>
                                    <h5>
                                        {{ $item->name }}
                                    </h5>
                                    <i>{{ $glob['art_jenre_text'] }}
                                        <span> {{ $item->genre }}</span></i>
                                    <i>{{ $glob['art_style_text'] }}
                                        <span> {{ $item->style }}</span></i>
                                    <i>{{ $glob['art_size_text'] }}
                                        <span> {{ App\Models\GalleryItem::getSizeByItemId($item->id) }}</span></i>
                                    <p>{{ $glob['art_price_text'] }}
                                        <span> {{ $glob['art_price_from_text'] }} {{ $item->price_from }} €</span>
                                    </p>
                                    <a
                                            href="{{ URL::current() . '/item/' . $item->id }}"><span>{{ $glob['art_order_text'] }}<i></i></span></a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="{{ asset('js/jcf.min.js') }}"></script>
    <script src="{{ asset('js/jcf.checkbox.min.js') }}"></script>
    <script src="{{ asset('js/slick.min.js') }}"></script>
    <script src="{{ asset('js/gallery-modular-catalog.min.js') }}"></script>

    <script>
        $(function () {
            $("body").on("click", ".color div", function () {
                $(".color div").removeClass("active");

                if ($(this).hasClass("active")) {
                    $(".color div").removeClass("active");
                } else {
                    $(this).addClass("active");
                }
            });

            $("body").on("click", "button", function () {
                var color = $(".color div.active").attr("name") ?
                    $(".color div.active").attr("name") :
                    "";
                var page = $("span.page-link").text();
                var checked = [];
                var checkedIds = "";

                checked.push(
                    $("input[type=checkbox]").map(function (index, item) {
                        if ($(item).prop("checked")) {
                            return $(item).attr("name");
                        }
                    })
                );

                for (i in checked[0]) {
                    if (checked[0][i].length > 0 && $.isNumeric(i))
                        checkedIds += checked[0][i] + ",";
                }

                checkedIds = checkedIds.substring(0, checkedIds.length - 1);

                var redirect =
                    "{{ URL::current() }}?page=" +
                    page +
                    "&tag=" +
                    checkedIds +
                    "&color=" +
                    color;

                location.href = redirect;
            });
        });
    </script>
@endsection
