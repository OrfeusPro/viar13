<div class="side-menu sidebar-inverse">
    <nav class="navbar navbar-default" role="navigation">
        <div class="side-menu-container">
            <div class="navbar-header">
                <a class="navbar-brand" href="{{ route('voyager.dashboard') }}">
                    <div class="logo-icon-container">
                        <?php $admin_logo_img = Voyager::setting('admin.icon_image', ''); ?>
                        @if($admin_logo_img == '')
                            <img src="{{ voyager_asset('images/logo-icon-light.png') }}" alt="Logo Icon">
                        @else
                            <img src="{{ Voyager::image($admin_logo_img) }}" alt="Logo Icon">
                        @endif
                    </div>
                    <div class="title">{{ Voyager::setting('admin.title', 'VOYAGER') }}</div>
                </a>
            </div>

            <div class="panel widget center bgimage"
                 style="background-image:url({{ Voyager::image(Voyager::setting('admin.bg_image'), voyager_asset('images/bg.jpg')) }}); background-size: cover; background-position: 0px;">
                <div class="dimmer"></div>
                <div class="panel-content">
                    <img src="{{ $user_avatar }}" class="avatar" alt="{{ Auth::user()->name }} avatar">
                    <h4>{{ ucwords(Auth::user()->name) }}</h4>
                    <p>{{ Auth::user()->email }}</p>

                    <a href="{{ route('voyager.profile') }}" class="btn btn-primary">{{ __('voyager::generic.profile') }}</a>
                    <div style="clear:both"></div>
                </div>
            </div>
        </div>
        <div id="adminmenu" data-alt-suggestions-pending="{{ (int) ($altSuggestionsPendingCount ?? 0) }}">
            <admin-menu :items="{{ menu('admin', '_json') }}"></admin-menu>
        </div>
    </nav>
</div>

{{--@if((int) ($altSuggestionsPendingCount ?? 0) > 0)--}}
{{--    <script>--}}
{{--        (function () {--}}
{{--            var pendingCount = {{ (int) ($altSuggestionsPendingCount ?? 0) }};--}}

{{--            function applyAltSuggestionBadge() {--}}
{{--                var menu = document.getElementById('adminmenu');--}}
{{--                var links;--}}
{{--                var i;--}}

{{--                if (!menu || pendingCount < 1) {--}}
{{--                    return;--}}
{{--                }--}}

{{--                links = menu.getElementsByTagName('a');--}}

{{--                for (i = 0; i < links.length; i += 1) {--}}
{{--                    if ((links[i].getAttribute('href') || '').indexOf('/admin/alt-suggestions') === -1) {--}}
{{--                        continue;--}}
{{--                    }--}}

{{--                    if (!links[i].querySelector('.alt-suggestions-menu-badge')) {--}}
{{--                        var badge = document.createElement('span');--}}
{{--                        badge.className = 'badge alt-suggestions-menu-badge pull-right';--}}
{{--                        badge.style.marginTop = '2px';--}}
{{--                        badge.style.background = '#f0ad4e';--}}
{{--                        links[i].appendChild(badge);--}}
{{--                    }--}}

{{--                    links[i].querySelector('.alt-suggestions-menu-badge').textContent = String(pendingCount);--}}
{{--                }--}}
{{--            }--}}

{{--            document.addEventListener('DOMContentLoaded', function () {--}}
{{--                var menu = document.getElementById('adminmenu');--}}
{{--                var observer;--}}

{{--                applyAltSuggestionBadge();--}}
{{--                window.setTimeout(applyAltSuggestionBadge, 100);--}}
{{--                window.setTimeout(applyAltSuggestionBadge, 500);--}}

{{--                if (menu && typeof MutationObserver !== 'undefined') {--}}
{{--                    observer = new MutationObserver(applyAltSuggestionBadge);--}}
{{--                    observer.observe(menu, {childList: true, subtree: true});--}}
{{--                    window.setTimeout(function () {--}}
{{--                        observer.disconnect();--}}
{{--                    }, 5000);--}}
{{--                }--}}
{{--            });--}}
{{--        }());--}}
{{--    </script>--}}
{{--@endif--}}
