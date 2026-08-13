<div class="breadcrumbs breadcrumbs__block" itemscope itemtype="https://schema.org/BreadcrumbList">
    <div itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
        <a href="{{ route('home') }}" class="breadcrumbs__link breadcrumbs__link_main" itemprop="item">
            <span itemprop="name">@lang('account.index1')</span>
        </a>
        <meta itemprop="position" content="1" />
    </div>
    <svg xmlns="http://www.w3.org/2000/svg" width="6" height="11" viewBox="0 0 6 11" fill="none"
         class="img-svg breadcrumbs__arrow replaced-svg">
        <path
            d="M0.24775 1.4341C0.180786 1.50106 0.147304 1.57807 0.147304 1.66513C0.147304 1.75218 0.180786 1.82919 0.24775 1.89615L4.1953 5.8437L0.247751 9.79124C0.180787 9.85821 0.147304 9.93522 0.147304 10.0223C0.147304 10.1093 0.180787 10.1863 0.247751 10.2533L0.749984 10.7555C0.816948 10.8225 0.893957 10.856 0.98101 10.856C1.06806 10.856 1.14507 10.8225 1.21204 10.7555L5.89284 6.07473C5.9598 6.00776 5.99329 5.93075 5.99329 5.8437C5.99329 5.75664 5.9598 5.67964 5.89284 5.61267L1.21204 0.931868C1.14507 0.864903 1.06806 0.831421 0.981009 0.831421C0.893956 0.831421 0.816947 0.864903 0.749983 0.931868L0.24775 1.4341Z"
            fill="#FA7846"></path>
    </svg>
    <div itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
        <a href="#" class="breadcrumbs__link" itemprop="item">
            <span itemprop="name">{!! $item->getTranslatedAttribute('name') !!}</span>
        </a>
        <meta itemprop="position" content="2" />
    </div>
    <script>
        // <![CDATA[  <-- For SVG support
        if ("WebSocket" in window) {
            (function () {
                function refreshCSS() {
                    var sheets = [].slice.call(
                        document.getElementsByTagName("link")
                    );
                    var head = document.getElementsByTagName("head")[0];
                    for (var i = 0; i < sheets.length; ++i) {
                        var elem = sheets[i];
                        var parent = elem.parentElement || head;
                        parent.removeChild(elem);
                        var rel = elem.rel;
                        if (
                            (elem.href && typeof rel != "string") ||
                            rel.length == 0 ||
                            rel.toLowerCase() == "stylesheet"
                        ) {
                            var url = elem.href.replace(
                                /(&|\?)_cacheOverride=\d+/,
                                ""
                            );
                            elem.href =
                                url +
                                (url.indexOf("?") >= 0 ? "&" : "?") +
                                "_cacheOverride=" +
                                new Date().valueOf();
                        }
                        parent.appendChild(elem);
                    }
                }

                var protocol =
                    window.location.protocol === "http:"
                        ? "ws://"
                        : "wss://";
                var address =
                    protocol +
                    window.location.host +
                    window.location.pathname +
                    "/ws";

                var socket = new WebSocket(address);
                socket.onmessage = function (msg) {
                    if (msg.data == "reload") window.location.reload();
                    else if (msg.data == "refreshcss") refreshCSS();
                };
                if (
                    sessionStorage &&
                    !sessionStorage.getItem(
                        "IsThisFirstTime_Log_From_LiveServer"
                    )
                ) {
                    console.log("Live reload enabled.");
                    sessionStorage.setItem(
                        "IsThisFirstTime_Log_From_LiveServer",
                        true
                    );
                }
            })();
        } else {
            console.error(
                "Upgrade your browser. This Browser is NOT supported WebSocket for Live-Reloading."
            );
        }
        // ]]>
    </script>
</div>
