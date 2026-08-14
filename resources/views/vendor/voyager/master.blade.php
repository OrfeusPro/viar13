<!DOCTYPE html>
<html lang="{{ config('app.locale') }}" dir="{{ __('voyager::generic.is_rtl') == 'true' ? 'rtl' : 'ltr' }}">
<head>
    <title>@yield('page_title', setting('admin.title') . " - " . setting('admin.description'))</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <meta name="assets-path" content="{{ route('voyager.voyager_assets') }}"/>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet">

    <!-- Favicon -->
    <?php $admin_favicon = Voyager::setting('admin.icon_image', ''); ?>
    @if($admin_favicon == '')
        <link rel="shortcut icon" href="{{ voyager_asset('images/logo-icon.png') }}" type="image/png">
    @else
        <link rel="shortcut icon" href="{{ Voyager::image($admin_favicon) }}" type="image/png">
    @endif

    <!-- App CSS -->
    <link rel="stylesheet" href="{{ voyager_asset('css/app.css') }}">

    @yield('css')
    @if(__('voyager::generic.is_rtl') == 'true')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-rtl/3.4.0/css/bootstrap-rtl.css">
        <link rel="stylesheet" href="{{ voyager_asset('css/rtl.css') }}">
    @endif

    <link rel="stylesheet" href="{{ asset('css/admin2.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/alt-suggestions-editor.css') }}?v=3">
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <!-- Few Dynamic Styles -->
    <style type="text/css">
        .voyager .side-menu .navbar-header {
            background:{{ config('voyager.primary_color','#22A7F0') }};
            border-color:{{ config('voyager.primary_color','#22A7F0') }};
        }
        .widget .btn-primary{
            border-color:{{ config('voyager.primary_color','#22A7F0') }};
        }
        .widget .btn-primary:focus, .widget .btn-primary:hover, .widget .btn-primary:active, .widget .btn-primary.active, .widget .btn-primary:active:focus{
            background:{{ config('voyager.primary_color','#22A7F0') }};
        }
        .voyager .breadcrumb a{
            color:{{ config('voyager.primary_color','#22A7F0') }};
        }
        .sa-conversations-floating-link {
            position: fixed;
            top: 70px;
            right: 20px;
            z-index: 1035;
            /* box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18); */
            cursor: ns-resize;
            user-select: none;
            -webkit-user-select: none;
        }
        @media (max-width: 768px) {
            .sa-conversations-floating-link {
                top: 60px;
                right: 10px;
                left: 10px;
                display: flex;
                justify-content: center;
            }
        }
    </style>

    @if(!empty(config('voyager.additional_css')))<!-- Additional CSS -->
        @foreach(config('voyager.additional_css') as $css)<link rel="stylesheet" type="text/css" href="{{ asset($css) }}">@endforeach
    @endif

    @yield('head')
</head>

<body class="voyager @if(isset($dataType) && isset($dataType->slug)){{ $dataType->slug }}@endif">

<div id="voyager-loader">
    <?php $admin_loader_img = Voyager::setting('admin.loader', ''); ?>
    @if($admin_loader_img == '')
        <img src="{{ voyager_asset('images/logo-icon.png') }}" alt="Voyager Loader">
    @else
        <img src="{{ Voyager::image($admin_loader_img) }}" alt="Voyager Loader">
    @endif
</div>

<?php
if (\Illuminate\Support\Str::startsWith(Auth::user()->avatar, 'http://') || \Illuminate\Support\Str::startsWith(Auth::user()->avatar, 'https://')) {
    $user_avatar = Auth::user()->avatar;
} else {
    $user_avatar = Voyager::image(Auth::user()->avatar);
}

$saUnreadConversationsCount = 0;
if (\Illuminate\Support\Facades\Schema::hasTable('sa_conversations')
    && \Illuminate\Support\Facades\Schema::hasColumn('sa_conversations', 'unread_for_manager')) {
    $saUnreadConversationsCount = (int) \Illuminate\Support\Facades\DB::table('sa_conversations')
        ->where('unread_for_manager', 1)
        ->count();
}
?>

