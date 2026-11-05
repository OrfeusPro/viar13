<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $meta_desc }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="shortcut icon" href="{{ asset('images/fav.png') }}" type="image/x-icon">
    <style>
        * { box-sizing: border-box; }
        html { background: #f6f6f4; color: #292929; font-family: Arial, Helvetica, sans-serif; }
        body { margin: 0; }
        a { color: #c95027; }
        .seo-shell { width: min(1040px, calc(100% - 32px)); margin: 0 auto; padding: 32px 0 64px; }
        .seo-nav,
        .seo-content {
            background: #fff;
            border: 1px solid #e8e4df;
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(38, 38, 38, .06);
        }
        .seo-nav { padding: 22px 28px; margin-bottom: 16px; }
        .seo-brand { color: #232323; font-size: 21px; font-weight: 700; text-decoration: none; }
        .seo-nav__links { display: flex; flex-wrap: wrap; align-items: center; gap: 10px 20px; }
        .seo-nav__links { margin-top: 20px; padding-top: 18px; border-top: 1px solid #ece8e4; }
        .seo-nav__links a { color: #494949; font-size: 14px; font-weight: 600; text-decoration: none; }
        .seo-nav__links a:hover { color: #c95027; }
        .seo-content { padding: 52px clamp(24px, 6vw, 72px); }
        .seo-content h1 { margin: 0 0 24px; color: #202020; font-size: clamp(32px, 5vw, 48px); line-height: 1.16; }
        .seo-content h2 { margin: 38px 0 12px; color: #292929; font-size: 24px; line-height: 1.35; }
        .seo-content p,
        .seo-content li,
        .seo-content dd { color: #4d4d4d; font-size: 16px; line-height: 1.75; }
        .seo-content .lead { color: #414141; font-size: 19px; }
        .seo-content .updated { margin-top: -14px; color: #777; font-size: 14px; }
        .seo-content ul,
        .seo-content ol { padding-left: 24px; }
        .seo-content ul { list-style: disc; }
        .seo-content ol { list-style: decimal; }
        .seo-analytics-page__data-summary { margin: 0; }
        .seo-analytics-page__data-summary dt { margin-top: 16px; color: #292929; font-weight: 700; }
        .seo-analytics-page__data-summary dd { margin: 4px 0 0; }
        @media (max-width: 700px) {
            .seo-shell { width: min(100% - 20px, 1040px); padding: 10px 0 36px; }
            .seo-nav { padding: 20px; }
            .seo-content { padding: 34px 22px; }
        }
    </style>
</head>
<body>
    <div class="seo-shell">
        <header class="seo-nav">
            <a class="seo-brand" href="{{ route('seo-analytics.home') }}">ViarCanvas SEO Analytics</a>
            <nav class="seo-nav__links" aria-label="{{ trans('seo_analytics.navigation.label') }}">
                <a href="{{ route('seo-analytics.home') }}">{{ trans('seo_analytics.navigation.home') }}</a>
                <a href="{{ route('seo-analytics.privacy') }}">{{ trans('seo_analytics.navigation.privacy') }}</a>
                <a href="{{ route('seo-analytics.terms') }}">{{ trans('seo_analytics.navigation.terms') }}</a>
            </nav>
        </header>

        <main class="seo-content">
            @yield('seo-content')
        </main>
    </div>
</body>
</html>
