<section class="examples c-examples section-p" id="examples">
    <div class="section-frame">
        <h2 class="page-title page-title_white">{{ trans('portrait.examples_title') }}</h2>
        <div class="examples-wrap example-flex">
            <div class="before-after__block">
                <div class="title-group">
                    <div class="before-after__title">{{ trans('portrait.before_after') }}</div>
                    <div class="before-after__subtitle">
                        {!! trans('portrait.before_after_desc') !!}
                    </div>
                </div>
                <div class="ba-slider">
                    @if(!$ba_items->isEmpty())
                        <picture>
                            @php
                                $simpsonsExampleBeforeSources = image_picture_sources($ba_items[0]->getUrl(), true);
                            @endphp
                            @if(!empty($simpsonsExampleBeforeSources['src_webp']))
                                <source srcset="{{ $simpsonsExampleBeforeSources['src_webp'] }}" type="image/webp">
                            @endif
                            @if(!empty($simpsonsExampleBeforeSources['src']) && !empty($simpsonsExampleBeforeSources['type']))
                                <source srcset="{{ $simpsonsExampleBeforeSources['src'] }}" type="{{ $simpsonsExampleBeforeSources['type'] }}">
                            @endif
                            <img src="{{ $simpsonsExampleBeforeSources['src'] }}" @altAttrs($item, 'media:new_before_items', $ba_items[0]->getUrl(), null, $ba_items[0]->getCustomProperty('image_alt_' . app()->getLocale()), $ba_items[0]->getCustomProperty('image_title_' . app()->getLocale()))/>
                        </picture>
                    @endif
                    @if(!$ba_items_after->isEmpty())
                        <div class="resize">
                            <picture>
                                @php
                                    $simpsonsExampleAfterSources = image_picture_sources($ba_items_after[0]->getUrl(), true);
                                @endphp
                                @if(!empty($simpsonsExampleAfterSources['src_webp']))
                                    <source srcset="{{ $simpsonsExampleAfterSources['src_webp'] }}" type="image/webp">
                                @endif
                                @if(!empty($simpsonsExampleAfterSources['src']) && !empty($simpsonsExampleAfterSources['type']))
                                    <source srcset="{{ $simpsonsExampleAfterSources['src'] }}" type="{{ $simpsonsExampleAfterSources['type'] }}">
                                @endif
                                <img srcset="{{ $simpsonsExampleAfterSources['src'] }}" @altAttrs($item, 'media:new_after_items', $ba_items_after[0]->getUrl(), null, $ba_items_after[0]->getCustomProperty('image_alt_' . app()->getLocale()), $ba_items_after[0]->getCustomProperty('image_title_' . app()->getLocale()))/>
                            </picture>
                        </div>
                    @endif
                    <span class="handle"></span>
                </div>
                <div class="before-after__text"> {!! trans('portrait.move_cursor') !!}</div>
                <div class="before-after__nav">
                    <div class="bf-arr bf-arr_l">
                        <svg width="5" height="9" viewBox="0 0 5 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M4.91409 8.45892C4.97136 8.3988 5 8.32966 5 8.2515C5 8.17335 4.97136 8.10421 4.91409 8.04409L1.5378 4.5L4.91409 0.955912C4.97136 0.895792 5 0.826653 5 0.748496C5 0.67034 4.97136 0.601202 4.91409 0.541081L4.48454 0.0901804C4.42726 0.0300598 4.3614 0 4.28694 0C4.21249 0 4.14662 0.0300598 4.08935 0.0901804L0.0859107 4.29258C0.0286369 4.35271 0 4.42184 0 4.5C0 4.57816 0.0286369 4.64729 0.0859107 4.70742L4.08935 8.90982C4.14662 8.96994 4.21249 9 4.28694 9C4.3614 9 4.42726 8.96994 4.48454 8.90982L4.91409 8.45892Z"
                                fill="white"/>
                        </svg>
                    </div>
                    <ul class="bf-obj">
                         @if(!$ba_items->isEmpty())
                            @foreach($ba_items as $ba_item)
                                <li @if($loop->first) class="active" @endif
                                    data-imageB="{{ $ba_item->getUrl() }}"
                                    @isset($ba_items_after[$loop->index])
                                    data-imageA="{{ $ba_items_after[$loop->index]->getUrl() }}"
                                    @endisset
                                    >
                                    {{ $loop->iteration }}
                                </li>
                            @endforeach
                        @endif
                    </ul>
                    <div class="bf-arr bf-arr_r">
                        <svg width="5" height="9" viewBox="0 0 5 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M0.0859108 8.45892C0.0286369 8.3988 0 8.32966 0 8.2515C0 8.17335 0.0286369 8.10421 0.0859108 8.04409L3.4622 4.5L0.0859108 0.955912C0.0286369 0.895792 0 0.826653 0 0.748496C0 0.67034 0.0286369 0.601202 0.0859108 0.541081L0.515464 0.0901804C0.572738 0.0300598 0.638603 0 0.713059 0C0.787515 0 0.853379 0.0300598 0.910653 0.0901804L4.91409 4.29258C4.97136 4.35271 5 4.42184 5 4.5C5 4.57816 4.97136 4.64729 4.91409 4.70742L0.910653 8.90982C0.853379 8.96994 0.787515 9 0.713059 9C0.638603 9 0.572738 8.96994 0.515464 8.90982L0.0859108 8.45892Z"
                                fill="white"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="examples-slider__block">
                <div class="title-group">
                    <div class="examples-slider__title">{!! trans('portrait.work_photos') !!}</div>
                </div>
                <div class="examples-slider__inner">
                        @if(!$ba_items2->isEmpty())
                            @foreach($ba_items2 as $ba_item)
                            <div class="examples-slide">
                                <a class="examples-slide__photo">
                                        <picture>
											@if(isset($ba_items2[$loop->index]) && $ba_items2[$loop->index]->getUrl())
                                                @php
                                                    $simpsonsExampleWorkSources = image_picture_sources($ba_item->getUrl(), true);
                                                @endphp
                                                @if(!empty($simpsonsExampleWorkSources['src_webp']))
                                                    <source srcset="{{ $simpsonsExampleWorkSources['src_webp'] }}" type="image/webp">
                                                @endif
                                                @if(!empty($simpsonsExampleWorkSources['src']) && !empty($simpsonsExampleWorkSources['type']))
                                                    <source srcset="{{ $simpsonsExampleWorkSources['src'] }}" type="{{ $simpsonsExampleWorkSources['type'] }}">
                                                @endif
                                            <img src="{{ $simpsonsExampleWorkSources['src'] }}" data-fancybox="group" data-src="{{ $ba_items2[$loop->index]->getUrl() }}" @altAttrs($item, 'media:works_examples_new', $ba_item->getUrl(), null, $ba_item->getCustomProperty('image_alt_' . app()->getLocale()), $ba_item->getCustomProperty('image_title_' . app()->getLocale())) loading="lazy"/>
											@endif
                                        </picture>
                                </a>
                                <a href="#" class="examples-slide__btn js-examples"
                                    data-catid="{{ $current_quiz_style_id }}"
                                   @if ($ba_item->getCustomProperty('name')) data-style="{{ str_trans($ba_item->getCustomProperty('name')) }}" @endif
                                   @if ($ba_item->getCustomProperty('size')) data-size="{{ str_trans($ba_item->getCustomProperty('size')) }}" @endif>{{ trans('homepage_new.our_portraits_btn_title') }}</a>
                            </div>
                            @endforeach
                        @endif
                </div>
                <div class="examples-arrow">
                    <a href="#" class="examples-prev examples-prev2" aria-label="examples prev">
                        <svg width="48" height="51" viewBox="0 0 48 51" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M27.2335 31.1145C27.334 31.0141 27.3842 30.8986 27.3842 30.768C27.3842 30.6374 27.334 30.5219 27.2335 30.4215L21.3122 24.5001L27.2335 18.5788C27.334 18.4784 27.3842 18.3629 27.3842 18.2323C27.3842 18.1017 27.334 17.9862 27.2335 17.8857L26.4802 17.1324C26.3797 17.0319 26.2642 16.9817 26.1336 16.9817C26.0031 16.9817 25.8876 17.0319 25.7871 17.1324L18.7659 24.1536C18.6655 24.254 18.6152 24.3696 18.6152 24.5001C18.6152 24.6307 18.6655 24.7462 18.7659 24.8467L25.7871 31.8679C25.8876 31.9683 26.0031 32.0186 26.1336 32.0186C26.2642 32.0186 26.3797 31.9683 26.4802 31.8679L27.2335 31.1145Z"
                                fill="#fff"/>
                        </svg>
                    </a>
                    <a href="#" class="examples-next examples-next2" aria-label="examples next">
                        <svg width="48" height="51" viewBox="0 0 48 51" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M20.7665 31.1145C20.666 31.0141 20.6158 30.8986 20.6158 30.768C20.6158 30.6374 20.666 30.5219 20.7665 30.4215L26.6878 24.5001L20.7665 18.5788C20.666 18.4784 20.6158 18.3629 20.6158 18.2323C20.6158 18.1017 20.666 17.9862 20.7665 17.8857L21.5198 17.1324C21.6203 17.0319 21.7358 16.9817 21.8664 16.9817C21.9969 16.9817 22.1124 17.0319 22.2129 17.1324L29.2341 24.1536C29.3345 24.254 29.3848 24.3696 29.3848 24.5001C29.3848 24.6307 29.3345 24.7462 29.2341 24.8467L22.2129 31.8679C22.1124 31.9683 21.9969 32.0186 21.8664 32.0186C21.7358 32.0186 21.6203 31.9683 21.5198 31.8679L20.7665 31.1145Z"
                                fill="#fff"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
