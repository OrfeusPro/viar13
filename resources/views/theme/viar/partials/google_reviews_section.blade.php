@php
    $cfg = (array) config('services.google', []);
    $section = (array) ($cfg['reviews_section'] ?? []);
    $enabled = (bool) ($section['enabled'] ?? false);
@endphp

@if($enabled)
    <section
        class="grs-wrap"
        id="google-reviews-section"
        data-endpoint="{{ url('/api/google-reviews') }}"
        data-i18n-more="{{ e(__('google_reviews.see_more')) }}"
        data-i18n-hide="{{ e(__('google_reviews.hide')) }}"
        data-i18n-reviews-label="{{ e(__('google_reviews.reviews')) }}"
    >
        <div class="grs-head">
            <div>
                <h2 class="grs-title" id="grs-title">Google Reviews</h2>
                <div class="grs-meta">
                    <div class="grs-rating" id="grs-rating">-</div>
                    <div class="grs-stars" aria-hidden="true"><span id="grs-stars-fill"></span></div>
                    <div class="grs-count" id="grs-count">- {{ __('google_reviews.reviews') }}</div>
                    <div class="grs-google" aria-hidden="true">
                        <svg viewBox="0 0 48 48" focusable="false">
                            <path fill="#EA4335" d="M24 9.5c3.54 0 6.72 1.22 9.24 3.23l6.9-6.9C36.06 2.39 30.39 0 24 0 14.61 0 6.5 5.38 2.56 13.22l8.04 6.24C12.33 13.53 17.73 9.5 24 9.5z"/>
                            <path fill="#4285F4" d="M46.5 24c0-1.6-.14-3.14-.4-4.64H24v9.02h12.65c-.55 2.9-2.2 5.36-4.68 7.02l7.19 5.58C43.73 36.74 46.5 30.93 46.5 24z"/>
                            <path fill="#FBBC05" d="M10.6 28.46c-.5-1.49-.78-3.08-.78-4.74s.28-3.25.78-4.74l-8.04-6.24C.92 16.2 0 19.98 0 23.72s.92 7.52 2.56 10.98l8.04-6.24z"/>
                            <path fill="#34A853" d="M24 48c6.39 0 11.76-2.11 15.68-5.72l-7.19-5.58c-2.0 1.34-4.56 2.13-8.49 2.13-6.27 0-11.67-4.03-13.4-9.96l-8.04 6.24C6.5 42.62 14.61 48 24 48z"/>
                        </svg>
                        Google
                    </div>
                </div>
            </div>

            <a class="grs-btn" id="grs-leave-review" href="#" target="_blank" rel="noopener noreferrer">{{ __('google_reviews.leave_review') }}</a>
        </div>

        <div class="grs-carousel">
            <button type="button" class="grs-nav grs-prev" aria-label="Previous reviews">
                <svg viewBox="0 0 24 24"><path d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
            </button>
            <div class="grs-track" id="grs-track" aria-label="Reviews"></div>
            <button type="button" class="grs-nav grs-next" aria-label="Next reviews">
                <svg viewBox="0 0 24 24"><path d="m8.59 16.59 1.41 1.41 6-6-6-6-1.41 1.41L13.17 12z"/></svg>
            </button>
        </div>
    </section>
@endif
