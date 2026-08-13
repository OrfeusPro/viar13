<div class="portraits__wrapper">
    <div class="portraits--info">
        <div class="portraits-heading">
            <h1 class="portraits-title">
                {{ trans('canvas.canvas') }}
            </h1>
            <p>{{ trans('canvas.canvas_desc') }}</p>
        </div>
        <div class="portraits-btn">
            <a href="#generate">{{ trans('portrait.to_order_portrait') }}</a>
        </div>
    </div>
    <div class="portraits-image-other">
        @php
            $canvasHeaderDesktopSources = image_picture_sources($item['canv_head_desk_img'] ?? null, true);
            $canvasHeaderMobileSources = image_picture_sources($item['canv_head_mob_img'] ?? null, true);
        @endphp
        <picture>
            @if(!empty($canvasHeaderMobileSources['src_webp']))
                <source media="(max-width: 700px)" srcset="{{ $canvasHeaderMobileSources['src_webp'] }}" type="image/webp">
            @endif
            @if(!empty($canvasHeaderDesktopSources['src_webp']))
                <source srcset="{{ $canvasHeaderDesktopSources['src_webp'] }}" type="image/webp">
            @endif
            @if(!empty($canvasHeaderMobileSources['src']) && !empty($canvasHeaderMobileSources['type']))
                <source media="(max-width: 700px)" srcset="{{ $canvasHeaderMobileSources['src'] }}" type="{{ $canvasHeaderMobileSources['type'] }}">
            @endif
            @if(!empty($canvasHeaderDesktopSources['src']) && !empty($canvasHeaderDesktopSources['type']))
                <source srcset="{{ $canvasHeaderDesktopSources['src'] }}" type="{{ $canvasHeaderDesktopSources['type'] }}">
            @endif
            <img src="{{ $canvasHeaderDesktopSources['src'] }}" alt="">
        </picture>
    </div>
</div>
