@if(!empty($recommendedItems) && count($recommendedItems) > 0)
<div class="cart-page-sidebar__recommendations">
    <div class="cart-recommendations">
        <div class="cart-recommendations__header">
            <div class="cart-recommendations__title">
                <span>@lang('cart_new.recommendations_title')</span>
            </div>
            <div class="cart-recommendations__subtitle">
                @lang('cart_new.recommendations_subtitle')
            </div>
        </div>

        <div class="cart-recommendations__slider">
            @foreach($recommendedItems as $index => $item)
            @php
                $isCanvas = isset($item['is_canvas_recommendation']) && $item['is_canvas_recommendation'];
                $typeUrl = $item['type_url'] ?? 'canvas';

                if ($isCanvas) {
                    $imageUrl = asset(env('THEME').'images/bg/canvas_photo.png');
                    $size = $item['size'];
                    $lastChar = strtolower(substr($size, -1));
                    $cleanSize = preg_replace('/[hst]$/i', '', $size);
                    $final_price = $item['discounted_price'] * 0.7;
                } else {
                    $images = json_decode($item['image'], true);
                    $imageUrl = is_array($images) && !empty($images) ? $images[0] : $item['image'];
                }
            @endphp
            <div class="cart-recommendations__slide">
                @if($isCanvas)
                    <div class="cart-recommendations__item--canvas">
                        <div class="cart-recommendations__item-image-full">
                            <img src="{{ $imageUrl }}" alt="{{ $item['name'] }}">
                        </div>
                        <div class="cart-recommendations__item-content-canvas">
                            <div class="canvas-info-row canvas-info-row-1">
                                <span class="canvas-title">{{ $item['name'] }}</span>
                                <span class="canvas-old-price">{{ $item['original_price'] }}€</span>
                            </div>
                            <div class="canvas-info-row canvas-info-row-2">
                                <span class="canvas-size">{{ $cleanSize }}</span>
                                <span class="canvas-mid-price">{{ $item['discounted_price'] }}€</span>
                            </div>
                            <div class="canvas-info-row canvas-info-row-3">
                                <button
                                    class="canvas-discount-btn">
                                    -{{ $item['discount_percent'] }}%
                                </button>
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" class="canvas-arrow">
                                    <path d="M6 12L10 8L6 4" stroke="#fa7846" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span class="canvas-final-price">{{ $final_price }}€</span>
                            </div>
                            <div class="canvas-info-row canvas-info-row-4">
                            <button
                                class="cart-recommendations__add-btn recommendation-canvas-add"
                                data-canvas-size="{{ $cleanSize }}"
                                data-canvas-full-size="{{ $item['size'] }}"
                                data-canvas-price="{{ $final_price }}"
                                data-canvas-active-image="{{ $item['activeImage'] }}">
                                @lang('collage_new.z7_generator_foto_text3')
                            </button>
                            </div>

                        </div>
                    </div>
                @else
                    <div class="cart-recommendations__item">
                        <a class="cart-recommendations__item-link">
                            <div class="cart-recommendations__item-image">
                                @if(isset($item['image']))
                                    <img src="{{ asset('storage/'.$imageUrl) }}" alt="{{ $item['name'] }}">
                                @else
                                    <div class="no-image">@lang('cart_new.no_image')</div>
                                @endif
                                <div class="cart-recommendations__item-discount-badge">
                                    -{{ $item['discount_percent'] }}%
                                </div>
                            </div>
                            <div class="cart-recommendations__item-content">
                                <div class="cart-recommendations__item-name">
                                    {{ $item['name'] }}
                                </div>
                                <div class="cart-recommendations__item-pricing">
                                    <span class="cart-recommendations__item-old-price">
                                        {{ $item['original_price'] }}€
                                    </span>
                                    <span class="cart-recommendations__item-new-price">
                                        {{ $item['discounted_price'] }}€
                                    </span>
                                </div>
                            </div>
                        </a>
                        <a href="{{ route('hb.gallery.item.single', ['type' => $typeUrl, 'item' => $item['id']]) }}?recommendation_discount=30"
                           class="cart-recommendations__add-btn"
                           target="_blank">
                            @lang('cart_new.view_with_discount')
                        </a>
                    </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

