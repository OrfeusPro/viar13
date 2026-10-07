@if($offer)
<div class="checkout-size-offer">
    <div class="checkout-size-offer__details">
        <div class="checkout-size-offer__heading"><strong>@lang('checkout_coupon.size_upgrade_title')</strong>
            <span class="checkout-size-offer__discount">−15%</span></div>
        <div class="checkout-size-offer__comparison">
            <div><span>@lang('checkout_coupon.size_current')</span><b>{{ $offer['current_size'] }} @lang('checkout_coupon.size_cm')</b><span>{{ number_format($offer['current_price'], 2, '.', '') }} €</span></div>
            <span class="checkout-size-offer__arrow" aria-hidden="true">→</span>
            <div><span>@lang('checkout_coupon.size_proposed')</span><b>{{ preg_replace('/[htsr]$/i', '', $offer['alternative_size']) }} @lang('checkout_coupon.size_cm')</b><span><s>{{ number_format($offer['before_discount_price'], 2, '.', '') }} €</s> <strong>{{ number_format($offer['alternative_price'], 2, '.', '') }} €</strong></span></div>
        </div>
        <p>@lang('checkout_coupon.size_preserve_photo')</p>
        <p>@lang('checkout_coupon.size_discount_terms')</p>
    </div>
    <div class="checkout-size-offer__action">
    <strong>@lang('checkout_coupon.size_extra', ['extra' => number_format($offer['extra'], 2, '.', '')])</strong>
    <button type="button" class="btn--orange js-checkout-size-offer" data-url="{{ route('cart.replace.size') }}"
        data-basket-key="{{ $basketKey }}" data-size="{{ $offer['alternative_size'] }}">@lang('checkout_coupon.size_replace', ['size' => preg_replace('/[htsr]$/i', '', $offer['alternative_size'])])</button>
    </div>
    <p class="checkout-size-offer__error js-checkout-size-error" role="alert"></p>
</div>
@endif
