$(document).ready(function() {

    let portraitsSlider = $(".examples-portrait__slider");
    portraitsSlider.slick({

        infinite: false,

        slidesToShow: 4,

        lazyLoad: "ondemand",

        prevArrow: ".examples-prev.examples-prev1",

        nextArrow: ".examples-next.examples-next1",

        responsive: [

            {

                breakpoint: 1350,

                settings: {

                    slidesToShow: 3,

                },

            },

            {

                breakpoint: 980,

                settings: {

                    slidesToShow: 2,

                },

            },

            {

                breakpoint: 702,

                settings: {

                    slidesToShow: 1,

                },

            },

        ],

    });

  function e() {
      let e = $(".popup-photo input[name='new_price']").val(),
          t = $(".popup-photo input[name='new_people_count_price']").val();
      t || (t = 0);
      let a = parseFloat(e) + parseFloat(t);
      $(".kviz-price span").text(a), $(".popup-photo input[name='overall_price']").val(a)
  }

  function t() {
      let e = $("#image-input");
      e.val(JSON.stringify(loadedImages))
  }

  function a(e) {
      let t = e.split(";base64,"),
          a = t[0].split(":")[1],
          n = atob(t[1]),
          s = new ArrayBuffer(n.length),
          r = new Uint8Array(s);
      for (let o = 0; o < n.length; o++) r[o] = n.charCodeAt(o);
      return new Blob([s], {
          type: a
      })
  }(dataCoutry = $("#country_block .select__option.selected").data("value")) && m(dataCoutry), $(".js-fast_order").click(function(e) {
      e.preventDefault(), $(".popup-fit").fadeOut(), $(".popup-photo").delay(500).fadeIn()
  }), $(".js-detail_order").click(function(e) {
      $(".popup-frame").fadeOut(), $(".js-popup").fadeOut(), $("body").removeClass("open-frame"), document.querySelector("#generator").scrollIntoView({
          behavior: "smooth"
      })
  }), $(".js-examples").click(function(t) {
      t.preventDefault();
      let a = $(this).data("catid"),
          n = $(this).data("size"),
          s = $(this).data("url");
      if ($("body").addClass("open-frame"), $(".popup-frame").css("display", "flex").hide().fadeIn(), $(".popup-fit").fadeIn(), s ? $(".js-detail_order").attr("href", s) : $(".js-detail_order").attr("href", $(".portraits-btn__style").attr("href")), a) {
          if ($(".popup-photo .select-style option[data-catid='" + a + "']").prop("selected", "selected"), $(".select-size").css("display", "none"), $(`.select-size[data-catid='${a}']`).css("display", "block"), $(".popup-photo input[name='new_catid']").val(a), n) {
              n = (n = (n = n.substring(0, n.length - 2)).trim()).split("х"), $(`.select-size[data-catid='${a}']`).find("option[value='" + n[0] + "x" + n[1] + "']").prop("selected", "selected");
              let r = $(`.select-size[data-catid='${a}']`).find("option:selected").data("price");
              $(".popup-photo input[name='new_size']").val(n[0] + "x" + n[1]), $(".popup-photo input[name='new_price']").val(r), e()
          } else {
              n = $(`.select-size[data-catid='${a}']`).find("option:selected").val();
              let o = $(`.select-size[data-catid='${a}']`).find("option:selected").data("price");
              $(".popup-photo input[name='new_size']").val(n), $(".popup-photo input[name='new_price']").val(o), e()
          }
          if ($(`.kviz-popup--portrait select[data-catid='${a}']`).length > 0) {
              $(".popup-photo").addClass("portrait-popup"), $(".kviz-popup--portrait select").css("display", "none"), $(`.kviz-popup--portrait select[data-catid='${a}']`).css("display", "block");
              let p = $(`.kviz-popup--portrait select[data-catid='${a}'] option:selected`).val(),
                  c = $(`.kviz-popup--portrait select[data-catid='${a}'] option:selected`).data("count-price");
              $(".popup-photo input[name='new_people_count']").val(p), $(".popup-photo input[name='new_people_count_price']").val(c), e()
          } else $(".popup-photo").removeClass("portrait-popup"), $(".kviz-popup--portrait select").css("display", "none"), $(".popup-photo input[name='new_people_count']").val(null), $(".popup-photo input[name='new_people_count_price']").val(null), e()
      }
  }), $(".select-size[data-catid='1']").css("display", "block"), $(".select-style").on("change", function() {
      let t = $(this).find(":selected").data("catid");
      $(".select-size[data-catid]").css("display", "none"), $(`.select-size[data-catid='${t}']`).css("display", "block"), $(".popup-photo input[name='new_catid']").val(t);
      let a = $(`.select-size[data-catid='${t}'] option:selected`).data("price"),
          n = $(`.select-size[data-catid='${t}'] option:selected`).val();
      if (n = n.trim(), $(".popup-photo input[name='new_size']").val(n), $(".popup-photo input[name='new_price']").val(a), e(), $(`.kviz-popup--portrait select[data-catid='${t}']`).length > 0) {
          $(".popup-photo").addClass("portrait-popup"), $(".kviz-popup--portrait select").css("display", "none"), $(`.kviz-popup--portrait select[data-catid='${t}']`).css("display", "block");
          let s = $(`.kviz-popup--portrait select[data-catid='${t}'] option:selected`).val(),
              r = $(`.kviz-popup--portrait select[data-catid='${t}'] option:selected`).data("count-price");
          $(".popup-photo input[name='new_people_count']").val(s), $(".popup-photo input[name='new_people_count_price']").val(r), e()
      } else $(".popup-photo").removeClass("portrait-popup"), $(".kviz-popup--portrait select").css("display", "none"), $(".popup-photo input[name='new_people_count']").val(null), $(".popup-photo input[name='new_people_count_price']").val(null), e()
  }), $(".select-count").on("change", function() {
      let t = $(this).find(":selected").val(),
          a = $(this).find(":selected").data("count-price");
      $(".popup-photo input[name='new_people_count']").val(t), $(".popup-photo input[name='new_people_count_price']").val(a), e()
  }), $(".select-size").on("change", function() {
      let t = $(this).find(":selected").data("price"),
          a = $(this).find(":selected").val();
      a = a.trim(), $(".popup-photo input[name='new_size']").val(a), $(".popup-photo input[name='new_price']").val(t), e()
  }), window.loadedImages = [];
  let n = $(".file-input"),
      s = n.val();
  s && (loadedImages = JSON.parse(s)), $(".file-input").on("change", function(e) {
      let n = $(this).closest(".formalization-content, .file-box").find(".images-container"),
          s = this.files,
          r = n;
      for (var o = $(this).val().replace(/.*\\/, ""), p = this.files[0].size, c = this, l = 0; p > 900;) p /= 1024, l++;
      for (let d = 0; d < s.length; d++) {
          let u = s[d],
              m = new FileReader;
          m.onload = function(e) {
              let n = e.target.result,
                  s = a(n),
                  o = new File([s], u.name, {
                      type: u.type
                  });
              loadedImages.push(o);
              let p = $("<div>").addClass("image-block").css("background-image", `url(${n})`);
              r.append(p);
              let c = $("<span>").addClass("ab-cross").text("X").click(function() {
                  p.remove();
                  let e = loadedImages.indexOf(o);
                  e > -1 && loadedImages.splice(e, 1)
              });
              p.append(c), t()
          }, m.readAsDataURL(u)
      }
      $(this).get(0).files.length, e.target.files, $(c).prop("readonly", !0), o ? ($(this).closest(".file-save").find(".abs-close").fadeIn(), $(this).siblings(".js-file-preview").hide(), $(this).siblings(".js-file-upload").hide(), $(this).siblings(".js-file-multiple").css({
          display: "flex"
      }), $(this).addClass("file-input_save")) : ($(this).siblings(".js-file-preview").css({
          display: "flex"
      }), $(this).siblings(".js-file-upload").hide(), $(this).siblings(".js-file-multiple").hide(), $(this).removeClass("file-input_save")), $(this).closest(".file-save").find(".abs-close").on("click", function() {
          (function e(t, a) {
              console.log(t), console.log(a);
              let n = $("#image-input");
              n.val(""), window.loadedImages = [], $(".images-container").html(""), $(a).prop("readonly", !1), $(a).val(null), $(t).hide(), $(a).siblings(".js-file-preview").show(), $(a).siblings(".js-file-upload").hide(), $(a).siblings(".js-file-multiple").hide()
          })(this, c)
      })
  }), $(".file-popup").on("change", function(e) {
      let t = $(this).closest(".popup-stock__inner").find(".images-container");
      if (this.files) {
          var a = this.files.length;
          for (i = 0; i < a; i++) {
              var n = new FileReader;
              n.onload = function(e) {
                  t.prepend(`
            <div class="image-block" style="background-image: url(${e.target.result});background-size: cover;">
            <svg class="img-cancel" width="11" height="10" viewBox="0 0 11 10" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="13.0545" height="1.08788" transform="matrix(0.707125 -0.707089 0.707125 0.707089 0 9.23071)" fill="#1E2533"/>
            <rect width="13.0545" height="1.08788" transform="matrix(-0.707125 -0.707089 0.707125 -0.707089 9.23242 10)" fill="#1E2533"/>
            </svg>
            </div>
            `)
              }, n.readAsDataURL(this.files[i])
          }
      }
  }), $(document).on("click", ".img-cancel", function() {
      $(this).parent().remove(), $(".file-popup").val("")
  });
  var r = !1,
      o = !1;

  function p(e) {
      var t = document.cookie.match(RegExp("(?:^|; )" + e.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, "\\$1") + "=([^;]*)"));
      return t ? decodeURIComponent(t[1]) : void 0
  }

  function c(e) {
      e.checkValidity() ? $(e).removeClass("error") : $(e).addClass("error")
  }
  $(".kviz-file-input").change(function() {
      r = !0
  }), $(".kviz-messege__tab").click(function() {
      o = !0
  }), setTimeout(function() {
      $(".loader-canvas button input").on("change", function() {
          var e = $(this).closest(".product-download");
          e.find(".abs-close").fadeIn(), e.find(".js-file-preview").hide(), e.find(".js-file-upload").hide(), e.find(".js-file-multiple").css({
              display: "flex"
          }), $(this).addClass("file-input_save"), e.find(".abs-close").on("click", function() {
              $(this).prop("readonly", !1), $(this).val(null), $(this).hide(), e.find(".js-file-preview").show(), e.find(".js-file-upload").hide(), e.find(".js-file-multiple").hide()
          })
      })
  }, 1e3), $(".why-composition-inner form, .why-form").on("submit", function() {
      $(this).find(".loading-bar").css("display", "flex").hide().fadeIn()
  }), $(".kviz-radio label span img").hover(function() {
      $(this).closest(".kviz-radio").addClass("hovered")
  }, function() {
      $(this).closest(".kviz-radio").removeClass("hovered")
  }), $(".portraits-slider").length > 0 && $(".portraits-slider").slick({
      infinite: !0,
      slidesToShow: 1,
      prevArrow: ".portraits-prev",
      nextArrow: ".portraits-next",
      fade: !0,
      dots: !0,
      autoplay: 1,
      delay: 3e3,
      appendDots: ".portraits-dots",
      responsive: [{
          breakpoint: 700,
          settings: {
              dots: !0
          }
      }, ]
  }),
  $(".portraits-slider").on('mouseover touchend', function() {
    $(this).slick('slickPause');
    $(this).slick('slickPlay');
  }),
  $(".portraits-slider").on('mouseenter touchstart', function() {
      // $(this).slick('slickSetOption', 'autoplaySpeed', 2000, true);
      $(this).slick('slickSetOption', 'autoplay', 1);
  }).on('mouseleave touchend', function() {
      // $(this).slick('slickSetOption', 'autoplaySpeed', 2000, true);
      $(this).slick('slickSetOption', 'autoplay', 1);
  }), document.querySelectorAll('a[href^="#generator"]').forEach(e => {
      e.addEventListener("click", function(e) {
          e.preventDefault(), document.querySelector(this.getAttribute("href")).scrollIntoView({
              behavior: "smooth"
          })
      })
  }), $(".cart-page-item__price").on("click", ".minus, .plus, .delete", function() {
      var e = parseInt($(this).siblings(".count__number").text());
      "plus" == $(this).data("type") && (e += 1), "minus" == $(this).data("type") && (e -= 1);
      var t = e,
          a = $(this).data("id");
      if (0 == e && d(a), 0 != t) {
          var n = new FormData;
          n.append("count", t), n.append("index", a), $.ajax({
              processData: !1,
              contentType: !1,
              type: "POST",
              headers: {
                  "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
              },
              url: "/basket/update/count",
              data: n,
              success: function(e) {
                  if (e) {
                      var t = jQuery.parseJSON(e);
                      t.success && (t.price ? ($("#cart_price_" + a).html(t.price), u()) : ($("#cart_price_" + a).html("0€"), u()))
                  }
              },
              error: function(e) {
                  console.log(e)
              }
          })
      }
  }), $(".cart-page-item").on("click", ".cart_item_delete", function() {
      basketId = $(this).data("id"), d(basketId)
  }), $(document).on("click", ".cart-page-sidebar__certificate--btn", function(e) {
      var t;
      console.log("Применен купон"), e.preventDefault();
      let a = $(this).prev().val();
      a && (t = a, $.ajax({
          type: "post",
          dataType: "html",
          headers: {
              "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
          },
          url: "/basket/coupon_use",
          data: {
              couponData: t
          },
          success: function(e) {
              if (e) {
                  var t = jQuery.parseJSON(e);
                  "user" == t.finded && $(".js-popup-login").click(), 1 == t.finded && u()
              }
          }
      }))
  }), $(document).on("click", "#country_block .select__option", function(e) {
      e.preventDefault();
      let t = $(this).data("value");
      $(".cart-data-page__input input").val(""), m(t)
  }), $(document).on("click", ".cart-data-page__input .select__option", function(e) {
      var t;
      e.preventDefault();
      let a = $(this).data("value"),
          n = $(this).text();
      $(".city-text").text(n), t = a, $(".cart-delivery-point__pickup-point--item").addClass("hidden-item"), $(`.cart-delivery-point__pickup-point--item[data-city="${t}"]`).removeClass("hidden-item"), $(".pickup-text").text($(".cart-delivery-point__pickup-point--item:not(.hidden-item)").length)
  }), $(".cart-page-item-edit input").on("change", function() {
      let e = $(this).parent(),
          t = $(this),
          a = new FileReader;
      a.onload = function(a) {
          var n, s;
          let r = a.target.result,
              o = t[0].files[0].name;
          n = {
              curId: e.data("curid"),
              imgSrc: e.data("img"),
              type: e.data("type"),
              cartId: e.data("cartid"),
              cartImgId: e.data("cartimgid"),
              newImgName: o,
              newImgSrc: r
          }, s = t, $.ajax({
              type: "post",
              dataType: "html",
              headers: {
                  "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
              },
              url: "/cart/updateimg",
              data: {
                  curId: n.curId,
                  imgSrc: n.imgSrc,
                  type: n.type,
                  cartId: n.cartId,
                  cartImgId: n.cartImgId,
                  newImgName: n.newImgName,
                  newImgSrc: n.newImgSrc
              },
              success: function(e) {
                  if (e) {
                      var t = jQuery.parseJSON(e);
                      t.Error ? alert(t.Error) : t.success && (console.log("ok"), s.closest(".cart-page-item__photo").find("img").attr("src", `${n.newImgSrc}`), u())
                  }
              }
          })
      }, a.readAsDataURL(t[0].files[0])
  }), $(".cart-page-item-remove").on("click", function(e) {
      var t, a;
      e.preventDefault();
      let n = $(this);
      t = {
          curId: n.data("curid"),
          imgSrc: n.data("img"),
          type: n.data("type"),
          cartId: n.data("cartid"),
          cartImgId: n.data("cartimgid")
      }, a = n, $.ajax({
          type: "post",
          dataType: "html",
          headers: {
              "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
          },
          url: "/cart/delimage",
          data: {
              curId: t.curId,
              imgSrc: t.imgSrc,
              type: t.type,
              cartId: t.cartId,
              cartImgId: t.cartImgId
          },
          success: function(e) {
              if (e) {
                  var t = jQuery.parseJSON(e);
                  t.Error ? alert(t.Error) : t.success && (a.closest(".cart-page-item__photo").remove(), console.log("ok"), u())
              }
          }
      })
  }), $(document).on("click", ".cart-page-sidebar__make--item", function() {
      let e = $(this).find("span").data("method");
      var t, a = 0;
      let n;
      t = e, n = document.getElementsByTagName("html")[0].getAttribute("lang"), $.ajax({
          type: "post",
          dataType: "html",
          headers: {
              "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
          },
          url: "/cart/setmaking",
          data: {
              makeInfo: t,
              makePrice: 0,
              lang: n
          },
          success: function(e) {
              if (e) {
                  var t = jQuery.parseJSON(e);
                  t.Error ? alert(t.Error) : t.success && (console.log("ok"), u())
              }
          }
      })
  }), $(document).on("click", ".select__option", function(e) {
      e.preventDefault()
  }), $(document).on("keyup", ".cart-data-page__input input", function() {
      let e = $(this).val().toUpperCase(),
          t = $(".cart-data-page__input.city .select__option.selected").length ? $(".cart-data-page__input .select__trigger a").text() : "";
      (t ? $(`.cart-delivery-point__pickup-point--item[data-city="${t}"]`) : $(".cart-delivery-point__pickup-point--item")).each(function() {
          $(this).data("name").toUpperCase().includes(e) ? $(this).removeClass("hidden-item") : $(this).addClass("hidden-item")
      }), $(".pickup-text").text($(".cart-delivery-point__pickup-point--item:not(.hidden-item)").length)
  });
  let l = {};

  function d(e) {
      $.ajax({
          type: "post",
          dataType: "html",
          headers: {
              "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
          },
          url: "/basket/remove",
          data: {
              basketId: e
          },
          success: function(t) {
              if (t) {
                  var a = jQuery.parseJSON(t);
                  if (a.Error || a.error) {
                      alert(a.Error || a.error);
                  } else if (a.success) {
                      $("#cart_item_" + e).html("");
                      u();
                  }
              }
          }
      })
  }

  function u() {
      $.ajax({
          type: "post",
          dataType: "html",
          headers: {
              "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
          },
          url: "/get_cart",
          success: function(e) {
              if (e) {
                  var t = jQuery.parseJSON(e);
                  t.Error ? alert(t.Error) : t.success && ($("#cart-sidebar").html(t.html), $("#total_item_counts").html(t.total_item_counts), t.terms.forEach(e => {
                      $(`#terms_${e.id}`).html(e.value)
                  }))
              }
          }
      })
  }

  function filterViarPickupOptions(e) {
      let t = (e || "").toString().toUpperCase();
      let a = $(".cart-master .select__options .select__option");
      if (!a.length) {
          return;
      }
      a.each(function() {
          let n = ($(this).data("country") || "ALL").toString().toUpperCase();
          let s = n.split(",").map(function(e) {
              return e.trim();
          }).filter(function(e) {
              return e.length > 0;
          });
          s.length || s.push("ALL");
          let r = !t || -1 !== s.indexOf("ALL") || -1 !== s.indexOf(t);
          let o = r;
          this.style.display = o ? "" : "none";
      });
      let r = a.filter(function() {
          return "none" !== this.style.display;
      });
      let o = r.filter(".selected").first();
      if (!o.length) {
          a.removeClass("selected");
          o = r.first();
          if (o.length) {
              o.addClass("selected");
              o.closest(".select__content").find(".select__trigger").removeClass("empty");
              o.closest(".select__content").find(".select__trigger a").text(o.text());
          }
      } else {
          o.closest(".select__content").find(".select__trigger a").text(o.text());
      }
  }

  function m(e) {
      console.log("country", e), filterViarPickupOptions(e), $(".town_delivery").css("display", "none"), $(".town_delivery-" + e).css("display", "block"), $.ajax({
          type: "post",
          dataType: "html",
          headers: {
              "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
          },
          url: `/get_towns?country=${e}`,
          success: function(e) {
              if (e) {
                  var t = jQuery.parseJSON(e);
                  t.Error || (t.success ? (console.log(t),
                      $(".cart-pickup").css("display", "block"),
                      $(".js-active").removeClass("js-active"),
                      $(".cart-pickup").addClass("js-active"),
                      $(".cart-delivery-item__content").css("display", "none"),
                      $(".cart-pickup .cart-delivery-item__content").css("display", "block"),
                      $(".cart-data-page__input .simplebar-content").html(t.towns),
                      $(".cities-inner").text($(".cities-inner").data("inner")), $(".cart-delivery-point__pickup-point--wrapper .simplebar-content").html(t.pickup_points),
                      $(".pickup-text").text($(".cart-delivery-point__pickup-point--item:not(.hidden-item)").length),
                      $(".all-city-cart").text($(".all-city-cart").data("default")),
                      $(".city-text").text($(".city-text").data("default")),
                      $(".cart-courier .cart-delivery-item__header > span").html(t.delivery_price + "€"),
                      $(".cart-pickup .delivery-price").html(t.delivery_venipak + "€")) : ($(".cart-pickup").css("display", "none"),
                      $(".cart-courier .cart-delivery-item__header > span").html(t.delivery_price + "€")))
              }
          }
      })
  }
  $(document).on("change", ".cart-delivery-comments__add-photo input", function() {
      let e = $(this),
          t = new FileReader;
      t.onload = function(t) {
          l.src = t.target.result, l.name = e[0].files[0].name, e.parent().find("span").css("display", "none"), e.parent().find(".cart-comments--complete").css("display", "block")
      }, t.readAsDataURL(e[0].files[0])
  }), $(document).on("click", ".cart_send_products", function() {
      let e = $(this).data("action"),
          t = $(".cart-page-sidebar__make--item.js-active span").data("method"),
          a = document.getElementsByTagName("html")[0].getAttribute("lang");
      $.ajax({
          type: "post",
          dataType: "html",
          headers: {
              "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
          },
          url: "/cart/setmaking",
          data: {
              makeInfo: t,
              makePrice: 0,
              lang: a
          },
          success: function(t) {
              if (t) {
                  var a = jQuery.parseJSON(t);
                  a.Error ? alert(a.Error) : a.success && (console.log("ok"), window.location.href = e)
              }
          }
      })
  }), $(document).on("click", ".cart_send_delivery", function() {
      let e = $(this).data("action"),
          t = {
              country: $("#country_block .selected").data("value") || "",
              price: $(".js-active .cart-delivery-item__header > span").text() || "",
              cartDate: $(".cart-delivery-calendar input").val() || "",
              cartComment: $(".cart-delivery-comments__content textarea").val() || "",
              cartCommentImage: l || ""
          };
      $(".cart-courier").hasClass("js-active") && (t.delivery_type = $(".cart-courier").data("delivery_type"), t.city = $(".cart-data-page__input.city input").val() || "", t.index = $(".cart-data-page__input.index input").val() || "", t.address = $("#address_delivery").val() || ""), $(".cart-pickup").hasClass("js-active") && (t.delivery_type = $(".cart-pickup").data("delivery_type"), t.city = $(".cart-delivery-point__pickup-point--item.js-active").data("city") || "", t.pickup = $(".cart-delivery-point__pickup-point--item.js-active").data("name") || ""), $(".cart-master").hasClass("js-active") && (t.delivery_type = $(".cart-master").data("delivery_type"), t.city = $(".cart-master .cart-delivery-page__select-country .select__trigger a").text() || "", t.pickup_workshop_id = $(".cart-master .cart-delivery-page__select-country .select__option.selected").data("value") || ""), $(".town_delivery").each(function() {
          $(this).hasClass("js-active") && (t.delivery_type = $(this).data("delivery_type"), t.city = $(this).attr("sale-city"), t.address = $(this).find("[name='address']").val(), t.delivery_town_id = $(this).attr("data-town_id") || "")
      }), $(".cart-data-page__input input").each(function() {
          c(this)
      }), !$(".cart-delivery-item.js-active .cart-data-page__input input.error").length > 0 ? $.ajax({
          type: "post",
          dataType: "html",
          headers: {
              "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
          },
          url: "/cart/setdelivery",
          data: t,
          success: function(t) {
              if (t) {
                  var a = jQuery.parseJSON(t);
                  a.Error ? alert(a.Error) : a.success && (console.log("ok"), window.location.href = e)
              }
          }
      }) : $("html, body").animate({
          scrollTop: $(".js-cart-delivery-item").offset().top
      }, 1e3)
  }), $(document).on("click", ".cart_send_pay", function() {
      let e = $(".cart-payments-page__item.js-active .window-prompt").text(),
          t = $(".cart-payments-page__item.js-active").data("type"),
          a = $(this).data("action");
      $(".cart-payments-sidebar__personal-data .cart-payments-page__item").hasClass("js-active") && $(".cart-payments-page__list .cart-payments-page__item").hasClass("js-active") && $.ajax({
          type: "post",
          dataType: "html",
          headers: {
              "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
          },
          url: "/cart/setpay",
          data: {
              paymentMethod: e,
              paymentData: t
          },
          success: function(e) {
              if (e) {
                  var t = jQuery.parseJSON(e);
                  t.Error ? alert(t.Error) : t.success && (console.log("ok"), window.location.href = a)
              }
          }
      })
  }), $(".cart-data-page__input input, .cart-data-page__input input").on("keyup", function() {
      c(this)
  }), $(document).on("click", ".cart_send_userdata", function(event) {
    const $name    = $(".cart-data-page__input.name input");
    const $surname = $(".cart-data-page__input.surname input");
    const $email   = $(".cart-data-page__input.email input");
    const $phone   = $(".js_get_country");
    const $phoneRec = $(".js_recipient_phone");
    const $phoneRecToggle = $(".js_recipient_toggle");

    // прогоняем 4 обязательных поля
    [$name, $surname, $email, $phone].forEach(($el) => {
    const val = String($el.val() || "").trim();
    const isEmpty = val.length === 0;
    // ставим/снимаем error на самом инпуте и его обёртке
    $el.toggleClass("error", isEmpty);
    $el.closest(".cart-data-page__input").toggleClass("error", isEmpty);
    });

    // если есть ошибки — не отправляем форму/AJAX
    const hasErrors = $(".cart-data-page__form").find("input.error").length > 0;
    if (hasErrors) {
        event.preventDefault();
        const $first = $(".cart-data-page__form").find("input.error").first();
        if ($first.length) {
            $("html, body").animate({ scrollTop: $first.offset().top - 100 }, 300);
            $first.trigger("focus");
        }
        return;
    }

    let targetUrl = $(this).data("action"),
        t = $(".cart-data-page__input.name input").val(),
        a = $(".cart-data-page__input.surname input").val(),
        n = $(".cart-data-page__input.email input").val(),
        s = $(".js_get_country").val(),
        r = $(".js_get_country").data("country"),
        phoneRec = ($phoneRecToggle.length && $phoneRecToggle.is(":checked") && $phoneRec.length) ? $phoneRec.val() : "";

    // Получение значений из чекбокса и всех инпутов юридического лица
    let isLegalEntity = $(".js_ur").is(":checked"),
        ur_name_l = $("input[name='ur_name_l']").val(),
        urRegNum = $("input[name='ur_reg_num']").val(),
        urLegalAddr = $("input[name='ur_legal_addr']").val(),
        urPnrNr = $("input[name='ur_pnr_nr']").val(),
        urBankName = $("input[name='ur_bank_name']").val(),
        urBankCode = $("input[name='ur_bank_code']").val(),
        urBankAccCode = $("input[name='ur_bank_acc_code']").val();

    // Добавление валидации на юридические поля, если чекбокс активирован
    if (isLegalEntity) {
        $(".ur_info input").each(function() {
            c(this);
        });
    }

    // Проверка на наличие ошибок
    if (!$(".cart-data-page__form").find("input.error").length > 0) {
        $.ajax({
            type: "post",
            dataType: "html",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            },
            url: "/cart/setuser",
            data: {
                name: t,
                surname: a,
                email: n,
                country_code: r,
                phone: s,
                phone_rec: phoneRec,
                ur_name: isLegalEntity, // Передаем true, если чекбокс активирован
                ur_name_l: isLegalEntity ? ur_name_l : null,
                ur_reg_num: isLegalEntity ? urRegNum : null,
                ur_legal_addr: isLegalEntity ? urLegalAddr : null,
                ur_pnr_nr: isLegalEntity ? urPnrNr : null,
                ur_bank_name: isLegalEntity ? urBankName : null,
                ur_bank_code: isLegalEntity ? urBankCode : null,
                ur_bank_acc_code: isLegalEntity ? urBankAccCode : null
            },
            success: function(t) {
                if (t) {
                    var a = jQuery.parseJSON(t);
                    if (a.Error) {
                        alert(a.Error);
                    } else if (a.success) {
                        console.log("ok");
                        window.location.href = targetUrl;
                    } else if (0 == a.success) {
                        var $inlineLogin = $(".js-cart-inline-login");
                        if ($inlineLogin.length) {
                            $(".js-cart-inline-login-email").text(n);
                            $(".js-cart-inline-login-error").text("");
                            var $password = $inlineLogin.find('input[name="password"]');
                            var isMobile = window.matchMedia("(max-width: 767px)").matches;

                            $inlineLogin
                                .prop("hidden", false)
                                .stop(true, true)
                                .hide()
                                .slideDown(200, function() {
                                    var targetTop = Math.max(0, $inlineLogin.offset().top - (isMobile ? 72 : 96));

                                    $("html, body").stop(true).animate({ scrollTop: targetTop }, 350, function() {
                                        // On phones an automatic focus opens the keyboard and can
                                        // move the newly revealed authorization block out of view.
                                        if (!isMobile) {
                                            $password.trigger("focus");
                                        }
                                    });
                                });
                        }
                    }
                }
            }
        });
    }
});
  let f = parseFloat($(".cart-page-sidebar__total-amount strong").text());
  $(document).on("click", ".js-cart-delivery-item", function() {
      let e = $(this).find(".delivery-price").text();
      $(".delivering strong").text(e), $(".cart-page-sidebar__total-amount strong").text(f + parseFloat(e) + " €")
  });
  let h = document.querySelectorAll(".timer");
  h.forEach(e => {
      if (e) {
          let t = new Date($(e).data("endtime")).getTime();
          setInterval(() => {
              let a = new Date().getTime(),
                  n = t - a;
              e.querySelector(".day span").innerHTML = Math.floor(n / 864e5), e.querySelector(".hour span").innerHTML = Math.floor(n % 864e5 / 36e5), e.querySelector(".minute span").innerHTML = Math.floor(n % 36e5 / 6e4), e.querySelector(".seconds span").innerHTML = Math.floor(n % 6e4 / 1e3)
          }, 1e3)
      }
  }), $(document).on("click", ".cart-bonuses-btn", function(e) {
      e.preventDefault(), $.ajax({
          type: "post",
          dataType: "html",
          headers: {
              "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
          },
          url: "/basket/submitbonuses",
          success: function(e) {
              if (e) {
                  var t = jQuery.parseJSON(e);
                  t.Error ? alert(t.Error) : t.success && u()
              }
          }
      })
  }), $(document).on("click", ".clear_coupon", function(e) {
      $.ajax({
          url: "/cart/clear_coupon",
          type: "get",
          success: function(e) {
              u(), console.log("AJAX request successful")
          },
          error: function(e) {
              console.error("Error occurred during AJAX request")
          }
      })
  })
}), $(document).on("click", ".check-js", function(e) {
  e.preventDefault(), $("body").addClass("open-frame"), $(".popup-frame").css("display", "flex").hide().fadeIn(), $(".popup-sizes").fadeIn()
}), $(document).ready(function() {
  $(document).on("click", ".sizesPopup-js", function(e) {
      e.preventDefault(), $("body").addClass("open-frame"), $(".popup-frame").css("display", "flex").hide().fadeIn(), $(".popup-sizes").fadeIn()
  })
}), $(document).on("click", ".js_submit_form", function(e) {
  (obj = $(this)).data("processed", !1), e.preventDefault(), window.loadedImages && window.loadedImages;
  if (0 == $(".image-block").length) {
      $(".popup-inv-size").addClass("active");
      return
  }
}), $(document).on("click", "#t3_submit_btn", function(e) {
  e.preventDefault();
  if (0 == $('.product-download input[type="file"]')[0].files.length) {
      $(".popup-inv-size").addClass("active");
      return
  }
	}), $(document).ready(function() {
	  function e() {
	      const menuLinks = $(".header-item_pc > ul > li > a");
	      const headerFixed = $(".vz-header").hasClass("is-fixed");
	      if (headerFixed) {
	          menuLinks.css("color", "");
	          return;
	      }
	      const isWhiteSlide = $(".portraits-slide.slick-active.mslidewhite").length > 0;
	      menuLinks.css("color", isWhiteSlide ? "white" : "");
	  }
	  $(".portraits-slider").on("beforeChange", () => {
	      setTimeout(() => {
	          e()
	      }, 100)
	  }), $(window).on("scroll", e), e()
	});


