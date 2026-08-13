<script>
    /*! Magnific Popup - v1.1.0 - 2016-02-20
     * http://dimsemenov.com/plugins/magnific-popup/
     * Copyright (c) 2016 Dmitry Semenov; */
  ! function(a) { "function" == typeof define && define.amd ? define(["jquery"], a) : a("object" == typeof exports ? require( "jquery") : window.jQuery || window.Zepto) }(function(a) { var b, c, d, e, f, g, h = "Close", i = "BeforeClose", j = "AfterClose", k = "BeforeAppend", l = "MarkupParse", m = "Open", n = "Change", o = "mfp", p = "." + o, q = "mfp-ready", r = "mfp-removing", s = "mfp-prevent-close", t = function() {}, u = !!window.jQuery, v = a(window), w = function(a, c) { b.ev.on(o + a + p, c) }, x = function(b, c, d, e) { var f = document.createElement("div"); return f.className = "mfp-" + b, d && (f.innerHTML = d), e ? c && c.appendChild(f) : (f = a(f), c && f.appendTo(c)), f }, y = function(c, d) { b.ev.triggerHandler(o + c, d), b.st.callbacks && (c = c.charAt(0).toLowerCase() + c.slice(1), b.st .callbacks[c] && b.st.callbacks[c].apply(b, a.isArray(d) ? d : [d])) }, z = function(c) { return c === g && b.currTemplate.closeBtn || (b.currTemplate.closeBtn = a(b.st.closeMarkup.replace( "%title%", b.st.tClose)), g = c), b.currTemplate.closeBtn }, A = function() { a.magnificPopup.instance || (b = new t, b.init(), a.magnificPopup.instance = b) }, B = function() { var a = document.createElement("p").style, b = ["ms", "O", "Moz", "Webkit"]; if (void 0 !== a.transition) return !0; for (; b.length;) if (b.pop() + "Transition" in a) return !0; return !1 }; t.prototype = { constructor: t, init: function() { var c = navigator.appVersion; b.isLowIE = b.isIE8 = document.all && !document.addEventListener, b.isAndroid = /android/gi .test(c), b.isIOS = /iphone|ipad|ipod/gi.test(c), b.supportsTransition = B(), b .probablyMobile = b.isAndroid || b.isIOS || /(Opera Mini)|Kindle|webOS|BlackBerry|(Opera Mobi)|(Windows Phone)|IEMobile/i.test( navigator.userAgent), d = a(document), b.popupsCache = {} }, open: function(c) { var e; if (c.isObj === !1) { b.items = c.items.toArray(), b.index = 0; var g, h = c.items; for (e = 0; e < h.length; e++) if (g = h[e], g.parsed && (g = g.el[0]), g === c.el[0]) { b.index = e; break } } else b.items = a.isArray(c.items) ? c.items : [c.items], b.index = c.index || 0; if (b.isOpen) return void b.updateItemHTML(); b.types = [], f = "", c.mainEl && c.mainEl.length ? b.ev = c.mainEl.eq(0) : b.ev = d, c .key ? (b.popupsCache[c.key] || (b.popupsCache[c.key] = {}), b.currTemplate = b .popupsCache[c.key]) : b.currTemplate = {}, b.st = a.extend(!0, {}, a.magnificPopup .defaults, c), b.fixedContentPos = "auto" === b.st.fixedContentPos ? !b .probablyMobile : b.st.fixedContentPos, b.st.modal && (b.st.closeOnContentClick = !1, b .st.closeOnBgClick = !1, b.st.showCloseBtn = !1, b.st.enableEscapeKey = !1), b .bgOverlay || (b.bgOverlay = x("bg").on("click" + p, function() { b.close() }), b.wrap = x("wrap").attr("tabindex", -1).on("click" + p, function(a) { b._checkIfClose(a.target) && b.close() }), b.container = x("container", b.wrap)), b.contentContainer = x("content"), b.st .preloader && (b.preloader = x("preloader", b.container, b.st.tLoading)); var i = a.magnificPopup.modules; for (e = 0; e < i.length; e++) { var j = i[e]; j = j.charAt(0).toUpperCase() + j.slice(1), b["init" + j].call(b) } y("BeforeOpen"), b.st.showCloseBtn && (b.st.closeBtnInside ? (w(l, function(a, b, c, d) { c.close_replaceWith = z(d.type) }), f += " mfp-close-btn-in") : b.wrap.append(z())), b.st.alignTop && (f += " mfp-align-top"), b.fixedContentPos ? b.wrap.css({ overflow: b.st.overflowY, overflowX: "hidden", overflowY: b.st.overflowY }) : b.wrap.css({ top: v.scrollTop(), position: "absolute" }), (b.st.fixedBgPos === !1 || "auto" === b.st.fixedBgPos && !b.fixedContentPos) && b .bgOverlay.css({ height: d.height(), position: "absolute" }), b.st.enableEscapeKey && d.on("keyup" + p, function(a) { 27 === a.keyCode && b.close() }), v.on("resize" + p, function() { b.updateSize() }), b.st.closeOnContentClick || (f += " mfp-auto-cursor"), f && b.wrap.addClass(f); var k = b.wH = v.height(), n = {}; if (b.fixedContentPos && b._hasScrollBar(k)) { var o = b._getScrollbarSize(); o && (n.marginRight = o) } b.fixedContentPos && (b.isIE7 ? a("body, html").css("overflow", "hidden") : n.overflow = "hidden"); var r = b.st.mainClass; return b.isIE7 && (r += " mfp-ie7"), r && b._addClassToMFP(r), b.updateItemHTML(), y( "BuildControls"), a("html").css(n), b.bgOverlay.add(b.wrap).prependTo(b.st .prependTo || a(document.body)), b._lastFocusedEl = document.activeElement, setTimeout(function() { b.content ? (b._addClassToMFP(q), b._setFocus()) : b.bgOverlay.addClass(q), d .on("focusin" + p, b._onFocusIn) }, 16), b.isOpen = !0, b.updateSize(k), y(m), c }, close: function() { b.isOpen && (y(i), b.isOpen = !1, b.st.removalDelay && !b.isLowIE && b.supportsTransition ? (b._addClassToMFP(r), setTimeout(function() { b._close() }, b.st.removalDelay)) : b._close()) }, _close: function() { y(h); var c = r + " " + q + " "; if (b.bgOverlay.detach(), b.wrap.detach(), b.container.empty(), b.st.mainClass && (c += b.st .mainClass + " "), b._removeClassFromMFP(c), b.fixedContentPos) { var e = { marginRight: "" }; b.isIE7 ? a("body, html").css("overflow", "") : e.overflow = "", a("html").css(e) } d.off("keyup" + p + " focusin" + p), b.ev.off(p), b.wrap.attr("class", "mfp-wrap") .removeAttr("style"), b.bgOverlay.attr("class", "mfp-bg"), b.container.attr("class", "mfp-container"), !b.st.showCloseBtn || b.st.closeBtnInside && b.currTemplate[b .currItem.type] !== !0 || b.currTemplate.closeBtn && b.currTemplate.closeBtn .detach(), b.st.autoFocusLast && b._lastFocusedEl && a(b._lastFocusedEl).focus(), b .currItem = null, b.content = null, b.currTemplate = null, b.prevHeight = 0, y(j) }, updateSize: function(a) { if (b.isIOS) { var c = document.documentElement.clientWidth / window.innerWidth, d = window.innerHeight * c; b.wrap.css("height", d), b.wH = d } else b.wH = a || v.height(); b.fixedContentPos || b.wrap.css("height", b.wH), y("Resize") }, updateItemHTML: function() { var c = b.items[b.index]; b.contentContainer.detach(), b.content && b.content.detach(), c.parsed || (c = b.parseEl(b .index)); var d = c.type; if (y("BeforeChange", [b.currItem ? b.currItem.type : "", d]), b.currItem = c, !b .currTemplate[d]) { var f = b.st[d] ? b.st[d].markup : !1; y("FirstMarkupParse", f), f ? b.currTemplate[d] = a(f) : b.currTemplate[d] = !0 } e && e !== c.type && b.container.removeClass("mfp-" + e + "-holder"); var g = b["get" + d.charAt(0).toUpperCase() + d.slice(1)](c, b.currTemplate[d]); b.appendContent(g, d), c.preloaded = !0, y(n, c), e = c.type, b.container.prepend(b .contentContainer), y("AfterChange") }, appendContent: function(a, c) { b.content = a, a ? b.st.showCloseBtn && b.st.closeBtnInside && b.currTemplate[c] === !0 ? b .content.find(".mfp-close").length || b.content.append(z()) : b.content = a : b .content = "", y(k), b.container.addClass("mfp-" + c + "-holder"), b.contentContainer .append(b.content) }, parseEl: function(c) { var d, e = b.items[c]; if (e.tagName ? e = { el: a(e) } : (d = e.type, e = { data: e, src: e.src }), e.el) { for (var f = b.types, g = 0; g < f.length; g++) if (e.el.hasClass("mfp-" + f[g])) { d = f[g]; break } e.src = e.el.attr("data-mfp-src"), e.src || (e.src = e.el.attr("href")) } return e.type = d || b.st.type || "inline", e.index = c, e.parsed = !0, b.items[c] = e, y( "ElementParse", e), b.items[c] }, addGroup: function(a, c) { var d = function(d) { d.mfpEl = this, b._openClick(d, a, c) }; c || (c = {}); var e = "click.magnificPopup"; c.mainEl = a, c.items ? (c.isObj = !0, a.off(e).on(e, d)) : (c.isObj = !1, c.delegate ? a .off(e).on(e, c.delegate, d) : (c.items = a, a.off(e).on(e, d))) }, _openClick: function(c, d, e) { var f = void 0 !== e.midClick ? e.midClick : a.magnificPopup.defaults.midClick; if (f || !(2 === c.which || c.ctrlKey || c.metaKey || c.altKey || c.shiftKey)) { var g = void 0 !== e.disableOn ? e.disableOn : a.magnificPopup.defaults.disableOn; if (g) if (a.isFunction(g)) { if (!g.call(b)) return !0 } else if (v.width() < g) return !0; c.type && (c.preventDefault(), b.isOpen && c.stopPropagation()), e.el = a(c.mfpEl), e .delegate && (e.items = d.find(e.delegate)), b.open(e) } }, updateStatus: function(a, d) { if (b.preloader) { c !== a && b.container.removeClass("mfp-s-" + c), d || "loading" !== a || (d = b.st .tLoading); var e = { status: a, text: d }; y("UpdateStatus", e), a = e.status, d = e.text, b.preloader.html(d), b.preloader.find( "a").on("click", function(a) { a.stopImmediatePropagation() }), b.container.addClass("mfp-s-" + a), c = a } }, _checkIfClose: function(c) { if (!a(c).hasClass(s)) { var d = b.st.closeOnContentClick, e = b.st.closeOnBgClick; if (d && e) return !0; if (!b.content || a(c).hasClass("mfp-close") || b.preloader && c === b.preloader[0]) return !0; if (c === b.content[0] || a.contains(b.content[0], c)) { if (d) return !0 } else if (e && a.contains(document, c)) return !0; return !1 } }, _addClassToMFP: function(a) { b.bgOverlay.addClass(a), b.wrap.addClass(a) }, _removeClassFromMFP: function(a) { this.bgOverlay.removeClass(a), b.wrap.removeClass(a) }, _hasScrollBar: function(a) { return (b.isIE7 ? d.height() : document.body.scrollHeight) > (a || v.height()) }, _setFocus: function() { (b.st.focus ? b.content.find(b.st.focus).eq(0) : b.wrap).focus() }, _onFocusIn: function(c) { return c.target === b.wrap[0] || a.contains(b.wrap[0], c.target) ? void 0 : (b._setFocus(), !1) }, _parseMarkup: function(b, c, d) { var e; d.data && (c = a.extend(d.data, c)), y(l, [b, c, d]), a.each(c, function(c, d) { if (void 0 === d || d === !1) return !0; if (e = c.split("_"), e.length > 1) { var f = b.find(p + "-" + e[0]); if (f.length > 0) { var g = e[1]; "replaceWith" === g ? f[0] !== d[0] && f.replaceWith(d) : "img" === g ? f.is("img") ? f.attr("src", d) : f.replaceWith(a("<img>").attr( "src", d).attr("class", f.attr("class"))) : f.attr(e[1], d) } } else b.find(p + "-" + c).html(d) }) }, _getScrollbarSize: function() { if (void 0 === b.scrollbarSize) { var a = document.createElement("div"); a.style.cssText = "width: 99px; height: 99px; overflow: scroll; position: absolute; top: -9999px;", document.body.appendChild(a), b.scrollbarSize = a.offsetWidth - a.clientWidth, document.body.removeChild(a) } return b.scrollbarSize } }, a.magnificPopup = { instance: null, proto: t.prototype, modules: [], open: function(b, c) { return A(), b = b ? a.extend(!0, {}, b) : {}, b.isObj = !0, b.index = c || 0, this.instance .open(b) }, close: function() { return a.magnificPopup.instance && a.magnificPopup.instance.close() }, registerModule: function(b, c) { c.options && (a.magnificPopup.defaults[b] = c.options), a.extend(this.proto, c.proto), this .modules.push(b) }, defaults: { disableOn: 0, key: null, midClick: !1, mainClass: "", preloader: !0, focus: "", closeOnContentClick: !1, closeOnBgClick: !0, closeBtnInside: !0, showCloseBtn: !0, enableEscapeKey: !0, modal: !1, alignTop: !1, removalDelay: 0, prependTo: null, fixedContentPos: "auto", fixedBgPos: "auto", overflowY: "auto", closeMarkup: '<button title="%title%" type="button" class="mfp-close">&#215;</button>', tClose: "Close (Esc)", tLoading: "Loading...", autoFocusLast: !0 } }, a.fn.magnificPopup = function(c) { A(); var d = a(this); if ("string" == typeof c) if ("open" === c) { var e, f = u ? d.data("magnificPopup") : d[0].magnificPopup, g = parseInt(arguments[1], 10) || 0; f.items ? e = f.items[g] : (e = d, f.delegate && (e = e.find(f.delegate)), e = e.eq(g)), b ._openClick({ mfpEl: e }, d, f) } else b.isOpen && b[c].apply(b, Array.prototype.slice.call(arguments, 1)); else c = a.extend(!0, {}, c), u ? d.data("magnificPopup", c) : d[0].magnificPopup = c, b.addGroup(d, c); return d }; var C, D, E, F = "inline", G = function() { E && (D.after(E.addClass(C)).detach(), E = null) }; a.magnificPopup.registerModule(F, { options: { hiddenClass: "hide", markup: "", tNotFound: "Content not found" }, proto: { initInline: function() { b.types.push(F), w(h + "." + F, function() { G() }) }, getInline: function(c, d) { if (G(), c.src) { var e = b.st.inline, f = a(c.src); if (f.length) { var g = f[0].parentNode; g && g.tagName && (D || (C = e.hiddenClass, D = x(C), C = "mfp-" + C), E = f .after(D).detach().removeClass(C)), b.updateStatus("ready") } else b.updateStatus("error", e.tNotFound), f = a("<div>"); return c.inlineElement = f, f } return b.updateStatus("ready"), b._parseMarkup(d, {}, c), d } } }); var H, I = "ajax", J = function() { H && a(document.body).removeClass(H) }, K = function() { J(), b.req && b.req.abort() }; a.magnificPopup.registerModule(I, { options: { settings: null, cursor: "mfp-ajax-cur", tError: '<a href="%url%">The content</a> could not be loaded.' }, proto: { initAjax: function() { b.types.push(I), H = b.st.ajax.cursor, w(h + "." + I, K), w("BeforeChange." + I, K) }, getAjax: function(c) { H && a(document.body).addClass(H), b.updateStatus("loading"); var d = a.extend({ url: c.src, success: function(d, e, f) { var g = { data: d, xhr: f }; y("ParseAjax", g), b.appendContent(a(g.data), I), c.finished = ! 0, J(), b._setFocus(), setTimeout(function() { b.wrap.addClass(q) }, 16), b.updateStatus("ready"), y("AjaxContentAdded") }, error: function() { J(), c.finished = c.loadError = !0, b.updateStatus("error", b.st .ajax.tError.replace("%url%", c.src)) } }, b.st.ajax.settings); return b.req = a.ajax(d), "" } } }); var L, M = function(c) { if (c.data && void 0 !== c.data.title) return c.data.title; var d = b.st.image.titleSrc; if (d) { if (a.isFunction(d)) return d.call(b, c); if (c.el) return c.el.attr(d) || "" } return "" }; a.magnificPopup.registerModule("image", { options: { markup: '<div class="mfp-figure"><div class="mfp-close"></div><figure><div class="mfp-img"></div><figcaption><div class="mfp-bottom-bar"><div class="mfp-title"></div><div class="mfp-counter"></div></div></figcaption></figure></div>', cursor: "mfp-zoom-out-cur", titleSrc: "title", verticalFit: !0, tError: '<a href="%url%">The image</a> could not be loaded.' }, proto: { initImage: function() { var c = b.st.image, d = ".image"; b.types.push("image"), w(m + d, function() { "image" === b.currItem.type && c.cursor && a(document.body).addClass(c .cursor) }), w(h + d, function() { c.cursor && a(document.body).removeClass(c.cursor), v.off("resize" + p) }), w("Resize" + d, b.resizeImage), b.isLowIE && w("AfterChange", b.resizeImage) }, resizeImage: function() { var a = b.currItem; if (a && a.img && b.st.image.verticalFit) { var c = 0; b.isLowIE && (c = parseInt(a.img.css("padding-top"), 10) + parseInt(a.img.css( "padding-bottom"), 10)), a.img.css("max-height", b.wH - c) } }, _onImageHasSize: function(a) { a.img && (a.hasSize = !0, L && clearInterval(L), a.isCheckingImgSize = !1, y( "ImageHasSize", a), a.imgHidden && (b.content && b.content.removeClass( "mfp-loading"), a.imgHidden = !1)) }, findImageSize: function(a) { var c = 0, d = a.img[0], e = function(f) { L && clearInterval(L), L = setInterval(function() { return d.naturalWidth > 0 ? void b._onImageHasSize(a) : (c > 200 && clearInterval(L), c++, void(3 === c ? e(10) : 40 === c ? e(50) : 100 === c && e(500))) }, f) }; e(1) }, getImage: function(c, d) { var e = 0, f = function() { c && (c.img[0].complete ? (c.img.off(".mfploader"), c === b.currItem && (b ._onImageHasSize(c), b.updateStatus("ready")), c.hasSize = ! 0, c.loaded = !0, y("ImageLoadComplete")) : (e++, 200 > e ? setTimeout(f, 100) : g())) }, g = function() { c && (c.img.off(".mfploader"), c === b.currItem && (b._onImageHasSize(c), b .updateStatus("error", h.tError.replace("%url%", c.src))), c .hasSize = !0, c.loaded = !0, c.loadError = !0) }, h = b.st.image, i = d.find(".mfp-img"); if (i.length) { var j = document.createElement("img"); j.className = "mfp-img", c.el && c.el.find("img").length && (j.alt = c.el.find( "img").attr("alt")), c.img = a(j).on("load.mfploader", f).on( "error.mfploader", g), j.src = c.src, i.is("img") && (c.img = c.img .clone()), j = c.img[0], j.naturalWidth > 0 ? c.hasSize = !0 : j .width || (c.hasSize = !1) } return b._parseMarkup(d, { title: M(c), img_replaceWith: c.img }, c), b.resizeImage(), c.hasSize ? (L && clearInterval(L), c.loadError ? (d .addClass("mfp-loading"), b.updateStatus("error", h.tError.replace( "%url%", c.src))) : (d.removeClass("mfp-loading"), b.updateStatus( "ready")), d) : (b.updateStatus("loading"), c.loading = !0, c.hasSize || (c .imgHidden = !0, d.addClass("mfp-loading"), b.findImageSize(c)), d) } } }); var N, O = function() { return void 0 === N && (N = void 0 !== document.createElement("p").style.MozTransform), N }; a.magnificPopup.registerModule("zoom", { options: { enabled: !1, easing: "ease-in-out", duration: 300, opener: function(a) { return a.is("img") ? a : a.find("img") } }, proto: { initZoom: function() { var a, c = b.st.zoom, d = ".zoom"; if (c.enabled && b.supportsTransition) { var e, f, g = c.duration, j = function(a) { var b = a.clone().removeAttr("style").removeAttr("class").addClass( "mfp-animated-image"), d = "all " + c.duration / 1e3 + "s " + c.easing, e = { position: "fixed", zIndex: 9999, left: 0, top: 0, "-webkit-backface-visibility": "hidden" }, f = "transition"; return e["-webkit-" + f] = e["-moz-" + f] = e["-o-" + f] = e[f] = d, b .css(e), b }, k = function() { b.content.css("visibility", "visible") }; w("BuildControls" + d, function() { if (b._allowZoom()) { if (clearTimeout(e), b.content.css("visibility", "hidden"), a = b._getItemToZoom(), !a) return void k(); f = j(a), f.css(b._getOffset()), b.wrap.append(f), e = setTimeout(function() { f.css(b._getOffset(!0)), e = setTimeout(function() { k(), setTimeout(function() { f.remove(), a = f = null, y( "ZoomAnimationEnded" ) }, 16) }, g) }, 16) } }), w(i + d, function() { if (b._allowZoom()) { if (clearTimeout(e), b.st.removalDelay = g, !a) { if (a = b._getItemToZoom(), !a) return; f = j(a) } f.css(b._getOffset(!0)), b.wrap.append(f), b.content.css( "visibility", "hidden"), setTimeout(function() { f.css(b._getOffset()) }, 16) } }), w(h + d, function() { b._allowZoom() && (k(), f && f.remove(), a = null) }) } }, _allowZoom: function() { return "image" === b.currItem.type }, _getItemToZoom: function() { return b.currItem.hasSize ? b.currItem.img : !1 }, _getOffset: function(c) { var d; d = c ? b.currItem.img : b.st.zoom.opener(b.currItem.el || b.currItem); var e = d.offset(), f = parseInt(d.css("padding-top"), 10), g = parseInt(d.css("padding-bottom"), 10); e.top -= a(window).scrollTop() - f; var h = { width: d.width(), height: (u ? d.innerHeight() : d[0].offsetHeight) - g - f }; return O() ? h["-moz-transform"] = h.transform = "translate(" + e.left + "px," + e .top + "px)" : (h.left = e.left, h.top = e.top), h } } }); var P = "iframe", Q = "//about:blank", R = function(a) { if (b.currTemplate[P]) { var c = b.currTemplate[P].find("iframe"); c.length && (a || (c[0].src = Q), b.isIE8 && c.css("display", a ? "block" : "none")) } }; a.magnificPopup.registerModule(P, { options: { markup: '<div class="mfp-iframe-scaler"><div class="mfp-close"></div><iframe class="mfp-iframe" src="//about:blank" frameborder="0" allowfullscreen></iframe></div>', srcAction: "iframe_src", patterns: { youtube: { index: "youtube.com", id: "v=", src: "//www.youtube.com/embed/%id%?autoplay=1" }, vimeo: { index: "vimeo.com/", id: "/", src: "//player.vimeo.com/video/%id%?autoplay=1" }, gmaps: { index: "//maps.google.", src: "%id%&output=embed" } } }, proto: { initIframe: function() { b.types.push(P), w("BeforeChange", function(a, b, c) { b !== c && (b === P ? R() : c === P && R(!0)) }), w(h + "." + P, function() { R() }) }, getIframe: function(c, d) { var e = c.src, f = b.st.iframe; a.each(f.patterns, function() { return e.indexOf(this.index) > -1 ? (this.id && (e = "string" == typeof this.id ? e.substr(e.lastIndexOf(this.id) + this.id .length, e.length) : this.id.call(this, e)), e = this .src.replace("%id%", e), !1) : void 0 }); var g = {}; return f.srcAction && (g[f.srcAction] = e), b._parseMarkup(d, g, c), b.updateStatus( "ready"), d } } }); var S = function(a) { var c = b.items.length; return a > c - 1 ? a - c : 0 > a ? c + a : a }, T = function(a, b, c) { return a.replace(/%curr%/gi, b + 1).replace(/%total%/gi, c) }; a.magnificPopup.registerModule("gallery", { options: { enabled: !1, arrowMarkup: '<button title="%title%" type="button" class="mfp-arrow mfp-arrow-%dir%"></button>', preload: [0, 2], navigateByImgClick: !0, arrows: !0, tPrev: "Previous (Left arrow key)", tNext: "Next (Right arrow key)", tCounter: "%curr% of %total%" }, proto: { initGallery: function() { var c = b.st.gallery, e = ".mfp-gallery"; return b.direction = !0, c && c.enabled ? (f += " mfp-gallery", w(m + e, function() { c.navigateByImgClick && b.wrap.on("click" + e, ".mfp-img", function() { return b.items.length > 1 ? (b.next(), !1) : void 0 }), d.on("keydown" + e, function(a) { 37 === a.keyCode ? b.prev() : 39 === a.keyCode && b .next() }) }), w("UpdateStatus" + e, function(a, c) { c.text && (c.text = T(c.text, b.currItem.index, b.items.length)) }), w(l + e, function(a, d, e, f) { var g = b.items.length; e.counter = g > 1 ? T(c.tCounter, f.index, g) : "" }), w("BuildControls" + e, function() { if (b.items.length > 1 && c.arrows && !b.arrowLeft) { var d = c.arrowMarkup, e = b.arrowLeft = a(d.replace(/%title%/gi, c.tPrev).replace( /%dir%/gi, "left")).addClass(s), f = b.arrowRight = a(d.replace(/%title%/gi, c.tNext) .replace(/%dir%/gi, "right")).addClass(s); e.click(function() { b.prev() }), f.click(function() { b.next() }), b.container.append(e.add(f)) } }), w(n + e, function() { b._preloadTimeout && clearTimeout(b._preloadTimeout), b ._preloadTimeout = setTimeout(function() { b.preloadNearbyImages(), b._preloadTimeout = null }, 16) }), void w(h + e, function() { d.off(e), b.wrap.off("click" + e), b.arrowRight = b.arrowLeft = null })) : !1 }, next: function() { b.direction = !0, b.index = S(b.index + 1), b.updateItemHTML() }, prev: function() { b.direction = !1, b.index = S(b.index - 1), b.updateItemHTML() }, goTo: function(a) { b.direction = a >= b.index, b.index = a, b.updateItemHTML() }, preloadNearbyImages: function() { var a, c = b.st.gallery.preload, d = Math.min(c[0], b.items.length), e = Math.min(c[1], b.items.length); for (a = 1; a <= (b.direction ? e : d); a++) b._preloadItem(b.index + a); for (a = 1; a <= (b.direction ? d : e); a++) b._preloadItem(b.index - a) }, _preloadItem: function(c) { if (c = S(c), !b.items[c].preloaded) { var d = b.items[c]; d.parsed || (d = b.parseEl(c)), y("LazyLoad", d), "image" === d.type && (d.img = a('<img class="mfp-img" />').on("load.mfploader", function() { d.hasSize = !0 }).on("error.mfploader", function() { d.hasSize = !0, d.loadError = !0, y("LazyLoadError", d) }).attr("src", d.src)), d.preloaded = !0 } } } }); var U = "retina"; a.magnificPopup.registerModule(U, { options: { replaceSrc: function(a) { return a.src.replace(/\.\w+$/, function(a) { return "@2x" + a }) }, ratio: 1 }, proto: { initRetina: function() { if (window.devicePixelRatio > 1) { var a = b.st.retina, c = a.ratio; c = isNaN(c) ? c() : c, c > 1 && (w("ImageHasSize." + U, function(a, b) { b.img.css({ "max-width": b.img[0].naturalWidth / c, width: "100%" }) }), w("ElementParse." + U, function(b, d) { d.src = a.replaceSrc(d, c) })) } } } }), A() });
