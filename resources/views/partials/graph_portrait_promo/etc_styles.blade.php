<section class="other-styles">
    <div class="other-content">
        <div class="title">
            <h2>{{ trans('gl.etc_styles') }}</h2>
        </div>
        <div class="other-slider">
            @if($graph_items)
            @foreach($graph_items as $item)
            <div class="other-item">
                <div class="item">
                    <div class="img">
                        @if($item->etc_style_image)
                        @php
                            $graphPromoStyleImageSources = image_picture_sources(data_get($item, 'etc_style_image'), true);
                        @endphp
                        <picture>
                            @if(!empty($graphPromoStyleImageSources['src_webp']))
                                <source srcset="{{ $graphPromoStyleImageSources['src_webp'] }}" type="image/webp">
                            @endif
                            @if(!empty($graphPromoStyleImageSources['src']) && !empty($graphPromoStyleImageSources['type']))
                                <source srcset="{{ $graphPromoStyleImageSources['src'] }}" type="{{ $graphPromoStyleImageSources['type'] }}">
                            @endif
                            <img alt="{{ $item['name'] }}" title="{{ $item['name'] }}"
                                src="{{ $graphPromoStyleImageSources['src'] }}" @altAttrs($item, 'etc_style_image', data_get($item, 'etc_style_image'))>
                        </picture>
                        @endif
                    </div>
                    <h3>{{ $item['name'] }}</h3>
                    <a href="{{ $item['slug'] }}"><span>{{ trans('gl.read_more') }}</span></a>
                </div>
            </div>
            @endforeach
            @endif
        </div>
    </div>
</section>
