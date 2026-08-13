<!-- before head code -->
{!! setting('stiliskripty.head_code') !!}
<!-- after head code -->

@if ($is_firefox == 1)
    <link rel="stylesheet" href="{{ asset('css/fontello.css') }}">
@else
    <link rel="preload" href="{{ asset('css/fontello.css') }}" as="style"
        onload="this.onload=null;this.rel='stylesheet'">
@endif

<style>
    .catalog-content .catalog-items {
        display: flex;
        flex-wrap: wrap;
    }

    .popup .js_spinner {
        text-align: center;
        margin: -30px auto 15px auto;
    }

    @media screen and (max-width: 768px) {

        .modular-tabs,
        .collage-tabs,
        .canvas-tabs {
            padding: 255px 15px 15px 15px;
        }
    }

    @media screen and (min-width: 1280px) {
        header .header-content .menu-logo nav li a {
            font-size: 14px;
        }
    }

    .other-styles .other-content .other-slider .other-item .item .img img {
        min-width: 88%;
    }

    .blog-article img {
        height: auto;
    }

    @media screen and (min-width: 768px) {
        .blog .blog-content .blog-items .blog-item h3 {
            height: 64px;
            overflow: hidden;
        }

        .blog .blog-content .blog-items .blog-item p {
            height: 48px;
            max-height: 48px;
        }
    }

    @media screen and (min-width: 1400px) {
        .blog .blog-content .blog-items .blog-item h3 {
            height: 68px;
            overflow: hidden;
        }
    }

    @media screen and (min-width: 1400px) {
        .blog .blog-content .blog-items .blog-item p {
            height: 48px;
            min-height: 48px;
        }

        .blog .blog-content .blog-items .blog-item h3 {
            height: 78px;
            overflow: hidden;
        }
    }


    .gallery-modular-one-content .slick-track {
        display: flex;
        align-items: center;
    }

    .gallery-modular-one-content .slick-slide {
        text-align: center;
    }

    .gallery-modular-one-content .slick-slide::before {
        content: '';
        display: inline-block;
        height: 100%;
        vertical-align: middle;
    }

    .gallery-modular-one-content .slick-slide img {
        vertical-align: middle;
        display: inline-block;
    }

    @media screen and (min-width: 1400px) {
        .generate .generate-content .gallery-photo .photo-content .photo-filter .filter-accordion .accordion-content .size li a {
            padding: 6px 12px;
        }
    }

    .generate .generate-content .gallery-photo .slider-tabs .tabs-content .picture .slick-list {
        /* display: flex;
    align-items: center; */
    }

    @media(min-width:1279px) {
        .generate .generate-content .gallery-photo .slider-tabs .tabs-content .picture .slick-slide {
            /* max-height: 450px;
   min-height: 450px; */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .generate .generate-content .gallery-photo .slider-tabs .tabs-content .picture .slick-slide img {
            object-fit: contain;
        }

        .gallery-reproductions-catalog .gallery-reproductions-catalog-content .catalog-content .catalog-items .catalog-item .img img {
            height: 143px;
            object-fit: contain;
        }
    }

    .gal__desc {
        margin-top: 50px;
        position: relative;
        overflow: hidden;
    }

    .popup {
        opacity: 0;
    }

    @media screen and (max-width: 768px) {
        .lt__cover {
            margin-bottom: 0;
        }

        .graphic-banner .graphic-banner-img {
            width: 295px;
            max-height: 180px;
            object-fit: cover
        }

        .graphic-banner .graphic-banner-content .title::after {
            background-size: cover !important;
        }
    }

    .dz-image>img {
        display: block !important;
        visibility: visible !important;
    }

    .js_decor_null {
        display: block !important;
    }

    .tel img {
        width: 32px;
        height: 32px;
    }

    .h__xtx {
        z-index: 3;
        color: #ff9118;
        font-size: 42px;
        font-weight: 100;
        line-height: 44px;
        position: relative;
        margin-bottom: 30px;
        font-family: 'Conv_MrDafoe-Regular';
    }

    @media screen and (min-width: 1280px) {
        .h__xtx {
            margin-bottom: 38px;
        }
    }

    span.hint__text {
        max-width: 150px;
    }

    @media screen and (min-width: 1700px) {
        .compositions .compositions-block::before {
            bottom: 12px;
            height: 187px;
        }
    }

    .dz-hidden-input {
        opacity: 0;
        position: absolute;
        left: -9999px
    }

    .wrapper+.jcf-file {
        opacity: 0;
        position: absolute;
        left: -9999px
    }

    .our-work-items {
        opacity: 0
    }

    .our-work-items.slick-initialized {
        opacity: 1
    }

    .product-download .img-item.isLoad button {
        display: block !important
    }

    .banner::before {
        content: '';
        pointer-events: none
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
        font-family: Inter, sans-serif
    }

    * {
        font-family: Inter, sans-serif
    }

    .packaging::after,
    .packaging::before {
        content: '';
        pointer-events: none
    }

    @media screen and (min-width:1024px) {
        .popup .popup-content {
            width: 754px !important
        }
    }

    @media screen and (min-width:768px) {
        .popup .popup-content {
            width: 600px;
            padding: 30px 20px
        }
    }

    .popup .popup-content {
        min-width: 320px
    }

    .js_no_touch .tools__mob,
    .js_touch .tools__desk {
        display: none !important
    }

    .js_touch .tools__mob {
        display: none
    }

    .js_touch .tabs-item2.active+.tools__mob {
        display: flex !important
    }

    .collages .collages-content .collages-text a:after,
    .collages::after,
    .collages::before,
    .foto .foto-content .foto-text a::after,
    .gallery .gallery-content .gallery-text a:after,
    .oiled .oiled-content .oiled-text a:after,
    .pictures .pictures-content .pictures-text a::after,
    .pictures .pictures-content .pictures-text a:after,
    .portrait .portrait-content .portrait-text a:after,
    .stylization .stylization-content .stylization-text .title::after,
    .stylization .stylization-content .stylization-text a:after,
    .title::after {
        content: '';
        background-size: 100% 100% !important
    }

</style>

<script>
    function testWebP(callback) {
        var webP = new Image();
        webP.onload = webP.onerror = function() {
            callback(webP.height == 2);
        };
        webP.src =
            'data:image/webp;base64,UklGRjoAAABXRUJQVlA4IC4AAACyAgCdASoCAAIALmk0mk0iIiIiIgBoSygABc6WWgAA/veff/0PP8bA//LwYAAA';
    };

    testWebP(function(support) {
        if (support) {
            $("html").addClass("webp");
        } else {
            $("html").addClass("no-webp");
        }
    });

    "ontouchstart" in window || navigator.MaxTouchPoints > 0 || navigator.msMaxTouchPoints > 0 ? $("html").addClass(
        "js_touch") : $("html").addClass("js_no_touch");
    var calc_float_val = function(t) {
        var a = Number(t);
        return parseFloat(a).toFixed(2)
    };

    function dataURItoBlob(t) {
        var a, e;
        a = -1 !== t.split(",")[0].indexOf("base64") ? atob(t.split(",")[1]) : decodeURI(t.split(",")[1]), e = t.split(
            ",")[0].split(":")[1].split(";")[0];
        for (var s = new Array, i = 0; i < a.length; i++) s[i] = a.charCodeAt(i);
        return new Blob([new Uint8Array(s)], {
            type: e
        })
    }
    $(document).on("click", ".js_mod_collapse", function(t) {
        t.PreventDefault, $(this).toggleClass("js_vis"), $(this).hasClass("js_vis") ? ($(this).find("span")
            .text($(this).data("hide")), $(this).prev().css("height", "100%"), $(this).prev().animate({
                height: "auto"
            }, 1)) : ($(this).find("span").text($(this).data("show")), $(this).prev().css("height",
            "467px"), $(this).prev().animate({
            height: 467
        }, 1))
    });

    // check support passive events listeners
    var supportsPassive = false;
    document.createElement("div").addEventListener("test", _ => {}, {
        get passive() {
            supportsPassive = true
        }
    });
    /* lazyload.js (c) Lorenzo Giuliani
     * MIT License (http://www.opensource.org/licenses/mit-license.html)
     *
     * expects a list of:
     * `<img src="blank.gif" data-src="my_image.png" width="600" height="400" class="lazy">`
     */
    $(function() {
        var $q = function(q, res) {
                if (document.querySelectorAll) {
                    res = document.querySelectorAll(q);
                } else {
                    var d = document,
                        a = d.styleSheets[0] || d.createStyleSheet();
                    a.addRule(q, 'f:b');
                    for (var l = d.all, b = 0, c = [], f = l.length; b < f; b++)
                        l[b].currentStyle.f && c.push(l[b]);

                    a.removeRule(0);
                    res = c;
                }
                return res;
            },
            addEventListener = function(evt, fn) {
                window.addEventListener ?
                    this.addEventListener(evt, fn, false) :
                    (window.attachEvent) ?
                    this.attachEvent('on' + evt, fn) :
                    this['on' + evt] = fn;
            },
            _has = function(obj, key) {
                return Object.prototype.hasOwnProperty.call(obj, key);
            };

        function loadImage(el, fn) {
            var img = new Image(),
                src = el.getAttribute('data-src');
            img.onload = function() {
                if (!!el.parent)
                    el.parent.replaceChild(img, el)
                else
                    el.src = src;

                fn ? fn() : null;
            }
            img.src = src;
        }

        function elementInViewport(el) {
            var rect = el.getBoundingClientRect()

            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.top <= (window.innerHeight || document.documentElement.clientHeight)
            )
        }

        var images = new Array(),
            query = $q('img.lazy'),
            processScroll = function() {
                for (var i = 0; i < images.length; i++) {
                    if (elementInViewport(images[i])) {
                        loadImage(images[i], function() {
                            images.splice(i, i);
                        });
                    }
                };
            };

        for (var i = 0; i < query.length; i++) {
            images.push(query[i]);
        };

        processScroll();
        addEventListener('scroll', processScroll, supportsPassive ? {
            passive: true
        } : false);

    });