$(document).ready(function(){
    $('#gift-card-form').on('submit', function(e){
        e.preventDefault();

        let formData = new FormData(this);
        formData.append('card_type', $('.hb_whom.kviz-radio_active').data("type"));

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            },
            data: formData,
            contentType: false,
            processData: false,
            success: function(response){
                var res = JSON.parse(response);
                if(res.success)
                {
                    $("body").addClass("open-frame");
                    $(".popup-frame").css("display", "flex").hide().fadeIn();
                    $(".popup-cart").fadeIn();
                }
            },
            error: function(xhr, status, error){
                console.error('Ошибка запроса:', error);
            }
        });
    });
});

$('.js-popup-repair-basket').click(function (e) {
    e.preventDefault();

    $('.popup-frame, .js-popup').fadeOut(300, function () {
        $('body').addClass('open-frame');
        $('.popup-frame').css("display", "flex").hide().fadeIn();
        $('.popup-repair-basket').fadeIn();
    });

    $('body').removeClass('open-frame');
});

$('.popup-repair-basket .popup-close').on('click', function (e) {
    e.preventDefault();
    $('.popup-frame, .js-popup').fadeOut(300);

    window.location.href = '/cart';
});

$(document).ready(function() {
    /* -- faq --*/
    var faqItem = $('.faq-item_hide').length;
	var faqFade = 0;
	var faqHideItem = false;

	$('.faq-all').click(function (e) {
		e.preventDefault();

		if (faqHideItem) {
			faqFade = 0;
			faqHideItem = false;
			$('.faq-item_hide').fadeOut();
			$(this).children('span').html('Больше портретов');
			let top = $('.faq').offset().top;
			$('body,html').animate({ scrollTop: top - 50 }, 500);
		} else {
			faqFade += 2;
			$('.faq-item_hide:lt(' + faqFade + ')').fadeIn();
			faqHideItem = false;
			if (faqFade > faqItem || faqFade == faqItem) {
				$(this).children('span').html('Скрыть');
				faqHideItem = true;
			}
		}
	});

	// $('.faq-item').click(function (e) {
	// 	e.preventDefault();
	// 	$(this).children('.faq-body').slideToggle();
	// 	$(this).toggleClass('faq-item_active');
	// });

    $(".zoom-icon").click(function (e) {
		e.preventDefault();

        const container = $(this).closest(".cart-page-item__photo");

        container.find(".popup-basket-image").addClass("active");
	});
});

