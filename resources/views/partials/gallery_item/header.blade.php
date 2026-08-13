<section class="gallery-modular-one generate" id="gal__item">
    <div class="gallery-modular-one-content generate-content">
        <div class="title">
            <h1 class="js_item_name main__item__name">{{ $item->name }}</h1>
        </div>

        <div class="gallery-photo">
            <div class="photo-content">
                <div class="slider-tabs">
                    <ul class="tabs">
                        <li><a class="active" data-tabs="tabs-item1"
                                href="javascript:void(0)">{!! trans('gl.pic_on_wall') !!}</a>
                        </li>
                        <li><a data-tabs="tabs-item2" href="javascript:void(0)">
                                {{ $canvas_head['calc_tab_2_main_title'] }}</a></li>
                    </ul>
                    <div class="tabs-content">
                        <div class="picture tabs-item tabs-item1 active">
                            @php
                                $images = json_decode($item->images, true);
                            @endphp
                            @if ($images)
                                @foreach ($images as $image)
                                    @php
                                        $src = '/storage/' . $image;
                                        $imageAlt = trans('homepage_new.image') . ' ' . $item->name . ($loop->iteration > 1 ? ' ' . $loop->iteration : '');
                                    @endphp
                                    <div>
                                        <img class="slick__img" src="{{ $src }}" @altAttrs($item, 'images', $image, null, $imageAlt)>
                                    </div>
                                @endforeach
                            @endif
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
                        <h4 class="sum__mod">
                            <div id="glob_summ2" style="display:inline-block;"></div>&euro;
                        </h4>
                    </div>
                    <div class="gal__desc">
                        {!! $item->description !!}
                    </div>
                </div>
                <div class="photo-filter">
                    <div class="filter-picture tabs-item tabs-item1 active">
                        @include('partials.gallery_item.form_tab')
                    </div>
                    @include('partials.module_pics.tab2_form')
                </div>
            </div>
        </div>
    </div>
</section>
