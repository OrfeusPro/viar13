<!DOCTYPE html>
@if (Config::get('app.locale') == 'ee')
    <html lang="et">
@else
    <html lang="{{ Config::get('app.locale') }}">
@endif

<head>
    <meta charset="UTF-8">

    @php
        $strip_tags_with_spaces = function ($value) {
            $value = preg_replace('/<[^>]*>/', ' ', (string) $value);
            $value = preg_replace('/\s+/u', ' ', $value);
            return trim($value);
        };
        $plain_title = isset($title) ? $strip_tags_with_spaces($title) : '';
        $plain_meta_desc = isset($meta_desc) ? $strip_tags_with_spaces($meta_desc) : '';
    @endphp

	<title>{{ $plain_title }}</title>
    <meta name="description" content="{{ $plain_meta_desc }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:url" content="{{ $og_url ?? url()->current() }}" />
	@if(Route::currentRouteName() == 'ads.canvas' ||
        Route::currentRouteName() == 'cart.index' ||
        Route::currentRouteName() == 'cart.step2' ||
        Route::currentRouteName() == 'cart.step3' ||
        Route::currentRouteName() == 'cart.step4'
    )
        <meta name="robots" content="noindex, nofollow" />
    @elseif(
        (Route::currentRouteName() == 'hb.gallery.module' && request()->hasAny(['search', 'color', 'size', 'order'])) ||
        (Route::currentRouteName() == 'hb.gallery.category' && request()->hasAny(['search', 'color', 'size', 'order'])) ||
        Route::currentRouteName() == 'hb.gallery.painters_letter'
    )
        <meta name="robots" content="noindex" />
    @endif

    @php
        $LL = \Mcamara\LaravelLocalization\Facades\LaravelLocalization::class;

        $supported     = $LL::getSupportedLocales();
        $currentLocale = app()->getLocale();
        $defaultLocale = $LL::getDefaultLocale(); // у тебя это ru
        $localizedUrls = isset($localized_urls) && is_array($localized_urls) ? $localized_urls : [];
        $normalize = function ($code) { return $code === 'ee' ? 'et' : ($code === 'ua' ? 'uk' : $code); };

        $alt = function (string $locale) use ($LL, $defaultLocale, $localizedUrls) {
            if (!empty($localizedUrls[$locale])) {
                return strtok($localizedUrls[$locale], '?');
            }

            $u = $LL::getLocalizedURL($locale, null, [], false);
            if ($locale === $defaultLocale) {
                $u = $LL::getNonLocalizedURL($u);
            }
            return strtok($u, '?');
        };
    @endphp

    @foreach ($supported as $localeCode => $props)
        <link rel="alternate"
            hreflang="{{ $normalize($localeCode) }}"
            href="{{ $alt($localeCode) }}" />
    @endforeach

    {{-- x-default на дефолтный без префикса --}}
    <link rel="alternate" hreflang="x-default" href="{{ $alt($defaultLocale) }}" />

    {{-- canonical: тоже без /ru для дефолтной локали --}}
    <link rel="canonical" href="{{ $canonical_url ?? $alt($currentLocale) }}" />


    {{-- og --}}
    <meta property="og:title" content="{{ $plain_title }}" />
    <meta property="og:description" content="{{ $plain_meta_desc }}" />
    <meta property="og:type" content="{{ $og_type ?? 'website' }}" />
    <meta property="og:image" content="{{ $og_image ?? (\URL::to('/') . '/img/logo.png') }}" />
    @if(!empty($og_image_width))
    <meta property="og:image:width" content="{{ $og_image_width }}" />
    @endif
    @if(!empty($og_image_height))
    <meta property="og:image:height" content="{{ $og_image_height }}" />
    @endif
    <meta property="og:site_name" content="@lang('settings.site_name')" />
    <link rel="image_src" href="{{ $image_src ?? ($og_image ?? (\URL::to('/') . '/img/logo.png')) }}" />
    {{-- end og --}}
    @if(Route::currentRouteName() == 'home')
    <script type="application/ld+json">
        {
        "@@context": "https://schema.org",
        "@type": "WebSite",
        "name": "@lang('settings.site_name')",
        "alternateName": @lang('settings.site_alternateName'),
        "url": "@lang('settings.site_url')"
        }
    </script>
    @endif

    @if(!empty($structured_data) && is_array($structured_data))
        @foreach($structured_data as $structured_item)
            <script type="application/ld+json">
                {!! json_encode($structured_item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
            </script>
        @endforeach
    @else
        <script type="application/ld+json">
            {
                "@@context": "https://schema.org",
                "@type": "WebPage",
                "name": "{{ $plain_title }}",
                "description": "{{ $plain_meta_desc }}",
                "image": "{{ \URL::to('/') }}/img/logo.png"
            }
        </script>
    @endif

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="verify-paysera" content="a25580ff84c541195306e67d151dbb58">
    <link rel="shortcut icon" href="{{ asset('images/fav.png') }}" type="image/x-icon">


    @if(Route::currentRouteName() == 'home')
        @include((config('theme.resource') ?: 'theme.viar.') .'pages.index.head_styles')
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

	<link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/main.min.css') }}" media="all" />
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/custom.css') }}">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/cart.min.css') }}" /> <!-- main menu app.blade new main 8-->
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/media.css') }}" media="all" />


    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/overall/overall.css') }}" media="all" />

    <!-- Preload Fonts -->
    <link rel="preload" href="{{ ver_asset(env('THEME').'fonts/Trajan-Pro-3-SemiBold.woff2') }}" as="font" type="font/woff2" crossorigin="anonymous">
    <link rel="preload" href="{{ ver_asset(env('THEME').'fonts/Trajan-Pro-3.woff2') }}" as="font" type="font/woff2" crossorigin="anonymous">
    <link rel="preload" href="{{ ver_asset(env('THEME').'fonts/icon-font.ttf') }}" as="font" type="font/ttf" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/add.css') }}" media="all">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/forms.css')}}" media="all">
    <!-- Scripts -->




    {{-- @if (Route::currentRouteName() == 'hb.gallery.index')
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/gallery/gallery.css') }}" onload="this.media='all'" media="all">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/gallery/gallery-media.css') }}" onload="this.media='all'" media="all">
    @endif
    @if (Route::currentRouteName() == 'hb.gallery.module')
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/mcard/mcard.css') }}" onload="this.media='all'" media="all">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/mcard/mcard-media.css') }}" onload="this.media='all'" media="all">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/module/module.css') }}" onload="this.media='all'" media="all">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/module/module-media.css') }}" onload="this.media='all'" media="all">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/gallery/gallery.css') }}" onload="this.media='all'" media="all">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/gallery/gallery-media.css') }}" onload="this.media='all'" media="all">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/reproduction/reproduction.css') }}" onload="this.media='all'" media="all">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/reproduction/reproduction-media.css') }}" onload="this.media='all'" media="all">
    @endif --}}

    @if (Route::currentRouteName() == 'hb.gallery.index'
        || Route::currentRouteName() == 'hb.gallery.module'
        || Route::currentRouteName() == 'hb.gallery.category'
        || Route::currentRouteName() == 'hb.gallery.item.single'
        || Route::currentRouteName() == 'hb.gallery.item'
		|| Route::currentRouteName() == 'about'
		|| Route::currentRouteName() == 'new_stocks'
        || Route::currentRouteName() == 'contacts'
        || Route::currentRouteName() == 'hb.gallery.age'
        || Route::currentRouteName() == 'hb.gallery.genre'
        || Route::currentRouteName() == 'hb.gallery.list_age'
        || Route::currentRouteName() == 'hb.gallery.list_genre'
        || Route::currentRouteName() == 'hb.gallery.modern_handmade_paintings'
        || Route::currentRouteName() == 'hb.gallery.modern_painters'
        || Route::currentRouteName() == 'hb.gallery.nationality'
        || Route::currentRouteName() == 'hb.gallery.list_nationality'
        || Route::currentRouteName() == 'hb.gallery.paintings_top'
        || Route::currentRouteName() == 'hb.gallery.style'
        || Route::currentRouteName() == 'hb.gallery.painters'
        || Route::currentRouteName() == 'hb.gallery.painters_letter'
        || Route::currentRouteName() == 'hb.gallery.list_styles'

        )
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/mcard/mcard.css') }}" onload="this.media='all'" media="all">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/mcard/mcard-media.css') }}" onload="this.media='all'" media="all">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/module/module.css') }}" onload="this.media='all'" media="all">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/module/module-media.css') }}" onload="this.media='all'" media="all">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/gallery/gallery.css') }}" onload="this.media='all'" media="all">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/gallery/gallery-media.css') }}" onload="this.media='all'" media="all">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/reproduction/reproduction.css') }}" onload="this.media='all'" media="all">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/reproduction/reproduction-media.css') }}" onload="this.media='all'" media="all">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css"/>
    @endif


    @if(Route::currentRouteName() == 'about' || Route::currentRouteName() == 'contacts')
    	<link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/contacts/contacts.css') }}" media="all" />
    	<link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/contacts/contacts-media.css') }}" media="all" />
    @endif

    @if(Route::currentRouteName() == 'new_stocks' || Route::currentRouteName() == 'new_account.mystocks')
    	<link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/stock/stock.css') }}" media="all" />
    	<link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/stock/stock-media.css') }}" media="all" />
    @endif

    @if(strpos(Route::currentRouteName(), 'new_account') !== false)
    	<link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/cabinet/cabinet.css') }}" media="all" />
    	<link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/cabinet/cabinet-media.css') }}" media="all" />
    @endif

    @if(Route::currentRouteName() == 'faq' || Route::currentRouteName() == 'blog_inner')
    	<link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/question-all/question-all.css') }}"/>
    	<link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/question-all/question-all-media.css') }}"/>
    @endif



	@if(Route::currentRouteName() == 'graphic_portrait.new_page'
    || Route::currentRouteName() == 'canvas'
    || Route::currentRouteName() == 'ads.canvas'
    || Route::currentRouteName() == 'collage')
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/mcard/mcard.css') }}" onload="this.media='all'" media="all">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/mcard/mcard-media.css') }}" onload="this.media='all'" media="all">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/before-after.min.css') }}">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/portrait/portrait.css') }}">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/portrait/portrait-media.css') }}">
    @endif

    @if(Route::currentRouteName() == 'graphic_portrait.new_page--royal' || Route::currentRouteName() == 'graphic_portrait.oil')
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/before-after.min.css') }}">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/royal/portrait.css') }}"/>
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/royal/portrait-media.css') }}">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/royal/royal.css') }}"/>

        @if(Route::currentRouteName() == 'graphic_portrait.oil')
            <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/oil/oil.css') }}"/>
        @endif

        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/question-all/question-all.css') }}"/>
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/question-all/question-all-media.css') }}"/>

        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/mcard/mcard.css') }}" onload="this.media='all'" media="all">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/mcard/mcard-media.css') }}" onload="this.media='all'" media="all">
    @endif


	@if(Route::currentRouteName() == 'graphic_portrait.new_page'

    || Route::currentRouteName() == 'graphic_portrait.new_page--royal'
    || Route::currentRouteName() == 'graphic_portrait.oil'
    || Route::currentRouteName() == 'canvas'
    || Route::currentRouteName() == 'ads.canvas'
    )
        <link rel="preload" href="{{ ver_asset(env('THEME').'style/fontello.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    @endif


	@if(Route::currentRouteName() == 'canvas' || Route::currentRouteName() == 'ads.canvas')
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/mod.css') }}">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/combine_canvas.css') }}">
    @endif

	@if(Route::currentRouteName() == 'collage' )
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/collage.css') }}" media="all">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/collage-media.css') }}" media="all">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/collage_combine.css') }}" media="all">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/col-mod.css') }}" media="all">
        <link href="//cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" media="all" />
    @endif

	@if(Route::currentRouteName() == 'blog'
    || Route::currentRouteName() == 'blog_category'
    || Route::currentRouteName() == 'blog_inner'
    || Route::currentRouteName() == "blog_category_all")
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/blog.css') }}">
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/blog-media.css') }}">
    @endif



    {!! $trackers ?? '' !!}
{{--    @php dd($trackers);  @endphp--}}
    @include((config('theme.resource') ?: 'theme.viar.') . 'partials.compact_overlays')
	@if(Route::currentRouteName() == 'cart.index'
    || Route::currentRouteName() == 'cart.step2'
    || Route::currentRouteName() == 'cart.step3'
    || Route::currentRouteName() == 'cart.step4'
    || Route::currentRouteName() == 'page.delivery')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/6.8.4/swiper-bundle.min.css" integrity="sha512-aMup4I6BUl0dG4IBb0/f32270a5XP7H1xplAJ80uVKP6ejYCgZWcBudljdsointfHxn5o302Jbnq1FXsBaMuoQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datepicker/1.0.10/datepicker.min.css" integrity="sha512-YdYyWQf8AS4WSB0WWdc3FbQ3Ypdm0QCWD2k4hgfqbQbRCJBEgX0iAegkl2S1Evma5ImaVXLBeUkIlP6hQ1eYKQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/simplebar/5.3.0/simplebar.min.css" integrity="sha512-uZTwaYYhJLFXaXYm1jdNiH6JZ1wLCTVnarJza7iZ1OKQmvi6prtk85NMvicoSobylP5K4FCdGEc4vk1AYT8b9Q==" crossorigin="anonymous" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.theme.min.css" integrity="sha512-9h7XRlUeUwcHUf9bNiWSTO9ovOWFELxTlViP801e5BbwNJ5ir9ua6L20tEroWZdm+HFBAWBLx2qH4l4QHHlRyg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.16/css/intlTelInput.css" integrity="sha512-gxWow8Mo6q6pLa1XH/CcH8JyiSDEtiwJV78E+D+QP0EVasFs8wKXq16G8CLD4CJ2SnonHr4Lm/yY2fSI2+cbmw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @endif

    <script src="https://www.google.com/recaptcha/api.js?hl={{ app()->getLocale() }}" async defer></script>

    @if(Route::currentRouteName() == 'canvas' || Route::currentRouteName() == 'ads.canvas')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script defer src="{{ ver_asset(env('THEME').'js/cart.min.js') }}"></script> <!-- main menu 9 -->
    <script defer src="{{ ver_asset(env('THEME').'js/custom.js') }}" ></script>
    <script defer src="{{ ver_asset(env('THEME').'js/add.js') }}"></script>
    <script defer src="{{ ver_asset(env('THEME').'js/script_canvas.js') }}" defer=""></script>
    <script defer src="{{ ver_asset(env('THEME').'js/InteriorGenerator.js') }}"></script>
    <script defer src="{{ ver_asset(env('THEME').'js/main.js') }}"></script>
    <script defer src="{{ ver_asset(env('THEME').'js/three.min.js') }}"></script>
    <script defer src="{{ ver_asset(env('THEME').'js/3dcanvas.js') }}"></script>

    <script defer src="{{ ver_asset(env('THEME').'js/jcf.min.js') }}"></script>
    <script defer src="{{ ver_asset(env('THEME').'js/jcf.radio.min.js') }}"></script>
    <script defer src="{{ ver_asset(env('THEME').'js/jcf.checkbox.min.js') }}"></script>
    <script defer src="{{ ver_asset(env('THEME').'js/jcf.range.min.js') }}"></script>
    <script defer src="{{ ver_asset(env('THEME').'js/jquery.collapse_storage.min.js') }}"></script>
    <script defer src="{{ ver_asset(env('THEME').'js/jquery.collapse.min.js') }}"></script>

    <script defer src="{{ ver_asset(env('THEME').'js/slick.min.js') }}"></script>
    <script defer src="{{ ver_asset(env('THEME').'js/templates.js') }}"></script>
    <script defer src="{{ ver_asset(env('THEME').'js/canvas.js') }}"></script>
    <script defer src="{{ ver_asset(env('THEME').'js/jquery.matchHeight.min.js') }}"></script>

    <script defer src="{{ ver_asset(env('THEME').'js/interior.js') }}"></script>
    <script defer src="{{ ver_asset(env('THEME').'js/new_bot_scripts.js') }}"></script>
    <script defer data-defdel="{{ ver_asset(env('THEME').'js/fancybox.js') }}"></script>
    <script defer data-defdel="{{ ver_asset(env('THEME').'js/swal.js') }}"></script>
    <script defer src="{{ ver_asset(env('THEME').'js/before-after.min.js') }}"></script>
    <script>
