$(document).ready(function () {
  // ///////////////////////////////////

  $(document).on("click", ".mc-js-list a", function (e) {
    // e.preventDefault();
    if ($(this).parent().find(".mc-subcat").length != 0) {
      $(this).parent().toggleClass("active");
      $(this).parent().find(".mc-subcat").slideToggle();
    } else {
      $(".mc-js-list a").removeClass("active");
      $(this).addClass("active");
    }
  });

  // //////////////////////////////////////

  let bsSlider = $(".bs-slider");

  bsSlider.slick({
    infinite: false,
    slidesToShow: 3,
    prevArrow: `<button type='button' class='slick-prev pull-left'><svg width="12" height="19" viewBox="0 0 12 19" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M11 18L2 9.5L11 1" stroke="#1E2533" stroke-width="2"/>
      </svg>
      </button>`,
    nextArrow: `<button type='button' class='slick-next pull-right'><svg width="12" height="19" viewBox="0 0 12 19" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M0.999999 0.999999L10 9.5L1 18" stroke="white" stroke-width="2"/>
              </svg>
              </button>`,
    dots: false,
    responsive: [
      {
        breakpoint: 981,
        settings: {
          slidesToShow: 2,
        },
      },
      {
        breakpoint: 701,
        settings: {
          slidesToShow: 1,
        },
      },
    ],
  });

  // //////////////////////////////////

  mobileOnlyCategorySlider(".cp-block-inner", 981);

  function mobileOnlyCategorySlider($slidername, $breakpoint) {
    var slider = $($slidername);
    var settings = {
      infinite: false,
      slidesToShow: 1,
      mobileFirst: true,
      arrows: false,
      variableWidth: true,
      responsive: [
        {
          breakpoint: 981,
          settings: "unslick",
        },
      ],
    };

    slider.slick(settings);

    $(window).on("resize", function () {
      if ($(window).width() > $breakpoint) {
        return;
      }
      if (!slider.hasClass("slick-initialized")) {
        return slider.slick(settings);
      }
    });
  }

  // ///////////////////////////////////////////

  const normalizeNumber = (value) => {
    const number = parseFloat(value);
    return Number.isNaN(number) ? 0 : number;
  };

  const getFrameButtonLabels = () => {
    const wrapper = $(".frame-wrapper");
    return {
      add: wrapper.data("add-label") || "Add",
      remove: wrapper.data("remove-label") || "Delete",
    };
  };

  const buildTypeText = (executionVal) => {
    const baseText = $(".rcard-type__set").first().data("text") || "";
    const activeText = $(".gallery-js-type.active").data("text") || "";
    if (executionVal == 1 && baseText && activeText) {
      return `${baseText} + ${activeText}`;
    }
    return activeText || baseText;
  };

  const updateFrameButtons = () => {
    const labels = getFrameButtonLabels();
    $(".frame-select-btn").each(function () {
      const isAdded = $(this).closest(".frame-item").hasClass("is-added");
      $(this)
        .text(isAdded ? labels.remove : labels.add)
        .toggleClass("frame-select-btn--remove", Boolean(isAdded));
      });
  };

  const getClientPhotoFile = () => {
    const input = document.querySelector(".gallery-client-photo-input");
    if (!input || !input.files || !input.files.length) {
      return null;
    }

    return input.files[0];
  };

  document.querySelectorAll(".cp-tab").forEach((element) => {
    element.addEventListener("click", function (e) {
      e.preventDefault();
      let _this = this;
      changeActiveTab(
        _this,
        ".category-pop--block",
        ".cp-block",
        ".cp-tab",
        "active"
      );

      if (mediaChecker("max", 981)) {
        $(".cp-block-inner")?.slick("refresh");
      }

      return false;
    });
  });

  // ///////////////////////////////////////

  const mediaChecker = (max_min, resolution, width = "width") =>
    window.matchMedia(`(${max_min}-${width}: ${resolution}px)`).matches;

  function changeActiveTab(
    _this,
    selectorTabWrap,
    selectorTabContent,
    selectorTabLink,
    classLinkActive
  ) {
    _this
      .closest(selectorTabWrap)
      .querySelectorAll(selectorTabLink)
      .forEach((element) => {
        element.classList.remove(classLinkActive);
      });

    _this.classList.add(classLinkActive);

    const indexTab = [..._this.parentElement.children].indexOf(_this);
    const newActiveTabContent = _this
      .closest(selectorTabWrap)
      .querySelectorAll(selectorTabContent)[indexTab];

    _this
      .closest(selectorTabWrap)
      .querySelectorAll(selectorTabContent)
      .forEach((element) => {
        element.classList.add("hidden-block");
      });

    newActiveTabContent.classList.remove("hidden-block");
  }

  /////////////////////////////////////////////////////

  var mcardSwiper = new Swiper(".mcard-navSlider-wrapper", {
    spaceBetween: 18,
    slidesPerView: 5,
    freeMode: true,
    direction: "vertical",
    navigation: {
      nextEl: ".swiper-next",
      prevEl: ".swiper-prev",
    },
  });
  new Swiper(".mcard-mainSlider-wrapper", {
    spaceBetween: 20,
    thumbs: {
      swiper: mcardSwiper,
    },
    navigation: {
      nextEl: ".swiper-next",
      prevEl: ".swiper-prev",
    },
    breakpoints: {
      1080: {
        navigation: false,
      },
    },
  });

  // ///////////////////////////////////

  $(document).on("click", ".mcard-type-item", function () {
    $(".mcard-type-item").removeClass("active");
    $(this).addClass("active");
    let exect = $(this).data('execution');
    galleryItemsCalculate(exect);
  });

  $(document).on("click", ".mcard-date-item", function () {
    $(".mcard-date-item").removeClass("active");
    $(this).addClass("active");
    galleryItemsCalculate();
  });

  //

  $(document).on("click", ".c-sizeCheck input", function () {
    $(this).closest('.c-sizeCheck').find('li').removeClass("active");
    $(this).closest('.c-sizeCheck').find('input').prop("checked", false);
    $(this).closest("li").addClass("active");
    $(this).prop("checked", true);
    galleryItemsCalculate();
  });

  $(document).on("click", ".js-mcard-size", function (e) {
    if ($(this).hasClass("mcard-size--open")) {
      return;
    }
    $(this).find(".filter-item--wrapper").fadeIn();
  });

  $(document).on("click", function (e) {
    let container = $(".mcard-size:not(.mcard-size--open)");
    let target = $(".c-sizeCheck input, .c-sizeCheck input + span");

    if (target.is(e.target)) {
      let text = $(e.target).closest("li").find("p").text();
      $(e.target).closest(".js-mcard-size").find("a > span").text(text);
      if (!$(e.target).closest(".js-mcard-size").hasClass("mcard-size--open")) {
        target.closest(".mcard-size .filter-item--wrapper").fadeOut();
      }
      galleryItemsCalculate()
    } else if (
      !container.is(e.target) &&
      container.has(e.target).length === 0
    ) {
      $(".mcard-size:not(.mcard-size--open) .filter-item--wrapper").fadeOut();
    }
  });

  //

  //

  $(document).on("click", ".rp-p-about li", function () {
    $(".rp-p-about li").removeClass("active");
    $(this).addClass("active");
  });

  $(document).on("click", ".alphabets li", function () {
    $(".alphabets li").removeClass("active");
    $(this).addClass("active");
  });

  $(document).on("click", ".rp-results li", function () {
    $(".rp-results li").removeClass("active");
    $(this).addClass("active");
  });

  $(document).on("click", ".mc-js-filter > a", function (e) {
    e.preventDefault();
    $(".mc-js-filter ").removeClass("active");
    $(".filter-item--wrapper").fadeOut();
    $(this).parent().addClass("active");
    $(this).next().fadeIn();
  });
  $(document).on("click", ".checkbox-settings p", function () {
    $(this).closest(".checkbox-settings").find("p").removeClass("active");
    $(this).addClass("active");
  });
  $(document).on("click", function (e) {
    let container = $(".filter-item");
    let target = $(".c-sizeCheck input");

    if (target.is(e.target)) {
    } else if (
      !container.is(e.target) &&
      container.has(e.target).length === 0
    ) {
      $(".mc-js-filter .filter-item--wrapper").fadeOut();
      $(".filter-item").removeClass("active");
    } else {
      if (
        $(e.target).closest(".filter-item").find(".filter-sort").length != 0
      ) {
        if ($(".sort-it li").has(e.target) || $(".sort-it li").is(e.target)) {
          let text = $(e.target).closest(".sort-it li").find("span").text();
          if (text) {
            $("#isort").text(text);
            $("#isort2").text(text);
            $(e.target).closest(".sort-it").find("li").removeClass("active");
            $(e.target).closest(".sort-it li").addClass("active");
          }
        }
      } else if (
        $(e.target).closest(".filter-item").find(".filter-material").length != 0
      ) {
        if (
          $(".filter-material li").has(e.target) ||
          $(".filter-material li").is(e.target)
        ) {
          $(e.target)
            .closest(".filter-material")
            .find("li")
            .removeClass("active");
          $(e.target).closest(".filter-material li").addClass("active");
          filterRam()
        }
      }
    }
  });

  $(document).on("click", ".color-grid li", function (e) {
    $(".color-grid li").removeClass("active");
    $(this).addClass("active");
    filterRam()
  });

  $(document).on('click', '.mc-search button', function(e) {
    e.preventDefault();
    filterRam()
  })

  $(document).on('keyup', '.mc-search input', function(e) {
    e.preventDefault();
    filterRam()
  })

  function filterRam() {
        let color = $(".color-grid li.active").data('ramcolorid');
    let material = parseInt($('.filter-material li.active').data('rammaterialid'));
    let search = $('#ram_search').val();
    const frameAddLabel = $(".frame-wrapper").data("add-label") || "Add";
    var form_data = new FormData();
    form_data.append('ramcolorid', color);
    form_data.append('rammaterialid', material);
    form_data.append('search_id', search);
    $.ajax({
      type: 'POST',
      processData: false,
      contentType: false,
      dataType: "json",
      headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
      },
      url: "/get/ram_search",
      data: form_data,
      success: function (response) {
        console.log(response)
        let content = '';
        response.forEach((el, i) => {
              const ramWebp = el.img_webp || el.img;
              content +=  `<div class="frame-item"
              data-code="${el.id}"
              data-price="${parseInt(el.price)}"
              data-price-text="${el.price}"
              data-material="${el.material || ''}"
              data-color="${el.color || ''}"
              data-width="${el.width || ''}"
              data-height="${el.height || ''}"
              data-src="${el.img}"
              data-ramprice="${parseInt(el.price)}">
              <div class="frame-icons">
                <span class="frame-info-zoom">
                  <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="14" cy="14" r="14" fill="#FA7846"></circle>
                    <g clip-path="url(#clip0_877_8)">
                      <path d="M22.6 21.9106L17.313 16.6236C18.1764 15.604 18.7 14.2878 18.7 12.85C18.7 9.62412 16.0759 7 12.85 7C9.62414 7 7 9.62412 7 12.85C7 16.0759 9.62412 18.7 12.85 18.7C14.2878 18.7 15.604 18.1764 16.6236 17.313L21.9106 22.6L22.6 21.9106ZM12.85 17.725C10.1621 17.725 7.97501 15.5379 7.97501 12.85C7.97501 10.1621 10.1621 7.97501 12.85 7.97501C15.5379 7.97501 17.725 10.1621 17.725 12.85C17.725 15.5379 15.5379 17.725 12.85 17.725Z" fill="white"></path>
                      <rect width="6.26573" height="0.68813" transform="matrix(-2.0104e-05 -1 1 2.0104e-05 12.4766 15.9268)" fill="white"></rect>
                      <rect width="6.26574" height="0.658255" transform="matrix(-1 -2.03991e-05 -2.05255e-05 -1 15.9277 13.0557)" fill="white"></rect>
                    </g>
                    <defs>
                      <clipPath id="clip0_877_8">
                        <rect width="15" height="15" fill="white" transform="translate(7 7)"></rect>
                      </clipPath>
                    </defs>
                  </svg>
                </span>
                <span class="frame-info-trigger">
                  <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="14" cy="14" r="14" fill="#FA7846"></circle>
                    <text x="14" y="19" text-anchor="middle" font-size="14" fill="#fff" font-family="Arial" font-weight="700">i</text>
                  </svg>
                </span>
              </div>
              <picture>
              <source srcset="${ramWebp}" type="image/webp">
              <source srcset="${el.img}" type="image/jpeg">
              <img width="150" height="150" src="${el.img}" alt="Viar" loading="lazy">
              </picture>
              <div class="fi-info">
              <p>
              ${el.lng_id}
              <span>${el.id}</span>
              </p>
              <b>+${el.price}</b>
              </div>
              <div class="frame-info">
              <ul>
              <li>
              <svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
              <g clip-path="url(#clip0_261_1144)">
              <path d="M7.90987 1.17996C7.78973 1.0598 7.5949 1.0598 7.47473 1.17996L2.48706 6.16764L0.525259 4.20583C0.405118 4.08565 0.210289 4.08565 0.0901174 4.20583C-0.0300391 4.32598 -0.0300391 4.5208 0.0901174 4.64097L2.26951 6.82036C2.38962 6.94047 2.58451 6.9405 2.70465 6.82036L7.90987 1.6151C8.03002 1.49493 8.03002 1.30012 7.90987 1.17996Z" fill="#1F9750"></path>
              </g>
              <defs>
              <clipPath id="clip0_261_1144">
              <rect width="8" height="8" fill="white"></rect>
              </clipPath>
              </defs>
              </svg>
              <span>${el.lng_stock}</span>
              </li>
              <li>
              <span>${el.lng_id}</span>
              <span>${el.id}</span>
              </li>
              <li>
              <span>${el.lng_color}</span>
              <span>${el.color}</span>
              </li>
              <li>
              <span>${el.lng_width}</span>
              <span>${el.height}</span>
              </li>
              <li>
              <span>${el.lng_height}</span>
              <span>${el.height}</span>
              </li>
              <li>
              <span>${el.lng_id}</span>
              <span>${el.lng_material}</span>
              </li>
              <li>
              <span>${el.lng_price}</span>
              <span>${el.price}</span>
              </li>
              </ul>
              </div>
              <a href="#" class="mf-popup frame-info-modal">
              More details </a>
              <button type="button" class="frame-select-btn">${frameAddLabel}</button>
              </div>`
        })
        $('.frames-list').html(content);
        updateFrameButtons();
        if (color) {
          $(".mc-f-selected[data-id='1'").remove();
          addRamFilterItem(response[0].color, 1)
        }
        if (material) {
          $(".mc-f-selected[data-id='2'").remove();
          addRamFilterItem($(".filter-material li.active").text(), 2)
        }
      },
    });
  }

  function addRamFilterItem(name, id) {
    console.log('here')
    let filterItems = $('.rc-f-selected-container');
    filterItems.append(
      `
                <div class="mc-f-selected" data-id="${id}">
                  <div>
                    ${name}
                  </div>
                  <svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="10.4429" height="0.870241"
                      transform="matrix(0.707093 -0.707121 0.707093 0.707121 0.00146484 7.38428)" fill="#1E2533"></rect>
                    <rect width="10.4429" height="0.870241"
                      transform="matrix(-0.707092 -0.707121 0.707092 -0.707121 7.38428 8)" fill="#1E2533"></rect>
                  </svg>
                </div>
      `
    )
  }

  $(document).on("click", ".rp-form ul li", function (e) {
    $(".rp-form ul li").removeClass("active");
    $(this).addClass("active");
  });

  //

  $(document).on("click", ".forms-grid div", function (e) {
    $(".forms-grid div").removeClass("active");
    $(this).addClass("active");
  });

  $(document).on("click", ".rcard-frame__inner > a", function (e) {
    e.preventDefault();
    const wrapper = $(this).next();
    const willOpen = !wrapper.is(":visible");

    if (willOpen && !$(".frame-item.active").length) {
      // No selection — reset labels/states to allow re-selecting the same frame after removal
      $(".frame-item").removeClass("active");
      updateFrameButtons();
      frameGallery = 0;
      galleryItemsCalculate();
    }

    wrapper.fadeToggle();
    $(this).parent().toggleClass("active");
  });

  $(document).on("click", ".mc-f-selected > svg", function () {
    let id = $(this).parent().data('id')
    $(this).parent().remove();
    if (id === 1) {
      $(".color-grid li").removeClass('active');
    } else {
      $(".filter-material li").removeClass('active')
    }
    filterRam()
  });



  $(document).on("click", ".rcard-type__set", function () {
    $(".rcard-type__set").removeClass("active");
    $(this).addClass("active");
    galleryItemsCalculate()
  });

  $(document).on("click", ".rcard-type__trigger", function () {
    $(this).toggleClass("active");
    if ($(this).data("frame")) {
      $(".rcard-frame").fadeToggle();
      if (!$(this).hasClass('active')) {
        // Clearing frame selection when toggling off
        frameGallery = 0;
        $('.frame-item, .frame-wrapper .frame-item').removeClass('active selected');
        updateFrameButtons();
        galleryItemsCalculate();
      }
    }

    galleryItemsCalculate()
  });

  document.querySelectorAll(".mcard-tab").forEach((element) => {
    element.addEventListener("click", function (e) {
      e.preventDefault();
      let _this = this;
      changeActiveTab(
        _this,
        ".mcard-about__inner",
        ".mcard-about__block",
        ".mcard-tab",
        "active"
      );
      $('.mca-reviews').slick('refresh');
      return false;
    });
  });


  // if ($('.mca-reviews').length) {
  //   new Swiper(".mca-reviews", {
  //     spaceBetween: 15,
  //     slidesPerView: 1,
  //     grid: {
  //       rows: 3,
  //     },
  //     navigation: {
  //       nextEl: ".mcard-about__block .mca-right",
  //       prevEl: ".mcard-about__block .mca-left",
  //     }
  //   });
  // }

  $('.mca-reviews').slick({
		rows: 3,
		dots: false,
    prevArrow: $('.mca-left'),
    nextArrow: $('.mca-right'),
		infinite: true,
		speed: 300,
		slidesToShow: 1,
		slidesToScroll: 1
});



  if (document.querySelector('.similar-slider')) {
    new Swiper(".similar-slider", {
      spaceBetween: 15,
      slidesPerView: 2,
      navigation: {
        nextEl: ".swiper-next",
        prevEl: ".swiper-prev",
      },
      autoplay: {
        delay: 1000,
        disableOnInteraction: true,
      },
      breakpoints: {
        1080: {
          slidesPerView: 4,
        },
        768: {
          slidesPerView: 2,
        },
      },
    });
    var similarSlider = document.querySelector('.similar-slider').swiper

    similarSlider.autoplay.stop();
    $(".similar-slider").mouseenter(function() {
      similarSlider.autoplay.start();
    });

    $(".similar-slider").mouseleave(function() {
      similarSlider.autoplay.stop();
    });
  }




  document.querySelectorAll(".alph-tabs > div").forEach((element) => {
    element.addEventListener("click", function (e) {
      e.preventDefault();
      let _this = this;
      changeActiveTab(
        _this,
        ".alphabets",
        ".alph-ru, .alph-en",
        ".alph-tabs > div",
        "active"
      );
      return false;
    });
  });

  new Swiper(".pp-list", {
    spaceBetween: 15,
    slidesPerView: 1,
    navigation: {
      nextEl: ".swiper-next",
      prevEl: ".swiper-prev",
    },
    breakpoints: {
      1080: {
        slidesPerView: 4,
      },
      768: {
        slidesPerView: 2,
      },
      425: {
        slidesPerView: 1,
      },
    },
  });

  new Swiper(".cats-slider", {
    spaceBetween: 20,
    slidesPerView: 1.1,
    navigation: {
      nextEl: ".swiper-next",
      prevEl: ".swiper-prev",
    },
    breakpoints: {
      1200: {
        slidesPerView: 4,
      },
      970: {
        slidesPerView: 3,
      },
      700: {
        slidesPerView: 2,
      },
      425: {
        slidesPerView: 1.5,
      },
    },
  });

  document.querySelectorAll(".rp-tab").forEach((element) => {
    element.addEventListener("click", function (e) {
      e.preventDefault();
      let _this = this;
      changeActiveTab(_this, ".reproduction", ".rp-block", ".rp-tab", "active");
      return false;
    });
  });

  let rcarddSwiper = new Swiper(".rcardd-swiper", {
    spaceBetween: 20,
    slidesPerView: 3,
    freeMode: true,
    direction: "vertical",
    navigation: {
      nextEl: ".swiper-next",
      prevEl: ".swiper-prev",
    },
  });
  new Swiper(".rcard-mainSlider-wrapper", {
    spaceBetween: 20,
    thumbs: {
      swiper: rcarddSwiper,
    },
    navigation: {
      nextEl: ".swiper-next",
      prevEl: ".swiper-prev",
    },
    breakpoints: {
      1080: {
        navigation: false,
      },
    },
  });

  let rcardvSwiper = new Swiper(".rcardv-swiper", {
    spaceBetween: 20,
    slidesPerView: 3,
    freeMode: true,
    direction: "vertical",
    navigation: {
      nextEl: ".rcm-block .swiper-next",
      prevEl: ".rcm-block .swiper-prev",
    },
  });

  rcarddSwiper.controller.control = this.rcardvSwiper;

  new Swiper(".photo-slider", {
    spaceBetween: 20,
    slidesPerView: 1,
    navigation: {
      nextEl: ".swiper-next",
      prevEl: ".swiper-prev",
    },
    breakpoints: {
      991: {
        slidesPerView: 2,
      },
      700: {
        slidesPerView: 1,
      },
    },
  });

  let sSwiper1 = new Swiper(".interior-item__slider", {
    spaceBetween: 12,
    slidesPerView: 1.8,
    navigation: {
      nextEl: ".interior-item__slider_block .swiper-next",
      prevEl: ".interior-item__slider_block .swiper-prev",
    },
    breakpoints: {
      525: {
        slidesPerView: 3,
      },
    },
  });

  $(document).on("click", ".mc-deliver", function (e) {
    e.preventDefault();
    $(".popup-frame").fadeIn();
    $(".popup-delivery").fadeIn();
  });
  $(document).on("click", ".mc-payment", function (e) {
    e.preventDefault();
    $(".popup-frame").fadeIn();
    $(".popup-payment").fadeIn();
  });
  const clearInfoTimer = (item) => {
    const timer = item.data('infoTimer');
    if (timer) {
      clearTimeout(timer);
      item.removeData('infoTimer');
    }
  };

  const hideInfoWithDelay = (item) => {
    clearInfoTimer(item);
    const timer = setTimeout(() => {
      item.removeClass('show-info');
    }, 150);
    item.data('infoTimer', timer);
  };

  $(document).on("mouseenter", ".frame-info-trigger, .frame-info", function () {
    const item = $(this).closest('.frame-item');
    clearInfoTimer(item);
    item.addClass('show-info');
  });

  $(document).on("mouseleave", ".frame-info-trigger, .frame-info", function () {
    const item = $(this).closest('.frame-item');
    hideInfoWithDelay(item);
  });

  $(document).on("click", ".frame-info-trigger", function (e) {
    e.preventDefault();
    e.stopPropagation();
    const item = $(this).closest('.frame-item');
    const isShown = item.hasClass('show-info');
    $('.frame-item').removeClass('show-info');
    if (!isShown) {
      item.addClass('show-info');
    }
  });

  function openFrameModal(frameItem) {
    const form = $('.popup-rframe').find('li span:last-child');

    // Clear previous values
    form.each(function () {
      $(this).text('');
    });

    const values = [
      frameItem.data('code') || '',
      frameItem.data('material') || '',
      frameItem.data('color') || '',
      frameItem.data('width') || '',
      frameItem.data('height') || '',
      frameItem.data('price-text') || ''
    ];

    values.forEach((val, i) => {
      form.eq(i + 1).text(val || '');
    });

    // Hide rows with empty values to keep layout aligned
    $('.popup-rframe .pf-info li').each(function(index) {
      const valueSpan = $(this).find('span:last-child');
      const valueText = valueSpan.text().trim();
      if (valueText === "" || valueText.toLowerCase() === "false") {
        $(this).hide();
      } else {
        $(this).show();
      }
    });

    // Update preview image in modal
    const preview = frameItem.find('picture source, picture img');
    const webp = preview.filter('source[type="image/webp"]').attr('srcset');
    const jpeg = preview.filter('source[type="image/jpeg"]').attr('srcset');
    const fallback = preview.filter('img').attr('src');
    const modalPicture = $('.popup-rframe .pf-img picture');
    if (webp) modalPicture.find('source[type="image/webp"]').attr('srcset', webp);
    if (jpeg) modalPicture.find('source[type="image/jpeg"]').attr('srcset', jpeg);
    if (fallback) modalPicture.find('img').attr('src', fallback);

    $(".popup-frame").fadeIn();
    $(".popup-rframe").fadeIn();
  }

  $(document).on("click", ".frame-info-modal", function (e) {
    e.preventDefault();
    e.stopPropagation();
    const frameItem = $(this).closest('.frame-item');
    openFrameModal(frameItem);
  });

  $(document).on("click", ".frame-info-zoom", function (e) {
    e.preventDefault();
    e.stopPropagation();
    const frameItem = $(this).closest('.frame-item');
    openFrameModal(frameItem);
  });

  $(document).on("click", function (e) {
    if (!$(e.target).closest('.frame-info-trigger, .frame-info').length) {
      $('.frame-item').removeClass('show-info');
    }
  });

  function mediaAction() {
    if (mediaChecker("max", 991)) {
      $(".mc-mainSlide").attr("href", "#");
      $(".mc-mainSlide").removeAttr("data-fancybox");
    }
  }
  mediaAction();

  new Swiper(".cp-v-slider", {
    spaceBetween: 40,
    slidesPerView: 1,
    navigation: {
      nextEl: ".swiper-next",
      prevEl: ".swiper-prev",
    },
  });

  // gallery calc
			$(document).on('click', '.frame-item', function(e) {
    if ($(e.target).closest(".frame-select-btn, .frame-info-trigger, .frame-info-zoom, .frame-info, .frame-info-modal").length) {
      return;
    }
    $(".frame-item, .frame-wrapper .frame-item").removeClass("selected");
    $(this).addClass("selected");
		  })

  $(document).on("click", ".frame-select-btn", function (e) {
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();
    const frameItem = $(this).closest(".frame-item");
    const isRemoveState = $(this).hasClass("frame-select-btn--remove");

    // Clear any previous selection
    $(".frame-item, .frame-wrapper .frame-item").removeClass("active is-added");
    $(".frame-select-btn").removeClass("frame-select-btn--remove").text(getFrameButtonLabels().add);

    if (!isRemoveState) {
      // Select this frame
      frameItem.addClass("active is-added");
      $(".rcard-type__trigger").addClass("active");
    } else {
      // Fully reset trigger if removing
      $(".rcard-type__trigger").removeClass("active");
      frameGallery = 0;
      // Ensure nothing stays active after removal
      $(".frame-item, .frame-wrapper .frame-item").removeClass("active is-added");
      $(this).removeClass("frame-select-btn--remove").text(getFrameButtonLabels().add);
    }

    galleryItemsCalculate();
    frameItem.closest(".frame-wrapper").fadeOut();
    frameItem.closest(".rcard-frame__inner").removeClass("active");
    updateFrameButtons();
    // Recalculate after explicit frame change to keep totals correct
    galleryItemsCalculate();
  });


    function calcExecutionPrice(sizeSize, execution) {
      let executionPrice = 0;

      if (!sizeSize) {
        return executionPrice;
      }

      if (execution == 1) {
          let height = sizeSize.split('x')[0];
          let length = sizeSize.split('x')[1];
          let area = height * length / 10000;

          if (area <= 0.4)
              executionPrice = area * 80;
          else if (area > 0.4 && area <= 1)
              executionPrice = area * 60;
          else if (area >= 1.01)
              executionPrice = area * 50;
      }

      return executionPrice;
  }

  let galleryCalcTextItem = $(".mcard-price span");
  let typeGallery = normalizeNumber($(".gallery-js-type.active").data("price")),
      dataGallery = normalizeNumber($(".gallery-js-data.active").data("price")),
      sizeGallery = normalizeNumber($(".gallery-js-size li.active input").data("price")),
      frameGallery = 0,
      exectValue = 0;
      var pid = $(".mcard-title").data("pid");
      var name = $(".mcard-title").data("name");
      var price = typeGallery + dataGallery + sizeGallery + frameGallery + exectValue;
      var holst_id = 2;
      var type = buildTypeText($(".gallery-js-type.active").data("execution"));
      var size = $(".gallery-js-size li.active input").data("size");
      var terms = $(".gallery-js-data.active").data("text");
      var userComment = "";
      var photo_ex = "";
      var dost_time = "";
      var image = null;
      var is_gall_with_img = 0;
      var pack = "undefined";
      var forma_id = "undefined";
      var users_count = "undefined";
      var decor_id = "undefined";
      var compl_id = "undefined";
      var hud_of = "";
      var ram_id = 12;

  galleryItemsCalculate()

  function galleryItemsCalculate(exect = null) {


    if ($('.rcard-type__trigger.active').length) {
    }
    const addedFrame = $('.frame-item.is-added');
    frameGallery = addedFrame.length ? normalizeNumber(addedFrame.data('price')) : 0;
    typeGallery = normalizeNumber($(".gallery-js-type.active").data("price")),
    dataGallery = normalizeNumber($(".gallery-js-data.active").data("price")),
    sizeGallery = normalizeNumber($(".gallery-js-size li.active input").data("price"));
    size = $(".gallery-js-size li.active input").data("size");
    type = buildTypeText(exect);
    terms = $(".gallery-js-data.active").data("text");

    if (!exect) {
      exect = $(".gallery-js-type.active").data("execution")
    }
    exectValue = calcExecutionPrice(size, exect)
    price = typeGallery + dataGallery + sizeGallery + frameGallery + exectValue;
    galleryCalcTextItem.text(price);
    updateFrameButtons();
  }

  $(document).on("change", ".js_size", function () {
    galleryItemsCalculate();
  });

  $(document).on("change", ".gallery-client-photo-input", function () {
    const file = this.files && this.files[0] ? this.files[0] : null;
    const $input = $(this);
    const $box = $input.closest(".file-save");

    if (!$box.length) {
      return;
    }

    if (!file) {
      $box.find(".abs-close").hide();
      $input.siblings(".js-file-preview").show();
      $input.siblings(".js-file-upload").hide();
      $input.siblings(".js-file-multiple").hide();
      return;
    }

    let size = file.size;
    const units = ["Bytes", "KB", "MB", "GB"];
    let unitIdx = 0;
    while (size > 900 && unitIdx < units.length - 1) {
      size /= 1024;
      unitIdx++;
    }
    const exactSize = `${Math.round(size * 100) / 100} ${units[unitIdx]}`;

    $box.find(".abs-close").fadeIn();
    $input.siblings(".js-file-preview").hide();
    $input.siblings(".js-file-upload").hide();
    $input.siblings(".js-file-multiple").css({ display: "flex" });
    $input.siblings(".js-file-upload").find("p").text(file.name);
    $input.siblings(".js-file-upload").find("span").text(exactSize);
  });

  $(document).on("click", ".gallery-photo-clear", function (e) {
    e.preventDefault();
    e.stopPropagation();

    const $close = $(this);
    const $box = $close.closest(".file-save");
    const $input = $box.find(".gallery-client-photo-input");

    $input.val("");
    $close.hide();
    $input.siblings(".js-file-preview").show();
    $input.siblings(".js-file-upload").hide();
    $input.siblings(".js-file-multiple").hide();
  });

  $("#buy .mm-btn").on("click", function (e) {
    e.preventDefault();

    // Ensure frame state is clean before calculating price
    const addedFrame = $('.frame-item.is-added');
    const addedFramePrice = addedFrame.length ? addedFrame.data('price') : null;
    frameGallery = normalizeNumber(addedFramePrice);
    if (!addedFrame.length) {
      $('.rcard-type__trigger').removeClass('active');
    }
    galleryItemsCalculate();

    var recommendationDiscount = $(this).data('recommendation-discount');
    var itemId = $(this).data('item-id');

    if (recommendationDiscount && recommendationDiscount !== '') {
      var currentPrice = price;

      $.ajax({
        type: 'POST',
        dataType: "json",
        headers: {
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        url: "/basket/add/recommended",
        data: {
          item_id: itemId,
          price: currentPrice
        },
        success: function (response) {
          $("body").addClass("open-frame");
          $(".popup-frame").css("display", "flex").hide().fadeIn();
          $(".popup-cart").fadeIn();
        },
        error: function(xhr, status, error) {
          if (xhr.responseJSON && xhr.responseJSON.message) {
            alert(xhr.responseJSON.message);
          }
        }
      });
    } else {
      const form_data = new FormData();
      form_data.append('pid', pid);
      form_data.append('price', price);
      form_data.append('name', name);
      form_data.append('userComment', userComment);
      form_data.append('photo_ex', photo_ex);
      form_data.append('pack', pack);
      form_data.append('dost_time', dost_time);
      form_data.append('image', image);
      form_data.append('is_gall_with_img', is_gall_with_img);
      form_data.append('forma_id', forma_id);
      form_data.append('size', size);
      form_data.append('users_count', users_count);
      form_data.append('type', type);
      form_data.append('holst_id', holst_id);
      form_data.append('hud_of', hud_of);
      form_data.append('decor_id', decor_id);
      form_data.append('compl_id', compl_id);
      form_data.append('terms', terms);
      const clientPhoto = getClientPhotoFile();
      if (clientPhoto) {
        form_data.append('orig_images[]', clientPhoto);
      }
      const addedFrame = $('.frame-item.is-added');
      if (addedFrame.length) {
        form_data.append('ram_id', addedFrame.data('code'));
        form_data.append('framePrice', normalizeNumber(addedFrame.data('price')));
      } else {
        form_data.append('ram_id', ram_id);
      }

      $.ajax({
        type: 'POST',
        processData: false,
        contentType: false,
        dataType: "json",
        headers: {
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        url: "/basket/add/portrait",
        data: form_data,
        success: function (response) {
          $("body").addClass("open-frame");
          $(".popup-frame").css("display", "flex").hide().fadeIn();
          $(".popup-cart").fadeIn();
        },
      });
    }

  });

    document.querySelectorAll('.mcard-tabs').forEach(container => {
      const tabsCount = container.querySelectorAll('.mcard-tab').length;
      if (tabsCount === 2) {
        container.classList.add('mcard-tabs--two');
      }
    });
});
