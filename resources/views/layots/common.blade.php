@if(empty($is_noscripts))
    @include('layots.head')
@else
    @include('layots.head', ['is_noscripts' => 1])
@endif

@yield('content')

@include('layots.footer')