</script>

<script>
    function changeStatus(val, id) {
        $.ajax({
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('change_order') }}',
            data: {
                status_name: val,
                order_id: id
            },
            success: function(response) {
                if (response) {
                    var data = jQuery.parseJSON(response);
                    if (data['info'] != 1) {
                        alert('Ошибка обновления статуса');
                    } else {
                        alert(data['success']);
                    }

                }
            }
        });
    }


    function change_pdf_locale(locale, userId) {
        $.ajax({
            type: 'POST',
            url: '{{ route('change_pdf_locale') }}',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                pdf_locale: locale,
                user_id: userId
            },
            success: function(response) {
                if (response.info === 1) {
                    alert(response.success);
                } else {
                    alert(response.success || 'Ошибка обновления языка счета');
                }
            },
            error: function(xhr) {
                let msg = 'Произошла ошибка.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    const first = Object.keys(errors)[0];
                    msg = errors[first][0];
                }
                alert(msg);
            }
        });
    }

    function changePainterSketchImagesStatus(val, id) {
        $.ajax({
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('changePainterSketchImagesStatus') }}',
            data: {
                status_name: val,
                order_id: id
            },
            success: function(response) {

                if (response) {
                    var data = jQuery.parseJSON(response);
                    if (data['info'] != 1) {
                        alert('Ошибка обновления статуса');
                    } else {
                        alert(data['success']);
                    }

                }
            }
        });
    }

    function changePainterImagesStatus(val, id) {
        $.ajax({
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('changePainterImagesStatus') }}',
            data: {
                status_name: val,
                order_id: id
            },
            success: function(response) {

                if (response) {
                    var data = jQuery.parseJSON(response);
                    if (data['info'] != 1) {
                        alert('Ошибка обновления статуса');
                    } else {
                        alert(data['success']);
                    }

                }
            }
        });
    }

    function changePaymentStatus(val, id) {

        $.ajax({
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('change_order_payment') }}',
            data: {
                status_name: val,
                order_id: id
            },
            success: function(response) {
                if (response) {
                    var data = jQuery.parseJSON(response);
                    if (data['info'] != 1) {
                        alert('Ошибка обновления статуса');
                    } else {
                        alert(data['success']);
                        location.reload();
                    }
                }
            }
        });
    }

    function changeClientStatus(status_id, user_id) {
        $.ajax({
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('change_client_status') }}',
            data: {
                status_id: status_id,
                user_id: user_id
            },
            success: function(response) {

                if (response) {
                    var data = jQuery.parseJSON(response);
                    if (data['info'] != 1) {
                        alert('Ошибка обновления статуса');
                    } else {
                        alert(data['success']);
                    }
                }
            }
        });
    }

    function deleteOrder(id) {
        $.ajax({
            type: 'post',
            dataType: 'html',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('delete_order') }}',
            data: {
                id: id
            },
            success: function(response) {

                if (response) {
                    var data = jQuery.parseJSON(response);

                    if (data['Error']) {
                        alert(data['Error']);
                    } else if (data['success']) {
                        $('.odd-id-' + id).remove();
                    }
                }
            }
        });
    }

    function aproveOrder(id, btn) {
        $.ajax({
            type: 'post',
            dataType: 'html',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('approve_order') }}',
            data: {
                id: id
            },
            success: function(response) {

                if (response) {
                    var data = jQuery.parseJSON(response);

                    if (data['Error']) {
                        alert(data['Error']);
                    } else if (data['success']) {
                        var parentBtn = $(btn).parent().parent();
                        $('.status', parentBtn).text('completed');
                        $(btn).remove();
                    }
                }
            }
        });
    }

    function setNaklNum(order_id, num) {
        $.ajax({
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('change_order_vrv') }}',
            data: {
                order_id: order_id,
                num: num
            },
            success: function(response) {

                if (response) {
                    var data = jQuery.parseJSON(response);
                    if (data['info'] != 1) {
                        alert('Ошибка обновления статуса');
                    } else {
                        alert(data['success']);
                        if (data['vr_id']) {
                            location.reload();
                        }
                    }
                }
            }
        });
    }

