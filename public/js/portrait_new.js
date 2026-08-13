$(document).ready(function () {

  let exampleSlider = $(".examples-about__slider");

  exampleSlider.slick({

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



  let exampleSlider2 = $(".examples-slider__inner");

  exampleSlider2.slick({

    infinite: false,

    slidesToShow: 2,

    lazyLoad: "ondemand",

    prevArrow: ".examples-prev.examples-prev2",

    nextArrow: ".examples-next.examples-next2",

    responsive: [

      {

        breakpoint: 981,

        settings: {

          slidesToShow: 1,

        },

      },

    ],

  });



  let othersSlider = $(".others-slider");

  othersSlider.slick({

    infinite: false,

    slidesToShow: 2,

    lazyLoad: "ondemand",

    prevArrow: ".examples-prev3",

    nextArrow: ".examples-next3",

    responsive: [

      {

        breakpoint: 1180,

        settings: {

          slidesToShow: 1,

        },

      },

    ],

  });



  let stageSlider = $(".stages-row");

  stageSlider.slick({

    infinite: false,

    slidesToShow: 1,

    prevArrow: ".examples-prev4",

    nextArrow: ".examples-next4",

    responsive: [

      {

        breakpoint: 5000,

        settings: "unslick",

      },

      {

        breakpoint: 500,

        settings: {

          slidesToShow: 1,

        },

      },

    ],

  });



  $(".ba-slider").beforeAfter();



  // TABS FUNCTIONS



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

  // trigger for comments

  document.querySelectorAll(".about-tab").forEach((element) => {

    element.addEventListener("click", function (e) {

      e.preventDefault();

      let _this = this;

      changeActiveTab(

        _this,

        ".about__screen",

        ".about__block",

        ".about-tab",

        "active"

      );

      try {
        exampleSlider[0]?.slick?.refresh();

      } catch(e) {
        console.log('Slider refresh isnt supported');
      }



      return false;

    });

  });



  $(".formalization-tab").click(function () {

    $(this).next().slideToggle();

    $(this).toggleClass("opened-tab");

    $(this).parent().next().fadeToggle();

  });



  $(".form-image").click(function () {

    $(".form-image").removeClass("active");

    $(this).toggleClass("active");

  });



  $(".kviz-c-group .kviz-radio").click(function (e) {
    $(this)

      .closest(".kviz-c-group")

      .find(".kviz-radio")

      .removeClass("kviz-radio_active");

    $(this).addClass("kviz-radio_active");
      $(this).find('input').trigger('change');
  });



  $(".kviz-types").click(function () {

    _this = $(this);

    _parent = _this.parent();

    if (_this.data("count") == 1) {

      _parent.find(".input-group").addClass("input_disabled");

      _parent.next().addClass("input_disabled");

      _parent.next().find("input").prop("disabled", true);

      _parent.next().find(".kviz-radio").removeClass("kviz-radio_active");

      _parent.find(".input-group input").prop("disabled", true);
        $("#count1").val('1');
    } else {

      _parent.find(".input-group").removeClass("input_disabled");

      _parent.next().removeClass("input_disabled");

      _parent.next().find("input").prop("disabled", false);

      _parent.find(".input-group input").prop("disabled", false);
        $("#count1").val('2');
    }
      $('#count1').trigger('change');
      $('#count1').trigger('keyup');
  });



  var playVideo = $(".video-btn");

  var playButton = $(".video-btn");

  var video = $("#videoPlayer");

  // Event listener for the play/pause button

  playVideo.click(function () {

    if (video[0].paused == true) {

      // Play the video

      video[0].play();



      // Update the button text to 'Pause'

      playButton.fadeOut();

    } else {

      // Pause the video

      video[0].pause();



      // Update the button text to 'Play'

      playButton.fadeIn();

    }

  });



  $(".hidden-trigger").click(function () {

    $(this).toggleClass("active");

    $(this).closest(".about__block").toggleClass("brief-v");

    if ($(this).closest(".about__block").hasClass('brief-v')) {

      $(".about__screen")[0].scrollIntoView({

        behavior: "smooth", // or "auto" or "instant"

        block: "start" // or "end"

      })

    }

  });



  function mediaChecker(max_min, resolution, width = "width") {

    return window.matchMedia(`(${max_min}-${width}: ${resolution}px)`).matches;

  }



  window.onresize = function () {

    mediaAction();

  };

  mediaAction();

  function mediaAction() {

    if (mediaChecker("max", 500)) {

      try {
        stageSlider[0]?.slick?.refresh();

      } catch(e) {
        console.log("Refresh");
      }


    }

  }



  let bf_obj = $(".bf-obj");



  bf_obj.slick({

    slidesToShow: 7,

    infinite: false,

    prevArrow: ".bf-arr_l",

    nextArrow: ".bf-arr_r",

  });



  $(".examples-slider__inner img").click(function () {

    let first = $(this).attr("src");

    let second = $(this).data("image");

    let slideTarget = $(this).closest('.examples-slide');

    let index = [...slideTarget[0].parentElement.children].indexOf(slideTarget[0]) + 1;

    //fromSliderBeforeAfterSetter(first, second);

    //beforeAfterNavSetter(index)

  });



  $('.bf-obj li').click(function() {

    $(`.bf-obj li`).removeClass("active");

    $(this).addClass("active");

    let first = $(this).data("imageb");

    let second = $(this).data("imagea");

    console.log(first, second)

    fromSliderBeforeAfterSetter(first, second);

  })



  function fromSliderBeforeAfterSetter(first, second) {

    $(".ba-slider > picture img").attr("srcset", first);

    $(".ba-slider > picture source").attr("srcset", first);

    $(".ba-slider .resize img").attr("srcset", second);

    $(".ba-slider .resize source").attr("srcset", second);

  }



  let _beforeAfterCount = $(".bf-obj li").length;

  console.log(_beforeAfterCount)

  if (_beforeAfterCount <= 7) {

    bf_obj.slick('unslick');

    $('.bf-arr').remove();

    $('.before-after__nav').css('justify-content', 'center');

  }

  let el = $(".examples-slider__inner .examples-slide:nth-child(3)");



  function beforeAfterNavSetter(index) {

    let slickIndex = index - 1;

    $(`.bf-obj li`).removeClass("active");

    $(`.bf-obj li:nth-child(${index})`).addClass("active");

    console.log(slickIndex)

    bf_obj.slick("slickGoTo", slickIndex);

  }









  $('.mobile-sizes--inner ul li').click(function() {

    let index = [...($(this))[0].parentElement.children].indexOf($(this)[0]) + 1;

    $('.sizes-item').removeClass('active');

    $(`.sizes-item:nth-child(${index})`).addClass('active');

  })

});