// Google rating badge + Google reviews section (loaded via backend API proxies)
$(document).ready(function () {
    function fmtIntSpaces(n) {
        return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    function setStarsWidth(el, rating) {
        if (!el) return;
        var r = Number(rating);
        if (!isFinite(r)) return;
        r = Math.max(0, Math.min(5, r));
        el.style.width = (r / 5 * 100).toFixed(1) + '%';
    }

    function initGoogleRatingBadge() {
        var root = document.getElementById('google-rating-badge');
        if (!root || !window.fetch) return;

        var endpoint = root.getAttribute('data-endpoint') || '/api/google-rating';
        var fill = document.getElementById('grb-stars-fill');
        var ratingEl = document.getElementById('grb-rating');
        var totalEl = document.getElementById('grb-total');
        var linkEl = document.getElementById('google-rating-badge-link');

        fetch(endpoint, { credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (!data || data.enabled === false) return;

                if (linkEl && data.url) linkEl.href = data.url;

                if (typeof data.rating === 'number' && isFinite(data.rating)) {
                    if (ratingEl) ratingEl.textContent = data.rating.toFixed(1);
                    setStarsWidth(fill, data.rating);
                }

                if (typeof data.user_ratings_total === 'number' && isFinite(data.user_ratings_total)) {
                    if (totalEl) totalEl.textContent = fmtIntSpaces(data.user_ratings_total) + ' Reviews';
                }
            })
            .catch(function () {});
    }

    function initGoogleReviewsSection() {
        var root = document.getElementById('google-reviews-section');
        if (!root || !window.fetch) return;

        var endpoint = root.getAttribute('data-endpoint') || '/api/google-reviews';
        var moreText = root.getAttribute('data-i18n-more') || 'See more';
        var hideText = root.getAttribute('data-i18n-hide') || 'Hide';
        var reviewsLabel = root.getAttribute('data-i18n-reviews-label') || 'Reviews';
        var track = document.getElementById('grs-track');
        var btnPrev = root.querySelector('.grs-prev');
        var btnNext = root.querySelector('.grs-next');

        function formatDateFromUnix(sec) {
            var d = new Date(sec * 1000);
            if (isNaN(d.getTime())) return '';
            var lang = (document.documentElement && document.documentElement.lang) ? document.documentElement.lang : (navigator.language || 'en');
            try {
                var parts = new Intl.DateTimeFormat(lang, { year: 'numeric', month: 'long', day: 'numeric' }).formatToParts(d);
                var month = parts.find(function (p) { return p.type === 'month'; });
                var day = parts.find(function (p) { return p.type === 'day'; });
                var year = parts.find(function (p) { return p.type === 'year'; });
                if (month && day && year) return month.value + ' ' + day.value + ' ' + year.value;
            } catch (e) {}
            return d.toLocaleDateString(lang);
        }

        function makeStars(rating) {
            var wrap = document.createElement('div');
            wrap.className = 'grs-stars';
            var fill = document.createElement('span');
            wrap.appendChild(fill);
            setStarsWidth(fill, rating);
            return wrap;
        }

        function renderCard(r) {
            var card = document.createElement('article');
            card.className = 'grs-card';

            var top = document.createElement('div');
            top.className = 'grs-card-top';

            var avatar = document.createElement('div');
            avatar.className = 'grs-avatar';
            if (r.profile_photo_url) {
                var img = document.createElement('img');
                img.src = r.profile_photo_url;
                img.alt = (r.author_name || 'Reviewer');
                avatar.appendChild(img);
            } else {
                avatar.textContent = (r.author_name ? String(r.author_name).trim().charAt(0).toUpperCase() : '?');
            }
            top.appendChild(avatar);

            var starsRow = document.createElement('div');
            starsRow.className = 'grs-card-stars';
            starsRow.appendChild(makeStars(r.rating));

            var name = document.createElement('div');
            name.className = 'grs-name';
            name.textContent = r.author_name || '';

            var date = document.createElement('div');
            date.className = 'grs-date';
            date.textContent = (function () {
                // Prefer localized relative time (month ago) based on current page language.
                var unix = Number(r.time);
                if (isFinite(unix) && unix > 0) {
                    var nowSec = Date.now() / 1000;
                    var diffSec = unix - nowSec; // negative for past
                    var abs = Math.abs(diffSec);

                    // Approx thresholds.
                    var minute = 60;
                    var hour = 60 * minute;
                    var day = 24 * hour;
                    var week = 7 * day;
                    var month = 30 * day;
                    var year = 365 * day;

                    var unit = 'day';
                    var value = diffSec / day;
                    if (abs < minute) { unit = 'second'; value = diffSec; }
                    else if (abs < hour) { unit = 'minute'; value = diffSec / minute; }
                    else if (abs < day) { unit = 'hour'; value = diffSec / hour; }
                    else if (abs < week) { unit = 'day'; value = diffSec / day; }
                    else if (abs < month) { unit = 'week'; value = diffSec / week; }
                    else if (abs < year) { unit = 'month'; value = diffSec / month; }
                    else { unit = 'year'; value = diffSec / year; }

                    value = Math.round(value);

                    var lang = (document.documentElement && document.documentElement.lang) ? document.documentElement.lang : (navigator.language || 'en');
                    if (typeof Intl !== 'undefined' && Intl.RelativeTimeFormat) {
                        try {
                            var rtf = new Intl.RelativeTimeFormat(lang, { numeric: 'auto' });
                            return rtf.format(value, unit);
                        } catch (e) {}
                    }

                    // Fallback: absolute localized date.
                    return formatDateFromUnix(unix);
                }

                return r.relative_time_description || '';
            })();

            var text = document.createElement('div');
            text.className = 'grs-text';
            text.textContent = r.text || '';

            card.appendChild(top);
            card.appendChild(starsRow);
            card.appendChild(name);
            card.appendChild(date);
            card.appendChild(text);

            if (r.text && String(r.text).length > 140) {
                var more = document.createElement('button');
                more.type = 'button';
                more.className = 'grs-more';
                more.textContent = moreText;
                more.addEventListener('click', function (ev) {
                    ev.preventDefault();
                    ev.stopPropagation();
                    var expanded = card.classList.toggle('is-expanded');
                    more.textContent = expanded ? hideText : moreText;
                });
                card.appendChild(more);
            }

            if (r.author_url) {
                card.style.cursor = 'pointer';
                card.addEventListener('click', function () {
                    window.open(r.author_url, '_blank', 'noopener');
                });
            }

            return card;
        }

        function scrollByCard(dir) {
            if (!track) return;
            var first = track.querySelector('.grs-card');
            var w = first ? first.getBoundingClientRect().width : 320;
            track.scrollBy({ left: dir * (w + 18), behavior: 'smooth' });
        }

        if (btnPrev) btnPrev.addEventListener('click', function (ev) {
            ev.preventDefault();
            ev.stopPropagation();
            scrollByCard(-1);
        });
        if (btnNext) btnNext.addEventListener('click', function (ev) {
            ev.preventDefault();
            ev.stopPropagation();
            scrollByCard(1);
        });

        fetch(endpoint, { credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (!data || data.enabled === false) {
                    root.style.display = 'none';
                    return;
                }

                if (data.name) {
                    var t = document.getElementById('grs-title');
                    if (t) t.textContent = data.name;
                }

                if (typeof data.rating === 'number' && isFinite(data.rating)) {
                    var rr = document.getElementById('grs-rating');
                    if (rr) rr.textContent = data.rating.toFixed(1);
                    setStarsWidth(document.getElementById('grs-stars-fill'), data.rating);
                }

                if (typeof data.user_ratings_total === 'number' && isFinite(data.user_ratings_total)) {
                    var c = document.getElementById('grs-count');
                    if (c) c.textContent = fmtIntSpaces(data.user_ratings_total) + ' ' + reviewsLabel;
                }

                var leave = document.getElementById('grs-leave-review');
                if (leave) {
                    leave.href = data.write_review_url || data.url || '#';
                }

                if (!track) return;
                track.innerHTML = '';
                var reviews = Array.isArray(data.reviews) ? data.reviews : [];
                if (!reviews.length) {
                    root.style.display = 'none';
                    return;
                }
                reviews.forEach(function (r) { track.appendChild(renderCard(r)); });
            })
            .catch(function () {
                root.style.display = 'none';
            });
    }

    initGoogleRatingBadge();
    initGoogleReviewsSection();
});