// mult
    </script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
    <script src="{{ ver_asset(env('THEME').'js/module.js') }}" defer></script>
@endif
<script src="https://cdn.jsdelivr.net/npm/heic2any/dist/heic2any.min.js"></script>

    @if(Route::currentRouteName() == 'modular-generator' )
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
        <script defer src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js" integrity="sha512-XtmMtDEcNz2j7ekrtHvOVR4iwwaD6o/FUJe6+Zq+HgcCsk3kj4uSQQR8weQ2QVj1o0Pk6PwYLohm206ZzNfubg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
        <script src="{{ver_asset(config('theme.current').'js/module-generator.js')}}" defer></script>
        <script data-defdel="{{ver_asset(config('theme.current').'js/fancybox.js')}}" defer></script>
        <script data-defdel="{{ver_asset(config('theme.current').'js/swal.js')}}" defer></script>
        <script defer src="{{ver_asset(config('theme.current').'js/jcf.min.js')}}"></script>
        {{-- <script defer src="{{ver_asset(config('theme.current').'js/main.js') }}"></script> --}}
        {{-- <script defer src="{{ver_asset(config('theme.current').'/jcf.file.min.js')}}"></script> --}}
        {{-- <script defer src="{{ver_asset(config('theme.current').'js/jcf.radio.min.js')}}"></script> --}}
        <script defer src="{{ver_asset(config('theme.current').'js/jcf.checkbox.min.js')}}"></script>
        <script defer src="{{ver_asset(config('theme.current').'js/jquery.collapse.min.js')}}"></script>
        <script defer src="{{ver_asset(config('theme.current').'js/jquery.collapse_storage.min.js')}}"></script>
        <script defer src="{{ver_asset(config('theme.current').'js/jquery.mask.min.js')}}"></script>
        <script defer src="{{ver_asset(config('theme.current').'js/jquery.matchHeight.min.js')}}"></script>
        <script defer src="{{ver_asset(config('theme.current').'js/modular-pictures.min.js')}}"></script>
        <script defer src="{{ver_asset(config('theme.current').'js/templates-modular.js')}}"></script>
        <script defer src="{{ver_asset(config('theme.current').'js/modular-generator1.js')}}"></script>
        <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
        <script src="{{ ver_asset(env('THEME').'js/module.js') }}" defer></script>
    @endif

    @includeIf(config('theme.resource').'pages.'.explode('.',Route::currentRouteName())[0] . '.head')

