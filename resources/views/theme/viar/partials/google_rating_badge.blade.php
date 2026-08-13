@php
    $cfg = (array) config('services.google', []);
    $widget = (array) ($cfg['reviews_widget'] ?? []);

    $enabled = (bool) ($widget['enabled'] ?? false);
    $rating = isset($widget['rating']) ? (float) $widget['rating'] : null;
    $total = isset($widget['total']) ? (int) $widget['total'] : null;
    $url = $widget['url'] ?? ($cfg['maps_url'] ?? null);

    // If there is no URL, widget is pointless (can't send users to Google).
    if (!$url) {
        $enabled = false;
    }

    $fmtRating = is_numeric($rating) ? number_format($rating, 1, '.', '') : null;
    $starsWidth = is_numeric($rating) ? max(0, min(100, ($rating / 5) * 100)) : 0;
@endphp

@if($enabled)
    <div
        id="google-rating-badge"
        data-endpoint="{{ url('/api/google-rating') }}"
        data-default-url="{{ e($url) }}"
    >
        <a id="google-rating-badge-link" href="{{ e($url) }}" target="_blank" rel="noopener noreferrer">
            <div class="grb-left">
                <svg class="grb-g" viewBox="0 0 48 48" aria-hidden="true" focusable="false">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.72 1.22 9.24 3.23l6.9-6.9C36.06 2.39 30.39 0 24 0 14.61 0 6.5 5.38 2.56 13.22l8.04 6.24C12.33 13.53 17.73 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.5 24c0-1.6-.14-3.14-.4-4.64H24v9.02h12.65c-.55 2.9-2.2 5.36-4.68 7.02l7.19 5.58C43.73 36.74 46.5 30.93 46.5 24z"/>
                    <path fill="#FBBC05" d="M10.6 28.46c-.5-1.49-.78-3.08-.78-4.74s.28-3.25.78-4.74l-8.04-6.24C.92 16.2 0 19.98 0 23.72s.92 7.52 2.56 10.98l8.04-6.24z"/>
                    <path fill="#34A853" d="M24 48c6.39 0 11.76-2.11 15.68-5.72l-7.19-5.58c-2.0 1.34-4.56 2.13-8.49 2.13-6.27 0-11.67-4.03-13.4-9.96l-8.04 6.24C6.5 42.62 14.61 48 24 48z"/>
                </svg>
                <div class="grb-rating" id="grb-rating">{{ $fmtRating ?? '—' }}</div>
                <div class="grb-stack">
                    <div class="grb-stars" aria-hidden="true"><span id="grb-stars-fill" style="width: {{ number_format($starsWidth, 1, '.', '') }}%"></span></div>
                    <div class="grb-total" id="grb-total">
                        {{ is_numeric($total) ? number_format($total, 0, '.', ' ') : '—' }} Reviews
                    </div>
                </div>
            </div>
        </a>
    </div>
@endif