</script>

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var csrfToken = $('[name="csrf_token"]').attr('content');

    setInterval(refreshToken, 3600000); // 1 hour 

    function refreshToken() {
        $.get('refresh-csrf').done(function(data) {
            csrfToken = data; // the new token
        });
    }

    setInterval(refreshToken, 3600000); // 1 hour 

    $(document).ajaxComplete(function(event, xhr, settings) {
        if (xhr.status == 400) {
            location.reload();
        }
    });
</script>



<style>
    .no-webp .popup .popup-content {
        background: #fff url(/img/popup-bg.png) no-repeat 50% 50%;
        background-size: cover;
    }

    .webp .popup .popup-content {
        background: #fff url(/img/popup-bg.webp) no-repeat 50% 50%;
        background-size: cover;
    }

    .no-webp .popup-login-registration .popup-content ul li a {
        background: url(/img/popup-link.png) no-repeat 50% 50%;
        background-size: cover
    }

    .webp .popup-login-registration .popup-content ul li a {
        background: url(/img/popup-link.webp) no-repeat 50% 50%;
        background-size: cover
    }

    .blog .title h1 {
        z-index: 3;
        width: 120px;
        color: #313131;
        font-size: 22px;
        font-weight: 600;
        text-align: right;
        line-height: 22px;
        position: relative;
        margin: 0 auto 20px;
        text-transform: uppercase;
    }

    @media screen and (min-width: 1280px) {
        .blog .title h1 {
            width: 170px;
            font-size: 30px;
            line-height: 30px;
            margin: 0 auto 43px;
            -webkit-transform: translate(-20px, 0);
            transform: translate(-20px, 0);
        }
    }

    .popup .popup-content {
        top: 50%;
        left: 50%;
        width: 90%;
        z-index: 3;
        padding: 15px 10px;
        position: absolute;
        border-radius: 19px;
        border: 5px solid #fff;
        box-shadow: 0 13px 32px rgba(16, 158, 199, .2);
        transform: translate(-50%, -50%)
    }

    .popup-login-registration .popup-content ul li a {
        width: 180px;
        padding: 16px 0;
        display: inline-block;
    }

    .js_zone_no_drop,
    div.js_zone {
        width: 100%
    }

    .js_zone .img__place {
        pointer-events: none;
        height: 84px;
        background-size: 47%;
        background-repeat: no-repeat;
        background-position: center center;
        display: flex;
        margin-bottom: 5px;
        border-radius: 10px;
        align-items: center;
        padding: 15px 2px 10px;
        justify-content: center;
        border: 1px solid #8c535b;
        background-image: url("{{ asset('img/download-icon.png') }}")
    }

    .spinner.small div {
        height: 8px;
        width: 8px
    }

    .spinner>div {
        width: 13px;
        height: 13px;
        background-color: #049cff;
        border-radius: 50%;
        display: inline-block;
        -webkit-animation: sk-bouncedelay 1.4s infinite ease-in-out both;
        animation: sk-bouncedelay 1.4s infinite ease-in-out both
    }

    .spinner .bounce1 {
        -webkit-animation-delay: -.32s;
        animation-delay: -.32s
    }

    audio {
        max-width: 100%
    }

    .spinner .bounce2 {
        -webkit-animation-delay: -.16s;
        animation-delay: -.16s
    }

    @-webkit-keyframes sk-bouncedelay {

        0%,
        100%,
        80% {
            -webkit-transform: scale(0)
        }

        40% {
            -webkit-transform: scale(1)
        }
    }

    @keyframes sk-bouncedelay {

        0%,
        100%,
        80% {
            -webkit-transform: scale(0);
            transform: scale(0)
        }

        40% {
            -webkit-transform: scale(1);
            transform: scale(1)
        }
    }

    .upl__btn .upl_ico {
        width: auto;
        max-width: 45px;
        height: auto;
        border: none;
        border-radius: 0
    }

    .dz-preview,
    .js_zone_no_drop img {
        min-height: 82px;
        max-height: 82px;
        object-fit: contain
    }

    @media screen and (min-width:1024px) {
        .popup .popup-content h3 {
            font-size: 19px;
            line-height: initial
        }
    }

    @media screen and (min-width:768px) {
        .popup .popup-content h3 {
            font-size: 19px;
            line-height: initial
        }
    }

    .upl__btn {
        border-radius: 10px;
        border: 1px solid #8c535b;
        width: 100%;
        height: 100%;
        display: block;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none
    }

    .drag-and-drop {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 100%;
        grid-column: 1/-1;
        border: 1px dashed #2d2d7d;
        padding: 9px;
        border-radius: 15px;
        pointer-events: none
    }

    .drag-and-drop .img.img__place {
        pointer-events: none;
        height: 54px;
        width: 54px !important;
        background-size: 45%;
        background-repeat: no-repeat;
        background-position: center center;
        display: flex;
        margin-bottom: 5px;
        border-radius: 10px;
        align-items: center;
        padding: 15px 2px 10px;
        justify-content: center;
        border: 1px solid #8c535b
    }

</style>
