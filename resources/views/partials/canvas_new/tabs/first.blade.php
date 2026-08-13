<div class="about__block about__first brief-v hidden-block">
    @php
        $s_items = $item->getMedia('tab_what_is');
    @endphp

    @if(!$s_items->isEmpty())
        @foreach($s_items->chunk(3) as $s_block_items)
            <div class="about__canvas-row">
            @foreach($s_block_items as $sub_item)
                    <div class="about__canvas-item">
                        <div class="about__canvas-img">
                            <picture>
                                @php
                                    $canvasNewWhatIsImageSources = image_picture_sources($sub_item->getUrl(), true);
                                @endphp
                                @if(!empty($canvasNewWhatIsImageSources['src_webp']))
                                    <source srcset="{{ $canvasNewWhatIsImageSources['src_webp'] }}" type="image/webp">
                                @endif
                                @if(!empty($canvasNewWhatIsImageSources['src']) && !empty($canvasNewWhatIsImageSources['type']))
                                    <source srcset="{{ $canvasNewWhatIsImageSources['src'] }}" type="{{ $canvasNewWhatIsImageSources['type'] }}">
                                @endif
                                <img src="{{ $canvasNewWhatIsImageSources['src'] }}" @altAttrs($item, 'media:tab_what_is', $sub_item->getUrl(), null, $sub_item->getCustomProperty('image_alt_' . app()->getLocale()), $sub_item->getCustomProperty('image_title_' . app()->getLocale()))>
                            </picture>
                        </div>
                        {!! trans('canvas.tab1_item'.$loop->iteration.'_desc') !!}
                    </div>
            @endforeach
            </div>

        @endforeach
    @endif

    <div class="hidden-trigger">
        <a class="anchor ellipse-arrow ellipse-arrow_white" aria-label="anchor link">
            <i class="fa-arrow-down"></i>
        </a>
        <span>{{ trans('canvas.tabs_show_btn_text') }}</span>
        <span>{{ trans('canvas.tabs_hide_btn_text') }}</span>
    </div>
</div>