</script>
<link rel="stylesheet" href="/voyager/orders.css?v=1.2">
<script>
    // new
    $(document).on("click", ".curier-call-btn", function(e) {
        e.preventDefault();
        $(".order__courier").stop().slideToggle("fast");
    });

    $('.js_show_chat').magnificPopup({
        type: 'inline',
        preloader: false,
        duration: 300, // duration of the effect, in milliseconds
        mainClass: 'mfp-fade',
        removalDelay: 300,
        callbacks: {
            beforeOpen: function() {}
        }
    });

    $(document).on("click", ".js_vr_sbm", function(e) {
        e.preventDefault();
        console.log('click');

        var order_id = $(this).data('id');
        var num = $(this).closest('div').find('.js_vr_num').val();
        if (num == '' || num == null) return;
        $.ajax({
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('update_vr_num') }}',
            data: {
                order_id: order_id,
                num: num
            },
            success: function(response) {

                if (response) {
                    var data = jQuery.parseJSON(response);
                    if (data['info'] != 1) {
                        alert('Ошибка обновления статуса');
                    } else {
                        alert(data['success']);
                        if (data['vr_id']) {
                            // $('#order_vr_'+order_id).text(data['vr_id']);
                            location.reload();
                        }
                    }
                }
            }
        });
    });

    $(document).on("click", ".js_vr_rem", function(e) {
        e.preventDefault();

        var order_id = $(this).data('id');
        $.ajax({
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('rem_vr_num') }}',
            data: {
                order_id: order_id,
            },
            success: function(response) {

                if (response) {
                    var data = jQuery.parseJSON(response);
                    if (data['info'] != 1) {
                        alert('Ошибка обновления статуса');
                    } else {
                        alert(data['success']);
                        location.reload();
                    }
                }
            }
        });
    });

    $(document).on("click", ".js_painter_time", function(e) {
        e.preventDefault();

        var order_id = $(this).data('id');
        var setted_time = $('.when_compl_' + order_id).val();
        setted_time += '';

        if (setted_time != '') {

            $.ajax({
                type: 'post',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('update_painter_time') }}',
                data: {
                    order_id: order_id,
                    setted_time: setted_time,
                },
                success: function(response) {

                    if (response) {
                        var data = jQuery.parseJSON(response);
                        if (data['info'] != 1) {
                            alert('Ошибка обновления');
                        } else {
                            alert(data['success']);
                            // location.reload();
                        }
                    }
                }
            });
        }

    });

    $(document).on("click", ".js_when_send_time", function(e) {
        e.preventDefault();

        var order_id = $(this).data('id');
        var setted_time = $('.when_send_' + order_id).val();
        setted_time += '';

        if (setted_time != '') {

            $.ajax({
                type: 'post',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('update_when_send_time') }}',
                data: {
                    order_id: order_id,
                    setted_time: setted_time,
                },
                success: function(response) {

                    if (response) {
                        var data = jQuery.parseJSON(response);
                        if (data['info'] != 1) {
                            alert('Ошибка обновления');
                        } else {
                            alert(data['success']);
                            // location.reload();
                        }
                    }
                }
            });
        }

    });

    $(document).on("click", ".prepayment_price_send", function(e) {
        e.preventDefault();

        var order_id = $(this).data('id');
        var prepayment_price = $('.js_prepayment_price_' + order_id).val();
        prepayment_price += '';

        if (prepayment_price != '') {

            $.ajax({
                type: 'post',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('update_prepayment_price') }}',
                data: {
                    order_id: order_id,
                    prepayment_price: prepayment_price,
                },
                success: function(response) {

                    if (response) {
                        var data = jQuery.parseJSON(response);
                        if (data['info'] != 1) {
                            alert('Ошибка обновления');
                        } else {
                            alert(data['success']);
                            // location.reload();
                        }
                    }
                }
            });
        }

    });



    $(document).on("change", "#zak_fitler", function(e) {
        $('#zak_filter_select').trigger('submit');
    });


    $(document).on("change", "select[name='nakl_num']", function(e) {
        var cur_val = $(this).find('option:selected').val();
        var order_id = $(this).data('id');
        setNaklNum(order_id, cur_val);
    });

    $(document).on("click", ".eticet-create-btn", function(e) {
        e.preventDefault();
        let id = $(this).data("id");
        $(".eticet__form").hide(0);
        $(id).stop().slideToggle("fast");
        initializeLabelPickupData($(id));
        $("html, body").animate({
            scrollTop: $(id).offset().top - 70
        }, "fast");
    });

    $(document).on("click", ".js_firm", function(e) {
        e.preventDefault();
        $(this).next('form').stop().slideToggle('fast');
    });

    var insurance_dif_coeff = 0.3;
    var taxFree_insurance = 0;

    $(document).on("change", "#insur", function(e) {
        var th = $(this);
        var suma = th.val();
        if (suma > 10) {
            var kaina = ((suma - taxFree_insurance) * insurance_dif_coeff) / 100;
            if (kaina < 0) {
                th.closest("table").find("#calc-re").html("0");
            } else {
                th.closest("table")
                    .find("#calc-res")
                    .html(Math.round(kaina * 100) / 100);
            }
        } else {
            th.closest("table").find("#calc-res").html("0");
        }
    });

    $(document).on("change", ".js_payed_update", function(e) {
        var th = $(this);
        changePaymentStatus(th.find('option:selected').val(), th.data('order'));
    });

    $(document).on("change", "#js_painter_assign", function(e) {
        var order_id = $(this).data('id');
        var painter_id = $(this).find('option:selected').val();


        $.ajax({
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('update_order_painter') }}',
            data: {
                order_id: order_id,
                painter_id: painter_id,
            },
            success: function(response) {

                if (response) {
                    var data = jQuery.parseJSON(response);
                    if (data['info'] != 1) {
                        alert('Ошибка обновления');
                    } else {
                        alert(data['success']);
                        location.reload();
                    }
                }
            }
        });


    });


    $(document).on("change", "#js_printing_assign", function(e) {
        var order_id = $(this).data('id');
        var printing_id = $(this).find('option:selected').val();


        $.ajax({
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('update_printing_order') }}',
            data: {
                order_id: order_id,
                printing_id: printing_id,
            },
            success: function(response) {

                if (response) {
                    var data = jQuery.parseJSON(response);
                    if (data['info'] != 1) {
                        alert('Ошибка обновления');
                    } else {
                        alert(data['success']);
                        location.reload();
                    }
                }
            }
        });


    });

    $(document).on("change", "#painter_payed", function(e) {
        var is_payed = $(this).val();
        var order_id = $(this).data('id');

        $.ajax({
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('update_painter_payed') }}',
            data: {
                order_id: order_id,
                is_payed: is_payed,
            },
            success: function(response) {

                if (response) {
                    var data = jQuery.parseJSON(response);
                    if (data['info'] != 1) {
                        alert('Ошибка обновления');
                    } else {
                        alert(data['success']);
                        // location.reload();
                    }
                }
            }
        });
    });

    $(document).on("change", "#spi", function(e) {
        var show_images = $(this).val();
        var order_id = $(this).data('id');

        $.ajax({
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('show_painter_images') }}',
            data: {
                order_id: order_id,
                show_images: show_images,
            },
            success: function(response) {

                if (response) {
                    var data = jQuery.parseJSON(response);
                    if (data['info'] != 1) {
                        alert('Ошибка обновления');
                    } else {
                        alert(data['success']);
                        // location.reload();
                    }
                }
            }
        });
    });

    $(document).on("click", ".js__close", function(e) {
        $(this).closest(".js__closing__form").stop().slideToggle("fast");
    });

    $(document).on("click", ".js__add__pack", function(e) {
        e.preventDefault();
        var th = $(this);
        var prev = th.parent().prev("p");
        prev.clone().insertAfter(prev);
    });

    function buildWarehouseOptionLabel(option) {
        const city = option.data("city");
        const address = option.val();
        const displayName = option.data("display-name") || $.trim(option.text());

        if (!address) {
            return displayName;
        }

        return [city, address].filter(Boolean).join(", ") + (displayName ? ` (${displayName})` : "");
    }

    function refreshWarehouseOptions(select) {
        select.find("option").each(function() {
            const option = $(this);

            if (!option.val()) {
                return;
            }

            if (!option.attr("data-display-name")) {
                option.attr("data-display-name", $.trim(option.text()));
            }

            option.data("display-name", option.attr("data-display-name"));
            option.text(buildWarehouseOptionLabel(option));
        });
    }

    function selectCurrentPickupPoint(container) {
        const desiredPickup = (
            container.closest(".form__label__cover").find("form").data("current-pickup-title")
            || container.closest(".form__label__cover").find("form").data("current-pickup-address")
            || ""
        ).toString().trim().toLowerCase();

        if (!desiredPickup) {
            return;
        }

        const matchedPickup = container.find(".warehousesSelect option").filter(function () {
            if (!this.value) {
                return false;
            }

            const optionDisplayName = ($(this).data("display-name") || "").toString().trim().toLowerCase();
            const optionText = $(this).text().trim().toLowerCase();
            const optionValue = $(this).val().toString().trim().toLowerCase();

            return optionDisplayName === desiredPickup || optionText === desiredPickup || optionValue === desiredPickup;
        }).first();

        if (matchedPickup.length) {
            container.find(".warehousesSelect").val(matchedPickup.val());
        }
    }

    function initializeLabelPickupData(labelContainer) {
        labelContainer.find(".selectDestination").each(function () {
            if ($(this).val() === "pickup") {
                $(this).trigger("change");
            }
        });
    }

    $(".citysSelect").select2();
    $(".warehousesSelect").each(function() {
        const select = $(this);

        refreshWarehouseOptions(select);
        select.select2();
    });

    $(document).on("change", ".selectDestination", function(e) {
        e.preventDefault();

        const container = $(this).closest("table");

        if ($(this).val() === "pickup") {
            container.find(".pickup_data").show();
            container.find(".address_data").hide();
            container.find(".warehousesSelect").attr("required", true);
            container.find("#g_post").removeAttr("required");
            container.find("#g_city").removeAttr("required");

            container.find("[name='r_country']").trigger("change");
        } else {
            container.find(".pickup_data").hide();
            container.find(".address_data").show();
            container.find(".warehousesSelect").removeAttr("required");
            container.find("#g_post").attr("required", true);
            container.find("#g_city").attr("required", true);
        }
    });

    $(document).on("change", ".citysSelect", function(e) {
        const selectedCity = $(this).val();
        const container = $(this).closest("table");
        const warehousesSelect = container.find(".warehousesSelect");

        if (!warehousesSelect.data("warehouses")) {
            warehousesSelect.data("warehouses", container.find(".warehousesSelect option").clone());
        }

        container.find(".warehousesSelect").empty();

        if (selectedCity === "") {
            warehousesSelect.data("warehouses").each(function () {
                container.find(".warehousesSelect").append($(this).clone());
            });
        } else {
            warehousesSelect.data("warehouses").each(function () {
                if ($(this).data("city") === selectedCity) {
                    container.find(".warehousesSelect").append($(this).clone());
                }
            });
        }

        container.find(".warehousesSelect").trigger("change");
    });

    $(document).on("change", ".warehousesSelect", function(e) {
        const container = $(this).closest("table");
        const selectedOption = container.find(".warehousesSelect option:selected");
        const pickupName = selectedOption.data("pickup-name") || selectedOption.data("display-name") || selectedOption.text();

        container.find("input[name='g_city_pickup']").val(selectedOption.data("city"));
        container.find("input[name='g_post_pickup']").val(selectedOption.data("post"));
        container.find("input[name='g_name_pickup']").val($.trim(pickupName));
        container.find("input[name='g_code_pickup']").val(selectedOption.data("company-code"));
        // $("input[name='g_address_pickup']").val(`${$('.warehousesSelect option:selected').data("city")}, ${$('.warehousesSelect').val()}`);
    });

    $(document).on("change", "[name='r_country']", function(e) {
        const container = $(this).closest("table");

        container.find(".pickup_data").hide();
        container.find(".pickup_data_loader").show();

        $.ajax({
            type: "post",
            dataType: "html",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            },
            url: `/get_towns_for_admin?country=${$(this).val()}`,
            success: function(e) {
                if (e) {
                    const data = JSON.parse(e);
                    container.find(".citysSelect").empty();
                    container.find(".warehousesSelect").empty();

                    if (data.towns && data.towns.length) {
                        container.find(".citysSelect").append(`<option value="">Все города</option>`);

                        data.towns.forEach(town => {
                            container.find(".citysSelect").append(`<option value="${town.city}">${town.city}</option>`);
                        });
                    }

                    if (data.pickup_points && data.pickup_points.length) {
                        container.find(".warehousesSelect").append(`<option value="">Виберите пункт</option>`);

                        data.pickup_points.forEach(wh => {
                            container.find(".warehousesSelect").append(
                                `<option value="${wh.address}" data-post="${wh.zip}" data-city="${wh.city}" data-display-name="${wh.display_name}" data-pickup-name="${wh.name || wh.display_name || ""}" data-company-code="${wh.company_code || wh.code || wh.id || ""}">${wh.display_name}</option>`
                            );
                        });

                        refreshWarehouseOptions(container.find(".warehousesSelect"));
                        container.find(".warehousesSelect").data("warehouses", container.find(".warehousesSelect option").clone());
                    }

                    container.find(".citysSelect").trigger("change");
                    selectCurrentPickupPoint(container);
                    container.find(".warehousesSelect").trigger("change");
                }
            },
            complete: function(e) {
                container.find(".pickup_data").show();
                container.find(".pickup_data_loader").hide();
            }
        })
    });

    $(document).on('submit', '.admin_chat_form', function(e) {
        e.preventDefault();

        var th = $(this).closest('.js_chat__cover');
        var order_id = $(this).find('[name="order_id"]').val();
        var painter_msg = $(this).find('[name="admin_msg"]').val();

        th.find('button').attr('disabled', true);

        $.ajax({
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('update_admin_chat_ajax') }}',
            data: {
                order_id: order_id,
                admin_msg: painter_msg
            },
            success: function(response) {
                th.find('button').removeAttr('disabled');
                if (response) {
                    var data = jQuery.parseJSON(response);
                    if (data['success'] != 1) {
                        alert('Ошибка обновления');
                    } else {
                        var d = new Date();
                        var curr_date = d.getDate();
                        var curr_month = d.getMonth() + 1;
                        var curr_year = d.getFullYear();

                        curr_month = curr_month += '';
                        if (curr_month.length == 1) {
                            curr_month = '0' + curr_month;
                        }
                        var date = curr_year + '/' + curr_month + '/' + curr_date;
                        var you_text = 'Вы';

                        if (th.find('.js_no_msgs').length) {
                            th.find('.js_no_msgs').remove();
                        }
                        th.find('.comments__main__list').append(`<li><div class='comment__user'>${you_text}</div><div class='comment__date'>${date}</div><div class='comment'>${painter_msg}</div></li>`);
                        th.find('.admin_msg').val('');

                        var cb = document.getElementById('chat_box_' + order_id);
                        cb.scrollTop = cb.scrollHeight;
                        alert("Сообщение добавлено");
                    }
                }
            }
        }).done(function() {
            th.find('button').removeAttr('disabled');
        });
    });

    $(document).on('submit', '.js_painter_form', function(e) {
        e.preventDefault();
        var th = $(this).closest('.js_chat__cover');
        var order_id = $(this).find('[name="order_id"]').val();
        var painter_msg = $(this).find('[name="painter_msg"]').val();
        var image_type = $(this).find('[name="image_type"]').val();

        th.find('button').attr('disabled', true);

        $.ajax({
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('update_order_chat_ajax') }}',
            data: {
                order_id: order_id,
                painter_msg: painter_msg,
                image_type: image_type
            },
            success: function(response) {
                th.find('button').removeAttr('disabled');
                if (response) {
                    var data = jQuery.parseJSON(response);
                    if (data['success'] != 1) {
                        alert('Ошибка обновления');
                    } else {
                        var d = new Date();
                        var curr_date = d.getDate();
                        var curr_month = d.getMonth() + 1;
                        var curr_year = d.getFullYear();

                        curr_month = curr_month += '';
                        if (curr_month.length == 1) {
                            curr_month = '0' + curr_month;
                        }

                        var date = curr_year + '/' + curr_month + '/' + curr_date;
                        var you_text = 'Администратор';


                        if (th.find('.js_no_msgs').length) {
                            th.find('.js_no_msgs').remove();
                        }
                        th.find('.comments__main__list').append(`<li><div class='comment__user'>${you_text}</div><div class='comment__date'>${date}</div><div class='comment'>${painter_msg}</div></li>`);
                        th.find('.painter_msg').val('');

                        var cb = document.getElementById('chat_box_' + order_id);
                        cb.scrollTop = cb.scrollHeight;

                    }
                }
            }
        }).done(function() {
            th.find('button').removeAttr('disabled');
        });
    });
    // endnde

	function changeImageStatus(val, id, image_id, routeName) {
		$.ajax({
			type: 'post',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: routeName,
			data: {
				status_name: val,
				order_id: id,
				order_painter_image_id: image_id
			},
			success: function(response) {
				if (response) {
					var data = jQuery.parseJSON(response);
					if (data['info'] != 1) {
						alert('Error status update');
					} else {
						alert("Статус обновлен");
						// window.location.reload();
					}
				}
			}
		});
	}


	function messageAdminRead(element) {
		var chatId = element.getAttribute('data-chat-id');

		$.ajax({
			type: 'post',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "{{ route('new_admin_message_read') }}",
			data: {
				chatId: chatId,
			},
			success: function(response) {
				if (response) {
					var data = jQuery.parseJSON(response);
					if (data['status'] != 1) {

					} else {
						element.classList.remove('active');
					}
				}
			}
		});
	}

    $(document).on('submit', '.send_message', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var th = $(this);

            var order_id = $(this).data('id');
            var route = $(this).data('route');
            var user_comment = $(this).find("[name='user_comment']").val();
            var chat_img_type = $(this).find("[name='chat_img_type']").val();
            var order_painter_image_id = $(this).find("[name='order_painter_image_id']").val();

            var dataToSend = {
                order_id: order_id,
                msg: user_comment,
                order_painter_image_id: order_painter_image_id
            };

            dataToSend[chat_img_type] = 1;

            $.ajax({
                method: 'POST',
                url: '{{ route('new_send_admin_to_client_painter_comments') }}',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: dataToSend,
                dataType: 'json',
                success: function (msg) {
                    // $('.js_spinner').removeClass('spinner');
                    th.find('.painter_btn').removeAttr('disabled');
                    th.find('.js_u_comm_textarea').val('');
                    if (msg.status == 1 || msg.status == true) {
                        var d = new Date();
                        var curr_date = d.getDate();
                        var curr_month = d.getMonth() + 1;
                        var curr_year = d.getFullYear();
                        var curr_hours = d.getHours();
                        var curr_minutes = d.getMinutes();
                        var curr_seconds = d.getSeconds();
                        curr_month = curr_month += '';
                        if (curr_month.length == 1) {
                            curr_month = '0' + curr_month;
                        }

                        // var date = curr_date + '.' + curr_month + '.' + curr_year;
                        var date = curr_year + '-' + curr_month + '-' + curr_date + ' ' + curr_hours + ':' + curr_minutes + ':' + curr_seconds;

                        th.find('.js_order_user_comments').append(`
                        <div class="chat-group">
                            <div class="chat-item">
                                <div class="chat-item__row">
                                    <div class="chat-name">
                                        Вы:								</div>
                                    <div class="chat-date">
                                        ${date}
                                    </div>
                                </div>
                                <div class="chat-item__body">
                                    <p>
                                        ${msg.comment}
                                    </p>
                                </div>
                            </div>
                        </div>

                        `);

                        // var containerHeight = th.find('.chat-scroll__container .scroll-block').height();
                        th.find('.chat-scroll__container .scroll-block').scrollTop(9999);
                        th.find("[name='user_comment']").val("");
                    }


                    // $('.js_spinner').jmspinner(false);
                },
                error: function (jqXHR, exception) {
                    // $('.js_spinner').jmspinner(false);

                }
            })

            return false
        });


        $(document).on('submit', '.js_send_user_add_images', function (e) {
            e.preventDefault();
            var th = $(this);

            // $('.js_spinner').jmspinner('large');
            th.find('.painter_btn').attr('disabled', true);

            let formData = new FormData();
            let order_id = th.find('[name="order_id"]').val();
            let user_comment = th.find('[name="user_comment"]').val();

            if (user_comment == null || user_comment == 'null') {
                user_comment = '';
            }

            formData.append('order_id', order_id);
            formData.append('client_comment', user_comment);
          formData.append("client_images[]", '');

            $.ajax({
                type: 'post',
                dataType: 'json',
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('new_send_admin_to_client_painter_comments') }}',
                data: formData,
                success: function (response) {
                    // $('.js_spinner').removeClass('spinner');
                    th.find('.painter_btn').removeAttr('disabled');
                    th.find('.js_u_comm_textarea').val('');
                    if (response.status == 1 || response.status == true) {
                        var d = new Date();
                        var curr_date = d.getDate();
                        var curr_month = d.getMonth() + 1;
                        var curr_year = d.getFullYear();
                        var curr_hours = d.getHours();
                        var curr_minutes = d.getMinutes();
                        var curr_seconds = d.getSeconds();
                        curr_month = curr_month += '';
                        if (curr_month.length == 1) {
                            curr_month = '0' + curr_month;
                        }

                        // var date = curr_date + '.' + curr_month + '.' + curr_year;
                        var date = curr_year + '-' + curr_month + '-' + curr_date + ' ' + curr_hours + ':' + curr_minutes + ':' + curr_seconds;

                        if (response.comment) {
                            th.find('.js_order_user_comments').append(`
                            <div class="chat-group">
                                <div class="chat-item">
                                    <div class="chat-item__row">
                                        <div class="chat-name">
                                            {{-- @if(Auth::user()->id==$u_comment->user_id) --}}
                                                @lang("account_new.orders.chat.you")
                                            {{-- @else --}}
                                                {{-- @lang("account_new.orders.chat.admin") --}}
                                            {{-- @endif --}}
                                        </div>
                                        <div class="chat-date">
                                            ${date}
                                        </div>
                                    </div>
                                    <div class="chat-item__body">
                                        <p>
                                            ${response.comment}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            `);
                        }

                        // var containerHeight = th.find('.chat-scroll__container .scroll-block').height();
                        th.find('.chat-scroll__container .scroll-block').scrollTop(9999);

                        // var containerHeight = th.find('.user_comment').height();


                        if (response.images) {
                            // th.next('.js_user_all_images').find(
                            // 	'.user__painter__imgs__list').children().remove();
                            // var cur_images = response.images.split(",");

                            // for (let index = 0; index < cur_images.length; index++) {
                            // 	const el = cur_images[index];
                            // 	th.next('.js_user_all_images').find(
                            // 		'.user__painter__imgs__list').append(`
                            // 		<li>
                            // 			<a href="${el}" target="_blank">
                            // 				<img src="${el}" alt="" class="img__user_upl">
                            // 			</a>
                            // 		</li>
                            // 	`);

                            // }

                            if (!response.comment) {
                                window.location.reload();
                            }
                        }
                    } else {
                        alert('Error');
                    }

                    th.trigger('reset');
                    document.getElementById("js_painter_images_" + order_id).value = "";
                    // $('.js_spinner').jmspinner(false);
                },
                error: function (error) {
                    // $('.js_spinner').removeClass('spinner');
                    th.find('.painter_btn').removeAttr('disabled');
                    th.trigger('reset');
                    document.getElementById("js_painter_images_" + order_id).value = "";
                    // $('.js_spinner').jmspinner(false);
                    console.log(error);
                }
            });

        });

        function admin_messageRead(element) {
            var chatId = element.getAttribute('data-chat-id');

            $.ajax({
                type: 'post',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('admin_to_client_message_read') }}",
                data: {
                    chatId: chatId,
                },
                success: function(response) {
                    if (response) {
                        var data = jQuery.parseJSON(response);
                        if (data['status'] != 1) {

                        } else {
                            element.classList.remove('active');
                        }
                    }
                }
            });
        }