<div id="canvas-upload-popup" class="canvas-upload-popup" style="display: none;">
    <div class="canvas-upload-popup__overlay"></div>
    <div class="canvas-upload-popup__content">
        <button class="canvas-upload-popup__close">&times;</button>
        <h3 class="canvas-upload-popup__title">@lang('gallery.module_b2_t4')</h3>
        <div class="canvas-upload-popup__file-area">
            <div class="file-save file-save__popup">
                <div class="abs-close" style="display: none;">X</div>
                <div class="file-save__item js-file-preview">
                    <svg width="50" height="50" viewBox="0 0 24 24" fill="none">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" stroke-width="2"/>
                        <polyline points="17 8 12 3 7 8" stroke="currentColor" stroke-width="2"/>
                        <line x1="12" y1="3" x2="12" y2="15" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <div class="file-save__title">
                        <p>@lang('homepage_new.load_photo')</p>
                        <span>@lang('homepage_new.pree_to_add_photo')</span>
                    </div>
                </div>
                <div class="file-save__item js-file-upload" style="display: none;">
                    <svg width="50" height="50" viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/>
                        <polyline points="21 15 16 10 5 21" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <div class="file-save__title">
                        <p class="file-name">photo.jpg</p>
                        <span class="file-size">2 Mb</span>
                    </div>
                </div>
                <input type="file" class="canvas-file-input" accept="image/*,image/heif,image/heic">
            </div>
        </div>

        <div class="canvas-upload-popup__info">
            <p><strong>@lang('cart.size'):</strong> <span id="canvas-popup-size"></span></p>
            <p><strong>@lang('cart_new.general_price'):</strong> <span id="canvas-popup-price"></span>€</p>
        </div>

        <button class="canvas-upload-popup__submit" id="canvas-popup-submit" disabled>
            @lang('cart_new.add_to_cart')
        </button>
    </div>
</div>

<style>
.cart-page-sidebar__recommendations {
    border-top: 1px solid #e0e0e0;
    padding-bottom: 20px;
}

.cart-recommendations {
    background-image: url('{{ asset(env("THEME").'images/bg/fon.png') }}');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    padding: 15px;
    border-radius: 8px;
    max-width: 400px;
    margin: 0 auto;
}

.cart-recommendations__header {
    margin-bottom: 15px;
}

.cart-recommendations__title {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.cart-recommendations__subtitle {
    font-size: 14px;
    color: #fa7846;
}

.cart-recommendations__slider {
    position: relative;
    padding: 0 5px;
    max-width: 100%;
}

.cart-recommendations__slider:not(.slick-initialized) .cart-recommendations__slide:not(:first-child) {
    display: none;
}

.cart-recommendations__slide {
    outline: none;
    width: 100% !important;
}

.cart-recommendations__slider .slick-list {
    overflow: hidden;
    margin: 0;
}

.cart-recommendations__slider .slick-track {
    display: flex !important;
}

.cart-recommendations__slider .slick-slide {
    float: none !important;
    padding: 0;
    box-sizing: border-box;
    width: 100% !important;
}

.cart-recommendations__slider .slick-slide > div {
    width: 100%;
}

.cart-recommendations__item {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    transition: box-shadow 0.3s ease;
    max-width: 100%;
    width: 100%;
}

.cart-recommendations__item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.cart-recommendations__item-link {
    display: flex;
    text-decoration: none;
    color: inherit;
    flex: 0 0 auto;
}

.cart-recommendations__item-image {
    position: relative;
    width: 120px;
    height: 120px;
    flex-shrink: 0;
    overflow: hidden;
}

.cart-recommendations__item-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.cart-recommendations__item-image .no-image {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    color: #999;
}

.cart-recommendations__item-discount-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    background: #fa7846;
    color: #fff;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.cart-recommendations__item-content {
    padding: 12px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    flex-grow: 1;
}

