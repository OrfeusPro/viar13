<div class="accordion-title h2_old">
    <span>{{ trans('canvas.form_step5_title') }}</span>
    <span class="tab-icon"></span>
</div>
<div class="accordion-content">
    <div class="kviz-row-single kviz-c-group">
        <div class="kviz-group__item">
            @if(isset($canvas_items))
                @foreach($canvas_items as $item)
                    <div class="kviz-radio js_canvas_type_item js-checkbox @if($item->default) kviz-radio_active @endif" data-stock="{{ $loop->iteration }}" data-price="{{ $item->price }}" data-id="{{ $item->id }}">
                            <div class="check"></div>
                            <label> <span>{{ $item->getTranslatedAttribute('name', app()->getLocale()) }} {{ $item->getTranslatedAttribute('density', app()->getLocale()) }}
                                    <img src="{{ asset('images/icon/info.svg') }}" alt=""></span>
                                <input name="canvas_type" type="radio" @if($item->default) checked @endif
                                        class="js_canvas_type"
                                       data-name="{{ $item->getTranslatedAttribute('name', app()->getLocale()) }}"
                                       data-id="{{ $item->id }}"
                                       data-price="{{ $item->price }}"
                                       value="{{ $item->price }}"/>
								@if($item->image)
                                <div class="pic-pop">
                                    <picture>
                                        @php
                                            $canvasToolImageSources = image_picture_sources(data_get($item, 'image'), true);
                                        @endphp
                                        @if(!empty($canvasToolImageSources['src_webp']))
                                            <source srcset="{{ $canvasToolImageSources['src_webp'] }}" type="image/webp">
                                        @endif
                                        @if(!empty($canvasToolImageSources['src']) && !empty($canvasToolImageSources['type']))
                                            <source srcset="{{ $canvasToolImageSources['src'] }}" type="{{ $canvasToolImageSources['type'] }}">
                                        @endif
                                        <img loading="lazy" src="{{ $canvasToolImageSources['src'] }}" @altAttrs($item, 'image', data_get($item, 'image'))>
                                    </picture>
                                </div>
                                @endif
                            </label>
                        </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