</script>
<style>
    b,
    optgroup,
    strong {
        font-weight: bold;
    }

    .js_firm_form .form_group {
        text-align: left;
    }

    .js_firm_form {
        max-width: 137px;
    }

    .n_btns_cover {
        display: flex;
        justify-content: space-between;
        display: grid;
        grid-gap: 10px;
        grid-template-columns: 1fr 1fr;
    }

    @media (max-width: 767px) {
        .n_btns_cover {
            grid-template-columns: minmax(0, 1fr) 42px;
            grid-gap: 8px;
        }

        .js_vr_sbm {
            width: 100%;
            max-width: 100%;
        }

        .js_firm {
            width: 100%;
            max-width: 100%;
        }

        .js_vr_rem {
            width: 42px;
            min-width: 42px;
            height: 42px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            justify-self: start;
            font-size: 18px;
            line-height: 1;
        }
    }

    .js_chat__cover {
        overflow: hidden;
        clear: both;
        padding: 15px;
        position: fixed;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        background: #fff;
        border-radius: 5px;
        min-width: 320px;
        max-width: 600px;
        width: 100%;
    }

    .comments__main__list li {
        padding: 5px;
        border-bottom: 1px solid #ebebeb;
    }

    .comment__date {
        float: right;
        font-weight: 500;
        font-size: 1em;
    }

    .ct-row p {
        font-weight: 500;
        font-size: 16px;
        line-height: 19px;
        color: #000000;
        margin: 0;
    }

    .ct-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .activeSpan {
        color: #fa7846;
    }

    .pt-3{
        padding-top: 10px;
    }

    .pt-4{
        padding-top: 15px;
    }

    .pt-5{
        padding-top: 20px;
    }

    .mb-0{
        margin-bottom: 0px!important;
    }

    .mb-2{
        margin-bottom: 5px!important;
    }

    .mb-3{
        margin-bottom: 10px!important;
    }

    .mb-4{
        margin-bottom: 15px!important;
    }

    .mb-5{
        margin-bottom: 20px!important;
    }

    .chat-container {
      background: #ffffff;
      border: 0.5px solid #848484;
      border-radius: 15px;
      padding: 15px 20px 25px;
      width: 100%;
      font-weight: 400;
      font-size: 16px;
      line-height: 19px;
    }
    .chat-scroll__container {
      position: relative;
    }
    .chat-scroll__container .scroll-up {
      top: -20px;
    }
    .chat-scroll__container .scroll-down {
      bottom: -22px;
    }
    .chat-inner {
      height: 188px;
      overflow-x: hidden;
      opacity: 1;
      transition: opacity 0.2s ease;
      margin: 10px 0;
      padding-right: 21px;
    }
    .chat-inner__block > *:not(:last-child) {
      margin-bottom: 10px;
    }
    .chat-item__row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 10px;
    }
    .chat-item {
      color: #848484;
    }
    .chat-item.active {
      color: #1e2533;
      font-weight: 600;
    }
    .chat-item__body {
      max-width: 520px;
      font-style: italic;
    }
    .chat-item__body > p {
      padding-left: 15px;
    }
    .chat-group > *:not(:last-child) {
      margin-bottom: 10px;
      padding-bottom: 0;
      border-bottom: none;
    }

    .chat-name {
      font-weight: 500;
      color:#1e2533;
    }
    .chat-date {
      text-align: right;
      font-style: italic;
      color: #1e2533;
      margin-left: auto;
    }
    .chat-item {
      padding-bottom: 10px;
      border-bottom: 0.5px solid #848484;
    }
    .chat-input {
      display: flex;
      flex-direction: row;
      gap: 10px;
      align-items: center;
    }
    .chat-input textarea {
      width: 100%;
      height: 76px;
      resize: none;
      border: none;
      outline: none;
      background: transparent;
      color: #848484;
      font-style: italic;
      font-weight: 400;
      font-size: 16px;
      line-height: 19px;
      padding-left: 15px;
      padding-top: calc((76px - 19px) / 2);
    }
    .chat-input textarea::placeholder,
    .chat-input textarea::-moz-placeholder,
    .chat-input textarea::-webkit-input-placeholder {
      color: #848484;
      font-style: italic;
      font-weight: 400;
      font-size: 16px;
      line-height: 19px;
    }
    .chat-input button {
      background: unset;
      font-size: 18px;
      line-height: 35px;
      text-align: center;
      color: #fa7846;
      cursor: pointer;
      float: right;
      margin-right: 18px;
      white-space: nowrap;
    }

    .img__place {
      border: 1.5px dashed #fa7846;
      border-radius: 8px;
      width: 100%;
      max-width: 140px;
      padding: 15px;
      min-height: 60px;
      height: 140px;

      pointer-events: none;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 4px;
    }

    .drop-image {
      display: flex;
      position: relative;
      text-align: center;
      width: fit-content;
    }
    .drop-image-title {
      font-size: 15px;
      line-height: 18px;
      color: #1e2533;
    }
    .drop-image p {
      font-size: 10px;
      line-height: 12px;
      letter-spacing: 0.02em;
      color: #9ca5b5;
    }
    .drop-image input {
      opacity: 0;
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 1;
    }
    .image-absoluteContainer {
      position: absolute;
      top: 0;
      left: 0;
      display: flex;
      flex-wrap: nowrap;
      gap: 10px;
    }
    .uploaded-image-styles {
      width: 115px;
      height: 140px;
      border-radius: 15px;
      overflow: hidden;
      position: relative;
    }
    .uploaded-image-styles img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .cabinet-btn {
      background: #fa7846;
      border-radius: 8px;
      font-weight: 700;
      font-size: 16px;
      line-height: 19px;
      display: flex;
      align-items: center;
      text-align: center;
      justify-content: center;
      color: #ffffff;
      height: 65px;
      min-width: 270px;
      padding: 0 20px;
      width: fit-content;
      margin-left: auto;
      cursor: pointer;
    }

    .uploaded-image-styles span {
      position: absolute;
      text-indent: -9999px;
      right: 10px;
      top: 10px;
      background: url(/img/delete-img.png) center no-repeat;
      background-size: 100%;
      border: none;
      height: 10px;
      width: 10px;
      display: block;
      opacity: 0;
      transition: all 0.3s ease;
      cursor: pointer;
    }
    .uploaded-image-styles:hover span {
      opacity: 1;
    }
    .s-row {
      display: flex;
      align-items: center;
      gap: 20px;
      width: fit-content;
      margin-left: auto;
    }
    .download-account {
      font-size: 18px;
      line-height: 25px;
      text-decoration-line: underline;
      color: #fa7846;
    }
    .loaded-image--scroll {
      position: relative;
      height: 145px;
      overflow-y: hidden;
      padding-bottom: 5px;
    }
    .complete-loadedImg {
      position: relative;
      height: 202px;
      overflow-y: hidden;
      padding-bottom: 5px;
    }
    .complete-loadedImg .img {
      width: 152px;
      height: 200px;
      object-fit: cover;
    }
    .my-loadedImages {
      display: grid;
      grid-gap: 20px;
    }
    .loadedInner {
      display: none;
    }
    .images-preview-on {
      display: block;
    }

    /*  */

    .cabinet-sketch__item:not(:last-child) {
      margin-bottom: 50px;
    }
    .sketch-grid {
      display: grid;
      grid-template-columns: 210px 1fr;
      grid-gap: 21px;
    }
    .cabinet-sketch__item > .ct-row {
      margin-top: 40px;
      margin-bottom: 10px;
    }
    .sketch-image {
      position: relative;
      border: 0.5px solid #fc8c5f;
      border-radius: 5px;
      height: 280px;
      background: white;
      display: flex;
      align-items: center;
      justify-content: center;
      max-width: 210px;
    }
    .sketch-image > span {
      font-family: "Trajan";
      font-style: normal;
      font-weight: 600;
      font-size: 24px;
      line-height: 36px;
      color: #fc8c5f;
      position: absolute;
      top: -17px;
      left: 7px;
    }
    .sketch-image .overlay {
      display: none;
    }
    .sketch-initial {
      padding: 15px;
    }
    .sketch-initial .sketchImg {
      width: 100%;
      height: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
    }
    .sketch-initial .sketchImg img {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
      margin: auto;
    }
    .btn-group {
      display: flex;
      flex-wrap: wrap;
      gap: 37px;
    }
    .btn-group .btn {
      margin: 0;
    }
    .sketch-success .success {
      display: flex;
    }
    .sketch-pending .pending {
      display: flex;
    }
    .overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(30, 37, 51, 0.75);
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 5px;
    }
    .overlay p {
      font-family: "Trajan";
      font-style: normal;
      font-weight: 600;
      font-size: 24px;
      line-height: 36px;
      text-align: center;
    }
    .pending > p {
      color: #fa7846;
    }
    .success > p {
      color: #02bc4d;
    }
    .pic-img img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .chatSend {
        border: 1px solid;
        padding: 4px;
        /* width: 400px; */
        text-align: center;
        border-radius: 5px;
    }

    .c-elementTitle {
        font-weight: 600;
        font-size: 20px;
        line-height: 24px;
        color: #1e2533;
        margin-bottom: 20px;
    }


    </style>