</head>
@if(Route::currentRouteName() == 'graphic_portrait.new_page'
||  Route::currentRouteName() == 'graphic_portrait.new_page--royal'
|| Route::currentRouteName() == 'graphic_portrait.oil'
|| Route::currentRouteName() == 'faq'
|| Route::currentRouteName() == 'condition'
|| Route::currentRouteName() == 'sizesprices'
|| Route::currentRouteName() == 'review'
|| strpos(Route::currentRouteName(), 'new_account') !== false
)
    <body class="vz-art load">
@elseif (Route::currentRouteName() == 'canvas' || Route::currentRouteName() == 'ads.canvas')
    <body class="vz-art load vz-canvas"  data-multiplier="{{ $contry_mult }}">
@elseif (Route::currentRouteName() == 'collage')
    <body class="vz-art load collage-bg">
@elseif (Route::currentRouteName() == 'about'
         || Route::currentRouteName() == 'contacts'
         || Route::currentRouteName() == 'new_stocks'
         )
    <body class="vz-art">
@elseif (Route::currentRouteName() == 'hb.gallery.index'
 || Route::currentRouteName() == 'hb.gallery.module'
 || Route::currentRouteName() == 'hb.gallery.category'
 || Route::currentRouteName() == 'hb.gallery.item.single'
 || Route::currentRouteName() == 'hb.gallery.item'
 || Route::currentRouteName() == 'hb.gallery.age'
 || Route::currentRouteName() == 'hb.gallery.genre'
 || Route::currentRouteName() == 'hb.gallery.list_age'
 || Route::currentRouteName() == 'hb.gallery.list_genre'
 || Route::currentRouteName() == 'hb.gallery.modern_handmade_paintings'
 || Route::currentRouteName() == 'hb.gallery.modern_painters'
 || Route::currentRouteName() == 'hb.gallery.nationality'
 || Route::currentRouteName() == 'hb.gallery.list_nationality'
 || Route::currentRouteName() == 'hb.gallery.paintings_top'
 || Route::currentRouteName() == 'hb.gallery.style'
 || Route::currentRouteName() == 'hb.gallery.painters'
 || Route::currentRouteName() == 'hb.gallery.painters_letter'
 || Route::currentRouteName() == 'hb.gallery.list_styles'
 || Route::currentRouteName() == 'modular-generator'
 || Route::currentRouteName() == 'simpsons'
 || Route::currentRouteName() == 'caricature'
 || Route::currentRouteName() == 'sub_caricature'
 || Route::currentRouteName() == 'gift_card_new'
 )
    <body class="vz-art load blog-frame">
