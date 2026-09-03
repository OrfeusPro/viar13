@foreach ($bot_items as $item)
<div class="arts__item">
    <div class="arts__title h2_old">
        {{ $item['title'] }}
    </div>
    <a href="{{ storefront_url($item['link']) }}">
        <div class="arts__image arts__image_vertical">
            <picture>
                <source srcset="{{ asset('img/vertical.webp') }}" type="image/webp">
                <img data-src="{{ asset('img/vertical.webp') }}" src="{{ asset('img/load.png') }}" alt="" class="arts__bg lazyload">
            </picture>
            <picture class="arts__product-image">
                {{-- <source srcset="img/art1.webp" type="image/webp">
                    <source srcset="img/art1.jpg" type="image/jpg">
                    <source srcset="img/art1-m.webp" media="(max-width: 767px)"> --}}
                @php
                    $botItemImageSources = !empty($item['image']) ? image_picture_sources('/storage/' . $item['image'], true) : null;
                @endphp
                @if(!empty($botItemImageSources['src_webp']))
                    <source srcset="{{ $botItemImageSources['src_webp'] }}" type="image/webp">
                @endif
                @if(!empty($botItemImageSources['src']) && !empty($botItemImageSources['type']))
                    <source srcset="{{ $botItemImageSources['src'] }}" type="{{ $botItemImageSources['type'] }}">
                @endif
                <img @if (!empty($botItemImageSources['src'])) src="{{ $botItemImageSources['src'] }}" @else src="{{ asset('img/load.png') }}" @endif alt="">
            </picture>
        </div>
    </a>

    <p class="arts__description">
        {{ $item['text'] }}
    </p>
    <p class="arts__price">
        {{ $data['from_text'] }} <span>{{ $item['price'] }}{{ $data['price_val'] }}</span>
    </p>
    <a class="arts__btn btn" href="{{ storefront_url($item['link']) }}">
        {{ $data['order_btn_text'] }}
    </a>
</div>
@endforeach
