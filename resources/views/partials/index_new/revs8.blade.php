<section class="certificate-frame">
    <div class="ellipse ellipse_black ellipse_top">
        <img src="{{ asset(env('THEME').'images/icon/ellipse-black.svg') }}" alt="img" loading="lazy">
    </div>
    <div class="certificate">
        <div class="section-frame">
            <div class="certificate-content">
                <div class="certificate-preview">
                    <picture>
                        <source srcset="{{ asset('theme/viar/images/gift/certificate_'.Config::get('app.locale').'.webp') }}" type="image/webp">
                        <source srcset="{{ asset('theme/viar/images/gift/certificate_'.Config::get('app.locale').'.png') }}">
                        <img src="{{ asset('theme/viar/images/gift/certificate_'.Config::get('app.locale').'.png') }}" class="certificate-photo"
                            alt="img" loading="lazy">
                    </picture>
                    <div class="certificate-gift">
                        <svg>
                            <use xlink:href="{{ asset(env('THEME').'sprite.svg#like') }}"></use>
                        </svg>
                        <div class="h2_old">{!! trans('homepage_new.gift_cart_banner_title') !!}</div>
                    </div>
                </div>
                <div class="certificate-info">
                    <div class="page-title h2_old">{!! trans('homepage_new.gift_cart_title') !!}</div>
                    <p>{!! trans('homepage_new.gift_cart_desc') !!}</p>
                    <a href="{{ route('gift_card') }}">{!! trans('homepage_new.gift_cart_order_text') !!}</a>
                </div>
            </div>
            <div class="reviews">
                <div class="page-title h2_old">{!! trans('homepage_new.gift_cart_order_clients_say') !!}</div>
                <div class="reviews-wrap">
                    <div class="reviews-slider">
                        @if($revs)
                            @foreach($revs as $item)
                                <div class="reviews-slide">
                                    <div class="reviews-slide-box">
                                        <div class="reviews-content">
                                            <picture>
                                                @php
                                                    $homeReviewImageSources = image_picture_sources($item->img, true);
                                                @endphp
                                                @if(!empty($homeReviewImageSources['src_webp']))
                                                    <source srcset="{{ $homeReviewImageSources['src_webp'] }}" type="image/webp">
                                                @endif
                                                @if(!empty($homeReviewImageSources['src']) && !empty($homeReviewImageSources['type']))
                                                    <source srcset="{{ $homeReviewImageSources['src'] }}" type="{{ $homeReviewImageSources['type'] }}">
                                                @endif
                                                <img src="{{ $homeReviewImageSources['src'] }}"
                                                    class="reviews-photo" alt="" loading="lazy">
                                            </picture>
                                            <div class="reviews-info">
                                                <div class="reviews-title">
                                                    <picture>
                                                        @php
                                                            $homeReviewAvatarSources = image_picture_sources($item->avatar, true);
                                                        @endphp
                                                        @if(!empty($homeReviewAvatarSources['src_webp']))
                                                            <source srcset="{{ $homeReviewAvatarSources['src_webp'] }}" type="image/webp">
                                                        @endif
                                                        @if(!empty($homeReviewAvatarSources['src']) && !empty($homeReviewAvatarSources['type']))
                                                            <source srcset="{{ $homeReviewAvatarSources['src'] }}" type="{{ $homeReviewAvatarSources['type'] }}">
                                                        @endif
                                                        <img src="{{ $homeReviewAvatarSources['src'] }}" alt="img"
                                                            loading="lazy">
                                                    </picture>
                                                    <div class="reviews-name">
                                                        <b>{{ $item->name }}</b>
                                                        <p>{{ $item->city }}</p>
                                                    </div>
                                                </div>
                                                <div class="reviews-text">{!! $item->text !!}</div>
                                            </div>
                                        </div>
                                        @if ($item->a_player)
                                            @php
                                                if (isset(json_decode($item->a_player)[0])) {
                                                    $file = json_decode($item->a_player)[0]->download_link;
                                                } else {
                                                    $file = '';
                                                }

                                            @endphp
                                            @if (Voyager::image($file))
                                                <audio controls src="{{ Voyager::image($file) }}"></audio>
                                            @endif
                                        @endif
                                    </div>
                                    {{-- @if($item->img)
                                        <picture>
                                            <img src="{{ Voyager::image($item->img) }}" class="reviews-bg"
                                                alt="" loading="lazy">
                                        </picture>
                                    @endif --}}
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <div class="reviews-arrow">
                        <a href="#" class="reviews-prev" aria-label="reviews-prev">
                            <i class="fa-arrow-prev"></i>
                        </a>
                        <a href="#" class="reviews-next" aria-label="reviews-next">
                            <i class="fa-arrow-next"></i>
                        </a>
                    </div>
                </div>
                <div class="reviews-counter">
                    <span class="counter-active">1</span>
                    <span>/</span>
                    <span class="reviews-all-slide">{{ count($revs) }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="ellipse ellipse_black">
        <img src="{{ asset(env('THEME').'images/icon/ellipse-black.svg') }}" alt="img" loading="lazy">
    </div>
</section>