<div class="app-container">
    <div class="fadetoblack visible-xs"></div>
    <div class="row content-container">
        @include('voyager::dashboard.navbar')
        @include('voyager::dashboard.sidebar')
        <script>
            (function(){
                    var appContainer = document.querySelector('.app-container'),
                        sidebar = appContainer.querySelector('.side-menu'),
                        navbar = appContainer.querySelector('nav.navbar.navbar-top'),
                        loader = document.getElementById('voyager-loader'),
                        hamburgerMenu = document.querySelector('.hamburger'),
                        sidebarTransition = sidebar.style.transition,
                        navbarTransition = navbar.style.transition,
                        containerTransition = appContainer.style.transition;

                    sidebar.style.WebkitTransition = sidebar.style.MozTransition = sidebar.style.transition =
                    appContainer.style.WebkitTransition = appContainer.style.MozTransition = appContainer.style.transition =
                    navbar.style.WebkitTransition = navbar.style.MozTransition = navbar.style.transition = 'none';

                    if (window.innerWidth > 768 && window.localStorage && window.localStorage['voyager.stickySidebar'] == 'true') {
                        appContainer.className += ' expanded no-animation';
                        loader.style.left = (sidebar.clientWidth/2)+'px';
                        hamburgerMenu.className += ' is-active no-animation';
                    }

                   navbar.style.WebkitTransition = navbar.style.MozTransition = navbar.style.transition = navbarTransition;
                   sidebar.style.WebkitTransition = sidebar.style.MozTransition = sidebar.style.transition = sidebarTransition;
                   appContainer.style.WebkitTransition = appContainer.style.MozTransition = appContainer.style.transition = containerTransition;
            })();
        </script>
        <!-- Main Content -->
        <div class="container-fluid">
            <div class="side-body padding-top">
                @yield('page_header')
                <div id="voyager-notifications"></div>
                <div class="sa-conversations-floating-link" data-state-url="{{ route('admin.sa.conversations.unread_state') }}">
                    <a href="{{ route('admin.sa.conversations.index', ['scope' => 'unread']) }}" class="btn btn-warning">
                        <i class="voyager-chat"></i> SA-диалоги
                        @if($saUnreadConversationsCount > 0)
                            <span class="badge sa-conversations-unread-badge" style="margin-left:8px; background:#d9534f;">{{ $saUnreadConversationsCount }}</span>
                        @else
                            <span class="badge sa-conversations-unread-badge" style="margin-left:8px;">0</span>
                        @endif
                    </a>
                </div>
                @yield('content')
            </div>
        </div>
    </div>
</div>
@include('voyager::partials.app-footer')

<!-- Javascript Libs -->

<script type="text/javascript" src="{{ asset('admin_assets/js/app.js') }}?v=1"></script>
<script src="{{ asset('admin_assets/js/custom_new_v2.js') }}?v8"></script>
<script src="{{ asset('admin_assets/js/alt-suggestions-editor.js') }}?v=2"></script>

<script>
    @if(Session::has('alerts'))
        let alerts = {!! json_encode(Session::get('alerts')) !!};
        helpers.displayAlerts(alerts, toastr);
    @endif

    @if(Session::has('message'))

    // TODO: change Controllers to use AlertsMessages trait... then remove this
    var alertType = {!! json_encode(Session::get('alert-type', 'info')) !!};
    var alertMessage = {!! json_encode(Session::get('message')) !!};
    var alerter = toastr[alertType];
<?php /// TODO: Test this  ?>
    if (alerter) {
        alerter(alertMessage);
    } else {
        toastr.error("toastr alert-type " + alertType + " is unknown");
    }
    @endif