@else
    <body class="vz-home load">
@endif
{!! $trackers_body !!}
@php
    $isTopSaleEnabled = filter_var(setting('site.top_sale', false), FILTER_VALIDATE_BOOLEAN);
@endphp
@if($isTopSaleEnabled)
<div class="top-sale">
    <img width="77" height="66" src="{{ asset(env('THEME').'images/sale.webp') }}" alt="">
    <p>@lang("header_footer_new.header.top_sale")</p>
</div>
@else
<style>
.vz-art.vz-header {
    top: 0 !important;
}

.br-object{
    top: 100px;
}
</style>
@endif

@if($creepingLine && !$creepingLine->isEmpty())
<style>
.vz-art.vz-header{
    top: {{ $isTopSaleEnabled ? '110px' : '0' }};
}

.br-object{
    top: 228px;
}

@media (max-width: 700px) {
    .br-object {
        top: 190px;
    }

    .breadcrumbs__arrow {
        margin-top: 4px;
    }
}

</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
      const speed = 50; // Скорость прокрутки в пикселях в секунду

      // Получаем все элементы с классом 'marquee-inner'
      const marquees = document.querySelectorAll('.marquee-inner');

      marquees.forEach(marqueeInner => {
        const marqueeContainer = marqueeInner.parentElement;

        // Функция для дублирования содержимого до необходимой ширины
        const duplicateContent = () => {
          // Очищаем все дублированные элементы перед повторной инициализацией
          marqueeInner.innerHTML = marqueeInner.innerHTML.split('<!-- duplicate -->')[0];

          const content = marqueeInner.innerHTML;
          let totalWidth = marqueeInner.scrollWidth;
          const containerWidth = marqueeContainer.clientWidth;

          // Дублируем содержимое до тех пор, пока общая ширина не превысит 2 * ширина контейнера
          while (totalWidth < 2 * containerWidth) {
            marqueeInner.innerHTML += '<!-- duplicate -->' + content;
            totalWidth = marqueeInner.scrollWidth;
          }
        };

        // Функция для расчета и установки длительности анимации
        const setAnimationDuration = () => {
          // Расчет после дублирования содержимого
          const contentWidth = marqueeInner.scrollWidth;

          // Поскольку содержимое дублировано для бесшовного цикла, путь прокрутки равен половине ширины
          const totalDistance = contentWidth / 2;

          // Рассчитываем длительность анимации
          const duration = totalDistance / speed; // в секундах

          // Устанавливаем CSS-переменную
          marqueeInner.style.animation = `scroll ${duration}s linear infinite`;
        };

        // Инициализация дублирования и установки длительности анимации
        const initializeMarquee = () => {
          duplicateContent();
          setAnimationDuration();
        };

        // Инициализируем маркер
        initializeMarquee();

        // Обновляем маркер при изменении размера окна
        window.addEventListener('resize', () => {
          initializeMarquee();
        });
      });
    });
  </script>

<div class="marquee-container">
    <div class="marquee-inner">
        @foreach ($creepingLine as $line)
            <span class="marquee-text">{!! $line->text !!}</span>
        @endforeach

        @foreach ($creepingLine as $line)
            <span class="marquee-text">{!! $line->text !!}</span>
        @endforeach

    </div>
</div>
@endif

    @if(Route::currentRouteName() == 'collage' )
        <div id="template-preview" style="display:none;">
            <div class="dz-preview dz-file-preview well" id="dz-preview-template">
                <div class="dz-image">
                    <img loading="lazy" data-dz-thumbnail="" src="{{ asset('img/loading.gif') }}" alt="loading">
                </div>
                <div class="dz-details">
                    <div class="dz-filename"><span data-dz-name=""></span></div>
                    <div class="dz-size" data-dz-size=""></div>
                </div>
                <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress=""></span></div>
                <div class="dz-success-mark"><span></span></div>
                <div class="dz-error-mark"><span></span></div>
                <div class="dz-error-message"><span data-dz-errormessage=""></span></div>
            </div>
        </div>
    @endif


    @include((config('theme.resource') ?: 'theme.viar.') . 'pages.socials.fb')
    @widget('Header', ['is_home' => 1])

    <main id="top">
		@yield('content')
    </main>

    @if(isset($seo) && $seo)
        <div class="seo-text section-frame">
            <div class="seo-text__wrapper">
                <div class="seo-text__text">
                    {!! $seo !!}
                </div>
            </div>
        </div>
    @endif

    @if(isset($is_cities_on_page) && $is_cities_on_page)
        @include((config('theme.resource') ?: 'theme.viar.') . 'partials.seo_city')
    @endif

    @if(Route::currentRouteName() == 'ads.canvas')