.cart-recommendations__item-name {
    font-size: 14px;
    font-weight: 500;
    color: #333;
    margin-bottom: 10px;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.cart-recommendations__item-pricing {
    display: flex;
    align-items: center;
    gap: 10px;
}

.cart-recommendations__item-old-price {
    font-size: 13px;
    color: #999;
    text-decoration: line-through;
}

.cart-recommendations__item-new-price {
    font-size: 17px;
    font-weight: 600;
    color: #fa7846;
}

.cart-recommendations__add-btn {
    margin: 12px;
    padding: 12px 16px;
    background: #fa7846;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 250px;
    text-decoration: none;
    display: block;
    text-align: center;
}

.cart-recommendations__add-btn:hover {
    background: #e66835;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(250, 120, 70, 0.3);
    color: #fff;
}

.cart-recommendations__slider .slick-prev,
.cart-recommendations__slider .slick-next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 10;
    color: #666;
}

.cart-recommendations__slider .slick-prev {
    left: -18px;
}

.cart-recommendations__slider .slick-next {
    right: -18px;
}

.cart-recommendations__slider .slick-prev:hover,
.cart-recommendations__slider .slick-next:hover {
    background: #fa7846;
    border-color: #fa7846;
    color: #fff;
}

.cart-recommendations__slider .slick-prev.slick-disabled,
.cart-recommendations__slider .slick-next.slick-disabled {
    opacity: 0.3;
    cursor: not-allowed;
    background: #f5f5f5;
}

.cart-recommendations__slider .slick-prev.slick-disabled:hover,
.cart-recommendations__slider .slick-next.slick-disabled:hover {
    background: #f5f5f5;
    border-color: #e0e0e0;
    color: #666;
}

.cart-recommendations__slider .slick-dots {
    display: flex !important;
    gap: 8px;
    align-items: center;
    justify-content: center;
    margin-top: 15px;
    padding: 0;
    list-style: none;
}

.cart-recommendations__slider .slick-dots li {
    width: 8px;
    height: 8px;
    margin: 0;
}

.cart-recommendations__slider .slick-dots li button {
    width: 8px;
    height: 8px;
    padding: 0;
    border: none;
    border-radius: 50%;
    background: #e0e0e0;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0;
    line-height: 0;
}

.cart-recommendations__slider .slick-dots li.slick-active button {
    background: #fa7846;
    width: 24px;
    border-radius: 4px;
}

.cart-recommendations__slider .slick-dots li button:hover {
    background: #fa7846;
    opacity: 0.7;
}

.cart-recommendations__item--canvas:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.cart-recommendations__item--canvas {
    border: none;
    border-radius: 8px;
    overflow: hidden;
    transition: box-shadow 0.3s ease;
    max-width: 100%;
    width: 100%;
    position: relative;
    display: flex;
    flex-direction: row;
    padding: 0;
    background: transparent;
    min-height: 200px;
}

.cart-recommendations__item-image-full {
    position: absolute;
    left: -6%;
    top: 52%;
    transform: translateY(-50%);
    width: 62%;
    height: auto;
    z-index: 3;
}

.cart-recommendations__item-image-full img {
    width: 100%;
    height: auto;
    object-fit: contain;
    display: block;
}

.cart-recommendations__item-content-canvas {
    position: relative;
    margin-left: 40%;
    flex: 1;
    padding: 12px 12px 12px 30px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 6px;
    z-index: 2;
    max-height: 144px;
}

.canvas-info-row {
    display: flex;
    align-items: center;
    gap: 5px;
}

.canvas-info-row-1 .canvas-title {
    font-size: 13px;
    font-weight: 600;
    color: #333;
}

.canvas-info-row-1 .canvas-old-price {
    font-size: 11px;
    color: #999;
    text-decoration: line-through;
}

.canvas-info-row-2 .canvas-size {
    font-size: 12px;
    color: #666;
}

.canvas-info-row-2 .canvas-mid-price {
    font-size: 13px;
    color: #fa7846;
    font-weight: 500;
}

.canvas-info-row-3 {
    align-items: center;
    gap: 6px;
}
.canvas-info-row-4 {
    align-items: center;
    gap: 6px;
}

