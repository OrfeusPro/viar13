@if(!$bestseller->isEmpty())

<div class="bestseller-main">
    <div class="section-frame">
        <div class="bestseller-main__inner">
            <h2 class="bs-title page-title">
                @if (Route::currentRouteName() == 'hb.gallery.index')
                    @lang("gallery.our_bestsellers")
                @elseif  (Route::currentRouteName() == 'hb.gallery.module' && $type == 'reproduction')
                @lang("gallery.reproduction_our_bestsellers")
                @elseif  (Route::currentRouteName() == 'hb.gallery.module' && $type == 'photo')
                    @lang("gallery.photo_our_bestsellers")
                @elseif  (Route::currentRouteName() == 'hb.gallery.module' && $type == 'module')
                    @lang("gallery.module_our_bestsellers")
                 @else
                 @lang("gallery.our_bestsellers")
                 @endif

            </h2>
            <div class="bs-slider ccs-slider">

                @foreach($bestseller as $best_item)
                @php
                    $images = json_decode($best_item->images, true);
                    if(isset($images[1])){
                        $image = '/storage/' . $images[1];
                    }
                    else{
                        $image = '/storage/' . $images[0];
                    }
                @endphp
                <div class="bs-item bs-dc">
                    <div class="bs-item-wrapper">
                        {{--
                        <div class="bs-discount">
                            -50 %
                        </div>
                        --}}
                        <div class="bs-item-inner">
                            <picture>
                                @if ($webpSrc = image_webp_url($image))
                                    <source srcset="{{ $webpSrc }}" type="image/webp">
                                @endif
                                <source srcset="https://viarcanvas.com{{ $image }}" type="image/jpeg">
                                <img width="430" height="396" src="https://viarcanvas.com{{ $image }}" @altAttrs($best_item, 'images', $image, null, App\Models\GalleryItem::getTransName($best_item->id)) loading="lazy">
                            </picture>
                            <div class="bs-item-content">
                                <p class="bs-price">
                                    @include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.custom_sizes_calc_cat', ['item' => $best_item,'full_current_price' => true])
                                </p>
                                <p class="bs-size">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M5.46667 5H5V5.46667V10.1333H5.93333V6.5933L9.80335 10.4633L10.4633 9.80335L6.5933 5.93333H10.1333V5H5.46667ZM18.5333 5H19V5.46667V10.1333H18.0667V6.5933L14.1966 10.4633L13.5367 9.80335L17.4067 5.93333H13.8667V5H18.5333ZM19 19H18.5333H13.8667V18.0667H17.4067L13.5367 14.1966L14.1966 13.5367L18.0667 17.4067V13.8667H19V18.5333V19ZM5.46667 19H5V18.5333V13.8667H5.93333V17.4067L9.80335 13.5367L10.4633 14.1966L6.5933 18.0667H10.1333V19H5.46667Z"
                                            fill="#FA7846" />
                                    </svg>
                                    <span>@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.custom_sizes_calc_cat', ['item' => $best_item, 'current_first_size' => true])</span>
                                </p>
                                <a href="{{ route('hb.gallery.item.single', ['type' => \App\Models\GalleryType::where('id', $best_item->id_type)->first()->url, 'item' => $best_item->id]) }}" class="mm-btn">
                                    @lang("gallery.bestseller_btn")
                                </a>
                            </div>
                        </div>
                    </div>
                    <p>{{ App\Models\GalleryItem::getTransName($best_item->id) }}</p>
                </div>
                @endforeach

            </div>
        </div>
    </div>
</div>
@endif