<!-- sticky offer -->

        <div id="offer" class="offer offer-hidden">
            <div class="popup-offer hb_popup_coupon"><a data-dismiss="dismiss"><strong>×</strong></a>
                <p class="hb_popup_coupon_t1"><span>@lang('coupon.hb_popup_coupon_t1')</span></p>
                <p class="hb_popup_coupon_t2"><strong>@lang('coupon.hb_popup_coupon_t2')</strong></p>
                <p class="hb_popup_coupon_t3">@lang('coupon.hb_popup_coupon_t3')</p>
                <p class="hb_popup_coupon_t4"><span class="coupon-code">@lang('coupon.hb_main_popup_coupon_t4')</span></p>
                <p class="hb_popup_coupon_t5">@lang('coupon.hb_popup_coupon_t5') @php $currentDate = Carbon::now(); $newDate = $currentDate->addDays(5); echo $newDate->format('d.m.Y'); @endphp</p>
            </div>
        </div>

        @php
            $currentDate = \Carbon\Carbon::now()->format('Y-m-d');
            $offerShownDate = session('offer_shown_date');
            $showOffer = false;

            if ($offerShownDate !== $currentDate) {
                session(['offer_shown_date' => $currentDate, 'offer_shown_today' => false]);
            }

            if (!session()->get('offer_shown_today')) {
                $showOffer = true;
                session(['offer_shown_today' => true]);
            }
        @endphp

        <script>
            function showOffer() {
                $('#offer').removeClass('offer-hidden');
            }

            function hideOffer() {
                $('#offer').addClass('offer-hidden');
            }

            $(document).ready(function() {
                @if($showOffer && !in_array(Route::currentRouteName(), ['home', 'canvas', 'ads.canvas'], true))
                    setTimeout(showOffer, 1000);
                @endif

                $('.popup-offer').on('click', function(ev){
                    ev.preventDefault();
                    showOffer();
                });

                $('.offer a[data-dismiss]').on('click touchend', function(ev) {
                    ev.preventDefault();
                    ev.stopPropagation();
                    hideOffer();
                });
            });
        </script>

        <script>
            const coupon_code = document.getElementById("coupon-code");
            if (coupon_code){
                coupon_code.addEventListener("click", function(event) {
                    event.preventDefault();
                    var couponCode = this.innerText.trim();
                    var textarea = document.createElement("textarea");
                    textarea.value = couponCode;
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand("copy");
                    document.body.removeChild(textarea);
                });
            }
        </script>

    @endif


    @widget('Footer')

    <a href="#top" class="scroll-button anchor" aria-label="anchor link">
        <i class="fa-arrow-next"></i>
    </a>

	@yield('footer_scripts')

    <div class="vz-art popup-frame target-frame">
        @include((config('theme.resource') ?: 'theme.viar.') . 'modals.login_reg_res')
        @include((config('theme.resource') ?: 'theme.viar.') . 'modals.target-box') {{-- why_are_you_leaving_questions --}}
        @yield('modals') {{-- pages.index.modals --}}
    </div>

    @yield('popup') {{-- popup --}}

	{{-- Товар добавлен в корзину. --}}
    <div class="popup-bask-add popup p__mod">
        <span class="close-popup"></span>
        <div class="popup-content">
            <span class="close-content"><i class="icon-icon4"></i></span>
            <div class="p__mod__title green__text">{{ trans('gl.suc') }}
                <span>
                    <img src="{{ asset(env('THEME').'img/checkmark_circle.1.png') }}" alt="">
                </span>
            </div>
        </div>
    </div>

	{{-- Неправильный размер. --}}
	<div class="popup-inv-size popup p__mod">
		<span class="close-popup"></span>
		<div class="popup-content">
			<span class="close-content" style="display: flex; flex-direction: row-reverse; cursor: pointer ">
            <img src="{{ asset(env('THEME').'img/cross1.png') }}" alt="" style="width:15px; height: 15px">
            </span>
			<div class="p__mod__title red__text h3_old" id="err_msgs">
				<span style="display:block;">{{ trans('gl.inv_filesize_or_ext') }}</span>
			</div>
		</div>
	</div>


    @if(Route::currentRouteName() != 'canvas' && Route::currentRouteName() != 'ads.canvas')
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
        <script defer src="{{ ver_asset(env('THEME').'js/cart.min.js') }}"></script> <!-- main menu 9 -->
        <script  src="{{ ver_asset(env('THEME').'js/custom.js') }}" defer></script>
    @endif

	@if(Route::currentRouteName() == 'cart.index'
    || Route::currentRouteName() == 'cart.step2'
    || Route::currentRouteName() == 'cart.step3'
    || Route::currentRouteName() == 'cart.step4'
     || Route::currentRouteName() == 'page.delivery')

        <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/6.8.4/swiper-bundle.min.js" integrity="sha512-BABFxitBmYt44N6n1NIJkGOsNaVaCs/GpaJwDktrfkWIBFnMD6p5l9m+Kc/4SLJSJ4mYf+cstX98NYrsG/M9ag==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/datepicker/1.0.10/datepicker.min.js" integrity="sha512-RCgrAvvoLpP7KVgTkTctrUdv7C6t7Un3p1iaoPr1++3pybCyCsCZZN7QEHMZTcJTmcJ7jzexTO+eFpHk4OCFAg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/datepicker/1.0.10/i18n/datepicker.ru-RU.min.js" integrity="sha512-kp29ggt/JOVnRNWjOUJJmKEgtlJdGgsPAOlMt+jCV7pxPq7mwtZewf49Axl1RHFN5DNJ1qzocWtT4A7uWqFjwQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/simplebar/5.3.0/simplebar.min.js" integrity="sha512-AS9rZZDdb+y4W2lcmkNGwf4swm6607XJYpNST1mkNBUfBBka8btA6mgRmhoFQ9Umy8Nj/fg5444+SglLHbowuA==" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/js/intlTelInput.min.js" integrity="sha512-OnkjbJ4TwPpgSmjXACCb5J4cJwi880VRe+vWpPDlr8M38/L3slN5uUAeOeWU2jN+4vN0gImCXFGdJmc0wO4Mig==" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.16/js/utils.min.js" integrity="sha512-2vFToKav5063UzIqgJVsl5xXfVq0zwqV2WQnPhgI08O+N+Ykyvz/TfaLDf+8Boo9IVoX0/wewt+NoYrwHry0Ww==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    @endif

    @if(Route::currentRouteName() != 'canvas' && Route::currentRouteName() != 'collage' && Route::currentRouteName() != 'ads.canvas')
        <script src="{{ ver_asset(env('THEME').'js/script.js') }}"></script>
    @endif
    @if(Route::currentRouteName() != 'canvas' && Route::currentRouteName() != 'ads.canvas')
    <script src="{{ ver_asset(env('THEME').'js/add.js') }}"></script>
    @endif

    @if(Route::currentRouteName() == 'home')
        <script src="{{ ver_asset(env('THEME') . 'js/emoj_form.js') }}" defer=""></script>
    @endif

    @if(Route::currentRouteName() == 'graphic_portrait.new_page'
    || Route::currentRouteName() == 'graphic_portrait.new_page--royal'
    || Route::currentRouteName() == 'graphic_portrait.oil'
    || Route::currentRouteName() == 'collage'
    || Route::currentRouteName() == 'blog'
    || Route::currentRouteName() == 'blog_category'
    || Route::currentRouteName() == 'blog_inner'
    || Route::currentRouteName() == "blog_category_all"
    || Route::currentRouteName() == 'hb.gallery.index'
    || Route::currentRouteName() == 'hb.gallery.module'
    || Route::currentRouteName() == 'hb.gallery.category'
    || Route::currentRouteName() == 'hb.gallery.item.single'
    || Route::currentRouteName() == 'hb.gallery.item'
    || Route::currentRouteName() == 'about'
    || Route::currentRouteName() == 'contacts'
    || strpos(Route::currentRouteName(), 'new_account') !== false
	)
        <script data-defdel="{{ ver_asset(env('THEME').'js/fancybox.js') }}"></script>
        <script data-defdel="{{ ver_asset(env('THEME').'js/swal.js') }}"></script>
    @endif

	@if(Route::currentRouteName() == 'graphic_portrait.new_page'
    || Route::currentRouteName() == 'graphic_portrait.new_page--royal'
    || Route::currentRouteName() == 'graphic_portrait.oil'
    || Route::currentRouteName() == 'collage')
        <script src="{{ ver_asset(env('THEME').'js/before-after.min.js') }}"></script>
    @endif

    @if(Route::currentRouteName() == 'graphic_portrait.new_page--royal')
        <script src="{{ ver_asset(env('THEME').'js/royal.js') }}"></script>
    @endif

    @if(Route::currentRouteName() == 'graphic_portrait.oil')
        <script src="{{ ver_asset(env('THEME').'js/oil.js') }}"></script>
    @endif

    @if(Route::currentRouteName() == 'graphic_portrait.new_page'
    || Route::currentRouteName() == 'graphic_portrait.new_page--royal'
    || Route::currentRouteName() == 'graphic_portrait.oil'
    || Route::currentRouteName() == 'simpsons' )
        <script src="{{ ver_asset(env('THEME').'js/portrait_new.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'js/portrait_new_phone.js') }}"></script>
    @endif
    @if(Route::currentRouteName() == 'graphic_portrait.new_page')

        <script src="{{ ver_asset(env('THEME').'js/portrait_calc.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
        <script src="{{ ver_asset(env('THEME').'js/module.js') }}" defer></script>
    @endif

    @if(Route::currentRouteName() == 'graphic_portrait.new_page--royal'
    || Route::currentRouteName() == 'graphic_portrait.oil')
        <script src="{{ ver_asset(env('THEME').'js/portrait--royal_calc.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
        <script src="{{ ver_asset(env('THEME').'js/module.js') }}" defer></script>
    @endif