.canvas-discount-btn {
    background: #fa7846;
    color: #fff;
    border: none;
    padding: 6px 12px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.canvas-discount-btn:hover {
    background: #e66835;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(250, 120, 70, 0.3);
}

.canvas-arrow {
    flex-shrink: 0;
    width: 14px;
    height: 14px;
}

.canvas-final-price {
    font-size: 16px;
    font-weight: 700;
    color: #fa7846;
}

.canvas-info-row-4 .cart-recommendations__add-btn {
    padding: 8px 12px;
    font-size: 12px;
    margin: 0;
    width: 100%;
}

.recommendation-canvas-add {
    padding: 8px 12px;
    width: auto;
    font-size: 12px;
}

@media (max-width: 768px) {
    .cart-recommendations {
        padding: 10px 5px;
        max-width: 100%;
    }

    .cart-recommendations__slider {
        padding: 0 30px;
    }

    .cart-recommendations__slider .slick-slide {
        width: calc(100vw - 80px) !important;
        max-width: 320px;
    }

    .cart-recommendations__slider .slick-list {
        margin: 0;
    }

    .cart-recommendations__item {
        max-width: 100%;
        display: flex;
        flex-direction: column;
        height: auto;
    }

    .cart-recommendations__item-link {
        flex-direction: column;
        flex: 0 0 auto;
    }

    .cart-recommendations__item-image {
        width: 100%;
        height: 90px;
        flex-shrink: 0;
    }

    .cart-recommendations__item-content {
        width: 100%;
        padding: 6px 8px;
        flex: 0 0 auto;
    }

    .cart-recommendations__item-name {
        font-size: 11px;
        margin-bottom: 4px;
        line-height: 1.2;
        -webkit-line-clamp: 2;
    }

    .cart-recommendations__item-pricing {
        margin-bottom: 3px;
    }

    .cart-recommendations__item-old-price {
        font-size: 10px;
    }

    .cart-recommendations__item-new-price {
        font-size: 13px;
    }

    .cart-recommendations__add-btn {
        width: calc(100% - 16px);
        margin: 4px 8px 6px;
        padding: 6px 8px;
        font-size: 11px;
        flex: 0 0 auto;
    }

    .cart-recommendations__slider .slick-prev,
    .cart-recommendations__slider .slick-next {
        width: 30px;
        height: 30px;
    }

    .cart-recommendations__slider .slick-prev {
        left: 2px;
    }

    .cart-recommendations__slider .slick-next {
        right: 2px;
    }

    .cart-recommendations__item--canvas {
        height: auto;
    }
}

.canvas-upload-popup {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 99999;
}

.canvas-upload-popup__overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
}

.canvas-upload-popup__content {
    top: 20%;
    position: relative;
    background: #fff;
    border-radius: 12px;
    max-width: 500px;
    width: 90%;
    margin: 50px auto;
    padding: 30px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
}

.canvas-upload-popup__close {
    position: absolute;
    top: 15px;
    right: 15px;
    background: transparent;
    border: none;
    font-size: 30px;
    line-height: 1;
    cursor: pointer;
    color: #999;
    transition: color 0.3s;
}

.canvas-upload-popup__close:hover {
    color: #333;
}

.canvas-upload-popup__title {
    font-size: 24px;
    font-weight: 600;
    color: #333;
    margin: 0 0 10px 0;
}

.canvas-upload-popup__file-area {
    margin-bottom: 25px;
}

.canvas-upload-popup .file-save {
    border: 2px dashed #e0e0e0;
    border-radius: 8px;
    padding: 30px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
}

.canvas-upload-popup .file-save:hover {
    border-color: #fa7846;
    background: #fff8f5;
}

.canvas-upload-popup .file-save__item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
}

.canvas-upload-popup .file-save__item svg {
    color: #fa7846;
}

