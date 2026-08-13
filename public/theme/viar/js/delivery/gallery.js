$(document).ready(function () {


  let gallerySlider = $(".gs-slider");

  gallerySlider.slick({
    infinite: false,
    slidesToShow: 1,
    arrows: false,
    dots: true,
    fade: true,
    autoplay: true
  });


  let categorySlider = $(".g-category-slider");

  categorySlider.slick({
    infinite: false,
    slidesToShow: 3,
    prevArrow:`<button type='button' class='slick-prev pull-left'><svg width="12" height="19" viewBox="0 0 12 19" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M11 18L2 9.5L11 1" stroke="#1E2533" stroke-width="2"/>
    </svg>
    </button>`,
    nextArrow:`<button type='button' class='slick-next pull-right'><svg width="12" height="19" viewBox="0 0 12 19" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0.999999 0.999999L10 9.5L1 18" stroke="white" stroke-width="2"/>
            </svg>
            </button>`,
    dots: false,
    responsive: [
      {
        breakpoint: 981,
        settings: {
          slidesToShow: 2,
        }
      },
      {
        breakpoint: 701,
        settings: {
          slidesToShow: 1
        }
      }
    ]
  });


  let bsSlider = $(".bs-slider");

  bsSlider.slick({
    infinite: false,
    slidesToShow: 3,
    prevArrow:`<button type='button' class='slick-prev pull-left'><svg width="12" height="19" viewBox="0 0 12 19" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M11 18L2 9.5L11 1" stroke="#1E2533" stroke-width="2"/>
    </svg>
    </button>`,
    nextArrow:`<button type='button' class='slick-next pull-right'><svg width="12" height="19" viewBox="0 0 12 19" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0.999999 0.999999L10 9.5L1 18" stroke="white" stroke-width="2"/>
            </svg>
            </button>`,
    dots: false,
    responsive: [
      {
        breakpoint: 981,
        settings: {
          slidesToShow: 2,
        }
      },
      {
        breakpoint: 701,
        settings: {
          slidesToShow: 1
        }
      }
    ]
  });

   
  mobileOnlyCategorySlider(".cp-block-inner", 981);

  function mobileOnlyCategorySlider($slidername, $breakpoint) {
    var slider = $($slidername);
    var settings = {
      infinite: false,
      slidesToShow: 1,
      mobileFirst: true,
      arrows: false,
      variableWidth: true,
      // responsive: [
      //   {
      //     breakpoint: 981,
      //     settings: "unslick"
      //   }
      // ]
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

      if (mediaChecker('max', 981)) {
        $(".cp-block-inner")?.slick('refresh');
      }

      return false;
    });
  });

  // document.querySelectorAll(".trybuy-main .cp-tab").forEach((element) => {
  //   element.addEventListener("click", function (e) {
  //     e.preventDefault();
  //     let _this = this;
  //     changeActiveTab(
  //       _this,
  //       ".trybuy-main .category-pop--block",
  //       ".trybuy-main .cp-block",
  //       ".trybuy-main .cp-tab",
  //       "active"
  //     );
    

  //     return false;
  //   });
  // });

  const mediaChecker = (max_min, resolution, width = "width") => window.matchMedia(`(${max_min}-${width}: ${resolution}px)`).matches;


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
});
