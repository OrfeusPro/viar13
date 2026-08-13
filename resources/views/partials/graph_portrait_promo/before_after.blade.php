@if(count($before_after)>0)
<section class="before-after">
    <div class="content">
        <div class="before">
            <div class="title">
                <h3>{{ trans('gl.before_after1') }}</h3>
                <h4>{{ trans('gl.before_after2') }}</h4>
            </div>
            <div class="text">
                {!! trans('gl.before_after3') !!}
                <span>({!! trans('gl.move_scroller') !!})</span>
            </div>

            <div class="before-slider">
                <div id="slider" class="beer-slider" data-beer-label="before">
                    @foreach($before_after as $img)
                    <img src="{{ Voyager::image($img['img_before']) }}" alt="">
                    <div class="beer-reveal" data-beer-label="after">
                        <img src="{{ Voyager::image($img['img_after']) }}" alt="">
                    </div>
                    @break
                    @endforeach
                </div>
            </div>
        </div>
        <div class="before-photos">
            <div class="title">
                <h3>{!! trans('gl.before_after4') !!}</h3>
            </div>
            <div class="photos-slider">
                @foreach($before_after->chunk(2) as $items)
                <div>
                    @foreach($items as $item)
                    <div class="photos-item js_change_active_sl" data-before="{{ Voyager::image($item['img_before']) }}"
                        data-after="{{ Voyager::image($item['img_after']) }}">
                        <span></span>
                        @php
                            $graphicBeforeAfterThumbSources = image_picture_sources($item['main_image'], true);
                        @endphp
                        <picture>
                            @if(!empty($graphicBeforeAfterThumbSources['src_webp']))
                                <source srcset="{{ $graphicBeforeAfterThumbSources['src_webp'] }}" type="image/webp">
                            @endif
                            @if(!empty($graphicBeforeAfterThumbSources['src']) && !empty($graphicBeforeAfterThumbSources['type']))
                                <source srcset="{{ $graphicBeforeAfterThumbSources['src'] }}" type="{{ $graphicBeforeAfterThumbSources['type'] }}">
                            @endif
                            <img class="" src="{{ $graphicBeforeAfterThumbSources['src'] }}"
                                data-src="{{ $graphicBeforeAfterThumbSources['src'] }}" alt="">
                        </picture>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
