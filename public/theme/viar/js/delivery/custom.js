// const { func } = require("prop-types");

$(document).ready(function () {
  function setPrice() {
    let s_price = $(".popup-photo input[name='new_price']").val();
    let c_price = $(".popup-photo input[name='new_people_count_price']").val();
    if (!c_price) {
      c_price = 0;
    }
    let o_price = parseFloat(s_price) + parseFloat(c_price);
    console.log(s_price, c_price);
    $(".kviz-price span").text(o_price);
    $(".popup-photo input[name='overall_price']").val(o_price);
  }

  $(".js-fast_order").click(function (e) {
    e.preventDefault();
    $(".popup-fit").fadeOut();
    $(".popup-photo").delay(500).fadeIn();
  });

  $(".js-detail_order").click(function (e) {
    /*e.preventDefault();*/
    $(".popup-frame").fadeOut();
    $(".js-popup").fadeOut();
    $("body").removeClass("open-frame");
    document.querySelector("#generator").scrollIntoView({
      behavior: "smooth",
    });
  });

  $(".js-examples").click(function (e) {
    e.preventDefault();

    let _id = $(this).data("catid");

    let size = $(this).data("size");

    let url = $(this).data("url");

    $("body").addClass("open-frame");
    $(".popup-frame").css("display", "flex").hide().fadeIn();
    $(".popup-fit").fadeIn();

    if (url) {
      $(".js-detail_order").attr("href", url);
    } else {
      $(".js-detail_order").attr(
        "href",
        $(".portraits-btn__style").attr("href")
      );
    }
    if (_id) {
      $(".popup-photo .select-style option[data-catid='" + _id + "']").prop(
        "selected",
        "selected"
      );
      $(`.select-size`).css("display", "none");
      $(`.select-size[data-catid='${_id}']`).css("display", "block");
      $(".popup-photo input[name='new_catid']").val(_id);
      if (size) {
        size = size.substring(0, size.length - 2);
        size = size.trim();
        size = size.split("х");
        $(`.select-size[data-catid='${_id}']`)
          .find("option[value='" + size[0] + "x" + size[1] + "']")
          .prop("selected", "selected");
        let price = $(`.select-size[data-catid='${_id}']`)
          .find("option:selected")
          .data("price");
        $(".popup-photo input[name='new_size']").val(size[0] + "x" + size[1]);
        $(".popup-photo input[name='new_price']").val(price);
        setPrice();
      } else {
        size = $(`.select-size[data-catid='${_id}']`)
          .find("option:selected")
          .val();
        let price = $(`.select-size[data-catid='${_id}']`)
          .find("option:selected")
          .data("price");

        $(".popup-photo input[name='new_size']").val(size);
        $(".popup-photo input[name='new_price']").val(price);
        setPrice();
      }

      if ($(`.kviz-popup--portrait select[data-catid='${_id}']`).length > 0) {
        $(".popup-photo").addClass("portrait-popup");
        $(`.kviz-popup--portrait select`).css("display", "none");
        $(`.kviz-popup--portrait select[data-catid='${_id}']`).css(
          "display",
          "block"
        );
        let count = $(
          `.kviz-popup--portrait select[data-catid='${_id}'] option:selected`
        ).val();
        let count_price = $(
          `.kviz-popup--portrait select[data-catid='${_id}'] option:selected`
        ).data("count-price");
        $(".popup-photo input[name='new_people_count']").val(count);
        $(".popup-photo input[name='new_people_count_price']").val(count_price);
        setPrice();
      } else {
        $(".popup-photo").removeClass("portrait-popup");
        $(`.kviz-popup--portrait select`).css("display", "none");
        $(".popup-photo input[name='new_people_count']").val(null);
        $(".popup-photo input[name='new_people_count_price']").val(null);
        setPrice();
      }
    }
  });

  $(`.select-size[data-catid='1']`).css("display", "block");

  $(".select-style").on("change", function () {
    let _id = $(this).find(":selected").data("catid");
    $(`.select-size[data-catid]`).css("display", "none");
    $(`.select-size[data-catid='${_id}']`).css("display", "block");
    $(".popup-photo input[name='new_catid']").val(_id);
    let price = $(`.select-size[data-catid='${_id}'] option:selected`).data(
      "price"
    );
    let size = $(`.select-size[data-catid='${_id}'] option:selected`).val();
    size = size.trim();
    $(".popup-photo input[name='new_size']").val(size);
    $(".popup-photo input[name='new_price']").val(price);
    setPrice();
    if ($(`.kviz-popup--portrait select[data-catid='${_id}']`).length > 0) {
      $(".popup-photo").addClass("portrait-popup");
      $(`.kviz-popup--portrait select`).css("display", "none");
      $(`.kviz-popup--portrait select[data-catid='${_id}']`).css(
        "display",
        "block"
      );
      let count = $(
        `.kviz-popup--portrait select[data-catid='${_id}'] option:selected`
      ).val();
      let count_price = $(
        `.kviz-popup--portrait select[data-catid='${_id}'] option:selected`
      ).data("count-price");
      $(".popup-photo input[name='new_people_count']").val(count);
      $(".popup-photo input[name='new_people_count_price']").val(count_price);
      setPrice();
    } else {
      $(".popup-photo").removeClass("portrait-popup");
      $(`.kviz-popup--portrait select`).css("display", "none");
      $(".popup-photo input[name='new_people_count']").val(null);
      $(".popup-photo input[name='new_people_count_price']").val(null);
      setPrice();
    }
  });

  $(".select-count").on("change", function () {
    let count = $(this).find(":selected").val();
    let count_price = $(this).find(":selected").data("count-price");
    $(".popup-photo input[name='new_people_count']").val(count);
    $(".popup-photo input[name='new_people_count_price']").val(count_price);
    setPrice();
  });

  $(".select-size").on("change", function () {
    let price = $(this).find(":selected").data("price");
    let size = $(this).find(":selected").val();
    size = size.trim();

    $(".popup-photo input[name='new_size']").val(size);
    $(".popup-photo input[name='new_price']").val(price);
    setPrice();
  });


  function updateInputValue() {
    const inputElement = $("#image-input");
    inputElement.val(JSON.stringify(loadedImages));
  }

  function dataURLtoBlob(dataURL) {
    const parts = dataURL.split(";base64,");
    const contentType = parts[0].split(":")[1];
    const byteString = atob(parts[1]);
    let arrayBuffer = new ArrayBuffer(byteString.length);
    let uint8Array = new Uint8Array(arrayBuffer);

    for (let i = 0; i < byteString.length; i++) {
      uint8Array[i] = byteString.charCodeAt(i);
    }

    return new Blob([arrayBuffer], { type: contentType });
  }
  window.loadedImages = [];
  const inputElement = $('.file-input');
  const inputValue = inputElement.val();
  if (inputValue) {
    loadedImages = JSON.parse(inputValue);
  }
  $(".file-input").on("change", function (e) {
    // let container = $(this).closest('.formalization-content, .file-box');
    // let imgContainer = container.find('.images-container');
    //   var filename = $(this).val().replace(/.*\\/, "");
    //   var _size = this.files[0].size;
    //   var _input = this;

    //   var fSExt = new Array("Bytes", "KB", "MB", "GB"),
    //     i = 0;
    //   while (_size > 900) {
    //     _size /= 1024;
    //     i++;
    //   }

    //   if (_input.files) {
    //     var filesAmount = _input.files.length;
    //     for (i = 0; i < filesAmount; i++) {
    //         var reader = new FileReader();

    //         reader.onload = function(event) {
    //           imgContainer.prepend(
    //             `
    //             <div class="image-block" style="background-image: url(${event.target.result});background-size: cover;">

    //             </div>
    //             `
    //           );
    //         }

    //         reader.readAsDataURL(_input.files[i]);
    //     }
    // }

    let container = $(this).closest(".formalization-content, .file-box");
    let imgContainer = container.find(".images-container");
    const fileList = this.files;
    const imagePreviewContainer = imgContainer;
    var filename = $(this).val().replace(/.*\\/, "");
    var _size = this.files[0].size;
    var _input = this;

    var fSExt = new Array("Bytes", "KB", "MB", "GB"),
      i = 0;
    while (_size > 900) {
      _size /= 1024;
      i++;
    }
    console.log(fileList);
    for (let i = 0; i < fileList.length; i++) {
      const file = fileList[i];
      const reader = new FileReader();

      reader.onload = function (event) {
        const imageData = event.target.result;
        const imageBlob = dataURLtoBlob(imageData);
        const imageFile = new File([imageBlob], file.name, { type: file.type });

        // Append the image file to your desired input file or process it further
        // e.g., send it to the server via AJAX

        console.log("Image File:", imageFile);
        loadedImages.push(imageFile);

        // Display image preview
        const imagePreview = $("<div>")
          .addClass("image-block")
          .css("background-image", `url(${imageData})`);
        imagePreviewContainer.append(imagePreview);

        // Add remove button
        const removeButton = $("<span>")
          .addClass("ab-cross")
          .text("X")
          .click(function () {
            imagePreview.remove();
            const index = loadedImages.indexOf(imageFile);
            if (index > -1) {
              loadedImages.splice(index, 1);
            }
          });
        imagePreview.append(removeButton);
        updateInputValue();
      };

      reader.readAsDataURL(file);
    }

    console.log($(this).val())
    var exactSize = Math.round(_size * 100) / 100 + " " + fSExt[i];
    let counterFile = $(this).get(0).files.length;

    let files = e.target.files;
    $(_input).prop("readonly", true);

    if (filename) {
      $(this).closest(".file-save").find(".abs-close").fadeIn();
      $(this).siblings(".js-file-preview").hide();
      $(this).siblings(".js-file-upload").hide();
      $(this).siblings(".js-file-multiple").css({ display: "flex" });
      $(this).addClass("file-input_save");
    } else {
      $(this).siblings(".js-file-preview").css({ display: "flex" });
      $(this).siblings(".js-file-upload").hide();
      $(this).siblings(".js-file-multiple").hide();
      $(this).removeClass("file-input_save");
    }

    $(this)
      .closest(".file-save")
      .find(".abs-close")
      .on("click", function () {
        $(_input).prop("readonly", false);
        $(_input).val(null);
        $(this).hide();
        $(_input).siblings(".js-file-preview").show();
        $(_input).siblings(".js-file-upload").hide();
        $(_input).siblings(".js-file-multiple").hide();
      });
  });
  $(".file-popup").on("change", function (e) {

    let container = $(this).closest('.popup-stock__inner');
    let imgContainer = container.find('.images-container');
    var _input = this;

    if (_input.files) {
      var filesAmount = _input.files.length;

      for (i = 0; i < filesAmount; i++) {
          var reader = new FileReader();

          reader.onload = function(event) {
            imgContainer.prepend(
              `
              <div class="image-block" style="background-image: url(${event.target.result});background-size: cover;">
              <svg class="img-cancel" width="11" height="10" viewBox="0 0 11 10" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect width="13.0545" height="1.08788" transform="matrix(0.707125 -0.707089 0.707125 0.707089 0 9.23071)" fill="#1E2533"/>
              <rect width="13.0545" height="1.08788" transform="matrix(-0.707125 -0.707089 0.707125 -0.707089 9.23242 10)" fill="#1E2533"/>
              </svg>

              </div>
              `
            );
          }

          reader.readAsDataURL(_input.files[i]);
      }
  }

  });
  $(document).on('click', '.img-cancel', function() {
    $(this).parent().remove();
    $('.file-popup').val('');
  })
  var fileValid = false;
  var messegeValid = false;
  $(".kviz-file-input").change(function () {
    fileValid = true;
  });
  $(".kviz-messege__tab").click(function () {
    messegeValid = true;
  });

  setTimeout(function () {
    $(".loader-canvas button input").on("change", function () {
      var container = $(this).closest(".product-download");

      console.log(this);
      container.find(".abs-close").fadeIn();
      container.find(".js-file-preview").hide();
      container.find(".js-file-upload").hide();
      container.find(".js-file-multiple").css({ display: "flex" });
      $(this).addClass("file-input_save");

      container.find(".abs-close").on("click", function () {
        $(this).prop("readonly", false);
        $(this).val(null);
        $(this).hide();
        container.find(".js-file-preview").show();
        container.find(".js-file-upload").hide();
        container.find(".js-file-multiple").hide();
      });
    });
  }, 1000);

  $(".why-composition-inner form, .why-form").on("submit", function () {
    $(this).find(".loading-bar").css("display", "flex").hide().fadeIn();
  });

  function getCookie(name) {
    var matches = document.cookie.match(
      new RegExp(
        "(?:^|; )" +
          name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, "\\$1") +
          "=([^;]*)"
      )
    );
    return matches ? decodeURIComponent(matches[1]) : undefined;
  }


  /*
  var _timeIn = 0;
  var alertwin = getCookie("alertwin");
  if (alertwin != "no") {
    $(document).mouseleave(function (e) {
      console.log(_timeIn, !_timeIn);
      if (!_timeIn) {
        if (e.clientY < 10) {
          if ($("body").hasClass("open-frame")) {
            if (!$(".popup-why").is(":visible")) {
              $(".js-popup").fadeOut();
              $(".popup-why").delay(500).fadeIn("fast");
            }
          } else {
            $("body").addClass("open-frame");
            $(".popup-frame").css("display", "flex").hide().fadeIn();
            $(".popup-why").delay(500).fadeIn("fast");
            _timeIn++;
          }

          var date = new Date();
          date.setDate(date.getDate() + 1);
          document.cookie =
            "alertwin=no; path=/; expires=" + date.toUTCString();
        }
      }
    });
  }

  $(".popup-why .popup-log-check").on("click", function () {
    let parent = $(this).closest("ul");
    parent.find(".popup-log-check").removeClass("log-check_active");
    $(this).removeClass("log-check_active");
  });

  $(".popup-why .popup-log-check").on("click", function () {
    let qId = $(this).data("question");
    console.log($(".question-input"));
    $(".popup-why").fadeOut();
    $(".popup-callback").delay(500).fadeIn("fast");
    console.log(qId);
    $("input[name='why_id']").val(qId);
  });
*/

  $(".kviz-radio label span img").hover(
    function () {
      $(this).closest(".kviz-radio").addClass("hovered");
    },
    function () {
      $(this).closest(".kviz-radio").removeClass("hovered");
    }
  );

  if ($(".portraits-slider").length > 0) {
    $(".portraits-slider").slick({
      infinite: true,
      slidesToShow: 1,
      prevArrow: ".portraits-prev",
      nextArrow: ".portraits-next",
      fade: true,
      dots: true,
      appendDots: ".portraits-dots",
      responsive: [
        {
          breakpoint: 700,
          settings: {
            dots: false,
          },
        },
      ],
    });
  }

  document.querySelectorAll('a[href^="#generator"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();

      document.querySelector(this.getAttribute("href")).scrollIntoView({
        behavior: "smooth",
      });
    });
  });


  //Работа с новой корзиной


  $(".cart-page-item__price").on(
    "click",
    ".minus, .plus, .delete",
    function () {
      //$(this).siblings('.count__number').text()
      var count = parseInt($(this).siblings(".count__number").text());
      if ($(this).data("type") == "plus") {
        count = count + 1;
      }
      if ($(this).data("type") == "minus") {
        count = count - 1;
      }

      var val = count;
      var index = $(this).data("id");

      if (count == 0) {
        delFromBasket(index);
      }

      if (val == 0) return;

      var form_data = new FormData();
      form_data.append("count", val);
      form_data.append("index", index);

      // update count
      $.ajax({
        processData: false,
        contentType: false,
        type: "POST",
        headers: {
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        url: "/basket/update/count",
        data: form_data,
        success: function (response) {
          if (response) {
            var data = jQuery.parseJSON(response);
            if (data.success) {
              // console.log(data);
              if (data.price) {
                $("#cart_price_" + index).html(data.price);
                get_cart();
              } else {
                $("#cart_price_" + index).html("0€");
                get_cart();
              }
            }
          }
        },
        error: function (error) {
          console.log(error);
        },
      });
    }
  );

  $(".cart-page-item").on("click", ".cart_item_delete", function () {
    basketId = $(this).data("id");
    delFromBasket(basketId);
  });

  $(document).on('click', '.cart-page-sidebar__certificate--btn', function(e) {
    console.log("Применен купон")

    e.preventDefault();
    let couponData = $(this).prev().val();
    console.log(couponData)
    if (couponData) {
      cartCoupon(couponData);
    }
  })




  $(document).on('click', '#country_block .select__option', function(e) {
    e.preventDefault();
    let dataCoutry = $(this).data('value');
    $('.cart-data-page__input input').val('');
    getCities(dataCoutry)
  })


  $(document).on('click', '.cart-data-page__input .select__option', function(e) {
    e.preventDefault();
    let dataCity = $(this).data('value');
    let city = $(this).text();
    $('.city-text').text(city)
    // $('.cart-data-page__input input').val('')
    getWarehouses(dataCity)
  })

  $('.cart-page-item-edit input').on('change', function() {
    // e.preventDefault();
    let el = $(this).parent();
    let _this = $(this)
    let reader = new FileReader();
    reader.onload = function(e) {
      let newImgSrc = e.target.result;
      let newImgName = _this[0].files[0].name;
      let dataObj = {
        curId: el.data("curid"),
        imgSrc: el.data("img"),
        type: el.data("type"),
        cartId: el.data("cartid"),
        cartImgId: el.data("cartimgid"),
        newImgName: newImgName,
        newImgSrc: newImgSrc
      };
      updateCartItemImg(dataObj, _this)
    }
    reader.readAsDataURL(_this[0].files[0]);

  })

  $(".cart-page-item-remove").on("click", function (e) {
    e.preventDefault();
    let el = $(this);
    let dataObj = {
      curId: el.data("curid"),
      imgSrc: el.data("img"),
      type: el.data("type"),
      cartId: el.data("cartid"),
      cartImgId: el.data("cartimgid")
    };
    delCartItemImg(dataObj, el);
  });

  $(document).on('click', '.cart-page-sidebar__make--item', function() {
    let makeInfo = $(this).find('span').data("method");
    let makePrice = 0;
    // let makePrice = $(this).find('strong').text();
    cartMake(makeInfo, makePrice);
  })

  $(document).on('click', '.select__option', function(e) {
    e.preventDefault()
  })

  $(document).on('keyup', '.cart-data-page__input input', function() {
    let value = ($(this).val()).toUpperCase();
    let checkSelected = $('.cart-data-page__input.city .select__option.selected').length;
    let city = checkSelected ? $('.cart-data-page__input .select__trigger a').text() : '';
    let warehouseList = city ? $(`.cart-delivery-point__pickup-point--item[data-city="${city}"]`) : $(`.cart-delivery-point__pickup-point--item`);
    console.log(city)
    warehouseList.each(function() {
      let dataThis = ($(this).data('name')).toUpperCase();
      if (!dataThis.includes(value)) {
        $(this).addClass('hidden-item');
      } else {
        $(this).removeClass('hidden-item');
      }
    })
    $('.pickup-text').text($('.cart-delivery-point__pickup-point--item:not(.hidden-item)').length)
  })

  function _inputValidate(input) {
    if (input.checkValidity()) {
      $(input).removeClass('error')
    } else {
      $(input).addClass('error')
    }
  }

  const IMG_INFO = {};

  $(document).on('change', '.cart-delivery-comments__add-photo input', function() {
    let _this = $(this)
    let reader = new FileReader();
    reader.onload = function(e) {
      IMG_INFO.src = e.target.result;
      IMG_INFO.name = _this[0].files[0].name;
      _this.parent().find('span').css('display','none');
      _this.parent().find('.cart-comments--complete').css('display','block');
    }
    reader.readAsDataURL(_this[0].files[0]);
  })


  $(document).on('click', '.cart_send_products', function() {
    let href = $(this).data('action');
    let makeInfo = $('.cart-page-sidebar__make--item.js-active span').data("method");
    let makePrice = 0;
    let lang = document.getElementsByTagName("html")[0].getAttribute("lang");

    $.ajax({
      type: "post",
      dataType: "html",
      headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
      },
      url: "/cart/setmaking",
      data: {
        makeInfo: makeInfo,
        makePrice: makePrice,
        lang: lang
      },
      success: function (response) {
        if (response) {
          var data = jQuery.parseJSON(response);
          if (data["Error"]) {
            alert(data["Error"]);
          } else if (data["success"]) {
            console.log("ok");
            window.location.href = href;
          }
        }
      },
    });
  })


  $(document).on('click', '.cart_send_delivery', function() {
    let href = $(this).data('action');
    let dataObject = {
      country: $('#country_block .selected').data('value') || '',
      price: $('.js-active .cart-delivery-item__header > span').text() || '',
      // deliveryType: $('.js-active .cart-delivery-item__header .window-prompt').text() || '',
      cartDate: $('.cart-delivery-calendar input').val() || '',
      cartComment: $('.cart-delivery-comments__content textarea').val() || '',
      cartCommentImage: IMG_INFO || ''
    }
    if ($('.cart-courier').hasClass('js-active')) {
      dataObject.delivery_type = $('.cart-courier').data('delivery_type');
      dataObject.city = $('.cart-data-page__input.city input').val() || '';
      dataObject.index = $('.cart-data-page__input.index input').val() || '';
      dataObject.address = $('.cart-data-page__input.name input').val() || '';
    }
    if ($('.cart-pickup').hasClass('js-active')) {
      dataObject.delivery_type = $('.cart-pickup').data('delivery_type');
      dataObject.city = $('.cart-delivery-point__pickup-point--item.js-active').data('city') || '';
      dataObject.pickup = $('.cart-delivery-point__pickup-point--item.js-active').data('name') || '';
    }
    if ($('.cart-master').hasClass('js-active')) {
      dataObject.delivery_type = $('.cart-master').data('delivery_type');
      dataObject.city = $('.cart-master .cart-delivery-page__select-country .select__trigger a').text() || '';
      dataObject.pickup_workshop_id = $('.cart-master .cart-delivery-page__select-country .select__option.selected').data('value') || '';
    }
    $('.town_delivery').each(function() {
      if ($(this).hasClass('js-active')) {
        dataObject.delivery_type = $(this).data('delivery_type');
        dataObject.city = $(this).attr('sale-city');
        dataObject.address = $(this).find("[name='address']").val() || '';
        dataObject.delivery_town_id = $(this).attr('data-town_id') || '';
      }
    });



    $('.cart-data-page__input input').each(function() {
      _inputValidate(this);
    })
   if (!$('.cart-delivery-item.js-active .cart-data-page__input input.error').length > 0) {
      $.ajax({
        type: "post",
        dataType: "html",
        headers: {
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        url: "/cart/setdelivery",
        data: dataObject,
        success: function (response) {
          if (response) {
            var data = jQuery.parseJSON(response);
            if (data["Error"]) {
              alert(data["Error"]);
            } else if (data["success"]) {
              console.log("ok");
              window.location.href = href;
            }
          }
        },
      });
    }
	else
	{
		$('html, body').animate({
			scrollTop: $(".js-cart-delivery-item").offset().top  // класс объекта к которому приезжаем
		}, 1000); // Скорость прокрутки
	}

  })


  $(document).on('click', '.cart_send_pay', function() {
    let paymentMethod = $('.cart-payments-page__item.js-active .window-prompt').text();
    let paymentData = $('.cart-payments-page__item.js-active').data('type');
    let href = $(this).data('action');

    if ($('.cart-payments-sidebar__personal-data .cart-payments-page__item').hasClass('js-active') && $('.cart-payments-page__list .cart-payments-page__item').hasClass('js-active')) {
      $.ajax({
        type: "post",
        dataType: "html",
        headers: {
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        url: "/cart/setpay",
        data: {
          paymentMethod: paymentMethod,
          paymentData: paymentData
        },
        success: function (response) {
          if (response) {
            var data = jQuery.parseJSON(response);
            if (data["Error"]) {
              alert(data["Error"]);
            } else if (data["success"]) {
              console.log("ok");
              window.location.href = href;
            }
          }
        },
      });
    }
  })

  $('.cart-data-page__input input, .cart-data-page__input input').on('keyup', function() {
    console.log(this.value)
    _inputValidate(this);
  })



  $(document).on('click', '.cart_send_userdata', function() {
    let href = $(this).data('action');


    let name = $('.cart-data-page__input.name input').val();
    let surname = $('.cart-data-page__input.surname input').val();
    let email = $('.cart-data-page__input.email input').val();
    let phone = $('.js_get_country').val();
    let country_code = $('.js_get_country').data('country');

    $('.cart-data-page__input input').each(function() {
      _inputValidate(this);
    })
    if (!$('.cart-data-page__form').find('input.error').length > 0) {
      $.ajax({
        type: "post",
        dataType: "html",
        headers: {
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        url: "/cart/setuser",
        data: {
          name: name,
          surname: surname,
          email: email,
          country_code: country_code,
          phone: phone
        },
        success: function (response) {
          if (response) {
            var data = jQuery.parseJSON(response);
            if (data["Error"]) {
              alert(data["Error"]);
            }
            else if (data["success"]) {
              console.log("ok");
              window.location.href = href;
            }
            else if (data["success"] == 0) {
              $('body').addClass('open-frame');
              $('.popup-frame').css("display", "flex").hide().fadeIn();
              $('.popup-login').fadeIn();
            }
          }
        },
      });
    }
  })



  function cartCoupon(couponData) {
    $.ajax({
      type: "post",
      dataType: "html",
      headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
      },
      url: "/basket/coupon_use",
      data: {
        couponData: couponData,
      },
      success: function (response) {
        if (response) {
          var data = jQuery.parseJSON(response);
          if (data['finded']=="user") {
          $(".js-popup-login").click();
          }
          if (data['finded']==1){
           get_cart();
        }}
      }
    });
  }

  function cartMake(makeInfo, makePrice) {
    let lang = document.getElementsByTagName("html")[0].getAttribute("lang");

    $.ajax({
      type: "post",
      dataType: "html",
      headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
      },
      url: "/cart/setmaking",
      data: {
        makeInfo: makeInfo,
        makePrice: makePrice,
        lang: lang
      },
      success: function (response) {
        if (response) {
          var data = jQuery.parseJSON(response);
          if (data["Error"]) {
            alert(data["Error"]);
          } else if (data["success"]) {
            console.log('ok')
            get_cart();
          }
        }
      },
    });
  }

  function delFromBasket(basketId) {
    $.ajax({
      type: "post",
      dataType: "html",
      headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
      },
      url: "/basket/remove",
      data: {
        basketId: basketId,
      },
      success: function (response) {
        if (response) {
          var data = jQuery.parseJSON(response);
          if (data["Error"] || data["error"]) {
            alert(data["Error"] || data["error"]);
          } else if (data["success"]) {
            $("#cart_item_" + basketId).html("");
            get_cart();
          }
        }
      },
    });
  }

  function delCartItemImg(dataObj, el) {
    $.ajax({
      type: "post",
      dataType: "html",
      headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
      },
      url: "/cart/delimage",
      data: {
        curId: dataObj.curId,
        imgSrc: dataObj.imgSrc,
        type: dataObj.type,
        cartId: dataObj.cartId,
        cartImgId: dataObj.cartImgId,
      },
      success: function (response) {
        if (response) {
          var data = jQuery.parseJSON(response);
          if (data["Error"]) {
            alert(data["Error"]);
          } else if (data["success"]) {

             el.closest(".cart-page-item__photo").remove();
            console.log("ok");
            get_cart();
          }
        }
      },
    });
  }







  function get_cart() {
    $.ajax({
      type: "post",
      dataType: "html",
      headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
      },
      url: "/get_cart",
      success: function (response) {
        if (response) {
          var data = jQuery.parseJSON(response);
          if (data["Error"]) {
            alert(data["Error"]);
          } else if (data["success"]) {
            $("#cart-sidebar").html(data["html"]);
            $("#total_item_counts").html(data["total_item_counts"]);
            data["terms"].forEach(term => {
              $(`#terms_${term['id']}`).html(term['value']);
            });

            if (typeof window.initRecommendationsSlider === 'function') {
              setTimeout(function() {
                window.initRecommendationsSlider();
              }, 100);
            }
          }
        }
      },
    });
  }

  function filterViarPickupOptions(countryCode) {
    let selectedCountry = (countryCode || '').toString().toUpperCase();
    let $options = $('.cart-master .select__options .select__option');
    if (!$options.length) {
      return;
    }

    $options.each(function() {
      let allowedCountriesRaw = ($(this).data('country') || 'ALL').toString().toUpperCase();
      let allowedCountries = allowedCountriesRaw.split(',')
        .map(function(code) { return code.trim(); })
        .filter(function(code) { return code.length > 0; });

      if (!allowedCountries.length) {
        allowedCountries.push('ALL');
      }

      let isVisible = !selectedCountry || allowedCountries.includes('ALL') || allowedCountries.includes(selectedCountry);
      this.style.display = isVisible ? '' : 'none';
    });

    let $visibleOptions = $options.filter(function() {
      return this.style.display !== 'none';
    });

    let $selected = $visibleOptions.filter('.selected').first();
    if (!$selected.length) {
      $options.removeClass('selected');
      $selected = $visibleOptions.first();
      if ($selected.length) {
        $selected.addClass('selected');
        $selected.closest('.select__content').find('.select__trigger').removeClass('empty');
        $selected.closest('.select__content').find('.select__trigger a').text($selected.text());
      }
    } else {
      $selected.closest('.select__content').find('.select__trigger a').text($selected.text());
    }
  }

  function getCities(country) {
    filterViarPickupOptions(country);

    $.ajax({
      type: "post",
      dataType: "html",
      headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
      },
      url: `/get_towns?country=${country}`,
      success: function (response) {
        if (response) {
          var data = jQuery.parseJSON(response);
          if (data["Error"]) {
          } else if (data["success"]) {
            console.log(data)
            $('.cart-payments-page__item:nth-child(4)').css('display', 'block')
            $('.cart-data-page__input .simplebar-content').html(data.towns);
            $('.cities-inner').text($('.cities-inner').data('inner'))
            $('.cart-delivery-point__pickup-point--wrapper .simplebar-content').html(data.pickup_points);
            $('.pickup-text').text($('.cart-delivery-point__pickup-point--item:not(.hidden-item)').length);
            $('.all-city-cart').text($('.all-city-cart').data('default'));
            $('.city-text').text($('.city-text').data('default'));
            $('.cart-courier .cart-delivery-item__header > span').html(data.delivery_price+'€');
            $('.cart-pickup .delivery-price').html(data.delivery_venipak+'€');

          } else {
            $('.cart-payments-page__item:nth-child(4)').css('display', 'none')
            $('.cart-courier .cart-delivery-item__header > span').html(data.delivery_price+'€');
          }
        }
      },
    });
  }

  function getWarehouses(city) {
    $('.cart-delivery-point__pickup-point--item').addClass('hidden-item');
    $(`.cart-delivery-point__pickup-point--item[data-city="${city}"]`).removeClass('hidden-item');
    $('.pickup-text').text($('.cart-delivery-point__pickup-point--item:not(.hidden-item)').length)
  }

  function updateCartItemImg(dataObj, _this) {
    $.ajax({
      type: "post",
      dataType: "html",
      headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
      },
      url: "/cart/updateimg",
      data: {
        curId: dataObj.curId,
        imgSrc: dataObj.imgSrc,
        type: dataObj.type,
        cartId: dataObj.cartId,
        cartImgId: dataObj.cartImgId,
        newImgName: dataObj.newImgName,
        newImgSrc: dataObj.newImgSrc
      },
      success: function (response) {
        if (response) {
          var data = jQuery.parseJSON(response);
          if (data["Error"]) {
            alert(data["Error"]);
          } else if (data["success"]) {
            console.log("ok");
            _this.closest('.cart-page-item__photo').find('img').attr('src', `${dataObj.newImgSrc}`);
            get_cart();
          }
        }
      },
    });
  }

  //Конец. работа с новой корзиной


  const INITIAL_PRICE = parseFloat($('.cart-page-sidebar__total-amount strong').text());
  $(document).on('click', '.js-cart-delivery-item', function() {
    let value = $(this).find('.delivery-price').text();
    $('.delivering strong').text(value);
    $('.cart-page-sidebar__total-amount strong').text(INITIAL_PRICE + parseFloat(value) + ' €')
  })



  //

  const timer = document.querySelectorAll(".timer");

  timer.forEach(el => {
    if (el) {
      const endTime = new Date($(el).data('endtime')).getTime();
      setInterval(() => {
        const currentTime = new Date().getTime();
        const remainingTime = endTime - currentTime;

        const days = Math.floor(remainingTime / (1000 * 60 * 60 * 24));
        const hours = Math.floor((remainingTime % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((remainingTime % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((remainingTime % (1000 * 60)) / 1000);

        el.querySelector(".day span").innerHTML = days;
        el.querySelector(".hour span").innerHTML = hours;
        el.querySelector(".minute span").innerHTML = minutes;
        el.querySelector(".seconds span").innerHTML = seconds;
      }, 1000);
    }
  })


//  function cart_bonuses(couponData) {
//      alert(couponData);
//    $.ajax({
//      type: "post",
//      dataType: "html",
//      headers: {
//        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
//      },
//      url: "/basket/coupon_use",
//      data: {
//        couponData: couponData,
//      },
//      success: function (response) {
//        if (response) {
//          var data = jQuery.parseJSON(response);
//          if (data["Error"]) {
//            alert(data["Error"]);
//          } else if (data["success"]) {
//            console.log('ok')
//            get_cart();
//          }
//        }
//      },
//    });
//  }



 $(document).on('click', '.cart-bonuses-btn', function() {
    couponData="test";
    $.ajax({
      type: "post",
      dataType: "html",
      headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
      },
      url: "/basket/submitbonuses",
      data: {
        couponData: couponData,
      },
      success: function (response) {
        if (response) {
          var data = jQuery.parseJSON(response);
          if (data["Error"]) {
            alert(data["Error"]);
          } else if (data["success"]) {
            console.log('ok')

            get_cart();
          }
        }
      },
    });
 })



});

$(document).on('click', '.check-js', function(e) {
    e.preventDefault();
    $('body').addClass('open-frame');
    $('.popup-frame').css("display", "flex").hide().fadeIn();
    $('.popup-sizes').fadeIn();
})
