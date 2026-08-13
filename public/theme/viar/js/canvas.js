$(document).ready(function () {
  function a() {
    return b;
  }
  var b = 0;
  $(".tabs-content .tabs-item li").on("click", function () {
    var a = $(this).data("tads");
    console.log("click");

    $(".tabs-item li").removeClass("active");
    $(".tabs-container .tabs-item").removeClass("active");
    $(this).addClass("active");
    $("." + a).addClass("active");
    $(".tabs-work-slider").get(0).slick.setPosition();

    $(".tabs-container .tabs-item").removeClass("open");
    $(".collapse").addClass("active"),
      $(".height-content").animate(
        {
          height: 467,
        },
        1e3,
        function () {}
      ),
      $(this).find("span").text($(this).parent().data("show")),
      event.preventDefault();
    var c = $(this).data("tads"),
      d = $("." + c).offset().top - 100;
    $("body,html").animate(
      {
        scrollTop: d,
      },
      1e3
    );
  });

  $(".collapse").on("click", function () {
    var th = $(this);
    var show = th.children().data("show");
    var hide = th.children().data("hide");

    if (show == undefined || hide == undefined) {
      show = th.data("show");
      hide = th.data("hide");
    }

    if (0 == a()) {
      (b = 1), console.log(b);
      var c = $(this).prev(),
        d = $(this).data("collapse"),
        e = $("." + d).height() + 100;
      $(".tabs-item").removeClass("open"),
        $(this).parent().addClass("open"),
        $(this).addClass("active"),
        $(c).animate(
          {
            height: e,
          },
          1e3,
          function () {}
        ),
        $(this).find("a").text(hide),
        event.preventDefault();
      var f = $("." + d).offset().top - 100;
      $("body,html").animate(
        {
          scrollTop: f,
        },
        1e3
      );
    } else {
      (b = 0), console.log(b);
      var c = $(this).prev(),
        d = $(this).data("collapse"),
        e = $("." + d).height();
      $(".tabs-item").removeClass("open"),
        $(this).parent().removeClass("open"),
        $(this).removeClass("active"),
        $(c).animate(
          {
            height: 467,
          },
          1e3,
          function () {}
        ),
        $(this).find("a").text(show);
      var f = $("body").offset().top;
      $("body,html").animate(
        {
          scrollTop: f,
        },
        1e3
      );
    }
  });

  $(".tabs-work-slider").slick({
    infinite: !0,
    slidesToShow: 4,
    slidesToScroll: 1,
    nextArrow:
      '<button class="slick-arrow next"><i class="icon-icon25"></i></button>',
    prevArrow:
      '<button class="slick-arrow prev"><i class="icon-icon22"></i></button>',
    responsive: [
      {
        breakpoint: 1280,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 1,
          nextArrow:
            '<button class="slick-arrow next"><i class="icon-icon25"></i></button>',
          prevArrow:
            '<button class="slick-arrow prev"><i class="icon-icon22"></i></button>',
        },
      },
      {
        breakpoint: 1024,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1,
          nextArrow:
            '<button class="slick-arrow next"><i class="icon-icon25"></i></button>',
          prevArrow:
            '<button class="slick-arrow prev"><i class="icon-icon22"></i></button>',
        },
      },
      {
        breakpoint: 767,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1,
          nextArrow:
            '<button class="slick-arrow next"><i class="icon-icon25"></i></button>',
          prevArrow:
            '<button class="slick-arrow prev"><i class="icon-icon22"></i></button>',
        },
      },
    ],
  }),
    $(".our-work-items").slick({
      infinite: !0,
      slidesToShow: 3,
      slidesToScroll: 1,
      nextArrow:
        '<button class="slick-arrow next"><i class="icon-icon25"></i></button>',
      prevArrow:
        '<button class="slick-arrow prev"><i class="icon-icon22"></i></button>',
      responsive: [
        {
          breakpoint: 1280,
          settings: {
            slidesToShow: 3,
            slidesToScroll: 1,
            nextArrow:
              '<button class="slick-arrow next"><i class="icon-icon25"></i></button>',
            prevArrow:
              '<button class="slick-arrow prev"><i class="icon-icon22"></i></button>',
          },
        },
        {
          breakpoint: 1024,
          settings: {
            slidesToShow: 2,
            slidesToScroll: 1,
            nextArrow:
              '<button class="slick-arrow next"><i class="icon-icon25"></i></button>',
            prevArrow:
              '<button class="slick-arrow prev"><i class="icon-icon22"></i></button>',
          },
        },
        {
          breakpoint: 767,
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1,
            nextArrow:
              '<button class="slick-arrow next"><i class="icon-icon25"></i></button>',
            prevArrow:
              '<button class="slick-arrow prev"><i class="icon-icon22"></i></button>',
          },
        },
      ],
    }),
    $(".stages-items").slick({
      slidesToShow: 3,
      slidesToScroll: 1,
      nextArrow:
        '<button class="slick-arrow next"><i class="icon-icon25"></i></button>',
      prevArrow:
        '<button class="slick-arrow prev"><i class="icon-icon22"></i></button>',
      responsive: [
        {
          breakpoint: 767,
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1,
            nextArrow:
              '<button class="slick-arrow next"><i class="icon-icon25"></i></button>',
            prevArrow:
              '<button class="slick-arrow prev"><i class="icon-icon22"></i></button>',
          },
        },
      ],
    }),
    $(window).width() >= 1025 && $(".stages-items").slick("unslick"),
    $(window).width() >= 768 &&
      // ($(".stages-item .img").matchHeight({
      //   byRow: !1,
      // }),
      // $(".stages-item .text").matchHeight({
      //   byRow: !1,
      // }),
      // $(".our-work-item .img").matchHeight({
      //   byRow: !1,
      // }),
      // $(".our-work-item .name").matchHeight({
      //   byRow: !1,
      // }),
      // $(".our-work-item .text").matchHeight({
      //   byRow: !1,
      // }),
      // $(".search-image-item").matchHeight({
      //   byRow: !1,
      // })),
    jcf.replaceAll(),
    $(".jcf-fake-input").text("Загрузить фотографии"),
    $(".slider-tabs").on("click", "a", function () {
      var a = $(this).data("tabs");
      $(".slider-tabs a").removeClass("active"),
        $(this).addClass("active"),
        $(".gallery-photo .tabs-item").removeClass("active"),
        $("." + a).addClass("active"),
        // $(".picture").get(0).slick("setPosition"),
        $(".interior-slider").length &&
          $(".interior-slider").get(0) &&
          $(".interior-slider").get(0).slick &&
          $(".interior-slider").slick('slickGoTo', 0);
        // $(".ramu-slider").get(0).slick("setPosition"),
        // $(".ramu-item .img").matchHeight({
        //   byRow: !1,
        // });
    }),
    // $(".picture").slick({
    //   infinite: !0,
    //   slidesToShow: 1,
    //   slidesToScroll: 1,
    //   nextArrow:
    //     '<button class="slick-arrow next"><i class="icon-icon25"></i></button>',
    //   prevArrow:
    //     '<button class="slick-arrow prev"><i class="icon-icon22"></i></button>',
    // }),
    $(".interior-slider").length &&
      $(".interior-slider").not(".slick-initialized").slick({
      infinite: !0,
      slidesToShow: 3,
      slidesToScroll: 1,
      nextArrow:
        `<a href="javascript:void(0)" class="slick-arrow next"><svg width="10" height="17" viewBox="0 0 10 17" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0.766461 15.1145C0.666015 15.0141 0.615791 14.8986 0.615791 14.768C0.615791 14.6374 0.666015 14.5219 0.766461 14.4215L6.68778 8.50014L0.766461 2.57882C0.666015 2.47838 0.615791 2.36286 0.615791 2.23228C0.615791 2.1017 0.666015 1.98619 0.766461 1.88574L1.51981 1.13239C1.62026 1.03195 1.73577 0.981724 1.86635 0.981724C1.99693 0.981724 2.11244 1.03195 2.21289 1.13239L9.2341 8.1536C9.33454 8.25405 9.38477 8.36956 9.38477 8.50014C9.38477 8.63072 9.33454 8.74623 9.2341 8.84668L2.21289 15.8679C2.11244 15.9683 1.99693 16.0186 1.86635 16.0186C1.73577 16.0186 1.62026 15.9683 1.51981 15.8679L0.766461 15.1145Z" fill="#FC8C5F"/>
        </svg>
        </a>`,
      prevArrow:
        `<a href="javascript:void(0)" class="slick-arrow prev"><svg width="10" height="16" viewBox="0 0 10 16" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M9.23354 14.5804C9.33399 14.4799 9.38421 14.3644 9.38421 14.2338C9.38421 14.1032 9.33399 13.9877 9.23354 13.8873L3.31222 7.96596L9.23354 2.04464C9.33399 1.9442 9.38421 1.82868 9.38421 1.6981C9.38421 1.56752 9.33399 1.45201 9.23354 1.35156L8.48019 0.598213C8.37974 0.497767 8.26423 0.447544 8.13365 0.447544C8.00307 0.447544 7.88756 0.497767 7.78711 0.598213L0.765904 7.61942C0.665458 7.71987 0.615234 7.83538 0.615234 7.96596C0.615234 8.09654 0.665458 8.21205 0.765904 8.3125L7.78711 15.3337C7.88756 15.4342 8.00307 15.4844 8.13365 15.4844C8.26423 15.4844 8.37974 15.4342 8.48019 15.3337L9.23354 14.5804Z" fill="#FC8C5F"/>
        </svg>
        </a>`,
      }),
    new jQueryCollapse($(".filter-accordion"), {
      open: function () {
        this.slideDown(400);
      },
      close: function () {
        this.slideUp(400);
      },
    }),
    $(".shapes li a").on("click", function () {
      $(".shapes li a").removeClass("active"), $(this).addClass("active");
    }),
    $(".size li a").on("click", function () {
      $(".size li a").removeClass("active"), $(this).addClass("active");
    }),
    $(".execution-item").on("click", function () {
      $(".execution-item").removeClass("active"), $(this).addClass("active");
    }),
    // $(".execution-item .img").matchHeight({
    //   byRow: !1,
    // }),
    $(".effects-item").on("click", function () {
      $(".effects-item").removeClass("active"), $(this).addClass("active");
    }),
    // $(".effects-item .img").matchHeight({
    //   byRow: !1,
    // }),
    $(".interior-item").on("click", function () {
      $(".interior-item").removeClass("active"), $(this).addClass("active");
    }),
    $(".ramu-item").on("click", function () {
      $(".ramu-item").removeClass("active"), $(this).addClass("active");
    }),
    $(".range-input").on("input", function () {}),
    // $(".ramu-slider").slick({
    //   rows: 1,
    //   infinite: !0,
    //   slidesToShow: 3,
    //   slidesToScroll: 1,
    //   nextArrow:
    //     '<a href="javascript:void(0)" class="slick-arrow next"><i class="icon-icon25"></i></a>',
    //   prevArrow:
    //     '<a href="javascript:void(0)" class="slick-arrow prev"><i class="icon-icon22"></i></a>',
    // }),
    // $(".ramu-item .img").matchHeight({
    //   byRow: !1,
    // }),
    $(".product-download .jcf-button-content").text("Загрузить"),
    $(".imageFile").change(function () {
      $(".product-download .jcf-extension-png .jcf-button-content").text("+ Х");
    });
    // $(".product-download .img").matchHeight({
    //   byRow: !1,
    // });
});

  $(window).resize(function () {
    $(window).width() >= 1025 && $(".stages-items").slick("unslick");
      // $(".product-download .img").matchHeight({
      //   byRow: !1,
      // });
  });
