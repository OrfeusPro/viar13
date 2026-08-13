    <div class="vz-art js-popup target-box popup-cart">
        <div class="popup-cart--wrapper">
            <i class="vz-art fa-close popup-close"></i>
            <div class="vz-art page-title popup-photo-title h2_old">{!! trans('canvas.modal_add_title') !!}</div>
            <div class="popup-cart--inner">
                <div class="cart-image">
                    {{-- <div class="cart-image--text">
                        <img loading="lazy" src="{{ asset('images/canvas/love.svg') }}" alt="">
                        <span>{{ trans('canvas.modal_add_photo') }}</span>
                    </div> --}}
                    <picture>
                        <source media="(max-width: 550px)" srcset="{{ asset('images/canvas/canvas-popup.png') }}">
                        <source media="(max-width: 550px)" srcset="{{ asset('images/canvas/canvas-popup.webp') }}"  type="image/webp">
                        <source srcset="{{ asset('images/canvas/canvas-popup.webp') }}"  type="image/webp">
                        <source srcset="{{ asset('images/canvas/canvas-popup.png') }}">
                        <img loading="lazy" src="{{ asset('images/canvas/canvas-popup.png') }}" alt="">
                    </picture>
                </div>
                <div class="portraits-btn">
                    <a href="#" class="portraits-btn__style" tabindex="0">{{ trans('canvas.modal_btn1') }}</a>
                    <a href="{{ route('cart.index') }}" rel="nofollow" class="go_to_cart" tabindex="0">{{ trans('canvas.modal_btn2') }}</a>
                </div>
            </div>
        </div>
    </div>