.canvas-upload-popup .file-save__title p {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.canvas-upload-popup .file-save__title span {
    font-size: 13px;
    color: #999;
}

.canvas-upload-popup .canvas-file-input {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.canvas-upload-popup__info {
    background: #f5f5f5;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.canvas-upload-popup__info p {
    margin: 5px 0;
    font-size: 14px;
    color: #333;
}

.canvas-upload-popup__submit {
    width: 100%;
    padding: 15px;
    background: #fa7846;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.canvas-upload-popup__submit:hover:not(:disabled) {
    background: #e66835;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(250, 120, 70, 0.4);
}

.canvas-upload-popup__submit:disabled {
    background: #ccc;
    cursor: not-allowed;
    opacity: 0.6;
}

@media (max-width: 768px) {
    .canvas-upload-popup__content {
        margin: 20px auto;
        padding: 20px;
    }

    .canvas-upload-popup__title {
        font-size: 20px;
    }
}
</style>

<script>
(function() {
    var attempts = 0;
    var maxAttempts = 50;

    function initSlider() {
        attempts++;

        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.slick !== 'undefined') {
            var $slider = jQuery('.cart-recommendations__slider');

            if ($slider.length === 0) {
                return;
            }

            if ($slider.hasClass('slick-initialized')) {
                $slider.slick('unslick');
            }

            $slider.slick({
                infinite: false,
                slidesToShow: 1,
                slidesToScroll: 1,
                dots: false,
                arrows: true,
                prevArrow: '<button type="button" class="slick-prev"><svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 1L1 7L7 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>',
                nextArrow: '<button type="button" class="slick-next"><svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1L7 7L1 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>',
                adaptiveHeight: true,
                speed: 400,
                cssEase: 'ease-in-out'
            });
        } else {
            if (attempts < maxAttempts) {
                setTimeout(initSlider, 100);
            }
        }
    }

    function initCanvasRecommendation() {
        if (typeof jQuery !== 'undefined') {
            var selectedFile = null;
            var canvasData = {};

            jQuery(document).on('click', '.recommendation-canvas-add', function(e) {
                e.preventDefault();

                var $btn = jQuery(this);
                canvasData = {
                    size: $btn.data('canvas-size'),
                    full_size: $btn.data('canvas-full-size'),
                    price: $btn.data('canvas-price'),
                    activeImage: $btn.data('canvas-active-image')
                };

                jQuery('#canvas-popup-size').text(canvasData.size);
                jQuery('#canvas-popup-price').text(canvasData.price);

                jQuery('#canvas-upload-popup').fadeIn(300);
                jQuery('body').css('overflow', 'hidden');
            });

            jQuery(document).on('click', '.canvas-upload-popup__close, .canvas-upload-popup__overlay', function() {
                jQuery('#canvas-upload-popup').fadeOut(300);
                jQuery('body').css('overflow', '');
                selectedFile = null;
                jQuery('.canvas-file-input').val('');
                jQuery('.js-file-preview').show();
                jQuery('.js-file-upload').hide();
                jQuery('#canvas-popup-submit').prop('disabled', true);
            });

            jQuery(document).on('change', '.canvas-file-input', function(e) {
                var file = e.target.files[0];
                if (file) {
                    selectedFile = file;
                    var fileSize = (file.size / (1024 * 1024)).toFixed(2);

                    jQuery('.js-file-preview').hide();
                    jQuery('.js-file-upload').show();
                    jQuery('.js-file-upload .file-name').text(file.name);
                    jQuery('.js-file-upload .file-size').text(fileSize + ' MB');
                    jQuery('#canvas-popup-submit').prop('disabled', false);
                }
            });

            jQuery(document).on('click', '#canvas-popup-submit', function(e) {
                e.preventDefault();

                if (!selectedFile) {
                    alert('{{ __("cart_new.please_select_image") }}');
                    return;
                }

                var $btn = jQuery(this);
                $btn.prop('disabled', true).text('{{ __("cart_new.adding") }}...');

                var formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('userImage', selectedFile);
                formData.append('size', canvasData.size);
                formData.append('full_size', canvasData.full_size);
                formData.append('price', canvasData.price);
                formData.append('name', 'Canvas');

                jQuery.ajax({
                    url: '/{{ app()->getLocale() }}/basket/add-canvas-recommendation',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            window.location.reload();
                        } else {
                            alert(response.message || '{{ __("cart_new.error") }}');
                            $btn.prop('disabled', false).text('{{ __("cart_new.add_to_cart") }}');
                        }
                    },
                    error: function() {
                        alert('{{ __("cart_new.error") }}');
                        $btn.prop('disabled', false).text('{{ __("cart_new.add_to_cart") }}');
                    }
                });
            });
        } else {
            setTimeout(initCanvasRecommendation, 100);
        }
    }

    initSlider();
    initCanvasRecommendation();
})();
</script>

@endif
