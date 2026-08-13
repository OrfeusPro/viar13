document.addEventListener("DOMContentLoaded", function (event) {
  var lazyloadImages = document.querySelectorAll("img.lazyload");
  var lazyloadThrottleTimeout;

  function lazyload() {
    if (lazyloadThrottleTimeout) {
      clearTimeout(lazyloadThrottleTimeout);
    }

    lazyloadThrottleTimeout = setTimeout(function () {
      var scrollTop = window.pageYOffset;
      lazyloadImages.forEach(function (img) {
        if (img.offsetTop < window.innerHeight + scrollTop) {
          img.src = img.dataset.src;
          img.classList.remove("lazyload");
        }
      });
      if (lazyloadImages.length == 0) {
        document.removeEventListener("scroll", lazyload);
        window.removeEventListener("resize", lazyload);
        window.removeEventListener("orientationChange", lazyload);
      }
    }, 20);
  }

  document.addEventListener("scroll", lazyload);
  window.addEventListener("resize", lazyload);
  window.addEventListener("orientationChange", lazyload);

  $("img.img-svg").each(function () {
    var $img = $(this);
    var imgClass = $img.attr("class");
    var imgURL = $img.attr("src");
    $.get(
      imgURL,
      function (data) {
        var $svg = $(data).find("svg");
        if (typeof imgClass !== "undefined") {
          $svg = $svg.attr("class", imgClass + " replaced-svg");
        }
        $svg = $svg.removeAttr("xmlns:a");
        if (
          !$svg.attr("viewBox") &&
          $svg.attr("height") &&
          $svg.attr("width")
        ) {
          $svg.attr(
            "viewBox",
            "0 0 " + $svg.attr("height") + " " + $svg.attr("width")
          );
        }
        $img.replaceWith($svg);
      },
      "xml"
    );
  });

  setTimeout(function () {
    var input1 = document.querySelector("#phone-banner");
    var input2 = document.querySelector("#phone");
    window.intlTelInput(input1, {
      initialCountry: "lv",
      onlyCountries: [
        "al",
        "ad",
        "at",
        "by",
        "be",
        "ba",
        "bg",
        "hr",
        "cz",
        "dk",
        "ee",
        "fo",
        "fi",
        "fr",
        "de",
        "gi",
        "gr",
        "va",
        "hu",
        "is",
        "ie",
        "it",
        "lv",
        "li",
        "lt",
        "lu",
        "mk",
        "mt",
        "md",
        "mc",
        "me",
        "nl",
        "no",
        "pl",
        "pt",
        "ro",
        "ru",
        "sm",
        "rs",
        "sk",
        "si",
        "es",
        "se",
        "ch",
        "ua",
        "gb",
      ],
      utilsScript: "/js/utils.js?1613236686837",
    });
    window.intlTelInput(input2, {
      // any initialisation options go here
    });
  }, 4000);

  function uploadFile() {
    $(".banner__input-hide").change(function (e) {
      console.log(e.target.files[0]);
      document.querySelector(".form-send__text span").textContent =
        e.target.files[0].name;
      document.querySelector(".form-send__lbl-text").textContent =
        Math.floor((e.target.files[0].size / 1024 / 1024) * 100) / 100 + "MB";
      document.querySelector(".banner__upload svg").style.display = "none";
      document.querySelector(".image-gallary").style.display = "block";
    });
    $(".form-send__input-hide").change(function (e) {
      console.log(e.target.files[0]);
      document.querySelector(".form-quiz__text span").textContent =
        e.target.files[0].name;
      document.querySelector(".form-quiz__size").textContent =
        Math.floor((e.target.files[0].size / 1024 / 1024) * 100) / 100 + "MB";
      document.querySelector(".form-send__upload svg").style.display = "none";
      document.querySelector(".image-gallary1").style.display = "block";
    });
  }
  uploadFile();

  // function menu(menuBtn, block, close) {
  //   if (document.querySelector(menuBtn)) {
  //     document.querySelector(menuBtn).addEventListener("click", () => {
  //       document.querySelector(block).classList.add("header__list_open");
  //       document.querySelector(menuBtn).style.display = "none";
  //       document.querySelector(close).style.display = "flex";
  //       document.body.style.overflow = "hidden";
  //     });
  //     document.querySelector(close).addEventListener("click", () => {
  //       document.body.style.overflow = "auto";
  //       document.querySelector(menuBtn).style.display = "flex";
  //       document.querySelector(close).style.display = "none";
  //       document.querySelector(block).classList.remove("header__list_open");
  //     });
  //   }
  // }
  // menu(".header__humburger", ".header__list", ".header__close");

  // function menuListOpen() {
  //   if (document.documentElement.clientWidth > 1360) {
  //     document.querySelectorAll(".header__list li").forEach(function (item) {
  //       item.addEventListener("click", function () {
  //         if (this.querySelector(".menu").classList.contains("menu__open")) {
  //           this.querySelector(".menu").classList.remove("menu__open");
  //           document.querySelector(".header").classList.remove("header__white");
  //           this.querySelector("svg").style.cssText =
  //             "transform: rotate(90deg)";
  //         } else {
  //           document
  //             .querySelectorAll(".header__list li")
  //             .forEach(function (allItems) {
  //               if (allItems.querySelector(".menu")) {
  //                 allItems
  //                   .querySelector(".menu")
  //                   .classList.remove("menu__open");
  //               }
  //             });
  //           if (this.querySelector(".menu")) {
  //             this.querySelector("svg").style.cssText =
  //               "transform: rotate(270deg)";
  //             this.querySelector(".menu").classList.add("menu__open");
  //             document.querySelector(".header").classList.add("header__white");
  //           }
  //         }
  //       });
  //     });
  //     document.querySelectorAll(".menu").forEach(function (item) {
  //       item.addEventListener("mouseleave", function () {
  //         this.classList.remove("menu__open");
  //         document.querySelector(".header").classList.remove("header__white");
  //         this.parentElement.querySelector("svg").style.cssText =
  //           "transform: rotate(90deg)";
  //       });
  //     });
  //     $(".menu").each(function () {
  //       let ths = $(this);
  //       ths.find(".menu__contentItem").not(":first").hide();
  //       ths
  //         .find(".menu__product-tab")
  //         .on("mouseenter", function () {
  //           ths
  //             .find(".menu__product-tab")
  //             .removeClass("menu__product-tab--activeTab")
  //             .eq($(this).index())
  //             .addClass("menu__product-tab--activeTab");
  //           ths.find(".menu__contentItem").hide().eq($(this).index()).fadeIn();
  //         })
  //         .eq(0)
  //         .addClass("active");
  //     });
  //   } else if (document.documentElement.clientWidth < 1360) {
  //     $(document).ready(function () {
  //       $(".header__list-item").click(function () {
  //         $(this).toggleClass("ins").find(".menu").slideToggle();
  //       });
  //     });
  //   }
  // }
  // menuListOpen();

  // function optionClick() {
  //   let selected = null;
  //   document.querySelectorAll(".content-quiz__block").forEach((item) => {
  //     item.addEventListener(
  //       "click",
  //       (event) => {
  //         if (event.target.checked) {
  //           if (selected) {
  //             selected.classList.remove("check");
  //           }
  //           selected = event.target;
  //           selected.classList.add("check");
  //         }
  //         if (event.target.checked) {
  //           // Если клик был на элемент первого блока вопросов
  //           document
  //             .querySelector(".content-quiz__next")
  //             .classList.add("nextActive"); // Делаем кнопку Далее активной
  //         } else if (
  //           event.target.classList.contains("content-quiz__item--second")
  //         ) {
  //           document
  //             .querySelector(".content-quiz__next")
  //             .classList.add("nextActive");
  //         } else if (
  //           event.target.classList.contains("content-quiz__item--third")
  //         ) {
  //           document
  //             .querySelector(".content-quiz__next")
  //             .classList.add("nextActive");
  //         } else if (
  //           event.target.classList.contains("content-quiz__item--fourth")
  //         ) {
  //           document
  //             .querySelector(".content-quiz__next")
  //             .classList.add("nextActive");
  //         }
  //       },
  //       false
  //     );
  //   });
  // }
  // optionClick();

  function quiz() {
    var q_form = document.querySelector('.js_quiz');

    let counter = -1,
      step = 1,
      titles = {
        zero: q_form.dataset.zero,
        first: q_form.dataset.first,
        second: q_form.dataset.second,
        third: q_form.dataset.third,
      };

    // Функция смены заголовка
    function setTitle() {
      let titleBlock = document.querySelector(".content-quiz__title"); // Заголовок
      let stepNum = document.querySelector(
        ".content-quiz__progress-title span"
      ); // Число процентов
      let stepBlock = document.querySelector(".content-quiz__progress-title");

      // Функция смены шага
      function setCurrentStep() {
        stepNum.textContent = step;
      }

      switch (counter) {
        case -1:
          titleBlock.textContent = titles.first;
          setCurrentStep();
          break;
        case 0:
          titleBlock.textContent = titles.second;
          setCurrentStep();
          break;
        case 1:
          titleBlock.textContent = titles.second;
          setCurrentStep();
          break;
        case 2:
          titleBlock.textContent = titles.third;
          setCurrentStep();
          break;
        case 3:
          function hideTitle() {
            var q_form = document.querySelector('.js_quiz');
            var q_form_done_text = q_form.dataset.all_done;

            stepBlock.textContent = q_form_done_text;
            titleBlock.classList.add("blockHide");
            function hideNew() {
              titleBlock.classList.add("dNone");
            }
            setTimeout(hideNew, 250);
          }
          hideTitle();
          break;
        default:
          titleBlock.textContent = "";
      }
    }

    // Функция скрытия блока с вариантами выбора
    function hide(block) {
      document.querySelector(block).classList.add("blockHide");
      function hideNew() {
        document.querySelector(block).classList.add("dNone");
      }
      setTimeout(hideNew, 250);
    }

    // Функция показа следующего блока с вариантами выбора
    function show(block) {
      function showNew() {
        document.querySelector(block).classList.remove("dNone");
        document.querySelector(block).classList.add("dBlock");
        function s() {
          document.querySelector(block).classList.remove("blockHide");
          document.querySelector(block).classList.add("blockShow");
        }
        setTimeout(s, 250);
      }
      setTimeout(showNew, 250);
    }

    function orange() {
      document
        .querySelector(".content-quiz__progress-orange")
        .addEventListener("click", () => {
          counter = 0;
          hide(".content-quiz__zero");
          setTimeout(hide(".content-quiz__zero"), 0);
          show(".content-quiz__first");
        });
      document
        .querySelector(".content-quiz__progress-btn")
        .addEventListener("click", () => {
          hide(".content-quiz__first");
          counter = -1;
          setTimeout(hide(".content-quiz__first"), 0);
          show(".content-quiz__zero");
        });
    }
    orange();

    function buttonClick(button, btnClass) {
      document.querySelector(button).addEventListener("click", function () {
        if (this.classList.contains(btnClass)) {
          counter = counter + 1;
          step = step + 1;
          setTitle();

          if (counter == 0) {
            hide(".content-quiz__zero");
            show(".content-quiz__second");
            document.querySelector(".quiz-right__badge").style.display = "none";
            document
              .querySelector(".content-quiz__skip")
              .classList.add("skipActive");
            document
              .querySelector(".content-quiz__skip")
              .classList.remove("dNone");
            document.querySelector(
              ".content-quiz__progress-block"
            ).style.cssText = "opacity: 0";
            counter = 1;
          } else if (counter == 1) {
            hide(".content-quiz__first");
            setTimeout(hide(".content-quiz__first"), 0);
            show(".content-quiz__second");
            document.querySelector(".quiz-right__badge").style.display = "none";
            document
              .querySelector(".content-quiz__skip")
              .classList.add("skipActive");
            document
              .querySelector(".content-quiz__skip")
              .classList.remove("dNone");
            document.querySelector(
              ".content-quiz__progress-block"
            ).style.cssText = "opacity: 0";
          } else if (counter == 2) {
            hide(".content-quiz__zero");
            setTimeout(hide(".content-quiz__zero"), 0);
            hide(".content-quiz__second");
            setTimeout(hide(".content-quiz__second"), 0);
            show(".content-quiz__third");
          } else if (counter == 3) {
            hide(".content-quiz__third");
            setTimeout(hide(".content-quiz__third"), 0);
            function nShow() {
              function showNew() {
                document.querySelector(".form-send").classList.add("dBlock");
                function s() {
                  document
                    .querySelector(".form-send")
                    .classList.add("blockShow");
                }
                setTimeout(s, 150);
              }
              setTimeout(showNew, 250);
            }
            nShow();
            document
              .querySelectorAll(".quiz-right__images img")
              .forEach((img) => {
                img.style.display = "none";
              });
            document.querySelector(".quiz-right__badge").style.display = "flex";
            document.querySelector(".quiz-right__finish img").style.display =
              "block";
            document
              .querySelector(".quiz-right__question")
              .classList.add("dNoneHigh");
            document.querySelector(".quiz-right__send").classList.add("dFlex");
            document
              .querySelector(".content-quiz__navigation")
              .classList.add("dNoneHigh");
            if (document.querySelector(".quiz-right__mobile-text")) {
              document.querySelector(".quiz-right__mobile-text").style.cssText =
                "text-align: left";
            }
          }

          document
            .querySelector(".content-quiz__next")
            .classList.remove("nextActive");
        } else {
          document
            .querySelector(".quiz-modal")
            .classList.add("quiz-modal__active");
          document
            .querySelector(".quiz-modal__btn")
            .addEventListener("click", () => {
              document
                .querySelector(".quiz-modal")
                .classList.remove("quiz-modal__active");
            });
        }
      });
    }
    buttonClick(".content-quiz__next", "nextActive");
    buttonClick(".content-quiz__skip", "skipActive");

    function setNewImage(radio, image) {
      document.querySelectorAll(radio).forEach((item) => {
        item.addEventListener("change", () => {
          if (item.checked) {
            document
              .querySelectorAll(".quiz-right__images img")
              .forEach((img) => {
                img.style.display = "none";
              });
            document.querySelector(image).style.display = "block";
          }
        });
      });
    }
    setNewImage(".content-quiz__item_first input", ".quiz-right__image1 img");
    setNewImage(".content-quiz__item_second input", ".quiz-right__image2 img");

    function setNewTitle(btn, pastBtn, title) {
      document.querySelector(btn).addEventListener("click", function () {
        document
          .querySelector(pastBtn)
          .classList.remove("content-quiz__progress-active");
        document.querySelector(".content-quiz__title").textContent = title;
        this.classList.add("content-quiz__progress-active");
      });
    }
    setNewTitle(
      ".content-quiz__progress-orange",
      ".content-quiz__progress-btn",
      "1. Для когого события вы ищете подарок?"
    );
    setNewTitle(
      ".content-quiz__progress-btn",
      ".content-quiz__progress-orange",
      "1. Для кого вы ищете подарок?"
    );
  }

  if($('.js_quiz').length){
    quiz();
  }

  // $(".quiz-form").submit(function (e) {
  //   e.preventDefault();
  //   var form = $(this);
  //   var data = form.serialize(); // подготавливаем данные
  //   $.ajax({
  //     // инициализируем ajax запрос
  //     type: "POST", // отправляем в POST формате, можно GET
  //     url: "send.php", // путь до обработчика, у нас он лежит в той же папке
  //     data: data,
  //     success: function (data) {
  //       // событие после удачного обращения к серверу и получения ответа
  //       document
  //         .querySelector(".quiz-thanks")
  //         .classList.add("quiz-thanks__show");
  //     },
  //   });
  //   return false;
  // });

  function viewMoreImages() {
    if (document.querySelector(".arts_more")) {
      document
        .querySelectorAll(".arts_more .arts__item")
        .forEach(function (item, i) {
          if (i >= 4) {
            item.classList.add("arts__hide");
          }
          document
            .querySelector(".arts__more")
            .addEventListener("click", () => {
              item.classList.remove("arts__hide");
              document.querySelector(".arts__more").style.display = "none";
            });
        });
    }
  }
  viewMoreImages();
});
