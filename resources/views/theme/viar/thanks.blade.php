<!DOCTYPE html>
@if (Config::get('app.locale') == 'ee')
    <html lang="et">
@else
    <html lang="{{ Config::get('app.locale') }}">
@endif

<head>
    <meta charset="UTF-8">
    <title>{{ trans('portrait_buy_form.ths_thanks') }} {{ trans('portrait_buy_form.ths_we_got_your_order') }}</title>
    <meta name="description" content="">
    {{-- og --}}
    <meta property="og:title" content="{{ trans('portrait_buy_form.ths_thanks') }} {{ trans('portrait_buy_form.ths_we_got_your_order') }}" />
    <meta property="og:image" content="{{ \URL::to('/') }}/img/logo.png" />
    {{-- endog --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset('images/fav.png') }}" type="image/x-icon">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="canonical" href="{{ url(Request::url()) }}" />

	<link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/main.min.css') }}" media="all" />
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/custom.css') }}">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/cart.min.css') }}" /> <!-- main menu -->

	<link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/overall/overall.css') }}" media="all" />
	<link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/media.css') }}" media="all" />
    <!-- Preload Fonts -->
    <link rel="preload" href="{{ ver_asset(env('THEME').'fonts/Trajan-Pro-3-SemiBold.woff2') }}" as="font" type="font/woff2" crossorigin="anonymous">
    <link rel="preload" href="{{ ver_asset(env('THEME').'fonts/Trajan-Pro-3.woff2') }}" as="font" type="font/woff2" crossorigin="anonymous">
    <link rel="preload" href="{{ ver_asset(env('THEME').'fonts/icon-font.ttf') }}" as="font" type="font/ttf" crossorigin="anonymous">

    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/add.css') }}" media="all">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/forms.css')}}" media="all">
    <!-- Scripts -->

    <script src="{{ ver_asset(env('THEME').'js/custom.js') }}" defer=""></script>
    <script src="{{ ver_asset(env('THEME').'js/script.js') }}" defer=""></script>

    {!! $trackers !!}
</head>

<body class="load vz-home" style="background: antiquewhite;">

    {{-- before --}}
    @widget('Header', ['is_home' => 1])

    {{-- after --}}
    <main id="top">
        @include(env("THEME_RESOURCES").'partials.thanks_part')
    </main>

    @widget('Footer')

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script defer src="{{ ver_asset(env('THEME').'js/cart.min.js') }}"></script> <!-- main menu 8 -->

    @if($order)

	<script>
        dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
        dataLayer.push({
        event: "purchase",
        ecommerce: {
            transaction_id: "{{ $order['id']}}",
            affiliation: "viarcanvas.com",
            value: "{{ $order['sale_price'] }}",
            currency: "EUR",
            items: [
            @foreach($order['items'] as $item)
                @if(isset($item['name']))

                @php 
                
                    $item['name'] = str_replace('<span>','',$item['name']);
                    $item['name'] = str_replace('</span>','',$item['name']);

                    if(isset($item['pid']) && $item['pid'])
                    {
                        $order['catid'] = $item['pid'];
                        $order['catid_name'] = $item['name'];
                    }

                    if($item['name'] == "Canvas")
                    {
                        $order['catid'] = 1;
                        $order['catid_name'] = $item['name'];
                    }

                    if($item['name'] == "Collage")
                    {
                        $order['catid'] = 2;
                        $order['catid_name'] = $item['name'];
                    }
                @endphp
            {
                item_name: "{{ $item['name'] }}",
                item_id: "{{ $order['catid'] }}",
                price: "{{ $item['sumPrice'] }}",
                item_brand: "VIAR",
                item_category: "{{$order['catid_name']}}",
                quantity: 1
            }, 
                @endif
            @endforeach
            ]
        }
        });
</script>

<script>
        window.dataLayer = window.dataLayer || []
        dataLayer.push({
          'ecommerce': {
            'currencyCode': 'EUR',
            'purchase': {
              'actionField': {
                'id': '{{ $order['id']}}',
                'affiliation': 'viarcanvas.com',
                'revenue': '{{ $order['sale_price'] }}',
              },
              'products': [
                  @foreach($order['items'] as $item)
                  @if(isset($item['name']))

                  @php 
                
                    $item['name'] = str_replace('<span>','',$item['name']);
                    $item['name'] = str_replace('</span>','',$item['name']);

                    if(isset($item['pid']) && $item['pid'])
                    {
                        $order['catid'] = $item['pid'];
                        $order['catid_name'] = $item['name'];
                    }

                    if($item['name'] == "Canvas")
                    {
                        $order['catid'] = 1;
                        $order['catid_name'] = $item['name'];
                    }

                    if($item['name'] == "Collage")
                    {
                        $order['catid'] = 2;
                        $order['catid_name'] = $item['name'];
                    }
                    @endphp

                {
                    'id': '{{ $order['catid'] }}',
                    'name': '{{ $item['name'] }}',
                    'sku': '{{ $order['catid'] }}',
                    'price': '{{ $item['sumPrice'] }}',
                    'category': '{{$order['catid_name']}}',
                    'quantity': "1"
                },
                    @endif
                    @endforeach
              ]},
          },
         'event': 'EE-event',
         'EE-event-category': 'Enhanced Ecommerce',
         'EE-event-action': 'Purchase',
         'EE-event-non-interaction': 'False',
        });
        </script>

    @endif

    
</body>

</html>
