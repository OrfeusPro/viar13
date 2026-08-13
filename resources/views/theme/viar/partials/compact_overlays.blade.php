@php
    $isCompactOverlayPage = in_array(Route::currentRouteName(), ['home', 'canvas', 'ads.canvas'], true);
@endphp

@if($isCompactOverlayPage)
<style>
    @if(!filter_var(setting('site.top_sale', false), FILTER_VALIDATE_BOOLEAN))
    .vz-art.vz-header {
        top: 0 !important;
    }
    @endif

    .vz-home .portraits-arrow,
    .vz-canvas .portraits-arrow {
        display: none !important;
    }

    .vz-home .portraits-dots,
    .vz-canvas .portraits-dots {
        z-index: 8;
        pointer-events: auto;
    }

    .vz-home .offer,
    .vz-canvas .offer {
        left: 20px;
        right: auto;
        bottom: 14px;
        z-index: 19;
    }

    .vz-home .hb_popup_coupon,
    .vz-canvas .hb_popup_coupon {
        max-width: 270px;
        min-height: 0;
        padding: 12px 14px;
        border-radius: 8px;
        box-shadow: 0 8px 22px rgba(32, 32, 32, .14);
    }

    .vz-home .hb_popup_coupon > a,
    .vz-canvas .hb_popup_coupon > a {
        right: 10px;
        top: 8px;
    }

    .vz-home .hb_popup_coupon_t1,
    .vz-home .hb_popup_coupon_t2,
    .vz-home .hb_popup_coupon_t3,
    .vz-home .hb_popup_coupon_t4,
    .vz-home .hb_popup_coupon_t5,
    .vz-canvas .hb_popup_coupon_t1,
    .vz-canvas .hb_popup_coupon_t2,
    .vz-canvas .hb_popup_coupon_t3,
    .vz-canvas .hb_popup_coupon_t4,
    .vz-canvas .hb_popup_coupon_t5 {
        margin: 0 0 4px;
        font-size: 12px;
        line-height: 1.25;
    }

    .vz-home .hb_popup_coupon_t2,
    .vz-canvas .hb_popup_coupon_t2 {
        font-size: 13px;
    }

    .vz-home .offer-hidden .popup-offer,
    .vz-canvas .offer-hidden .popup-offer {
        width: 44px;
        height: 44px;
        padding: 0;
        overflow: hidden;
        border-radius: 8px;
        transform: none;
    }

    .vz-home .offer-hidden .popup-offer::before,
    .vz-canvas .offer-hidden .popup-offer::before {
        left: 0;
        top: 0;
        width: 44px;
        height: 44px;
        border-radius: 8px;
        transform: none;
    }

    .vz-home .offer-hidden .hb_popup_coupon > *,
    .vz-canvas .offer-hidden .hb_popup_coupon > * {
        display: none;
    }

    .vz-home .cky-btn-revisit-wrapper,
    .vz-home .cky-revisit-bottom-left,
    .vz-home .cky-revisit-bottom-right,
    .vz-home .cc-revoke,
    .vz-home .cc-revoke.cc-bottom,
    .vz-home #cookie-law-info-again,
    .vz-home [class*="revisit"][class*="cookie"],
    .vz-home [id*="revisit"][id*="cookie"],
    .vz-canvas .cky-btn-revisit-wrapper,
    .vz-canvas .cky-revisit-bottom-left,
    .vz-canvas .cky-revisit-bottom-right,
    .vz-canvas .cc-revoke,
    .vz-canvas .cc-revoke.cc-bottom,
    .vz-canvas #cookie-law-info-again,
    .vz-canvas [class*="revisit"][class*="cookie"],
    .vz-canvas [id*="revisit"][id*="cookie"] {
        display: none !important;
    }

    .vz-home .cky-consent-container,
    .vz-home .cc-window,
    .vz-home .cookieconsent,
    .vz-home #cookieNotice,
    .vz-home #cookie-law-info-bar,
    .vz-canvas .cky-consent-container,
    .vz-canvas .cc-window,
    .vz-canvas .cookieconsent,
    .vz-canvas #cookieNotice,
    .vz-canvas #cookie-law-info-bar {
        left: 12px !important;
        right: auto !important;
        bottom: 12px !important;
        top: auto !important;
        max-width: 320px !important;
        width: calc(100vw - 24px) !important;
        border-radius: 8px !important;
        box-shadow: 0 8px 22px rgba(32, 32, 32, .14) !important;
    }

    .vz-home .cky-consent-container *,
    .vz-home .cc-window *,
    .vz-home .cookieconsent *,
    .vz-home #cookieNotice *,
    .vz-home #cookie-law-info-bar *,
    .vz-canvas .cky-consent-container *,
    .vz-canvas .cc-window *,
    .vz-canvas .cookieconsent *,
    .vz-canvas #cookieNotice *,
    .vz-canvas #cookie-law-info-bar * {
        font-size: 12px !important;
        line-height: 1.25 !important;
    }

    .vz-home .cky-title,
    .vz-home .cc-header,
    .vz-home .cookie-title,
    .vz-canvas .cky-title,
    .vz-canvas .cc-header,
    .vz-canvas .cookie-title {
        font-size: 13px !important;
        margin-bottom: 6px !important;
    }

    .vz-home .cky-notice-btn-wrapper,
    .vz-home .cc-compliance,
    .vz-canvas .cky-notice-btn-wrapper,
    .vz-canvas .cc-compliance {
        gap: 6px !important;
    }

    .vz-home .cky-btn,
    .vz-home .cc-btn,
    .vz-canvas .cky-btn,
    .vz-canvas .cc-btn {
        min-height: 32px !important;
        padding: 6px 10px !important;
        border-radius: 4px !important;
    }

    @media (max-width: 700px) {
        .vz-home .offer,
        .vz-canvas .offer {
            left: 12px;
            bottom: 10px;
        }

        .vz-home .hb_popup_coupon,
        .vz-canvas .hb_popup_coupon {
            max-width: min(270px, calc(100vw - 92px));
        }

        .vz-home .cky-consent-container,
        .vz-home .cc-window,
        .vz-home .cookieconsent,
        .vz-home #cookieNotice,
        .vz-home #cookie-law-info-bar,
        .vz-canvas .cky-consent-container,
        .vz-canvas .cc-window,
        .vz-canvas .cookieconsent,
        .vz-canvas #cookieNotice,
        .vz-canvas #cookie-law-info-bar {
            left: 10px !important;
            bottom: 10px !important;
            max-width: 300px !important;
            width: calc(100vw - 20px) !important;
        }
    }
</style>
<script>
    window.ViarCompactOverlays = true;
</script>
@endif