@if( Route::currentRouteName() == 'simpsons' )
    <script src="{{ ver_asset(env('THEME').'js/simpsons.js') }}"></script>
{{--    <script src="{{ver_asset(config('theme.current').'js/sharj.js') }}"></script>--}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
    <script src="{{ ver_asset(env('THEME').'js/module.js') }}" defer></script>
@endif

@if( Route::currentRouteName() == 'caricature' || Route::currentRouteName() == 'sub_caricature')
{{--    <script src="{{ ver_asset(env('THEME').'js/simpsons.js') }}"></script>--}}
    <script src="{{ ver_asset(env('THEME').'js/sharj.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
    <script src="{{ ver_asset(env('THEME').'js/module.js') }}" defer></script>
@endif


    @if(Route::currentRouteName() == 'home'
    || Route::currentRouteName() == 'graphic_portrait.new_page'
    || Route::currentRouteName() == 'graphic_portrait.new_page--royal'
    || Route::currentRouteName() == 'graphic_portrait.oil'
    || Route::currentRouteName() == 'collage'
    )
        <script>
            var mult = $('body').data('multiplier');

            if (isNaN(mult)){
                mult = 1;
            }
        </script>

        {{-- <script>
            setTimeout(function() {
                const elem = document.createElement('link');
                elem.type = 'text/css';
                elem.rel = 'stylesheet';
                elem.href = '/theme/viar/style/intlTelInput.min.css';
                document.body.appendChild(elem);
            }, 2000);
        </script> --}}

        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/intlTelInput.min.css') }}">
        <script src="{{ ver_asset(env('THEME').'js/intlTelInput.min.js') }}"></script>

        @if (Session::has('success_photo'))
            <script>
                $('.js_pop_photo_form').addClass('active');

                $(".close-content, .n-p-btn-back").on("click", function() {
                    $(".popup").removeClass("active");
                });
            </script>
        @endif

    @endif


	@if(Route::currentRouteName() == 'collage' )
        {{-- @include(env('THEME_RESOURCES') . 'pages.collage.header_js') --}}
        <script src="{{ ver_asset(env('THEME').'js/collage_header_js.js') }}"></script>

        <script>
            window.onload = function() {

                $.ajax({
                type: "GET",
                url: '/collage-js',
                success: function(data){
                    $('.script').append(data);
                    $('.collage__formalization .collage-formalization-row, .collage__formalization .formalization__block--bottom').css('opacity', 1);
                    $('.collage-loading').fadeOut();
                }
                })
            }
        </script>

        <script src="{{ ver_asset(env('THEME').'js/script_canvas.js') }}" defer=""></script>
        <script src="{{ ver_asset(env('THEME').'js/main.js') }}"></script>
        <script src="//cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script src="{{ ver_asset(env('THEME').'js/jquery.mask.min.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'js/jquery.matchHeight.min.js') }}"></script>

        <script src="{{ ver_asset(env('THEME').'js/jcf.min.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'js/jcf.radio.min.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'js/jcf.range.cs.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'js/jcf.checkbox.min.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'js/jquery.collapse_storage.min.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'js/jquery.collapse.min.js') }}"></script>
        <script type="text/javascript">
            window.onShowInterior = function () {
                return {
                size: {
                    h: centimeter_height,
                    w: centimeter_width
                },
                src: editor.out(editor.canvas.width, editor.canvas.height, false)
                };
            }
        </script>


        <script src="{{ ver_asset(env('THEME').'js/collage-constructor.min.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'spinner/jm.spinner.js') }}"></script>
        <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/interior.css') }}">
        <script src="{{ ver_asset(env('THEME').'js/jquery.matchHeight.min.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'js/interior.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'js/dropzone/dropzone.min.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'js/email-decode.min.js') }}" data-cfasync="false"></script>
        <script src="{{ ver_asset(env('THEME').'js/rocket-loader.min.js') }}" data-cf-settings="73915442719c06acdf329ab4-|49" defer=""></script>


        <div class="script">

        </div>
    @endif


	@if(Route::currentRouteName() == 'blog'
    || Route::currentRouteName() == 'blog_category'
    || Route::currentRouteName() == 'blog_inner'
    || Route::currentRouteName() == "blog_category_all")
        <script src="{{ ver_asset(env('THEME').'js/blog.js') }}" defer></script>
    @endif




    @if(Route::currentRouteName() == 'hb.gallery.item.single' || Route::currentRouteName() == 'hb.gallery.item')
        {{-- для страницы item-card-reproduction.blade.php --}}
        <script>
            window.onShowInterior = function() {
                return {
                    size: {
                        h: size_centimeter_h,
                        w: size_centimeter_w
                    },
                    src: editor.out(editor.canvas.width, editor.canvas.height, false)
                };
            }
        </script>
        <script src="{{ ver_asset(env('THEME').'js/InteriorGenerator.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'js/jcf.min.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'js/jcf.range.min.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'js/interiorGallery.js') }}"></script>
        {{-- конец для страницы item-card-reproduction.blade.php --}}
    @endif

    @if(Route::currentRouteName() == 'hb.gallery.index'
    || Route::currentRouteName() == 'hb.gallery.module'
    || Route::currentRouteName() == 'hb.gallery.category'
    || Route::currentRouteName() == 'hb.gallery.item.single'
    || Route::currentRouteName() == 'hb.gallery.item'
    || Route::currentRouteName() == 'hb.gallery.age'
    || Route::currentRouteName() == 'hb.gallery.genre'
    || Route::currentRouteName() == 'hb.gallery.list_age'
    || Route::currentRouteName() == 'hb.gallery.list_genre'
    || Route::currentRouteName() == 'hb.gallery.modern_handmade_paintings'
    || Route::currentRouteName() == 'hb.gallery.modern_painters'
    || Route::currentRouteName() == 'hb.gallery.nationality'
    || Route::currentRouteName() == 'hb.gallery.list_nationality'
    || Route::currentRouteName() == 'hb.gallery.paintings_top'
    || Route::currentRouteName() == 'hb.gallery.style'
    || Route::currentRouteName() == 'hb.gallery.painters'
    || Route::currentRouteName() == 'hb.gallery.painters_letter'
    || Route::currentRouteName() == 'hb.gallery.list_styles'

    )
        <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
        <script src="{{ ver_asset(env('THEME').'js/module.js') }}" defer></script>
    @endif


    @if (Route::currentRouteName() == 'hb.gallery.index'
     || Route::currentRouteName() == 'about'
     || Route::currentRouteName() == 'contacts'
     || Route::currentRouteName() == 'new_stocks'
     )
        <script src="{{ ver_asset(env('THEME').'js/gallery.js') }}" defer></script>
    @endif



    @if(Route::currentRouteName() == 'canvas' || Route::currentRouteName() == 'ads.canvas')
        <style>
            @media screen and (max-width: 43.75em) {
                .popup-photo-canvas .kviz-input_pc {
                    display: block;
                    order: -1;
                }
            }
            img.img-svg__posa {
                position: absolute;
                right: 16px;
                top: 50%;
                transform: translateY(-50%);
            }
            .tabs-container .tabs-items .prices-sizes .prices-sizes-content .prices-cintant a::after,
            .canvas-banner .canvas-banner-content .canvas-tabs .tabs-content .order::after {
                content: '';
                background-size: 100% 100%;
            }
            .js-popup .jcf-fake-input,
            .hidden__labels .jcf-fake-input,
            .why-composition-inner  .jcf-fake-input,
            .js-popup .jcf-upload-button,
            .hidden__labels .jcf-upload-button,
            .why-composition-inner .jcf-upload-button,
            .why-form .jcf-fake-input,
            .why-form .jcf-upload-button{
                display: none;
            }
            @media screen and (max-width: 1024px) {
                .vz-art.vz-header {
                    background-color: unset;
                }
            }
            .jcf-extension-svg .jcf-fake-input,
            .jcf-extension-png .jcf-fake-input,
            .jcf-extension-jpg .jcf-fake-input,
            .jcf-extension-jpeg .jcf-fake-input,
            .jcf-extension-webp .jcf-fake-input,
            .jcf-extension-bmp .jcf-fake-input{
                display: block!important;
                font-size: 12px;
                text-align: center;
            }
            .tabs-item2 .tools__desk{
                opacity: 0;
                pointer-events: none;
            }
            .tabs-item2.active + .tools__mob{
                position: absolute;
                right: 30px;
                bottom: 31px;
                padding: 0;
            }
            .js_cost_canvas{
                padding-left: 26px
            }
        </style>
    @endif

    @if(Route::currentRouteName() == 'about' || Route::currentRouteName() == 'contacts')
        <script src="{{ ver_asset(env('THEME').'js/contacts.js') }}" defer></script>
    @endif

    @if(Route::currentRouteName() == 'new_stocks' ||
        Route::currentRouteName() == 'new_account.mystocks' ||
        Route::currentRouteName() == 'new_account.settings' ||
        Route::currentRouteName() == 'new_account.index' ||
        Route::currentRouteName() == 'new_account.orders'
        )
        <script src="{{ ver_asset(env('THEME').'js/stock.js') }}" defer></script>
    @endif


    @if(strpos(Route::currentRouteName(), 'new_account') !== false)
        <script src="{{ ver_asset(env('THEME').'js/cabinet.js') }}" defer></script>
    @endif

    @yield('script')


    @if(Route::currentRouteName() == 'simpsons')
        <!--
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"  integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
        -->
        <script src="{{ver_asset(config('theme.current').'js/intlTelInput.min.js') }}"></script>
        <!--<script src="{{ver_asset(config('theme.current').'js/script.js') }}"></script> -->
        <script src="{{ver_asset(config('theme.current').'js/add.js') }}"></script>
        <script data-defdel="{{ver_asset(config('theme.current').'js/fancybox.js') }}"></script>
        <script data-defdel="{{ver_asset(config('theme.current').'js/swal.js') }}"></script>
        <script src="{{ver_asset(config('theme.current').'js/before-after.min.js') }}"></script>
        <script src="{{ver_asset(config('theme.current').'js/main.js') }}"></script>
        <!--  <script src="{{ver_asset(config('theme.current').'js/sharj.js') }}"></script>      -->
    @endif

@if(Route::currentRouteName() == 'caricature' || Route::currentRouteName() == 'sub_caricature')
    <link rel="stylesheet" href="{{ ver_asset(config('theme.current').'style/before-after.min.css') }}"/>
    <link rel="stylesheet" href="{{ ver_asset(config('theme.current').'style/portrait/portrait.css') }}"/>
    <link rel="stylesheet" href="{{ ver_asset(config('theme.current').'style/portrait/portrait-media.css') }}"/>
    <link rel="stylesheet" href="{{ ver_asset(config('theme.current').'style/intlTelInput.min.css') }}"/>
    <link rel="stylesheet" href="{{ ver_asset(config('theme.current').'style/question-all/question-all.css') }}"/>
    <link rel="stylesheet" href="{{ ver_asset(config('theme.current').'style/question-all/question-all-media.css') }}"/>
    <link rel="stylesheet" href="{{ ver_asset(config('theme.current').'style/sharj/sharj.css') }}"/>
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/mcard/mcard.css') }}" onload="this.media='all'" media="all">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/mcard/mcard-media.css') }}" onload="this.media='all'" media="all">

    <script src="{{ver_asset(config('theme.current').'js/intlTelInput.min.js') }}"></script>
    <script src="{{ver_asset(config('theme.current').'js/add.js') }}"></script>
    <script data-defdel="{{ver_asset(config('theme.current').'js/fancybox.js') }}"></script>
    <script data-defdel="{{ver_asset(config('theme.current').'js/swal.js') }}"></script>
    <script src="{{ver_asset(config('theme.current').'js/before-after.min.js') }}"></script>
    <script src="{{ver_asset(config('theme.current').'js/main.js') }}"></script>
@endif

@if(Route::currentRouteName() == 'gift_card_new')
    <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.css">
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <link href="{{ ver_asset(config('theme.current').'images/fav.png') }}" rel="shortcut icon" type="image/x-icon">
    <link href="{{ ver_asset(config('theme.current').'fonts/Trajan-Pro-3.woff2') }}" rel="preload" as="font" type="font/woff2" crossorigin>
    <link href="{{ ver_asset(config('theme.current').'style/main.min.css') }}" rel="stylesheet">
    <link href="{{ ver_asset(config('theme.current').'style/media.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ ver_asset(config('theme.current').'style/gift/gift.css') }}">
    <link rel="stylesheet" href="{{ ver_asset(config('theme.current').'style/gift/gift-media.css') }}">
    <link rel="stylesheet" href="{{ ver_asset('css/gift-card.css') }}">
@endif


@if(Route::currentRouteName() != 'ads.canvas'
&& Route::currentRouteName() != 'cart.step2'
&& Route::currentRouteName() != 'cart.step3'
&& Route::currentRouteName() != 'cart.step4')
<!-- sticky offer -->

    <div id="offer" class="offer offer-hidden">
        <div class="popup-offer hb_popup_coupon"><a data-dismiss="dismiss"><strong>×</strong></a>
            <p class="hb_popup_coupon_t1"><span>@lang('coupon.hb_main_popup_coupon_t1')</span></p>
            <p class="hb_popup_coupon_t2"><strong>@lang('coupon.hb_main_popup_coupon_t2')</strong></p>
            <p class="hb_popup_coupon_t3">@lang('coupon.hb_main_popup_coupon_t3')</p>
            <p class="hb_popup_coupon_t4"><span class="coupon-code">@lang('coupon.hb_main_popup_coupon_t4')</span></p>
            <p class="hb_popup_coupon_t5">@lang('coupon.hb_main_popup_coupon_t5') @php $currentDate = Carbon::now(); $newDate = $currentDate->addDays(2); echo $newDate->format('d.m.Y'); @endphp</p>
        </div>
    </div>

    @php
        $currentDate = \Carbon\Carbon::now()->format('Y-m-d');
        $offerShownDate = session('offer_shown_date');
        $showOffer = false;

        if ($offerShownDate !== $currentDate) {
            session(['offer_shown_date' => $currentDate, 'offer_shown_today' => false]);
        }

        if (!session()->get('offer_shown_today')) {
            $showOffer = true;
            session(['offer_shown_today' => true]);
        }
    @endphp

    <script>
        function showOffer() {
            $('#offer').removeClass('offer-hidden');
        }

        function hideOffer() {
            $('#offer').addClass('offer-hidden');
        }

        $(document).ready(function() {
            @if($showOffer && !in_array(Route::currentRouteName(), ['home', 'canvas', 'ads.canvas'], true))
                setTimeout(showOffer, 1000);
            @endif

            $('.popup-offer').on('click', function(ev){
                ev.preventDefault();
                showOffer();
            });

            $('.offer a[data-dismiss]').on('click touchend', function(ev) {
                ev.preventDefault();
                ev.stopPropagation();
                hideOffer();
            });
        });
    </script>

    <script>
        const coupon_code = document.getElementById("coupon-code");
        if (coupon_code){
            coupon_code.addEventListener("click", function(event) {
                event.preventDefault();
                var couponCode = this.innerText.trim();
                var textarea = document.createElement("textarea");
                textarea.value = couponCode;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand("copy");
                document.body.removeChild(textarea);
            });
        }
    </script>

@endif


<script>
$(".close-content, .n-p-btn-back").on("click", function() {
$(".popup").removeClass("active");
});

$(".vz-art.js-popup.target-box.popup-cart").on('click', function(event) {
    if (!$(event.target).hasClass('go_to_cart')) {
        location.reload();
    }
});

</script>

@include((config('theme.resource') ?: 'theme.viar.') . 'partials.google_rating_badge')

</body>

</html>