</script>
<script>
    (function () {
        var storageKey = 'saConversationsFloatingTop';
        var container = document.querySelector('.sa-conversations-floating-link');
        if (!container) {
            return;
        }

        var savedTop = parseInt(window.localStorage ? localStorage.getItem(storageKey) : '', 10);
        if (!isNaN(savedTop)) {
            container.style.top = savedTop + 'px';
        }

        var startY = 0;
        var startTop = 0;
        var isDragging = false;
        var hasMoved = false;

        function currentTop() {
            var value = parseInt(container.style.top || window.getComputedStyle(container).top || '70', 10);
            return isNaN(value) ? 70 : value;
        }

        function clampTop(value) {
            var minTop = 60;
            var maxTop = Math.max(minTop, window.innerHeight - container.offsetHeight - 20);
            return Math.min(Math.max(value, minTop), maxTop);
        }

        function onPointerMove(e) {
            if (!isDragging) {
                return;
            }

            var deltaY = e.clientY - startY;
            if (Math.abs(deltaY) > 3) {
                hasMoved = true;
            }

            container.style.top = clampTop(startTop + deltaY) + 'px';
            container.style.bottom = 'auto';
        }

        function onPointerUp() {
            if (!isDragging) {
                return;
            }

            isDragging = false;
            document.removeEventListener('pointermove', onPointerMove);
            document.removeEventListener('pointerup', onPointerUp);

            if (window.localStorage) {
                localStorage.setItem(storageKey, String(currentTop()));
            }

            setTimeout(function () {
                hasMoved = false;
            }, 0);
        }

        container.addEventListener('pointerdown', function (e) {
            if (typeof e.button !== 'undefined' && e.button !== 0) {
                return;
            }

            startY = e.clientY;
            startTop = currentTop();
            isDragging = true;
            hasMoved = false;

            document.addEventListener('pointermove', onPointerMove);
            document.addEventListener('pointerup', onPointerUp);
        });

        container.addEventListener('click', function (e) {
            if (hasMoved) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, true);

        window.addEventListener('resize', function () {
            container.style.top = clampTop(currentTop()) + 'px';
        });
    })();
</script>
<script>
    (function () {
        var container = document.querySelector('.sa-conversations-floating-link');
        var badge = container ? container.querySelector('.sa-conversations-unread-badge') : null;
        var stateUrl = container ? container.getAttribute('data-state-url') : '';
        if (!container || !badge || !stateUrl || typeof window.fetch !== 'function') {
            return;
        }

        var lastUnreadCount = parseInt((badge.textContent || '0').trim(), 10);
        if (isNaN(lastUnreadCount)) {
            lastUnreadCount = 0;
        }

        var isPolling = false;

        function playNotificationSound() {
            try {
                var AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) {
                    return;
                }

                var context = new AudioCtx();
                var oscillator = context.createOscillator();
                var gainNode = context.createGain();

                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(880, context.currentTime);
                gainNode.gain.setValueAtTime(0.0001, context.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.15, context.currentTime + 0.01);
                gainNode.gain.exponentialRampToValueAtTime(0.0001, context.currentTime + 0.35);

                oscillator.connect(gainNode);
                gainNode.connect(context.destination);
                oscillator.start();
                oscillator.stop(context.currentTime + 0.35);
                oscillator.onended = function () {
                    if (typeof context.close === 'function') {
                        context.close();
                    }
                };
            } catch (e) {
                // noop
            }
        }

        function updateBadge(count) {
            badge.textContent = String(count);
            if (count > 0) {
                badge.style.background = '#d9534f';
            } else {
                badge.style.background = '';
            }
        }

        function handleState(payload) {
            if (!payload || payload.status !== 'ok' || !payload.data) {
                return;
            }

            var nextUnreadCount = parseInt(payload.data.unread_count || 0, 10);
            if (isNaN(nextUnreadCount)) {
                nextUnreadCount = 0;
            }

            updateBadge(nextUnreadCount);

            if (nextUnreadCount > lastUnreadCount) {
                playNotificationSound();
                if (typeof toastr !== 'undefined') {
                    toastr.info('Появилось новое SA-обращение.');
                }
            }

            lastUnreadCount = nextUnreadCount;

            window.dispatchEvent(new CustomEvent('sa-conversations-state-updated', {
                detail: payload
            }));
        }

        function pollState() {
            if (isPolling) {
                return;
            }

            isPolling = true;
            fetch(stateUrl, {
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(function (response) { return response.json(); })
                .then(handleState)
                .catch(function () {
                    // noop
                })
                .finally(function () {
                    isPolling = false;
                });
        }

        setInterval(pollState, 10000);
        setTimeout(pollState, 2000);
    })();
</script>
@include('voyager::media.manager')
@yield('javascript')
@stack('javascript')
@if(!empty(config('voyager.additional_js')))<!-- Additional Javascript -->
    @foreach(config('voyager.additional_js') as $js)<script type="text/javascript" src="{{ asset($js) }}"></script>@endforeach
@endif

</body>
</html>
