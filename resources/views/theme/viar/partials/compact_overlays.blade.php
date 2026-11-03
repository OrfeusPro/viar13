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