<script>
    /**
     * SA Polling interval: pulls new messages automatically without page reload
     */
    setInterval(function() {
        var openChat = $('.js_chat__cover:visible');
        if (openChat.length && openChat.attr('id') && openChat.attr('id').startsWith('sa_chat_')) {
            var orderId = openChat.attr('id').replace('sa_chat_', '');
            
            // find real last msg id
            var lastId = 0;
            $('#sa_chat_messages_' + orderId + ' .chat-group').each(function() {
                var id = parseInt($(this).attr('data-msg-id'));
                if (!isNaN(id) && id > lastId) {
                    lastId = id;
                }
            });
            
            $.ajax({
                url: '{{ route("admin.sa.messages") }}',
                type: 'GET',
                data: {
                    order_id: orderId,
                    last_id: lastId
                },
                success: function(res) {
                    if (res.status === 'ok') {
                        // update bot mode label
                        var label = $('#sa_bot_mode_label_' + orderId);
                        if (label.length && res.bot_mode) {
                            var b = res.bot_mode;
                            if (b === 'active') label.text('ACTIVE').css('color', 'green');
                            else if (b === 'paused') label.text('PAUSED').css('color', 'orange');
                            else if (b === 'handoff_to_manager') label.text('HANDOFF_TO_MANAGER').css('color', 'red');
                            else label.text(b.toUpperCase()).css('color', 'black');
                        }

                        // append new messages
                        if (res.messages && res.messages.length > 0) {
                            // clear temporary UI bubbles since real messages arrived
                            $('#sa_chat_messages_' + orderId + ' .chat-group[data-msg-id="temp"]').remove();

                            res.messages.forEach(function(msg) {
                                if ($('#sa_chat_messages_' + orderId + ' .chat-group[data-msg-id="'+msg.id+'"]').length > 0) return;

                                var sender = msg.direction === 'inbound' ? 'Клиент' : 'Менеджер/Бот';
                                var safeText = (msg.text || '').replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
                                var escText = safeText.replace(/\n/g, "<br>");
                                
                                var statusHtml = '';
                                if (msg.direction === 'outbound') {
                                    if (msg.status === 'sent') statusHtml = '<i class="voyager-paper-plane"></i> Отправлено';
                                    else if (msg.status === 'delivered') statusHtml = '<i class="voyager-double-check"></i> Доставлено';
                                    else if (msg.status === 'read') statusHtml = '<i class="voyager-double-check" style="color: #34b7f1;"></i> Прочитано';
                                    else if (msg.status === 'failed') statusHtml = '<i class="voyager-x" style="color: red;"></i> Ошибка';
                                    else statusHtml = '(' + msg.status + ')';
                                }

                                var html = `
                                <div class="chat-group" data-msg-id="${msg.id}">
                                    <div class="chat-item">
                                        <div class="chat-item__row">
                                            <div class="chat-name">${sender}</div>
                                            <div class="chat-date">${msg.sent_at} 
                                                <span style="margin-left: 10px; font-weight: bold; color: #555;">
                                                    ${statusHtml}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="chat-item__body">
                                            <p>${escText}</p>
                                        </div>
                                    </div>
                                </div>`;
                                $('#sa_chat_messages_' + orderId).append(html);
                            });
                        }
                    }
                }
            });
        }
    }, 5000);

    /**
     * AJAX handler to change the bot mode (Pause, Resume, Handoff)
     */
    function setSaBotMode(action, orderId) {
        if (!confirm('Вы уверены, что хотите выполнить это действие?')) return;
        
        $.ajax({
            type: 'POST',
            url: '{{ route("admin.sa.bot_control") }}',
            data: {
                _token: '{{ csrf_token() }}',
                order_id: orderId,
                action: action
            },
            success: function(response) {
                if (response.status === 'ok') {
                    var label = $('#sa_bot_mode_label_' + orderId);
                    if (label.length) {
                        if (action === 'resume_bot') {
                            label.text('ACTIVE').css('color', 'green');
                        } else if (action === 'pause_bot') {
                            label.text('PAUSED').css('color', 'orange');
                        } else if (action === 'handoff_to_manager') {
                            label.text('HANDOFF_TO_MANAGER').css('color', 'red');
                        }
                    }

                    if (typeof toastr !== 'undefined') {
                        toastr.success('Режим бота успешно изменен.');
                    } else {
                        alert('Режим бота успешно изменен.');
                    }
                } else {
                    alert('Ошибка: ' + (response.message || 'Неизвестная ошибка'));
                }
            },
            error: function(xhr) {
                alert('Ошибка при выполнении запроса: ' + xhr.statusText);
            }
        });
    }

    /**
     * AJAX handler to send a message from the admin UI
     */
    function sendSaMessage(orderId) {
        var textUrl = $('#sa_message_text_' + orderId).val();
        var handoff = $('#sa_message_handoff_' + orderId).is(':checked') ? 1 : 0;

        if (!textUrl.trim()) {
            alert('Введите текст сообщения');
            return;
        }

        var btn = $('#sa_chat_block_' + orderId + ' .btn-success');
        btn.prop('disabled', true).text('Отправка...');

        $.ajax({
            type: 'POST',
            url: '{{ route("admin.sa.send_message") }}',
            data: {
                _token: '{{ csrf_token() }}',
                order_id: orderId,
                text: textUrl,
                handoff: handoff
            },
            success: function(response) {
                if (response.status === 'ok') {
                    $('#sa_message_text_' + orderId).val('');
                    
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Сообщение отправлено!');
                    } else {
                        alert('Сообщение отправлено!');
                    }
                    
                    btn.prop('disabled', false).html('<i class="voyager-paper-plane"></i> Отправить в WA');
                    
                    // Append HTML bubble
                    var safeText = textUrl.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
                    var escText = safeText.replace(/\n/g, "<br>");
                    
                    var html = `
                    <div class="chat-group" data-msg-id="temp">
                        <div class="chat-item">
                            <div class="chat-item__row">
                                <div class="chat-name">Менеджер/Бот</div>
                                <div class="chat-date">Только что 
                                    <span style="margin-left: 10px; font-weight: bold; color: #555;">
                                        <i class="voyager-paper-plane"></i> Отправлено
                                    </span>
                                </div>
                            </div>
                            <div class="chat-item__body">
                                <p>${escText}</p>
                            </div>
                        </div>
                    </div>`;
                    $('#sa_chat_messages_' + orderId).append(html);
                    
                    if (handoff) {
                        var label = $('#sa_bot_mode_label_' + orderId);
                        if(label.length) {
                             label.text('HANDOFF_TO_MANAGER').css('color', 'red');
                        }
                    }

                } else {
                    alert('Ошибка отправки: ' + (response.message || 'Неизвестная ошибка'));
                    btn.prop('disabled', false).html('<i class="voyager-paper-plane"></i> Отправить в WA');
                }
            },
            error: function(xhr) {
                alert('Ошибка при отправке: ' + xhr.statusText);
                btn.prop('disabled', false).text('Отправить');
            }
        });
    }

    $(document).on('click', '.js-copy-payment-request-link', function () {
        var copyLink = $(this).data('copy-link');
        if (copyLink) {
            var tempInput = document.createElement('input');
            tempInput.value = copyLink;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            return;
        }

        var $input = $(this).closest('.payment-request-quick__item').find('.js-payment-request-link').first();
        if ($input.length) {
            $input.trigger('focus').trigger('select');
            document.execCommand('copy');
        }
    });

    function openPaymentRequestQuickModal(action, orderId) {
        var $form = $('#paymentRequestQuickForm');
        if (!$form.length) {
            return;
        }

        $form.attr('action', action || '');
        $('#payment_request_context_order_id').val(orderId || '');
        $('#paymentRequestQuickModal').modal('show');
    }

    $(document).on('click', '.js-open-payment-request-modal', function () {
        openPaymentRequestQuickModal($(this).data('action'), $(this).data('order-id'));
    });

    $('#paymentRequestQuickModal').on('shown.bs.modal', function () {
        $('.modal-backdrop').last().addClass('payment-request-modal-backdrop');
    });

    $('#paymentRequestQuickModal').on('hidden.bs.modal', function () {
        $('.modal-backdrop.payment-request-modal-backdrop').removeClass('payment-request-modal-backdrop');
    });

    @if(old('context_order_id') && ($errors->has('amount') || $errors->has('purpose')))
        $(function () {
            var orderId = '{{ old('context_order_id') }}';
            var $trigger = $('.js-open-payment-request-modal[data-order-id="' + orderId + '"]').first();
            if ($trigger.length) {
                openPaymentRequestQuickModal($trigger.data('action'), orderId);
            }
        });
    @endif
</script>
